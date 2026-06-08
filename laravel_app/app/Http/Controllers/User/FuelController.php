<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FuelLog;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class FuelController extends Controller
{
    public function index()
    {
        $vehicles = auth()->user()->vehicles;
        $fuelLogs = FuelLog::whereIn('vehicle_id', $vehicles->pluck('id'))
            ->with('vehicle')
            ->latest('fill_date')
            ->get();
            
        return view('user.fuel.index', compact('fuelLogs', 'vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'liters' => 'required|numeric|min:0.1',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'required|integer|min:0',
            'station_name' => 'nullable|string|max:255',
            'fill_date' => 'required|date',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        if ($vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        FuelLog::create($request->all());

        // Update vehicle odometer if it's higher
        if ($request->odometer > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $request->odometer]);
        }

        return redirect()->back()->with('success', 'Fuel log added successfully!');
    }

    public function destroy(FuelLog $fuel)
    {
        if ($fuel->vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        $fuel->delete();
        return redirect()->back()->with('success', 'Log deleted successfully!');
    }
}
