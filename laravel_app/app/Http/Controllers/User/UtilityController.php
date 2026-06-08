<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function stations()
    {
        // Pass all fuel stations as JSON for JS proximity sorting
        $fuelStations = Station::where('type', 'fuel')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'location', 'latitude', 'longitude']);

        return view('user.utilities.stations', compact('fuelStations'));
    }

    public function fines()
    {
        $userFines = \App\Models\Fine::where('user_id', auth()->id())
            ->with('station')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.utilities.fines', compact('userFines'));
    }
}
