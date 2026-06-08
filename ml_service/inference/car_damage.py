from pathlib import Path
from typing import Any, Dict, List

import cv2
import numpy as np
import torch
import segmentation_models_pytorch as smp
import albumentations as A
from albumentations.pytorch import ToTensorV2

from preprocess.image_utils import load_numpy_from_bytes, numpy_to_base64


ROOT_DIR = Path(__file__).resolve().parents[1]
CAR_DAMAGE_MODEL_PATH = ROOT_DIR / "models" / "car_damage" / "car_damage_model.pth"

_car_damage_model: torch.nn.Module | None = None
_device = torch.device("cuda" if torch.cuda.is_available() else "cpu")

IMAGE_SIZE = 512
_transform = A.Compose([
    A.Resize(IMAGE_SIZE, IMAGE_SIZE),
    A.Normalize(
        mean=(0.485, 0.456, 0.406),
        std=(0.229, 0.224, 0.225)
    ),
    ToTensorV2()
])


def _load_model() -> None:
    global _car_damage_model
    if _car_damage_model is None and CAR_DAMAGE_MODEL_PATH.exists():
        _car_damage_model = smp.Unet(
            encoder_name="efficientnet-b0",
            encoder_weights=None,
            in_channels=3,
            classes=1,
            activation=None
        )
        _car_damage_model.load_state_dict(torch.load(str(CAR_DAMAGE_MODEL_PATH), map_location=_device))
        _car_damage_model.to(_device)
        _car_damage_model.eval()


def run_inference(image_bytes: bytes, filename: str | None = None) -> Dict[str, Any]:
    """
    Run car damage segmentation using the trained U-Net EfficientNet-B0 model.
    """
    _load_model()

    if _car_damage_model is None:
        return {
            "model": "car_damage_segmentation",
            "filename": filename,
            "message": "car_damage_model.pth file not found on disk.",
        }

    image_rgb = load_numpy_from_bytes(image_bytes)
    orig_h, orig_w = image_rgb.shape[:2]

    augmented = _transform(image=image_rgb)
    input_tensor = augmented["image"].unsqueeze(0).to(_device)

    with torch.no_grad():
        output = _car_damage_model(input_tensor)
        pred_mask = torch.sigmoid(output).cpu()[0, 0].numpy()
        pred_mask = (pred_mask > 0.5).astype(np.uint8)

    # Resize mask back to original size
    pred_mask_resized = cv2.resize(pred_mask, (orig_w, orig_h), interpolation=cv2.INTER_NEAREST)

    # Create visualization
    # Highlight damage in red
    blended = image_rgb.copy()
    mask_indices = pred_mask_resized > 0
    
    # Create a red overlay
    overlay = np.zeros_like(image_rgb)
    overlay[mask_indices] = [255, 0, 0]
    
    blended[mask_indices] = cv2.addWeighted(image_rgb, 0.7, overlay, 0.3, 0)[mask_indices]

    damage_pixel_count = int(pred_mask_resized.sum())
    total_pixels = int(pred_mask_resized.size)
    damage_ratio = float(damage_pixel_count) / total_pixels

    return {
        "model": "car_damage_segmentation",
        "filename": filename,
        "damage_ratio": damage_ratio,
        "damage_pixel_count": damage_pixel_count,
        "processed_image": numpy_to_base64(blended),
    }


def _process_frame_bgr(frame_bgr: np.ndarray, filename: str | None = None) -> Dict[str, Any]:
    """Process a single BGR frame using numpy arrays directly."""
    _load_model()
    if _car_damage_model is None:
        return {"error": "Model not loaded"}

    image_rgb = cv2.cvtColor(frame_bgr, cv2.COLOR_BGR2RGB)
    orig_h, orig_w = image_rgb.shape[:2]

    augmented = _transform(image=image_rgb)
    input_tensor = augmented["image"].unsqueeze(0).to(_device)

    with torch.no_grad():
        output = _car_damage_model(input_tensor)
        pred_mask = torch.sigmoid(output).cpu()[0, 0].numpy()
        pred_mask = (pred_mask > 0.5).astype(np.uint8)

    pred_mask_resized = cv2.resize(pred_mask, (orig_w, orig_h), interpolation=cv2.INTER_NEAREST)

    blended = image_rgb.copy()
    mask_indices = pred_mask_resized > 0
    overlay = np.zeros_like(image_rgb)
    overlay[mask_indices] = [255, 0, 0]
    blended[mask_indices] = cv2.addWeighted(image_rgb, 0.7, overlay, 0.3, 0)[mask_indices]

    damage_pixel_count = int(pred_mask_resized.sum())
    damage_ratio = float(damage_pixel_count) / pred_mask_resized.size

    return {
        "model": "car_damage_segmentation",
        "filename": filename,
        "damage_ratio": damage_ratio,
        "processed_image": numpy_to_base64(blended),
    }


def run_video_inference_stream(video_bytes: bytes, filename: str | None = None):
    import json as _json
    import tempfile
    import os

    _load_model()

    with tempfile.NamedTemporaryFile(suffix=".mp4", delete=False) as f_in:
        f_in.write(video_bytes)
        in_path = f_in.name

    try:
        cap = cv2.VideoCapture(in_path)
        if not cap.isOpened():
            yield _json.dumps({"event": "error", "error": "Could not open video file."}) + "\n"
            return

        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        fps = cap.get(cv2.CAP_PROP_FPS) or 30.0
        # Ultra-Real-Time Sampling (10 FPS)
        frame_interval = max(1, int(round(fps / 10))) 
        max_frames = 300 

        yield _json.dumps({
            "event": "start",
            "total_frames": total_frames,
            "fps": round(fps, 2),
            "frame_interval": frame_interval,
            "max_frames": max_frames,
        }) + "\n"

        frame_count = 0
        processed = 0

        while processed < max_frames:
            ret, frame = cap.read()
            if not ret or frame is None:
                break

            if frame_count % frame_interval == 0:
                try:
                    result = _process_frame_bgr(frame, filename=filename)
                    result["event"] = "frame"
                    result["frame_index"] = processed
                    result["timestamp"] = round(frame_count / fps, 2)
                    yield _json.dumps(result) + "\n"
                    processed += 1
                except Exception:
                    pass

            frame_count += 1

        cap.release()
        yield _json.dumps({"event": "done", "total_processed": processed}) + "\n"

    except Exception as e:
        yield _json.dumps({"event": "error", "error": str(e)}) + "\n"
    finally:
        try:
            os.remove(in_path)
        except Exception:
            pass
