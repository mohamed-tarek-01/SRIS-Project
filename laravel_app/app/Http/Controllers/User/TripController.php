<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Mail\ServiceDueAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TripController extends Controller
{
    public function index()
    {
        $vehicles = auth()->user()->vehicles;

        // Pass vehicle efficiency data as JSON for JS calculator
        $vehicleData = $vehicles->mapWithKeys(fn($v) => [
            $v->id => [
                'fuel_efficiency' => $v->fuel_efficiency ?? 12,
                'fuel_type'       => $v->fuel_type,
            ]
        ]);

        $trips = Trip::whereIn('vehicle_id', $vehicles->pluck('id'))
            ->with('vehicle')
            ->latest()
            ->get();

        return view('user.trips.index', compact('trips', 'vehicles', 'vehicleData'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'    => 'required|exists:vehicles,id',
            'origin'        => 'required|string|max:255',
            'destination'   => 'required|string|max:255',
            'distance_km'   => 'required|numeric|min:0.1',
            'fuel_consumed' => 'nullable|numeric|min:0',
            'fuel_cost'     => 'nullable|numeric|min:0',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        if ($vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        Trip::create([
            'vehicle_id'    => $request->vehicle_id,
            'origin'        => $request->origin,
            'destination'   => $request->destination,
            'distance_km'   => $request->distance_km,
            'fuel_consumed' => $request->fuel_consumed,
            'fuel_cost'     => $request->fuel_cost,
        ]);

        // Accumulate km on vehicle odometer
        $newOdometer = $vehicle->current_odometer + $request->distance_km;
        $vehicle->update(['current_odometer' => $newOdometer]);

        // Check if any reminders are due (within 100 km)
        $this->checkServiceDueAlerts($vehicle, $newOdometer);

        return redirect()->back()->with('success', 'Trip recorded and odometer updated!');
    }

    public function destroy(Trip $trip)
    {
        if ($trip->vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        $trip->delete();
        return redirect()->back()->with('success', 'Trip removed!');
    }

    /**
     * After each trip, fire email alerts for any reminder whose due_odometer
     * is within 100 km of the current odometer.
     */
    protected function checkServiceDueAlerts(Vehicle $vehicle, float $currentOdometer): void
    {
        $dueReminders = Reminder::where('vehicle_id', $vehicle->id)
            ->where('is_completed', false)
            ->whereNotNull('due_odometer')
            ->where('due_odometer', '>', $currentOdometer)          // not yet passed
            ->where('due_odometer', '<=', $currentOdometer + 100)   // within 100 km
            ->get();

        foreach ($dueReminders as $reminder) {
            try {
                $user = $vehicle->user;
                Mail::to($user->email)->send(new ServiceDueAlert($vehicle, $reminder, (int) $currentOdometer));
            } catch (\Throwable $e) {
                // Never let a mail failure break the trip save
                \Log::error('ServiceDueAlert mail failed: ' . $e->getMessage());
            }
        }
    }
}
