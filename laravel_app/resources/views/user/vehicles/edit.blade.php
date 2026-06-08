@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-12 pb-20">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-primary-500/10 flex items-center justify-center text-primary-400 border border-primary-500/20 shadow-lg shadow-primary-500/5">
                    <i data-lucide="edit-3" class="w-6 h-6"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Edit Vehicle</h1>
            </div>
            <p class="text-slate-400 font-medium max-w-xl">Update the details of your {{ $vehicle->brand }} {{ $vehicle->model }}.</p>
        </div>
        
        <a href="{{ route('user.vehicles.index') }}" class="group relative px-6 py-3 bg-white/5 text-slate-300 border border-white/10 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-white/10 hover:text-white transition-all flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Garage
        </a>
    </div>

    <!-- Edit Form -->
    <div class="glass-panel rounded-[2.5rem] border border-white/5 p-8 md:p-12">
        <form action="{{ route('user.vehicles.update', $vehicle) }}" method="POST" enctype="multipart/form-data" class="space-y-8"
              x-data="{ cc: '{{ old('engine_cc', $vehicle->engine_cc) }}', eff: '{{ old('fuel_efficiency', $vehicle->fuel_efficiency) }}' }" 
              x-init="$watch('cc', value => {
                  if (!value) return;
                  let val = parseInt(value);
                  if (val <= 1200) eff = 16;
                  else if (val <= 1600) eff = 13;
                  else if (val <= 2000) eff = 10;
                  else eff = 7;
              })">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Brand</label>
                    <input type="text" name="brand" required value="{{ old('brand', $vehicle->brand) }}" placeholder="e.g. BMW" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                    @error('brand') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Model</label>
                    <input type="text" name="model" required value="{{ old('model', $vehicle->model) }}" placeholder="e.g. M4 Competition" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                    @error('model') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Year</label>
                    <input type="number" name="year" required value="{{ old('year', $vehicle->year) }}" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                    @error('year') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Plate Number</label>
                    <input type="text" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}" placeholder="e.g. ABC-123" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                    @error('plate_number') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Fuel Type</label>
                    <select name="fuel_type" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all appearance-none">
                        <option value="petrol" class="bg-dark-800" {{ $vehicle->fuel_type === 'petrol' ? 'selected' : '' }}>Petrol</option>
                        <option value="diesel" class="bg-dark-800" {{ $vehicle->fuel_type === 'diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="electric" class="bg-dark-800" {{ $vehicle->fuel_type === 'electric' ? 'selected' : '' }}>Electric</option>
                        <option value="hybrid" class="bg-dark-800" {{ $vehicle->fuel_type === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                    @error('fuel_type') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Current Odometer (km)</label>
                    <input type="number" name="current_odometer" required value="{{ old('current_odometer', $vehicle->current_odometer) }}" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                    @error('current_odometer') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Engine CC</label>
                    <input type="number" name="engine_cc" x-model="cc" required placeholder="e.g. 1600" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                    @error('engine_cc') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Fuel Efficiency (km/L)</label>
                    <input type="number" step="0.1" name="fuel_efficiency" x-model="eff" required placeholder="e.g. 12" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                    @error('fuel_efficiency') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-4 pt-4">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Car Photo (Optional)</label>
                
                @if($vehicle->image_path)
                    <div class="flex items-center gap-6 p-4 rounded-2xl bg-white/5 border border-white/5">
                        <img src="{{ asset('storage/' . $vehicle->image_path) }}" alt="Current Image" class="w-24 h-16 object-cover rounded-xl border border-white/10">
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">Current Photo</span>
                    </div>
                @endif
                
                <input type="file" name="image" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-slate-400 focus:outline-none focus:border-primary-500/50 transition-all file:mr-4 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-primary-500 file:text-white hover:file:bg-primary-400 cursor-pointer">
                @error('image') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-6 border-t border-white/5 flex gap-4">
                <button type="submit" class="flex-1 py-5 bg-primary-500 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-[11px] hover:bg-primary-400 transition-all shadow-xl shadow-primary-500/20 active:scale-[0.98]">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
