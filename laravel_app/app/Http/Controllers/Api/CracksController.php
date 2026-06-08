<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MLService;
use App\Models\MLPrediction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CracksController extends Controller
{

    public function __construct(
        protected MLService $mlService,
        protected \App\Services\DetectionProcessor $processor
    ) {
    }

    public function detect(Request $request)
    {
        try {
            $validated = $request->validate([
                'file' => ['required', 'file'],
                'location_text' => ['nullable', 'string'],
                'latitude' => ['nullable', 'numeric'],
                'longitude' => ['nullable', 'numeric'],
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
                ]);

                return new StreamedResponse(function() use ($file, $context) {
                    $body = $this->mlService->streamCracks($file);
                    $buffer = '';
                    while (!$body->eof()) {
                        $chunk = $body->read(1024);
                        echo $chunk;
                        $buffer .= $chunk;

                        while (($pos = strpos($buffer, "\n")) !== false) {
                            $line = substr($buffer, 0, $pos);
                            $buffer = substr($buffer, $pos + 1);
                            
                            $data = json_decode($line, true);
                            if ($data && isset($data['event']) && $data['event'] === 'frame') {
                                if (!empty($data['detections'])) {
                                    $this->processor->process('cracks', $data, $context);
                                }
                            }
                        }

                        if (ob_get_level() > 0) ob_flush();
                        flush();
                    }
                }, 200, [
                    'Content-Type' => 'application/x-ndjson',
                    'X-Accel-Buffering' => 'no',
                    'Cache-Control' => 'no-cache',
                ]);
            }

            $result = $this->mlService->detectCracks($file);
            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            // Store file
            $storedPath = null;
            if (isset($result['detection_image']) && !empty($result['detection_image'])) {
                $imageContent = base64_decode($result['detection_image']);
                $fileName = 'predictions/' . uniqid() . '.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageContent);
                $storedPath = $fileName;
            } else {
                $storedPath = $file->store('predictions', 'public');
            }

            // Use centralized processor
            $result = $this->processor->process('cracks', $result, array_merge($validated, [
                'input_type' => 'image',
                'image_path' => $storedPath,
                'execution_time' => $executionTime,
                'user_id' => $userId,
            ]));

        } catch (\Exception $e) {
            $result = ['error' => 'Request failed: ' . $e->getMessage()];
        }

        return response()->json($result);
    }
}

