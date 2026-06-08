<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MLPrediction;
use Illuminate\Http\Request;

class HazardController extends Controller
{
    /**
     * Get nearby road hazards based on latitude and longitude.
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'radius' => ['nullable', 'numeric', 'max:5000'], // radius in meters, max 5km
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = $request->radius ?? 1000; // default 1km

        // Haversine formula to find records within the given radius (in meters)
        // 6371000 is Earth's radius in meters
        $hazards = MLPrediction::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereIn('model_type', ['cracks', 'accident', 'fire_smoke']) // Types that are road hazards
            ->selectRaw("*, ( 6371000 * acos( cos( radians(?) ) *
                cos( radians( latitude ) )
                * cos( radians( longitude ) - radians(?)
                ) + sin( radians(?) ) *
                sin( radians( latitude ) ) )
            ) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $hazards
        ]);
    }
}
