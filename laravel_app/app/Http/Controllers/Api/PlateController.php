<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MLService;
use App\Models\MLPrediction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlateController extends Controller
{
    public function __construct(
        protected MLService $mlService,
        protected \App\Services\DetectionProcessor $processor
    ) {
    }

    public function detect(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file'],
            'station_id' => ['nullable', 'exists:stations,id'],
            'location_text' => ['nullable', 'string'],
        ]);

        $file = $validated['file'];
        $startTime = microtime(true);
        $userId = auth()->id();

        $is_video = str_starts_with($file->getMimeType(), 'video/') ||
            in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'avi', 'webm', 'mov']);

        if ($is_video) {
            $context = array_merge($validated, [
                'input_type' => 'video',
                'user_id' => $userId,
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
            ]);

            return new StreamedResponse(function () use ($file, $context) {
                $body = $this->mlService->streamPlate($file);
                $buffer = '';
                while (!$body->eof()) {
                    $chunk = $body->read(1024);
                    echo $chunk;
                    $buffer .= $chunk;

                    // Parse NDJSON lines from buffer
                    while (($pos = strpos($buffer, "\n")) !== false) {
                        $line = substr($buffer, 0, $pos);
                        $buffer = substr($buffer, $pos + 1);

                        $data = json_decode($line, true);
                        if ($data && isset($data['event']) && $data['event'] === 'confirmed') {
                            // Side effects for confirmed plates in video
                            $this->processor->process('plate', $data, $context);
                        }
                    }

                    if (ob_get_level() > 0)
                        ob_flush();
                    flush();
                }
            }, 200, [
                'Content-Type' => 'application/x-ndjson',
                'X-Accel-Buffering' => 'no',
                'Cache-Control' => 'no-cache',
            ]);
        }

        $result = $this->mlService->detectPlate($file);
        $executionTime = (int) ((microtime(true) - $startTime) * 1000);

        // Store file
        $storedPath = null;
        if (isset($result['processed_image']) && !empty($result['processed_image'])) {
            $imageContent = base64_decode($result['processed_image']);
            $fileName = 'predictions/' . uniqid() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageContent);
            $storedPath = $fileName;
        } else {
            $storedPath = $file->store('predictions', 'public');
        }

        // Use centralized processor for side effects
        $result = $this->processor->process('plate', $result, array_merge($validated, [
            'input_type' => 'image',
            'image_path' => $storedPath,
            'execution_time' => $executionTime,
            'user_id' => $userId,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ]));

        return response()->json($result);
    }
}

