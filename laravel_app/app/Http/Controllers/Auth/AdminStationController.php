<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;

class AdminStationController extends Controller
{
    public function index()
    {
        $stations = Station::latest()->paginate(10);
        return view('admin.stations.index', compact('stations'));
    }

    public function create()
    {
        return view('admin.stations.upsert');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255', 'unique:stations,name'],
            'location'  => ['nullable', 'string', 'max:255'],
            'price'     => ['required', 'integer', 'min:0'],
            'latitude'  => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        Station::create($validated);

        return redirect()->route('admin.stations.index')->with('success', 'Station created successfully.');
    }

    public function edit(Station $station)
    {
        return view('admin.stations.upsert', compact('station'));
    }

    public function update(Request $request, Station $station)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255', 'unique:stations,name,' . $station->id],
            'location' => ['nullable', 'string', 'max:255'],
            'price'    => ['required', 'integer', 'min:0'],
        ]);

        // Coordinates are locked — do NOT update them
        $station->name     = $validated['name'];
        $station->location = $validated['location'] ?? null;
        $station->price    = $validated['price'];
        $station->save();

        return redirect()->route('admin.stations.index')->with('success', 'Station updated successfully.');
    }

    public function destroy(Station $station)
    {
        $station->delete();
        return redirect()->route('admin.stations.index')->with('success', 'Station deleted successfully.');
    }
}
