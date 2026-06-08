<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceLog;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    // Services that require an odometer reading
    const ODO_REQUIRED_SERVICES = [
        'Oil Change', 'Tire Rotation', 'Transmission Service',
        'Spark Plugs', 'Timing Belt', 'Clutch Replacement',
        'Full Inspection', 'Wheel Alignment',
    ];

    const ALL_SERVICE_TYPES = [
        'Oil Change', 'Tire Rotation', 'Brake Inspection',
        'Air Filter Replacement', 'Transmission Service', 'Battery Check',
        'Coolant Flush', 'Spark Plugs', 'Car Wash', 'Full Inspection',
        'Wheel Alignment', 'AC Service', 'Timing Belt', 'Clutch Replacement', 'Other',
    ];

    public function index()
    {
        $vehicles = auth()->user()->vehicles;
        $maintenanceLogs = MaintenanceLog::whereIn('vehicle_id', $vehicles->pluck('id'))
            ->with('vehicle')
            ->latest('service_date')
            ->get();

        $serviceTypes   = self::ALL_SERVICE_TYPES;
        $odoRequired    = self::ODO_REQUIRED_SERVICES;

        return view('user.maintenance.index', compact('maintenanceLogs', 'vehicles', 'serviceTypes', 'odoRequired'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'   => 'required|exists:vehicles,id',
            'service_type' => 'required|in:' . implode(',', self::ALL_SERVICE_TYPES),
            'odometer'     => 'nullable|integer|min:0',
            'cost'         => 'required|numeric|min:0',
            'service_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        if ($vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        MaintenanceLog::create([
            'vehicle_id'   => $request->vehicle_id,
            'service_type' => $request->service_type,
            'odometer'     => $request->odometer ?? 0,
            'cost'         => $request->cost,
            'service_date' => $request->service_date,
            'notes'        => $request->notes,
        ]);

        // Only update odometer if service is odometer-based and value provided
        if ($request->odometer && $request->odometer > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $request->odometer]);
        }

        return redirect()->back()->with('success', 'Maintenance log added successfully!');
    }

    public function destroy(MaintenanceLog $maintenance)
    {
        if ($maintenance->vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        $maintenance->delete();
        return redirect()->back()->with('success', 'Log deleted successfully!');
    }
}
