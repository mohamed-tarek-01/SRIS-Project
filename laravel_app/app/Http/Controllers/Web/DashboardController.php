<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MLPrediction;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $transactions = [];
        $latestAlerts = [];
        $modelUsageStats = [];
        $recentPredictions = [];
        $totalPredictions = 0;
        $predictionsByModel = [];
        $topUsers = null;

        // Get ML analytics for current user or all users (admin)
        if ($user->role === 'user') {
            $transactions = \App\Models\TollTransaction::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            // User's predictions analytics
            $userPredictions = MLPrediction::where('user_id', $user->id);
            $totalPredictions = $userPredictions->count();

            // Predictions by model
            $rawStats = $userPredictions->select('model_type')
                ->groupBy('model_type')
                ->selectRaw('model_type, COUNT(*) as count, AVG(confidence_score) as avg_confidence')
                ->get()
                ->keyBy('model_type');

            // Ensure all user models are present
            $userModels = ['dashboard', 'traffic'];
            $predictionsByModel = collect($userModels)->map(function ($modelType) use ($rawStats) {
                return $rawStats->get($modelType) ?: (object) [
                    'model_type' => $modelType,
                    'count' => 0,
                    'avg_confidence' => 0
                ];
            });

            // Recent predictions (increased for pagination)
            $recentPredictions = MLPrediction::where('user_id', $user->id)
                ->latest()
                ->take(40)
                ->get();
        }

        if ($user->role === 'admin') {
            $latestAlerts = \App\Models\SystemAlert::latest()
                ->take(40)
                ->get();

            // Admin sees only their own predictions analytics and logs
            $userPredictions = MLPrediction::where('user_id', $user->id);
            $totalPredictions = $userPredictions->count();

            // Predictions by model (only current admin's)
            $rawStats = $userPredictions->select('model_type')
                ->groupBy('model_type')
                ->selectRaw('model_type, COUNT(*) as count, AVG(confidence_score) as avg_confidence')
                ->get()
                ->keyBy('model_type');

            // Ensure all admin models are present
            $adminModels = ['plate', 'cracks', 'accident', 'fire_smoke', 'vehicles', 'car_damage'];
            $predictionsByModel = collect($adminModels)->map(function ($modelType) use ($rawStats) {
                return $rawStats->get($modelType) ?: (object) [
                    'model_type' => $modelType,
                    'count' => 0,
                    'avg_confidence' => 0
                ];
            });

            // Recent predictions from current admin only (increased for pagination)
            $recentPredictions = MLPrediction::where('user_id', $user->id)
                ->latest()
                ->take(40)
                ->with('user')
                ->get();

            // Top users by predictions count (optional: keep system-wide or filter)
            $topUsers = MLPrediction::select('user_id')
                ->groupBy('user_id')
                ->selectRaw('user_id, COUNT(*) as prediction_count')
                ->orderByDesc('prediction_count')
                ->limit(5)
                ->with('user:id,name,email')
                ->get();

            // Alerts distribution for chart
            $alertsCountByType = \App\Models\SystemAlert::select('type')
                ->groupBy('type')
                ->selectRaw('type, COUNT(*) as count')
                ->get();
        }

        // Car Analytics (for both user and admin)
        $vehicleIds = $user->vehicles()->pluck('id');
        $fuelTotal = \App\Models\FuelLog::whereIn('vehicle_id', $vehicleIds)->sum('cost');
        $maintenanceTotal = \App\Models\MaintenanceLog::whereIn('vehicle_id', $vehicleIds)->sum('cost');
        $carTotalExpenses = $fuelTotal + $maintenanceTotal;

        // Efficiency data
        $efficiencyData = [];
        $totalEfficiency = 0;
        $vehiclesWithData = 0;
        foreach ($user->vehicles as $vehicle) {
            $logs = $vehicle->fuelLogs()->orderBy('odometer')->get();
            if ($logs->count() >= 2) {
                $totalKm = $logs->last()->odometer - $logs->first()->odometer;
                $totalLiters = $logs->sum('liters') - $logs->first()->liters;
                $efficiency = $totalLiters > 0 ? $totalKm / $totalLiters : 0;
                $efficiencyData[$vehicle->id] = round($efficiency, 2);
                $totalEfficiency += $efficiency;
                $vehiclesWithData++;
            } else {
                $efficiencyData[$vehicle->id] = 0;
            }
        }
        $avgFleetEfficiency = $vehiclesWithData > 0 ? round($totalEfficiency / $vehiclesWithData, 1) : 0;

        return view('dashboard', [
            'user' => $user,
            'transactions' => $transactions,
            'latestAlerts' => $latestAlerts,
            'totalPredictions' => $totalPredictions,
            'predictionsByModel' => $predictionsByModel,
            'recentPredictions' => $recentPredictions,
            'topUsers' => $topUsers,
            'carTotalExpenses' => $carTotalExpenses,
            'fuelTotal' => $fuelTotal,
            'maintenanceTotal' => $maintenanceTotal,
            'efficiencyData' => $efficiencyData,
            'avgFleetEfficiency' => $avgFleetEfficiency,
            'vehicles' => $user->vehicles,
            'alertsCountByType' => $alertsCountByType ?? collect()
        ]);
    }
}
