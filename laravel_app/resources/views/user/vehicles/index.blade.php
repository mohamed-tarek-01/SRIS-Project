@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-12 pb-20" x-data="{ showAddModal: false }">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-primary-500/10 flex items-center justify-center text-primary-400 border border-primary-500/20 shadow-lg shadow-primary-500/5">
                    <i data-lucide="car" class="w-6 h-6"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">My Garage</h1>
            </div>
            <p class="text-slate-400 font-medium max-w-xl">Manage your vehicle profiles, track status, and keep your documents updated.</p>
        </div>
        
        @if(count($vehicles) < 2)
            <button @click="showAddModal = true" class="group relative px-8 py-4 bg-white text-dark-900 rounded-2xl font-black uppercase tracking-widest text-xs transition-all hover:bg-primary-400 hover:text-white active:scale-95 flex items-center gap-2 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-primary-400 to-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <i data-lucide="plus" class="w-4 h-4 relative z-10"></i>
                <span class="relative z-10">Add New Vehicle</span>
            </button>
        @else
            <button disabled class="group relative px-8 py-4 bg-slate-800 text-slate-500 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center gap-2 cursor-not-allowed border border-white/5" title="Maximum limit of 2 vehicles reached">
                <i data-lucide="lock" class="w-4 h-4 relative z-10"></i>
                <span class="relative z-10">Limit Reached</span>
            </button>
        @endif
    </div>

    <!-- Vehicles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($vehicles as $vehicle)
            <div class="group glass-panel rounded-[2.5rem] border border-white/5 overflow-hidden transition-all duration-500 hover:border-primary-500/30 hover:shadow-2xl hover:shadow-primary-500/10 flex flex-col">
                <!-- Image Wrapper -->
                <div class="aspect-[16/10] relative overflow-hidden bg-slate-900">
                    @if($vehicle->image_path)
                        <img src="{{ asset('storage/' . $vehicle->image_path) }}" alt="{{ $vehicle->brand }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-800">
                            <i data-lucide="car" class="w-20 h-20 opacity-20"></i>
                        </div>
                    @endif
                    <!-- Floating Badge -->
                    <div class="absolute top-6 left-6 px-4 py-1.5 rounded-full bg-black/60 backdrop-blur-md border border-white/10 text-[10px] font-black text-white uppercase tracking-widest">
                        {{ $vehicle->fuel_type }}
                    </div>
                </div>

                <!-- Info Section -->
                <div class="p-8 space-y-6 flex-1 flex flex-col">
                    <div>
                        <h3 class="text-2xl font-black text-white leading-none mb-2">{{ $vehicle->brand }} {{ $vehicle->model }}</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em]">{{ $vehicle->year }} • {{ $vehicle->plate_number ?? 'NO PLATE' }}</p>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-white/5 border border-white/5 p-4 rounded-2xl">
                            <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest mb-1">Odometer</p>
                            <p class="text-lg font-black text-white">{{ number_format($vehicle->current_odometer) }} <span class="text-[10px] text-slate-400">km</span></p>
                        </div>
                        <div class="bg-white/5 border border-white/5 p-4 rounded-2xl">
                            <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest mb-1">Engine</p>
                            <p class="text-lg font-black text-white">{{ $vehicle->engine_cc ?? 'N/A' }} <span class="text-[10px] text-slate-400">CC</span></p>
                        </div>
                        <div class="bg-white/5 border border-white/5 p-4 rounded-2xl">
                            <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest mb-1">Status</p>
                            <p class="text-lg font-black text-green-400">Active</p>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/5 mt-auto flex items-center justify-between">
                        <div class="flex gap-2">
                            <a href="{{ route('user.vehicles.edit', $vehicle) }}" class="p-3 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('user.vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Delete this vehicle?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-3 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/20 transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                        <a href="#" class="text-[10px] font-black text-primary-400 uppercase tracking-widest hover:text-white transition-colors flex items-center gap-1">
                            Details <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 flex flex-col items-center text-center space-y-6">
                <div class="w-24 h-24 rounded-[2rem] bg-slate-900 flex items-center justify-center border border-white/5 text-slate-700">
                    <i data-lucide="car" class="w-10 h-10"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white mb-2">No Vehicles Found</h3>
                    <p class="text-slate-500 text-sm max-w-xs mx-auto">Start by adding your first vehicle to track its maintenance and fuel usage.</p>
                </div>
                <button @click="showAddModal = true" class="px-8 py-4 bg-primary-500 text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-primary-400 transition-all shadow-lg shadow-primary-500/20">
                    Add My First Car
                </button>
            </div>
        @endforelse
    </div>

    <!-- Add Vehicle Modal -->
    <template x-teleport="body">
        <div x-show="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
            <div @click="showAddModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>
            
            <div class="relative w-full max-w-xl glass-panel rounded-[2.5rem] border border-white/10 p-8 md:p-12 shadow-2xl bg-dark-800 overflow-hidden">
                <!-- Modal Glow -->
                <div class="absolute -top-24 -left-24 w-48 h-48 bg-primary-500/20 blur-[100px] rounded-full"></div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-10">
                        <div>
                            <h2 class="text-3xl font-black text-white leading-none mb-2">New Vehicle</h2>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em]">Enter car details to register</p>
                        </div>
                        <button @click="showAddModal = false" class="text-slate-500 hover:text-white transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <form action="{{ route('user.vehicles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                          x-data="{ cc: '', eff: '' }" 
                          x-init="$watch('cc', value => {
                              if (!value) return;
                              let val = parseInt(value);
                              if (val <= 1200) eff = 16;
                              else if (val <= 1600) eff = 13;
                              else if (val <= 2000) eff = 10;
                              else eff = 7;
                          })">
                        @csrf
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Brand</label>
                                <input type="text" name="brand" required placeholder="e.g. BMW" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Model</label>
                                <input type="text" name="model" required placeholder="e.g. M4 Competition" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Year</label>
                                <input type="number" name="year" required value="{{ date('Y') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Plate Number</label>
                                <input type="text" name="plate_number" placeholder="e.g. ABC-123" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Fuel Type</label>
                                <select name="fuel_type" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all appearance-none">
                                    <option value="petrol" class="bg-dark-800">Petrol</option>
                                    <option value="diesel" class="bg-dark-800">Diesel</option>
                                    <option value="electric" class="bg-dark-800">Electric</option>
                                    <option value="hybrid" class="bg-dark-800">Hybrid</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Initial Odometer</label>
                                <input type="number" name="current_odometer" required value="0" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Engine CC</label>
                                <input type="number" name="engine_cc" x-model="cc" required placeholder="e.g. 1600" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Fuel Eff. (km/L)</label>
                                <input type="number" step="0.1" name="fuel_efficiency" x-model="eff" required placeholder="e.g. 12" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-primary-500/50 transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Car Photo</label>
                            <input type="file" name="image" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-slate-400 focus:outline-none focus:border-primary-500/50 transition-all file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-primary-500 file:text-white hover:file:bg-primary-400 cursor-pointer">
                        </div>

                        <button type="submit" class="w-full py-5 bg-white text-dark-900 rounded-[1.5rem] font-black uppercase tracking-widest text-[11px] hover:bg-primary-500 hover:text-white transition-all shadow-xl active:scale-[0.98] mt-6">
                            Register Vehicle
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
