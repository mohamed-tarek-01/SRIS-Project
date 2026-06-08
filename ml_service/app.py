import base64
from fastapi import FastAPI, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import Response, StreamingResponse
from typing import Any, Dict

from inference import plate, cracks, dashboard, accident, fire_smoke, traffic, vehicles, car_damage


app = FastAPI(title="Road Inspection ML Service", version="0.1.0")


app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.get("/health")
async def health() -> Dict[str, str]:
    return {"status": "ok"}


@app.post("/plate/detect")
async def plate_detect(file: UploadFile = File(...), format: str = "json") -> Any:
    image_bytes = await file.read()
    result = plate.run_inference(image_bytes, filename=file.filename)
    if format == "image" and result.get("processed_image"):
        img_data = base64.b64decode(result["processed_image"])
        return Response(content=img_data, media_type="image/jpeg")
    return result


@app.post("/cracks/detect")
async def cracks_detect(file: UploadFile = File(...), format: str = "json") -> Any:
    file_bytes = await file.read()
    if file.filename and file.filename.lower().endswith(('.mp4', '.avi', '.webm', '.mov')):
        result = cracks.run_video_inference(file_bytes, filename=file.filename)
    else:
        result = cracks.run_inference(file_bytes, filename=file.filename)
    if format == "image":
        b64_img = result.get("detection_image")
        if b64_img:
            img_data = base64.b64decode(b64_img)
            return Response(content=img_data, media_type="image/jpeg")
    return result


@app.post("/cracks/detect/stream")
async def cracks_detect_stream(file: UploadFile = File(...)) -> StreamingResponse:
    file_bytes = await file.read()
    return StreamingResponse(
        cracks.run_video_inference_stream(file_bytes, filename=file.filename),
        media_type="application/x-ndjson"
    )


@app.post("/plate/detect/stream")
async def plate_detect_stream(file: UploadFile = File(...)) -> StreamingResponse:
    file_bytes = await file.read()
    return StreamingResponse(
        plate.run_video_inference_stream(file_bytes, filename=file.filename),
        media_type="application/x-ndjson"
    )


@app.post("/car_dashboard/detect/stream")
async def dashboard_detect_stream(file: UploadFile = File(...)) -> StreamingResponse:
    file_bytes = await file.read()
    return StreamingResponse(
        dashboard.run_video_inference_stream(file_bytes, filename=file.filename),
        media_type="application/x-ndjson"
    )


@app.post("/car_dashboard/detect")
async def dashboard_detect(file: UploadFile = File(...), format: str = "json") -> Any:
    image_bytes = await file.read()
    result = dashboard.run_inference(image_bytes, filename=file.filename)
    if format == "image" and result.get("processed_image"):
        img_data = base64.b64decode(result["processed_image"])
        return Response(content=img_data, media_type="image/jpeg")
    return result


@app.post("/accident/detect")
async def accident_detect(file: UploadFile = File(...), format: str = "json") -> Any:
    image_bytes = await file.read()
    result = accident.run_inference(image_bytes, filename=file.filename)
    if format == "image" and result.get("processed_image"):
        img_data = base64.b64decode(result["processed_image"])
        return Response(content=img_data, media_type="image/jpeg")
    return result


@app.post("/accident/detect/stream")
async def accident_detect_stream(file: UploadFile = File(...)) -> StreamingResponse:
    file_bytes = await file.read()
    return StreamingResponse(
        accident.run_video_inference_stream(file_bytes, filename=file.filename),
        media_type="application/x-ndjson"
    )


@app.post("/fire_smoke/detect")
async def fire_smoke_detect(file: UploadFile = File(...), format: str = "json") -> Any:
    image_bytes = await file.read()
    result = fire_smoke.run_inference(image_bytes, filename=file.filename)
    if format == "image" and result.get("processed_image"):
        img_data = base64.b64decode(result["processed_image"])
        return Response(content=img_data, media_type="image/jpeg")
    return result


@app.post("/fire_smoke/detect/stream")
async def fire_smoke_detect_stream(file: UploadFile = File(...)) -> StreamingResponse:
    file_bytes = await file.read()
    return StreamingResponse(
        fire_smoke.run_video_inference_stream(file_bytes, filename=file.filename),
        media_type="application/x-ndjson"
    )


@app.post("/traffic/detect")
async def traffic_detect(file: UploadFile = File(...), format: str = "json") -> Any:
    image_bytes = await file.read()
    result = traffic.run_inference(image_bytes, filename=file.filename)
    if format == "image" and result.get("processed_image"):
        img_data = base64.b64decode(result["processed_image"])
        return Response(content=img_data, media_type="image/jpeg")
    return result


@app.post("/traffic/detect/stream")
async def traffic_detect_stream(file: UploadFile = File(...)) -> StreamingResponse:
    file_bytes = await file.read()
    return StreamingResponse(
        traffic.run_video_inference_stream(file_bytes, filename=file.filename),
        media_type="application/x-ndjson"
    )


@app.post("/vehicles/detect")
async def vehicles_detect(file: UploadFile = File(...), format: str = "json") -> Any:
    image_bytes = await file.read()
    result = vehicles.run_inference(image_bytes, filename=file.filename)
    if format == "image" and result.get("processed_image"):
        img_data = base64.b64decode(result["processed_image"])
        return Response(content=img_data, media_type="image/jpeg")
    return result


@app.post("/vehicles/detect/stream")
async def vehicles_detect_stream(file: UploadFile = File(...)) -> StreamingResponse:
    file_bytes = await file.read()
    return StreamingResponse(
        vehicles.run_video_inference_stream(file_bytes, filename=file.filename),
        media_type="application/x-ndjson"
    )


@app.post("/car_damage/detect")
async def car_damage_detect(file: UploadFile = File(...), format: str = "json") -> Any:
    image_bytes = await file.read()
    result = car_damage.run_inference(image_bytes, filename=file.filename)
    if format == "image" and result.get("processed_image"):
        img_data = base64.b64decode(result["processed_image"])
        return Response(content=img_data, media_type="image/jpeg")
    return result


@app.post("/car_damage/detect/stream")
async def car_damage_detect_stream(file: UploadFile = File(...)) -> StreamingResponse:
    file_bytes = await file.read()
    return StreamingResponse(
        car_damage.run_video_inference_stream(file_bytes, filename=file.filename),
        media_type="application/x-ndjson"
    )


if __name__ == "__main__":
    import uvicorn

    uvicorn.run("app:app", host="0.0.0.0", port=8000, reload=True)

