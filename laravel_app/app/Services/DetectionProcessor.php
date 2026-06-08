<?php

namespace App\Services;

use App\Models\MLPrediction;
use App\Models\SystemAlert;
use App\Models\Station;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DetectionProcessor
{
    /**
     * Process a single detection result (from image or video frame/event).
     */
    public function process(string $modelType, array $result, array $context = [])
    {
        $stationId = $context['station_id'] ?? null;
        $locationText = $context['location_text'] ?? 'Unknown Location';
        $inputType = $context['input_type'] ?? 'image';
        $imagePath = $context['image_path'] ?? null;
        $executionTime = $context['execution_time'] ?? null;
        $userId = $context['user_id'] ?? Auth::id();

        // 1. Specific logic by model type
        switch ($modelType) {
            case 'plate':
                $this->handlePlateDetection($result, $stationId, $locationText);
                break;
            case 'accident':
                $this->handleAccidentDetection($result, $locationText, $context);
                break;
            case 'fire_smoke':
                $this->handleFireSmokeDetection($result, $locationText, $context);
                break;
            // Add other models as needed
        }

        // 2. Log to MLPrediction if user is authenticated
        if ($userId) {
            $this->logPrediction($userId, $modelType, $result, $inputType, $imagePath, $executionTime, $context);
        }

        return $result;
    }

    protected function handlePlateDetection(array &$result, $stationId, $locationText)
    {
        $plates = [];
        if (isset($result['plates'])) {
            $plates = $result['plates'];
        } elseif (isset($result['plate_text'])) {
            // Video "confirmed" event might just have plate_text
            $plates = [['plate_text' => $result['plate_text']]];
        }

        if (empty($plates))
            return;

        $result['toll_status'] = [];
        $tollService = app(TollService::class);

        foreach ($plates as $plate) {
            $plateText = $plate['plate_text'] ?? '';
            if (empty($plateText))
                continue;

            $tollResult = $tollService->processToll($plateText, $stationId);

            $result['toll_status'][] = [
                'plate_text' => $plateText,
                'status' => $tollResult
            ];

            if ($tollResult['status'] === 'unregistered') {
                $station = $stationId ? Station::find($stationId) : null;
                SystemAlert::create([
                    'type' => 'Fake Plate',
                    'location_text' => $station ? $station->name : $locationText,
                    'details' => [
                        'plate_text' => $plateText,
                        'full_result' => $result,
                        'latitude' => $station ? $station->latitude : ($context['latitude'] ?? null),
                        'longitude' => $station ? $station->longitude : ($context['longitude'] ?? null),
                    ],
                ]);
                $result['alert_triggered'] = true;
            }
        }
    }

    protected function handleAccidentDetection(array $result, $locationText, $context)
    {
        // Accident model uses 'Moderate' and 'Severe' as class labels
        $accidentLabels = ['moderate', 'severe'];
        $accidentDetected = false;
        $severity = null;

        if (isset($result['detections'])) {
            foreach ($result['detections'] as $det) {
                $label = strtolower($det['label'] ?? '');
                if (in_array($label, $accidentLabels) && ($det['confidence'] ?? 0) > 0.4) {
                    $accidentDetected = true;
                    $severity = ucfirst($label); // 'Moderate' or 'Severe'
                    break;
                }
            }
        }

        if ($accidentDetected) {
            // Duplicate Prevention (2 hours)
            $recentAlert = SystemAlert::where('type', 'Accident')
                ->where('location_text', $locationText)
                ->where('created_at', '>=', now()->subHours(2))
                ->first();

            if (!$recentAlert) {
                SystemAlert::create([
                    'type' => 'Accident',
                    'location_text' => $locationText,
                    'details' => array_merge($result, [
                        'latitude'  => $context['latitude'] ?? null,
                        'longitude' => $context['longitude'] ?? null,
                        'severity'  => $severity,
                    ]),
                ]);
            }
        }
    }

    protected function handleFireSmokeDetection(array $result, $locationText, $context)
    {
        $fireDetected = false;
        $smokeDetected = false;

        if (isset($result['detections'])) {
            foreach ($result['detections'] as $det) {
                $label = strtolower($det['label'] ?? '');
                if (str_contains($label, 'fire'))
                    $fireDetected = true;
                if (str_contains($label, 'smoke'))
                    $smokeDetected = true;
            }
        }

        if ($fireDetected || $smokeDetected) {
            $type = $fireDetected ? 'Fire' : 'Smoke';

            $recentAlert = SystemAlert::where('type', $type)
                ->where('location_text', $locationText)
                ->where('created_at', '>=', now()->subHours(2))
                ->first();

            if (!$recentAlert) {
                SystemAlert::create([
                    'type' => $type,
                    'location_text' => $locationText,
                    'details' => array_merge($result, [
                        'latitude' => $context['latitude'] ?? null,
                        'longitude' => $context['longitude'] ?? null,
                    ]),
                ]);
            }
        }
    }

    protected function logPrediction($userId, $modelType, $result, $inputType, $imagePath, $executionTime, $context)
    {
        // For video streams, we might not want to log EVERY frame, 
        // but we definitely want to log the events.

        $confidence = null;
        if (isset($result['confidence'])) {
            $confidence = $result['confidence'] * 100;
        } elseif (isset($result['detections']) && count($result['detections']) > 0) {
            $totalConf = 0;
            foreach ($result['detections'] as $det) {
                $totalConf += ($det['confidence'] ?? 0);
            }
            $confidence = ($totalConf / count($result['detections'])) * 100;
        }

        MLPrediction::create([
            'user_id' => $userId,
            'model_type' => $modelType,
            'image_path' => $imagePath,
            'prediction_result' => $result,
            'confidence_score' => $confidence,
            'execution_time_ms' => $executionTime,
            'input_type' => $inputType,
            'station_id' => $context['station_id'] ?? null,
            'location_text' => $context['location_text'] ?? null,
            'latitude' => $context['latitude'] ?? null,
            'longitude' => $context['longitude'] ?? null,
        ]);
    }
}
