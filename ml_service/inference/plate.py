from pathlib import Path
from typing import Any, Dict, List, Optional
import re
import cv2
import numpy as np
from ultralytics import YOLO

from preprocess.image_utils import load_numpy_from_bytes, numpy_to_base64

ROOT_DIR = Path(__file__).resolve().parents[1]
MODELS_DIR = ROOT_DIR / "models" / "plates"

PLATE_MODEL_PATH = MODELS_DIR / "plate_model.pt"
OCR_MODEL_PATH = MODELS_DIR / "ocr.pt"

_plate_model: YOLO | None = None
_ocr_model: YOLO | None = None


_CONFUSION_GROUPS: List[set] = [
    {'س', 'ش'},
    {'ن', 'ب', 'ت', 'ث'},
    {'ح', 'ج', 'خ'},
    {'ر', 'ز'},
    {'ط', 'ظ'},
    {'ع', 'غ'},
    {'ص', 'ض'},
    {'ف', 'ق'},
    {'ه', 'ة'},
    {'ا', 'أ', 'إ', 'آ'},
    {'د', 'ذ'},
    {'و', 'ؤ'},
    {'١', '٧'},
    {'٢', '٣'},
    {'٦', '٧'},
    {'٠', '٥'},
    {'٤', '٩'},
]

# Build O(1) lookup: char → group index
_CHAR_TO_GROUP: Dict[str, int] = {}
for _i, _grp in enumerate(_CONFUSION_GROUPS):
    for _ch in _grp:
        _CHAR_TO_GROUP[_ch] = _i


def _chars_confused(c1: str, c2: str) -> bool:
    if c1 == c2:
        return True
    g1 = _CHAR_TO_GROUP.get(c1)
    g2 = _CHAR_TO_GROUP.get(c2)
    return g1 is not None and g1 == g2


def _ocr_edit_distance(t1: str, t2: str, max_dist: int = 2) -> int:
    m, n = len(t1), len(t2)
    if abs(m - n) > max_dist:
        return -1

    # 1-D DP (standard Levenshtein with custom costs)
    dp = list(range(n + 1))
    for i in range(1, m + 1):
        prev = dp[0]
        dp[0] = i
        for j in range(1, n + 1):
            temp = dp[j]
            sub_cost = 0 if _chars_confused(t1[i - 1], t2[j - 1]) else 1
            dp[j] = min(
                prev + sub_cost,   # substitution (or 0-cost confusion match)
                dp[j] + 1,         # deletion from t1
                dp[j - 1] + 1,     # insertion into t1
            )
            prev = temp
        if min(dp) > max_dist:     # early exit
            return -1

    dist = dp[n]
    return dist if dist <= max_dist else -1


def _bbox_iou(b1: List[float], b2: List[float]) -> float:
    ix1, iy1 = max(b1[0], b2[0]), max(b1[1], b2[1])
    ix2, iy2 = min(b1[2], b2[2]), min(b1[3], b2[3])
    inter = max(0.0, ix2 - ix1) * max(0.0, iy2 - iy1)
    if inter == 0:
        return 0.0
    a1 = (b1[2] - b1[0]) * (b1[3] - b1[1])
    a2 = (b2[2] - b2[0]) * (b2[3] - b2[1])
    union = a1 + a2 - inter
    return inter / union if union > 0 else 0.0


class EgyptianPlateTracker:
    def __init__(
        self,
        min_confirmations: int = 3,
        window_size: int = 8,
        iou_threshold: float = 0.35,
        new_car_threshold: int = 3,   # edit dist > this → different car
    ):
        self.min_confirmations = min_confirmations
        self.window_size = window_size
        self.iou_threshold = iou_threshold
        self.new_car_threshold = new_car_threshold

        self._clusters: List[Dict] = []

        self._fired_texts: set = set()

    # ── Spatial helpers ───────────────────────────────────────

    def _find_cluster(self, bbox: List[float]) -> int:
        best, best_iou = -1, self.iou_threshold
        for i, c in enumerate(self._clusters):
            iou = _bbox_iou(c["bbox"], bbox)
            if iou > best_iou:
                best_iou, best = iou, i
        return best

    def _smooth_bbox(self, cluster: Dict) -> None:
        recent = [t[2] for t in cluster["texts"][-self.window_size:]]
        cluster["bbox"] = [
            sum(b[k] for b in recent) / len(recent)
            for k in range(4)
        ]

    # ── Voting ────────────────────────────────────────────────

    def _majority_winner(self, cluster: Dict) -> Optional[str]:
        texts = cluster["texts"]
        if len(texts) < self.min_confirmations:
            return None

        # tally: text → (count, best_confidence)
        tally: Dict[str, List] = {}
        for text, conf, _ in texts:
            if text not in tally:
                tally[text] = [0, 0.0]
            tally[text][0] += 1
            if conf > tally[text][1]:
                tally[text][1] = conf

        if not tally:
            return None

        # Sort by count desc, then confidence desc
        ranked = sorted(tally.items(), key=lambda kv: (kv[1][0], kv[1][1]), reverse=True)
        best_text, (best_count, _) = ranked[0]

        return best_text if best_count >= self.min_confirmations else None

    # ── New-car detection ─────────────────────────────────────

    def _is_new_car(self, incoming_text: str, winner_text: str) -> bool:
        dist = _ocr_edit_distance(incoming_text, winner_text, self.new_car_threshold)
        return dist == -1   # -1 means distance > new_car_threshold → new car

    # ── Public API ────────────────────────────────────────────

    def add_detections(self, plates: List[Dict]) -> List[str]:
        confirmed_this_frame: List[str] = []

        for plate in plates:
            text: str = plate.get("plate_text", "").strip()
            conf: float = plate.get("confidence", 0.0)
            bbox: List[float] = plate.get("bbox", [])

            if not text or not bbox or conf < 0.5:
                continue

            idx = self._find_cluster(bbox)

            if idx == -1:
                # ── No matching cluster → brand new detection ──────────
                self._clusters.append({
                    "state":  "collecting",
                    "bbox":   list(bbox),
                    "texts":  [(text, conf, bbox)],
                    "winner": None,
                })
                continue

            cluster = self._clusters[idx]

            if cluster["state"] == "confirmed":
                # ── Already confirmed → check for new car ──────────────
                if self._is_new_car(text, cluster["winner"]):
                    # Reset: a new car is at this position
                    cluster["state"]  = "collecting"
                    cluster["texts"]  = [(text, conf, bbox)]
                    cluster["winner"] = None
                    cluster["bbox"]   = list(bbox)
                # Otherwise: same car still at gate → ignore silently
                continue

            # ── Collecting state: accumulate and vote ──────────────────
            cluster["texts"].append((text, conf, bbox))
            cluster["texts"] = cluster["texts"][-self.window_size:]
            self._smooth_bbox(cluster)

            winner = self._majority_winner(cluster)
            if winner:
                # ── Global dedup: never fire the same text twice ───────
                if winner in self._fired_texts:
                    # Already charged this session — just mark confirmed
                    cluster["state"]  = "confirmed"
                    cluster["winner"] = winner
                else:
                    cluster["state"]  = "confirmed"
                    cluster["winner"] = winner
                    self._fired_texts.add(winner)
                    confirmed_this_frame.append(winner)

        # Housekeeping
        if len(self._clusters) > 40:
            self._clusters = self._clusters[-40:]

        return confirmed_this_frame



_CROP_H_MARGIN = 0.08
_CROP_V_MARGIN = 0.03


def _crop_plate(
    image: np.ndarray,
    bbox: List[float],
    other_bboxes: Optional[List[List[float]]] = None,
) -> np.ndarray:

    H, W = image.shape[:2]
    x1, y1, x2, y2 = bbox

    # Safety: ensure bbox is within image boundaries
    cx1 = int(max(0, x1))
    cy1 = int(max(0, y1))
    cx2 = int(min(W, x2))
    cy2 = int(min(H, y2))

    return image[cy1:cy2, cx1:cx2]


# ─────────────────────────────────────────────────────────────

def _load_models() -> None:
    global _plate_model, _ocr_model
    if _plate_model is None:
        _plate_model = YOLO(str(PLATE_MODEL_PATH))
    if _ocr_model is None:
        _ocr_model = YOLO(str(OCR_MODEL_PATH))


# ─────────────────────────────────────────────────────────────
#  Detection helpers (unchanged)
# ─────────────────────────────────────────────────────────────

def _run_plate_detection(image: np.ndarray) -> tuple[List[Dict[str, Any]], np.ndarray | None]:
    assert _plate_model is not None
    image_bgr = cv2.cvtColor(image, cv2.COLOR_RGB2BGR)
    results = _plate_model.predict(source=image_bgr, verbose=False, conf=0.5)
    detections: List[Dict[str, Any]] = []

    plotted_img_rgb = None
    for r in results:
        plotted_img_bgr = r.plot()
        plotted_img_rgb = cv2.cvtColor(plotted_img_bgr, cv2.COLOR_BGR2RGB)

        if r.boxes is None:
            continue
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


def _run_ocr_on_crop(image: np.ndarray) -> List[Dict[str, Any]]:
    assert _ocr_model is not None
    plate_res = cv2.resize(image, (640, 640))
    results = _ocr_model.predict(source=plate_res, verbose=False, conf=0.51)
    chars: List[Dict[str, Any]] = []

    for r in results:
        if r.boxes is None:
            continue
        names = r.names
        for box in r.boxes:
            x1, y1, x2, y2 = box.xyxy[0].tolist()
            conf = float(box.conf[0].item())
            cls_id = int(box.cls[0].item())
            label = names.get(cls_id, str(cls_id))

            chars.append({
                "bbox": [x1, y1, x2, y2],
                "confidence": conf,
                "class_id": cls_id,
                "label": label,
            })

    return chars



_RE_ARABIC_LETTER = re.compile(r'[\u0621-\u064A]')
_RE_ARABIC_DIGIT  = re.compile(r'[\u0660-\u0669]')

VALID_PLATE_FORMATS = {
    (2, 3), (2, 4),
    (3, 3), (3, 4),
}


def _is_valid_egyptian_plate(text: str) -> bool:
    if not text:
        return False
    letters = [c for c in text if _RE_ARABIC_LETTER.match(c)]
    digits  = [c for c in text if _RE_ARABIC_DIGIT.match(c)]
    # Every character must be either a letter or a digit (no junk)
    if len(letters) + len(digits) != len(text):
        return False
    return (len(letters), len(digits)) in VALID_PLATE_FORMATS


def _assemble_plate_string(chars: List[Dict[str, Any]]) -> str:
    if not chars:
        return ""

    # Calculate the max height of detected characters to filter out small noise (like 'مصر' or 'D')
    max_h = max((c["bbox"][3] - c["bbox"][1]) for c in chars)
    
    # Filter out characters that are too small (e.g., less than 40% of the max height)
    valid_chars = [c for c in chars if (c["bbox"][3] - c["bbox"][1]) > 0.4 * max_h]

    char_list = [(int(c["bbox"][0]), c["label"]) for c in valid_chars]
    char_list  = sorted(char_list, key=lambda x: x[0])

    letters = [c[1] for c in char_list if re.match(r'[\u0621-\u064A]', c[1])]
    numbers = [c[1] for c in char_list if re.match(r'[\u0660-\u0669]', c[1])]

    # Arabic letters are RTL on the plate; reverse to get logical order
    letters = letters[::-1]
    plate   = "".join(letters + numbers)

    # ── Post-processing validation ─────────────────────────────
    if not _is_valid_egyptian_plate(plate):
        return ""   # Discard hallucinated / malformed reading

    return plate


def run_inference(image_bytes: bytes, filename: str | None = None) -> Dict[str, Any]:
    _load_models()

    image_rgb = load_numpy_from_bytes(image_bytes)
    plates: List[Dict[str, Any]] = []

    plate_dets, plotted_img_rgb = _run_plate_detection(image_rgb)

    if plate_dets:
        # Step 1: remove duplicate detections of the same physical plate
        plate_dets = _deduplicate_detections(plate_dets, iou_threshold=0.50)
        
        # Pre-collect all unique bboxes so each plate knows its neighbours
        all_bboxes = [d["bbox"] for d in plate_dets]

        for det in plate_dets:
            # Pass every OTHER bbox as a neighbour so _crop_plate widens margins
            other_bboxes = [b for b in all_bboxes if b is not det["bbox"]]

            plate_crop_rgb = _crop_plate(image_rgb, det["bbox"], other_bboxes)
            chars      = _run_ocr_on_crop(plate_crop_rgb)
            plate_text = _assemble_plate_string(chars)

            if not plate_text:
                continue

            plates.append({
                "bbox":       det["bbox"],
                "confidence": det["confidence"],
                "plate_text": plate_text,
                "characters": chars,
            })

    processed_image_b64 = numpy_to_base64(plotted_img_rgb) if plotted_img_rgb is not None else None

    return {
        "model":           "plate_detection_ocr_pipeline",
        "filename":        filename,
        "num_plates":      len(plates),
        "plates":          plates,
        "processed_image": processed_image_b64,
    }


def _deduplicate_detections(dets: List[Dict], iou_threshold: float = 0.50) -> List[Dict]:
    if len(dets) <= 1:
        return dets

    # Sort by confidence descending
    dets = sorted(dets, key=lambda d: d["confidence"], reverse=True)
    keep = []
    suppressed = set()

    for i, det_i in enumerate(dets):
        if i in suppressed:
            continue
        keep.append(det_i)
        for j in range(i + 1, len(dets)):
            if j in suppressed:
                continue
            if _bbox_iou(det_i["bbox"], dets[j]["bbox"]) > iou_threshold:
                suppressed.add(j)

    return keep


def _process_frame_bgr(frame_bgr: np.ndarray, filename: str | None = None) -> Dict[str, Any]:
    image_rgb = cv2.cvtColor(frame_bgr, cv2.COLOR_BGR2RGB)
    H, W, _ = image_rgb.shape

    plates: List[Dict[str, Any]] = []
    plate_dets, plotted_img_rgb = _run_plate_detection(image_rgb)

    # Step 2: remove duplicate detections of the same physical plate
    plate_dets = _deduplicate_detections(plate_dets, iou_threshold=0.50)

    # Step 3: independent OCR for each unique plate location
    for det in plate_dets:
        x1, y1, x2, y2 = map(int, det["bbox"])
        x1 = max(0, x1);  y1 = max(0, y1)
        x2 = min(W, x2);  y2 = min(H, y2)

        plate_crop_rgb = image_rgb[y1:y2, x1:x2]
        chars      = _run_ocr_on_crop(plate_crop_rgb)
        plate_text = _assemble_plate_string(chars)

        if not plate_text:          # discard invalid / garbage readings
            continue

        plates.append({
            "bbox":       det["bbox"],
            "confidence": det["confidence"],
            "plate_text": plate_text,
            "characters": chars,
        })

    processed_image_b64 = numpy_to_base64(plotted_img_rgb) if plotted_img_rgb is not None else None

    return {
        "model":           "plate_detection_ocr_pipeline",
        "filename":        filename,
        "num_plates":      len(plates),
        "plates":          plates,
        "processed_image": processed_image_b64,
    }


def run_video_inference_stream(video_bytes: bytes, filename: str | None = None):
    import json as _json
    import tempfile
    import os

    _load_models()

    tracker = EgyptianPlateTracker(
        min_confirmations=3,   # plate must appear consistently in 3 of 8 frames
        window_size=8,
        iou_threshold=0.35,
        new_car_threshold=2,
    )

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
        frame_interval = max(1, int(round(fps / 10)))  # sample at ~10 FPS
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

                    # ── Tracker: feed ALL plates from this frame ──────────
                    newly_confirmed = tracker.add_detections(result["plates"])
                    for plate_text in newly_confirmed:
                        yield _json.dumps({
                            "event": "confirmed",
                            "plate_text": plate_text,
                            "timestamp": round(frame_count / fps, 2),
                        }) + "\n"

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