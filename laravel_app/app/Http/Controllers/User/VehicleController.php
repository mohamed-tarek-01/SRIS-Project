<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = auth()->user()->vehicles()->latest()->get();
        return view('user.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('user.vehicles.create');
    }

    public function store(Request $request)
    {
        // Enforce 2-car maximum
        if (auth()->user()->vehicles()->count() >= 2) {
            return redirect()->route('user.vehicles.index')
                ->with('error', 'You can only register a maximum of 2 vehicles.');
        }

        $request->validate([
            'brand'            => 'required|string|max:255',
            'model'            => 'required|string|max:255',
            'year'             => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'plate_number'     => 'nullable|string|max:255',
            'fuel_type'        => 'required|in:petrol,diesel,electric,hybrid',
            'engine_cc'        => 'required|integer|min:50|max:10000',
            'fuel_efficiency'  => 'required|numeric|min:1|max:50',
            'current_odometer' => 'required|integer|min:0',
            'image'            => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image');
        $data['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('vehicles', 'public');
        }

        Vehicle::create($data);

        return redirect()->route('user.vehicles.index')->with('success', 'Vehicle added successfully!');
    }

    public function edit(Vehicle $vehicle)
    {
        $this->authorizeOwner($vehicle);
        return view('user.vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorizeOwner($vehicle);

        $request->validate([
            'brand'            => 'required|string|max:255',
            'model'            => 'required|string|max:255',
            'year'             => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'plate_number'     => 'nullable|string|max:255',
            'fuel_type'        => 'required|in:petrol,diesel,electric,hybrid',
            'engine_cc'        => 'required|integer|min:50|max:10000',
            'fuel_efficiency'  => 'required|numeric|min:1|max:50',
            'current_odometer' => 'required|integer|min:0',
            'image'            => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            if ($vehicle->image_path) {
                Storage::disk('public')->delete($vehicle->image_path);
            }
            $data['image_path'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle->update($data);

        return redirect()->route('user.vehicles.index')->with('success', 'Vehicle updated successfully!');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorizeOwner($vehicle);

        if ($vehicle->image_path) {
            Storage::disk('public')->delete($vehicle->image_path);
        }

        $vehicle->delete();

        return redirect()->route('user.vehicles.index')->with('success', 'Vehicle deleted successfully!');
    }

    protected function authorizeOwner(Vehicle $vehicle)
    {
        if ($vehicle->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
