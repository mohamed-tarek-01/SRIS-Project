@extends('layouts.app')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    /* Dark map theme */
    .leaflet-layer,
    .leaflet-control-zoom-in,
    .leaflet-control-zoom-out,
    .leaflet-control-attribution {
        filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
    }
</style>

<div class="max-w-7xl mx-auto space-y-8 pb-20" x-data="stationFinder()">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20 shadow-lg shadow-blue-500/5">
                    <i data-lucide="map" class="w-6 h-6"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Fuel Stations</h1>
            </div>
            <p class="text-slate-400 font-medium max-w-xl">Find nearby fuel stations based on your live GPS location. Optimized for Helwan.</p>
        </div>
        
        <button @click="locateMe" class="group relative px-8 py-4 bg-blue-500 text-white rounded-2xl font-black uppercase tracking-widest text-xs transition-all hover:bg-blue-400 active:scale-95 flex items-center gap-2 shadow-lg shadow-blue-500/20" :disabled="loading">
            <i data-lucide="crosshair" class="w-4 h-4 relative z-10" :class="loading ? 'animate-spin' : ''"></i>
            <span class="relative z-10" x-text="loading ? 'Locating...' : 'Find Nearest to Me'"></span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Stations List -->
        <div class="lg:col-span-1 space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
            <template x-if="sortedStations.length === 0">
                <div class="glass-panel p-8 rounded-3xl text-center border border-white/5">
                    <i data-lucide="map-pin-off" class="w-8 h-8 text-slate-500 mx-auto mb-3"></i>
                    <p class="text-slate-400 text-sm font-medium">Click "Find Nearest" to see stations sorted by distance.</p>
                </div>
            </template>

            <template x-for="station in sortedStations" :key="station.id">
                <div class="glass-panel p-6 rounded-3xl border border-white/5 hover:border-blue-500/30 transition-all cursor-pointer group" @click="focusStation(station)">
                    <div class="flex justify-between items-start gap-4">
                        <div>
                            <h3 class="text-white font-black text-lg leading-tight mb-1" x-text="station.name"></h3>
                            <p class="text-slate-500 text-xs font-medium" x-text="station.location"></p>
                        </div>
                        <div class="bg-blue-500/10 text-blue-400 border border-blue-500/20 px-3 py-1.5 rounded-xl text-center shrink-0">
                            <span class="block text-sm font-black" x-text="station.distanceKm"></span>
                            <span class="block text-[8px] uppercase tracking-widest font-bold">KM</span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Map -->
        <div class="lg:col-span-2">
            <div class="glass-panel rounded-[2.5rem] border border-white/5 overflow-hidden h-[600px] relative">
                <div id="map" class="w-full h-full z-0"></div>
                
                <!-- Floating Info -->
                <div class="absolute bottom-8 left-8 max-w-xs p-6 bg-slate-900/90 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl space-y-2 z-10 pointer-events-none">
                    <div class="flex items-center gap-3 text-blue-400">
                        <i data-lucide="info" class="w-5 h-5"></i>
                        <span class="text-[10px] font-black uppercase tracking-widest">Live Map</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">Map updates in real-time. Click on any station marker or list item to view details.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    const allStations = @json($fuelStations);

    function stationFinder() {
        return {
            loading: false,
            userLat: null,
            userLng: null,
            sortedStations: [],
            map: null,
            markers: [],
            userMarker: null,

            init() {
                // Initialize map centered on Helwan
                this.map = L.map('map').setView([29.8450, 31.3300], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(this.map);

                // Add all stations to map initially
                this.addStationMarkers(allStations);
            },

            addStationMarkers(stations) {
                // clear old markers
                this.markers.forEach(m => this.map.removeLayer(m));
                this.markers = [];

                stations.forEach(st => {
                    if (st.latitude && st.longitude) {
                        const m = L.marker([st.latitude, st.longitude])
                            .addTo(this.map)
                            .bindPopup(`<b>${st.name}</b><br>${st.location}`);
                        this.markers.push(m);
                    }
                });
            },

            locateMe() {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser');
                    return;
                }

                this.loading = true;
                
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.userLat = position.coords.latitude;
                        this.userLng = position.coords.longitude;
                        
                        this.updateMapAndList();
                        this.loading = false;
                    },
                    (error) => {
                        alert('Unable to retrieve your location');
                        this.loading = false;
                    }
                );
            },

            updateMapAndList() {
                // Add/Move user marker
                if (this.userMarker) {
                    this.map.removeLayer(this.userMarker);
                }
                
                // Blue circle for user
                this.userMarker = L.circleMarker([this.userLat, this.userLng], {
                    radius: 8,
                    fillColor: "#3b82f6",
                    color: "#fff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(this.map).bindPopup("You are here").openPopup();

                this.map.setView([this.userLat, this.userLng], 14);

                // Calculate distances and sort
                let withDistances = allStations.map(st => {
                    const dist = this.getDistanceFromLatLonInKm(this.userLat, this.userLng, st.latitude, st.longitude);
                    return { ...st, distanceKm: dist.toFixed(1), rawDist: dist };
                });

                withDistances.sort((a, b) => a.rawDist - b.rawDist);
                this.sortedStations = withDistances;
            },

            focusStation(station) {
                this.map.setView([station.latitude, station.longitude], 16);
                
                // Open popup for the selected station
                const latLng = L.latLng(station.latitude, station.longitude);
                this.markers.forEach(m => {
                    if (m.getLatLng().equals(latLng)) {
                        m.openPopup();
                    }
                });
            },

            // Haversine formula
            getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
                const R = 6371; // Radius of the earth in km
                const dLat = this.deg2rad(lat2-lat1);
                const dLon = this.deg2rad(lon2-lon1); 
                const a = 
                    Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) * 
                    Math.sin(dLon/2) * Math.sin(dLon/2); 
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
                return R * c; // Distance in km
            },

            deg2rad(deg) {
                return deg * (Math.PI/180);
            }
        }
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>
@endsection
