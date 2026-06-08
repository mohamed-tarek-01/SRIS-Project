@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-12 pb-20" x-data="tripCalculator()">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20 shadow-lg shadow-emerald-500/5">
                    <i data-lucide="map-pin" class="w-6 h-6"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Trip History</h1>
            </div>
            <p class="text-slate-400 font-medium max-w-xl">Automatically calculate fuel consumption and cost based on your vehicle's engine CC and efficiency.</p>
        </div>
    </div>

    @if($vehicles->isEmpty())
        <div class="glass-panel rounded-[2.5rem] p-12 text-center space-y-6 border border-white/5">
            <div class="w-20 h-20 bg-slate-900 rounded-[2rem] flex items-center justify-center mx-auto text-slate-700">
                <i data-lucide="car" class="w-10 h-10"></i>
            </div>
            <div class="max-w-xs mx-auto">
                <h3 class="text-xl font-black text-white mb-2">No Vehicles Found</h3>
                <p class="text-slate-500 text-sm">Add a vehicle first to start logging your journeys.</p>
            </div>
            <a href="{{ route('user.vehicles.index') }}" class="inline-flex px-8 py-4 bg-white text-dark-900 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-primary-500 hover:text-white transition-all">
                Go to Garage
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- New Trip Form -->
            <div class="lg:col-span-1">
                <div class="glass-panel rounded-[2.5rem] border border-white/5 p-8 sticky top-32">
                    <h3 class="text-xl font-black text-white mb-8 flex items-center gap-3">
                        <i data-lucide="navigation-2" class="w-5 h-5 text-emerald-400"></i>
                        Log Journey
                    </h3>
                    
                    <form action="{{ route('user.trips.store') }}" method="POST" class="space-y-5" @submit="onSubmit">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Vehicle</label>
                            <select name="vehicle_id" x-model="vehicleId" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-emerald-500/50 transition-all appearance-none">
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" class="bg-dark-800">{{ $vehicle->brand }} {{ $vehicle->model }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="flex justify-between items-center text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">
                                <span>Origin</span>
                                <button type="button" @click="useMyLocation" class="text-emerald-400 hover:text-emerald-300 flex items-center gap-1" :disabled="loadingLocation">
                                    <i data-lucide="crosshair" class="w-3 h-3"></i>
                                    <span x-text="loadingLocation ? 'Locating...' : 'Use My Location'"></span>
                                </button>
                            </label>
                            <input type="text" name="origin" x-model="origin" required placeholder="e.g. Cairo" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-emerald-500/50 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Destination</label>
                            <input type="text" name="destination" x-model="destination" required placeholder="e.g. Helwan" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-emerald-500/50 transition-all">
                        </div>

                        <button type="button" @click="calculateTrip" class="w-full py-4 bg-white/5 text-emerald-400 border border-emerald-500/30 rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-emerald-500/10 transition-all flex items-center justify-center gap-2" :disabled="calculating">
                            <i data-lucide="calculator" class="w-4 h-4" :class="calculating ? 'animate-spin' : ''"></i>
                            <span x-text="calculating ? 'Calculating...' : 'Calculate Distance & Fuel'"></span>
                        </button>

                        <div x-show="distance > 0" class="grid grid-cols-3 gap-2 mt-4 animate-fade-in-up" style="display: none;">
                            <div class="bg-white/5 border border-emerald-500/20 p-3 rounded-xl text-center">
                                <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">Dist.</p>
                                <p class="text-sm font-black text-white"><span x-text="distance"></span> <span class="text-[9px] text-emerald-400">km</span></p>
                            </div>
                            <div class="bg-white/5 border border-emerald-500/20 p-3 rounded-xl text-center">
                                <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">Fuel</p>
                                <p class="text-sm font-black text-white"><span x-text="fuelConsumed"></span> <span class="text-[9px] text-emerald-400">L</span></p>
                            </div>
                            <div class="bg-white/5 border border-emerald-500/20 p-3 rounded-xl text-center">
                                <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">Cost</p>
                                <p class="text-sm font-black text-white"><span x-text="fuelCost"></span> <span class="text-[9px] text-emerald-400">EGP</span></p>
                            </div>
                        </div>

                        <input type="hidden" name="distance_km" x-model="distance">
                        <input type="hidden" name="fuel_consumed" x-model="fuelConsumed">
                        <input type="hidden" name="fuel_cost" x-model="fuelCost">

                        <button type="submit" class="w-full py-5 bg-emerald-500 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-[11px] hover:bg-emerald-400 transition-all shadow-xl shadow-emerald-500/10 active:scale-[0.98] mt-6" :disabled="distance == 0 || calculating">
                            Record Trip
                        </button>
                    </form>
                </div>
            </div>

            <!-- Trips Timeline -->
            <div class="lg:col-span-2 space-y-8 relative">
                <!-- Timeline Line -->
                <div class="absolute left-10 top-10 bottom-10 w-0.5 bg-gradient-to-b from-emerald-500/50 via-white/5 to-transparent hidden md:block"></div>
                
                @forelse($trips as $trip)
                    <div class="relative pl-0 md:pl-20 group">
                        <!-- Timeline Dot -->
                        <div class="absolute left-[34px] top-1/2 -translate-y-1/2 w-3.5 h-3.5 rounded-full bg-emerald-500 border-4 border-slate-950 z-10 hidden md:block group-hover:scale-150 transition-transform duration-300"></div>
                        
                        <div class="glass-panel rounded-[2.5rem] border border-white/5 p-8 transition-all hover:border-emerald-500/30 flex flex-col md:flex-row items-center justify-between gap-8">
                            <div class="flex items-center gap-6">
                                <div class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-emerald-400">
                                    <i data-lucide="navigation" class="w-7 h-7"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <h4 class="text-xl font-black text-white uppercase">{{ $trip->origin }} <span class="text-slate-600 px-2">→</span> {{ $trip->destination }}</h4>
                                    </div>
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                                        {{ $trip->vehicle->brand }} • {{ number_format($trip->distance_km, 1) }} KM
                                        @if($trip->fuel_consumed)
                                        • <span class="text-emerald-400">{{ number_format($trip->fuel_consumed, 1) }} L</span> ({{ number_format($trip->fuel_cost, 0) }} EGP)
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                                        {{ $trip->created_at->format('M d, Y') }}
                                    </p>
                                </div>
                                
                                <form action="{{ route('user.trips.destroy', $trip) }}" method="POST" onsubmit="return confirm('Remove trip?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-3 rounded-xl bg-white/5 border border-white/10 text-slate-600 hover:text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/20 transition-all">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="glass-panel rounded-[2.5rem] p-20 text-center space-y-6 border border-white/5">
                        <div class="w-20 h-20 bg-slate-900 rounded-[2rem] flex items-center justify-center mx-auto text-slate-700">
                            <i data-lucide="map" class="w-10 h-10"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white mb-2">No Journeys Recorded</h3>
                            <p class="text-slate-500 text-sm">Start logging your trips to build your travel history.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>

@if($vehicles->isNotEmpty())
<script>
    const vehicleData = @json($vehicleData);
    const PETROL_PRICE = 25.0; // EGP per Litre

    function tripCalculator() {
        return {
            vehicleId: '{{ $vehicles->first()->id }}',
            origin: '',
            destination: '',
            distance: 0,
            fuelConsumed: 0,
            fuelCost: 0,
            originLat: null,
            originLng: null,
            loadingLocation: false,
            calculating: false,

            useMyLocation() {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser');
                    return;
                }
                this.loadingLocation = true;
                navigator.geolocation.getCurrentPosition(async (position) => {
                    this.originLat = position.coords.latitude;
                    this.originLng = position.coords.longitude;
                    
                    try {
                        // Reverse geocode
                        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${this.originLat}&lon=${this.originLng}`);
                        const data = await res.json();
                        this.origin = data.address.city || data.address.town || data.address.suburb || "Current Location";
                    } catch (e) {
                        this.origin = "Current Location";
                    }
                    this.loadingLocation = false;
                }, (err) => {
                    alert('Could not get location. Please enter origin manually.');
                    this.loadingLocation = false;
                });
            },

            async calculateTrip() {
                if (!this.origin || !this.destination) {
                    alert('Please enter both origin and destination.');
                    return;
                }

                this.calculating = true;

                try {
                    // Forward geocode destination
                    const destRes = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.destination)}+Egypt&limit=1`);
                    const destData = await destRes.json();
                    
                    if (!destData || destData.length === 0) {
                        throw new Error('Destination not found');
                    }

                    const destLat = destData[0].lat;
                    const destLng = destData[0].lon;

                    // If we don't have origin coords, forward geocode origin
                    let origLat = this.originLat;
                    let origLng = this.originLng;

                    if (!origLat || !origLng) {
                        const origRes = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.origin)}+Egypt&limit=1`);
                        const origData = await origRes.json();
                        if (!origData || origData.length === 0) throw new Error('Origin not found');
                        origLat = origData[0].lat;
                        origLng = origData[0].lon;
                    }

                    // Get Route from OSRM
                    const routeRes = await fetch(`https://router.project-osrm.org/route/v1/driving/${origLng},${origLat};${destLng},${destLat}?overview=false`);
                    const routeData = await routeRes.json();

                    if (routeData.code !== 'Ok') throw new Error('Could not calculate route');

                    // distance in meters to km
                    this.distance = (routeData.routes[0].distance / 1000).toFixed(1);

                    // calculate fuel and cost
                    const vData = vehicleData[this.vehicleId];
                    const eff = vData.fuel_efficiency || 12; // fallback

                    this.fuelConsumed = (this.distance / eff).toFixed(1);
                    this.fuelCost = (this.fuelConsumed * PETROL_PRICE).toFixed(0);

                } catch (e) {
                    alert('Error calculating trip: ' + e.message + '. Please ensure locations are valid cities/areas in Egypt.');
                    // Allow manual fallback?
                    const manualDist = prompt("Could not calculate automatically. Enter distance in km manually:", "10");
                    if (manualDist) {
                        this.distance = parseFloat(manualDist).toFixed(1);
                        const vData = vehicleData[this.vehicleId];
                        const eff = vData.fuel_efficiency || 12;
                        this.fuelConsumed = (this.distance / eff).toFixed(1);
                        this.fuelCost = (this.fuelConsumed * PETROL_PRICE).toFixed(0);
                    }
                }

                this.calculating = false;
            },

            onSubmit(e) {
                if (this.distance == 0) {
                    e.preventDefault();
                    alert('Please calculate the trip distance first.');
                }
            }
        }
    }
</script>
@endif

@endsection
