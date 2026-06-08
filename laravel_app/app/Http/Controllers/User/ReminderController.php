<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index()
    {
        $vehicles = auth()->user()->vehicles;
        $reminders = Reminder::whereIn('vehicle_id', $vehicles->pluck('id'))
            ->with('vehicle')
            ->orderBy('is_completed')
            ->orderBy('due_date')
            ->get();
            
        return view('user.reminders.index', compact('reminders', 'vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'title'      => 'required|string|max:255',
            'type'       => 'required|in:maintenance,insurance,license,other',
            'due_date'   => 'nullable|date',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        if ($vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        Reminder::create($request->only('vehicle_id', 'title', 'type', 'due_date'));

        return redirect()->back()->with('success', 'Reminder set successfully!');
    }

    public function toggleComplete(Reminder $reminder)
    {
        if ($reminder->vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        $reminder->update(['is_completed' => !$reminder->is_completed]);
        return redirect()->back()->with('success', 'Reminder status updated!');
    }

    public function destroy(Reminder $reminder)
    {
        if ($reminder->vehicle->user_id !== auth()->id()) {
            abort(403);
        }

        $reminder->delete();
        return redirect()->back()->with('success', 'Reminder removed!');
    }
}
