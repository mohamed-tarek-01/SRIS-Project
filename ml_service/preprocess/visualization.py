import io
from typing import Optional

import cv2
import numpy as np
from PIL import Image, ImageDraw, ImageFont


def numpy_to_pil(image_np: np.ndarray) -> Image.Image:
    """Convert NumPy array (RGB) to PIL Image."""
    if image_np.dtype == np.uint8:
        return Image.fromarray(image_np)
    else:
        image_uint8 = (image_np * 255).astype(np.uint8)
        return Image.fromarray(image_uint8)


def pil_to_numpy(image_pil: Image.Image) -> np.ndarray:
    """Convert PIL Image to NumPy array (RGB)."""
    return np.array(image_pil)


def draw_text_on_image(
    image_pil: Image.Image,
    texts: list[str],
    position: tuple[int, int] = (10, 10),
    font_size: int = 20,
    text_color: tuple[int, int, int] = (255, 0, 0),
    bg_color: Optional[tuple[int, int, int]] = None,
) -> Image.Image:
    """Draw text on a PIL image."""
    draw = ImageDraw.Draw(image_pil)
    y_offset = position[1]
    
    try:
        font = ImageFont.load_default()
    except Exception:
        font = None
    
    for text in texts:
        if bg_color:
            bbox = draw.textbbox((position[0], y_offset), text, font=font)
            draw.rectangle(bbox, fill=bg_color)
        
        draw.text((position[0], y_offset), text, fill=text_color, font=font)
        y_offset += font_size + 5
    
    return image_pil


def image_to_bytes(image: Image.Image, format: str = "PNG") -> bytes:
    """Convert PIL Image to bytes."""
    buffer = io.BytesIO()
    image.save(buffer, format=format)
    buffer.seek(0)
    return buffer.getvalue()

def label_to_color(mask: np.ndarray) -> np.ndarray:
    color_map = {
        0: [0, 0, 0],      # Background: Black
        1: [255, 255, 0],  # Lane: Yellow
        2: [255, 0, 0],    # Crack: Red
        3: [0, 0, 255]     # Pothole: Blue
    }
    h, w = mask.shape
    color_mask = np.zeros((h, w, 3), dtype=np.uint8)
    for k, v in color_map.items():
        color_mask[mask == k] = v
    return color_mask
