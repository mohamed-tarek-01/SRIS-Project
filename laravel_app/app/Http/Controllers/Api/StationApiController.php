<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;

class StationApiController extends Controller
{
    /**
     * Check if a station exists near the given coordinates.
     */
    public function checkLocation(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = 100; // 100 meters radius for a toll gate

        // Haversine formula to find stations within the radius
        $station = Station::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw("*, ( 6371000 * acos( cos( radians(?) ) *
                cos( radians( latitude ) )
                * cos( radians( longitude ) - radians(?)
                ) + sin( radians(?) ) *
                sin( radians( latitude ) ) )
            ) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $station // will be null if not found
        ]);
    }

    /**
     * Dynamically create a new station from the plate scanner page.
     */
    public function createDynamic(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $station = Station::create([
            'name' => $request->name,
            'location' => $request->location,
            'price' => $request->price,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'data' => $station,
            'message' => 'Toll Gate created successfully at this location.'
        ]);
    }
}
