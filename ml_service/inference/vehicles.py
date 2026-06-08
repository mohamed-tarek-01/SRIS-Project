from pathlib import Path
from typing import Any, Dict, List

import cv2
import numpy as np
from ultralytics import YOLO

from preprocess.image_utils import load_numpy_from_bytes, numpy_to_base64


ROOT_DIR = Path(__file__).resolve().parents[1]
VEHICLES_MODEL_PATH = ROOT_DIR / "models" / "vehicles" / "vehicles_model.pt"

_vehicles_model: YOLO | None = None


def _load_model() -> None:
    global _vehicles_model
    if _vehicles_model is None and VEHICLES_MODEL_PATH.exists():
        _vehicles_model = YOLO(str(VEHICLES_MODEL_PATH))


def run_inference(image_bytes: bytes, filename: str | None = None) -> Dict[str, Any]:
    """
    Run vehicle detection using the trained YOLOv11 model.
    """
    _load_model()

    if _vehicles_model is None:
        return {
            "model": "vehicles_detection",
            "filename": filename,
            "detections": [],
            "message": "vehicles_model.pt file not found on disk.",
        }

    image = load_numpy_from_bytes(image_bytes)
    image_bgr = cv2.cvtColor(image, cv2.COLOR_RGB2BGR)

    results = _vehicles_model.predict(source=image_bgr, verbose=False, imgsz=640)
    detections: List[Dict[str, Any]] = []

    processed_image_b64 = None
    for r in results:
        plotted_img_bgr = r.plot()
        plotted_img_rgb = cv2.cvtColor(plotted_img_bgr, cv2.COLOR_BGR2RGB)
        processed_image_b64 = numpy_to_base64(plotted_img_rgb)

        names = r.names
        if r.boxes is not None:
            for box in r.boxes:
                x1, y1, x2, y2 = box.xyxy[0].tolist()
                conf = float(box.conf[0].item())
                cls_id = int(box.cls[0].item())
                label = names.get(cls_id, str(cls_id)) if isinstance(names, dict) else str(cls_id)

                detections.append(
                    {
                        "bbox": [x1, y1, x2, y2],
                        "confidence": conf,
                        "class_id": cls_id,
                        "label": label,
                    }
                )

    return {
        "model": "vehicles_detection",
        "filename": filename,
        "detections": detections,
        "processed_image": processed_image_b64,
    }


def _process_frame_bgr(frame_bgr: np.ndarray, filename: str | None = None) -> Dict[str, Any]:
    """Process a single BGR frame using numpy arrays directly (no disk I/O)."""
    _load_model()
    if _vehicles_model is None:
        return {"error": "Model not loaded"}

    results = _vehicles_model.predict(source=frame_bgr, verbose=False, imgsz=640)
    detections: List[Dict[str, Any]] = []

    processed_image_b64 = None
    for r in results:
        plotted_img_bgr = r.plot()
        plotted_img_rgb = cv2.cvtColor(plotted_img_bgr, cv2.COLOR_BGR2RGB)
        processed_image_b64 = numpy_to_base64(plotted_img_rgb)

        names = r.names
        if r.boxes is not None:
            for box in r.boxes:
                x1, y1, x2, y2 = box.xyxy[0].tolist()
                conf = float(box.conf[0].item())
                cls_id = int(box.cls[0].item())
                label = names.get(cls_id, str(cls_id)) if isinstance(names, dict) else str(cls_id)
                detections.append({
                    "bbox": [x1, y1, x2, y2],
                    "confidence": conf,
                    "class_id": cls_id,
                    "label": label,
                })

    return {
        "model": "vehicles_detection",
        "filename": filename,
        "detections": detections,
        "processed_image": processed_image_b64,
    }


def run_video_inference_stream(video_bytes: bytes, filename: str | None = None):
    """
    Generator that yields NDJSON strings (one JSON object per line).
    Samples 1 frame per second from the video.
    """
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
        # Increase sampling to 8-10 FPS for a smoother "walkthrough" feel
        frame_interval = max(1, int(round(fps / 8))) 
        max_frames = 300 # Increase to allow longer real-time walkthroughs

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
