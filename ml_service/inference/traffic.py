from pathlib import Path
from typing import Any, Dict, List
import cv2
import numpy as np
from ultralytics import YOLO
import easyocr
from preprocess.image_utils import load_numpy_from_bytes, numpy_to_base64

TRAFFIC_MODEL_PATH = Path("/app/models/traffic_signs/ts_model.pt")
OCR_MODEL_PATH = Path("/app/models/easy_ocr")

_traffic_model: YOLO | None = None
_reader = easyocr.Reader(['en'], gpu=False, model_storage_directory=str(OCR_MODEL_PATH))

def read_speed_limit(crop):
    VALID_LIMITS = {"20", "30", "50"}
    CONFUSION_MAP = {
        "80": "30", "28": "20", "29": "20",
        "59": "50", "56": "50", "8": "30",
        "3": "30", "2": "20", "5": "50",
    }
    crop_resized = cv2.resize(crop, (128, 128))
    gray = cv2.cvtColor(crop_resized, cv2.COLOR_BGR2GRAY)
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    enhanced = clahe.apply(gray)
    
    strategies = [gray, enhanced]
    all_results = []
    for version in strategies:
        results = _reader.readtext(version, allowlist='0123456789', detail=1)
        for (_, text, confidence) in results:
            cleaned = text.strip().replace(" ", "")
            if cleaned in VALID_LIMITS and confidence > 0.3:
                return cleaned
            all_results.append((cleaned, confidence))
    for (cleaned, confidence) in all_results:
        if cleaned in CONFUSION_MAP and confidence > 0.3:
            return CONFUSION_MAP[cleaned]
    return None

def _load_model() -> None:
    global _traffic_model
    if _traffic_model is None and TRAFFIC_MODEL_PATH.exists():
        _traffic_model = YOLO(str(TRAFFIC_MODEL_PATH))

def process_detection(frame_bgr: np.ndarray):
    _load_model()
    if _traffic_model is None: return [], frame_bgr
    results = _traffic_model.predict(source=frame_bgr, verbose=False, imgsz=640)
    plotted_img = frame_bgr.copy()
    h, w = frame_bgr.shape[:2]
    detections = []
    for r in results:
        if r.boxes is not None:
            for box in r.boxes:
                x1, y1, x2, y2 = map(int, box.xyxy[0])
                conf = float(box.conf[0].item())
                cls_id = int(box.cls[0].item())
                label_name = r.names.get(cls_id, str(cls_id))
                limit = None
                if label_name == "speed_limit":
                    pad = 5
                    crop = frame_bgr[max(0, y1-pad):min(h, y2+pad), max(0, x1-pad):min(w, x2+pad)]
                    limit = read_speed_limit(crop)
                    label = f"speed_limit: {limit} km/h" if limit else "speed_limit: ?"
                    color = (0, 200, 0) if limit else (0, 165, 255)
                else:
                    label = f"{label_name} {conf:.2f}"
                    color = (255, 100, 0)
                cv2.rectangle(plotted_img, (x1, y1), (x2, y2), color, 2)
                (tw, th), _ = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2)
                cv2.rectangle(plotted_img, (x1, y1 - th - 8), (x1 + tw + 4, y1), color, -1)
                cv2.putText(plotted_img, label, (x1 + 2, y1 - 4), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)
                detections.append({"bbox": [x1, y1, x2, y2], "confidence": conf, "label": label_name, "speed_limit": limit})
    return detections, plotted_img

def run_inference(image_bytes: bytes, filename: str | None = None) -> Dict[str, Any]:
    image = load_numpy_from_bytes(image_bytes)
    image_bgr = cv2.cvtColor(image, cv2.COLOR_RGB2BGR)
    detections, plotted_img = process_detection(image_bgr)
    return {
        "model": "traffic_detection",
        "detections": detections,
        "processed_image": numpy_to_base64(cv2.cvtColor(plotted_img, cv2.COLOR_BGR2RGB)),
    }

def _process_frame_bgr(frame_bgr: np.ndarray, filename: str | None = None) -> Dict[str, Any]:
    detections, plotted_img = process_detection(frame_bgr)
    return {
        "model": "traffic_detection",
        "detections": detections,
        "processed_image": numpy_to_base64(cv2.cvtColor(plotted_img, cv2.COLOR_BGR2RGB)),
    }

def run_video_inference_stream(video_bytes: bytes, filename: str | None = None):
    import json as _json, tempfile, os
    with tempfile.NamedTemporaryFile(suffix=".mp4", delete=False) as f:
        f.write(video_bytes); in_path = f.name
    cap = cv2.VideoCapture(in_path)
    try:
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
        frame_count = 0; processed = 0
        while processed < max_frames:
            ret, frame = cap.read()
            if not ret: break
            if frame_count % frame_interval == 0:
                res = _process_frame_bgr(frame)
                res.update({"event": "frame", "frame_index": processed, "timestamp": round(frame_count/fps, 2)})
                yield _json.dumps(res) + "\n"; processed += 1
            frame_count += 1
    finally:
        cap.release(); os.remove(in_path)