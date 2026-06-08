import base64
from io import BytesIO
from typing import Tuple

import cv2
import numpy as np
from PIL import Image, ImageOps


def load_image_from_bytes(image_bytes: bytes) -> Image.Image:
    """Convert raw bytes to a PIL image in RGB mode."""
    img = Image.open(BytesIO(image_bytes))
    img = ImageOps.exif_transpose(img)
    return img.convert("RGB")


def image_to_numpy(image: Image.Image) -> np.ndarray:
    """Convert PIL image to NumPy array (H, W, C) in uint8."""
    return np.array(image)


def load_numpy_from_bytes(image_bytes: bytes) -> np.ndarray:
    """Shortcut: bytes -> RGB PIL -> NumPy array."""
    return image_to_numpy(load_image_from_bytes(image_bytes))


def get_image_size(image: Image.Image) -> Tuple[int, int]:
    """Return (width, height) of image."""
    return image.size


def numpy_to_base64(image_np: np.ndarray, format: str = ".jpg") -> str:
    """Convert a NumPy image array to a base64 string."""
    # Convert RGB (from PIL) to BGR for OpenCV
    if image_np.ndim == 3 and image_np.shape[2] == 3:
        image_bgr = cv2.cvtColor(image_np, cv2.COLOR_RGB2BGR)
    else:
        image_bgr = image_np

    _, buffer = cv2.imencode(format, image_bgr)
    return base64.b64encode(buffer).decode("utf-8")

