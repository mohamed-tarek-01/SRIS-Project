from pathlib import Path
from typing import Any, Dict, List

import os
import tempfile
import cv2
import numpy as np
import base64
from ultralytics import YOLO

from preprocess.image_utils import load_numpy_from_bytes, numpy_to_base64
from preprocess.visualization import numpy_to_pil, image_to_bytes

ROOT_DIR = Path(__file__).resolve().parents[1]
CRACKS_DIR = ROOT_DIR / "models" / "cracks"

CRACKS_YOLO_PATH = CRACKS_DIR / "cracks_model.pt"

_cracks_model: YOLO | None = None

def _load_models() -> None:
    global _cracks_model

    if _cracks_model is None and CRACKS_YOLO_PATH.exists():
        _cracks_model = YOLO(str(CRACKS_YOLO_PATH))

def _run_cracks_detection(image_source: str | np.ndarray) -> tuple[List[Dict[str, Any]], np.ndarray | None]:
    if _cracks_model is None:
        return [], None

    results = _cracks_model.predict(source=image_source, verbose=False, imgsz=800, conf=0.1)
    detections: List[Dict[str, Any]] = []

    plotted_img_rgb = None
    for r in results:
        plotted_img_bgr = r.plot()
        plotted_img_rgb = cv2.cvtColor(plotted_img_bgr, cv2.COLOR_BGR2RGB)

        if r.boxes is not None:
            names = r.names
            for box in r.boxes:
                x1, y1, x2, y2 = box.xyxy[0].tolist()
                conf = float(box.conf[0].item())
                cls_id = int(box.cls[0].item())
                label = names.get(cls_id, str(cls_id))
                detections.append({
                    "bbox": [x1, y1, x2, y2],
                    "confidence": conf,
                    "class_id": cls_id,
                    "label": label,
                })

    return detections, plotted_img_rgb

def run_inference(image_bytes: bytes, filename: str | None = None) -> Dict[str, Any]:
    _load_models()

    with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as f:
        f.write(image_bytes)
        tmp_path = f.name

    try:
        image_bgr = cv2.imread(tmp_path)
        if image_bgr is None:
            image_bgr = cv2.cvtColor(load_numpy_from_bytes(image_bytes), cv2.COLOR_RGB2BGR)

        detections, plotted_img_rgb = _run_cracks_detection(tmp_path)
    finally:
        if os.path.exists(tmp_path):
            os.remove(tmp_path)

    result = {
        "model": "cracks_potholes_detection",
        "filename": filename,
        "num_detections": len(detections),
        "detections": detections,
        "detection_image": numpy_to_base64(plotted_img_rgb) if plotted_img_rgb is not None else None,
    }

    return result

def run_video_inference(video_bytes: bytes, filename: str | None = None) -> Dict[str, Any]:
    _load_models()

    with tempfile.NamedTemporaryFile(suffix=".mp4", delete=False) as f_in:
        f_in.write(video_bytes)
        in_path = f_in.name

    try:
        cap = cv2.VideoCapture(in_path)
        if not cap.isOpened():
            return {"error": "Could not open video file. The format may not be supported."}

        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))

        mid_frame = max(0, total_frames // 2) if total_frames > 0 else 0
        cap.set(cv2.CAP_PROP_POS_FRAMES, mid_frame)
        ret, frame = cap.read()
        cap.release()

        if not ret or frame is None:
            cap2 = cv2.VideoCapture(in_path)
            ret, frame = cap2.read()
            cap2.release()
            if not ret or frame is None:
                return {"error": "Could not extract any frame from the video. The codec may not be supported."}

        ok, buf = cv2.imencode('.jpg', frame)
        if not ok:
            return {"error": "Could not encode video frame as JPEG."}

        frame_bytes = buf.tobytes()
        result = run_inference(frame_bytes, filename=filename)

        result["source"] = "video_frame"
        result["total_frames"] = total_frames
        return result

    except Exception as e:
        import traceback
        return {"error": f"Video processing error: {str(e)}", "detail": traceback.format_exc()}
    finally:
        try:
            os.remove(in_path)
        except Exception:
            pass


def _process_frame_bgr(frame_bgr: np.ndarray, filename: str | None = None) -> Dict[str, Any]:
    """Process a single BGR frame using numpy arrays directly (no disk I/O)."""

    detections, plotted_img_rgb = _run_cracks_detection(frame_bgr)
    if plotted_img_rgb is None:
        plotted_img_rgb = cv2.cvtColor(frame_bgr, cv2.COLOR_BGR2RGB)

    result: Dict[str, Any] = {
        "model": "cracks_potholes_video",
        "filename": filename,
        "num_detections": len(detections),
        "detections": detections,
        "detection_image": numpy_to_base64(plotted_img_rgb),
    }

    return result


def run_video_inference_stream(video_bytes: bytes, filename: str | None = None):
    """
    Generator that yields NDJSON strings (one JSON object per line).
    Samples 1 frame per second from the video (max 60 frames).
    Each yielded line is a complete JSON object followed by a newline.
    """
    import json as _json

    _load_models()

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
                except Exception as e:
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


def get_detection_image(image_bytes: bytes) -> bytes:
    _load_models()
    with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as f:
        f.write(image_bytes)
        tmp_path = f.name
    try:
        detections, plotted_img_rgb = _run_cracks_detection(tmp_path)
        if plotted_img_rgb is None:
            image_bgr = cv2.imread(tmp_path)
            if image_bgr is not None:
                plotted_img_rgb = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2RGB)
            else:
                plotted_img_rgb = load_numpy_from_bytes(image_bytes)
        return image_to_bytes(numpy_to_pil(plotted_img_rgb), format="PNG")
    finally:
        if os.path.exists(tmp_path):
            os.remove(tmp_path)