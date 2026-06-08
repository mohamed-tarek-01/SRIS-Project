<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FuelLog;
use App\Models\MaintenanceLog;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $vehicles = $user->vehicles;
        $vehicleIds = $vehicles->pluck('id');

        // 1. Total Expenses (Fuel + Maintenance)
        $fuelTotal = FuelLog::whereIn('vehicle_id', $vehicleIds)->sum('cost');
        $maintenanceTotal = MaintenanceLog::whereIn('vehicle_id', $vehicleIds)->sum('cost');
        $totalExpenses = $fuelTotal + $maintenanceTotal;

        // 2. Fuel Efficiency Insights (km per liter)
        $efficiencyData = [];
        foreach ($vehicles as $vehicle) {
            $logs = $vehicle->fuelLogs()->orderBy('odometer')->get();
            if ($logs->count() >= 2) {
                $totalKm = $logs->last()->odometer - $logs->first()->odometer;
                $totalLiters = $logs->sum('liters') - $logs->first()->liters; 
                $efficiency = $totalLiters > 0 ? $totalKm / $totalLiters : 0;
                $efficiencyData[$vehicle->id] = round($efficiency, 2);
            } else {
                $efficiencyData[$vehicle->id] = 0;
            }
        }

        return view('user.analytics.index', compact(
            'fuelTotal', 
            'maintenanceTotal', 
            'totalExpenses', 
            'efficiencyData',
            'vehicles'
        ));
    }
}
