@extends('layouts.app')

@section('title', 'Drive Mode')

@section('content')
<div x-data="driveModeHandler()" class="max-w-3xl mx-auto" x-init="initAudio()">
    <!-- Header -->
    <div class="text-center space-y-4 mb-8 animate-fade-in-up">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full shadow-[0_0_15px_rgba(59,130,246,0.3)] transition-colors duration-500"
            :class="isTracking ? 'bg-blue-500/20' : 'bg-slate-500/10'">
            <i data-lucide="navigation" class="w-8 h-8 transition-colors duration-500" :class="isTracking ? 'text-blue-400' : 'text-slate-400'"></i>
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">
            Drive Mode
        </h1>
        <p class="text-slate-400 max-w-lg mx-auto">
            Real-time geofencing and audio alerts for road hazards ahead. Keep this screen open while driving.
        </p>
    </div>

    <!-- Main HUD -->
    <div class="glass-panel p-8 rounded-3xl border transition-all duration-500 animate-fade-in-up shadow-2xl relative overflow-hidden"
        style="animation-delay: 100ms;"
        :class="alertActive ? 'border-red-500/50 shadow-[0_0_50px_rgba(239,68,68,0.2)]' : (isTracking ? 'border-blue-500/30' : 'border-white/5')">
        
        <!-- Danger Background Glow -->
        <div x-show="alertActive" class="absolute inset-0 bg-red-500/10 animate-pulse pointer-events-none"></div>

        <div class="flex flex-col items-center justify-center space-y-8 relative z-10">
            
            <!-- Status Indicator -->
            <div class="flex flex-col items-center gap-2">
                <span class="flex h-4 w-4 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                        :class="isTracking ? 'bg-blue-400' : 'hidden'"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4"
                        :class="isTracking ? 'bg-blue-500' : 'bg-slate-600'"></span>
                </span>
                <span class="text-xs font-black uppercase tracking-widest"
                    :class="alertActive ? 'text-red-400' : (isTracking ? 'text-blue-400' : 'text-slate-500')"
                    x-text="statusText">
                </span>
            </div>

            <!-- Big Alert Warning -->
            <div x-show="alertActive" class="text-center space-y-2 animate-fade-in-up">
                <i data-lucide="alert-triangle" class="w-16 h-16 text-red-500 mx-auto animate-bounce"></i>
                <h2 class="text-2xl font-black text-red-400 uppercase tracking-widest">Hazard Ahead!</h2>
                <p class="text-white text-lg font-bold" x-text="alertMessage"></p>
                <p class="text-red-300 font-mono text-xl" x-text="alertDistance + 'm'"></p>
            </div>

            <!-- Dashboard Stats -->
            <div x-show="!alertActive && isTracking" class="grid grid-cols-2 gap-8 w-full max-w-md animate-fade-in-up">
                <div class="glass-panel p-4 rounded-2xl flex flex-col items-center border border-white/5 bg-black/20">
                    <i data-lucide="crosshair" class="w-5 h-5 text-indigo-400 mb-2"></i>
                    <span class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">Nearby Hazards</span>
                    <span class="text-3xl font-black text-white" x-text="nearbyHazards.length"></span>
                </div>
                <div class="glass-panel p-4 rounded-2xl flex flex-col items-center border border-white/5 bg-black/20">
                    <i data-lucide="activity" class="w-5 h-5 text-emerald-400 mb-2"></i>
                    <span class="text-[10px] text-slate-500 font-black uppercase tracking-widest mb-1">System Status</span>
                    <span class="text-lg font-black text-emerald-400 uppercase mt-1">Online</span>
                </div>
            </div>

            <!-- Control Button -->
            <button @click="toggleTracking" 
                class="px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-sm transition-all shadow-lg flex items-center gap-3"
                :class="isTracking ? 'bg-slate-800 text-white hover:bg-slate-700' : 'bg-blue-600 text-white hover:bg-blue-500 hover:shadow-[0_0_20px_rgba(37,99,235,0.4)]'">
                <i :data-lucide="isTracking ? 'square' : 'play'" class="w-5 h-5"></i>
                <span x-text="isTracking ? 'Stop Tracking' : 'Start Drive Mode'"></span>
            </button>

            <!-- Location debug (optional) -->
            <div x-show="isTracking" class="text-[9px] font-mono text-slate-600">
                <span x-text="currentLat ? currentLat.toFixed(6) : '---'"></span>, 
                <span x-text="currentLng ? currentLng.toFixed(6) : '---'"></span>
            </div>
        </div>
    </div>
</div>

<script>
    function driveModeHandler() {
        return {
            isTracking: false,
            watchId: null,
            statusText: 'System Standby',
            currentLat: null,
            currentLng: null,
            nearbyHazards: [],
            alertActive: false,
            alertMessage: '',
            alertDistance: 0,
            lastFetchTime: 0,
            alertCooldowns: {}, // Store cooldowns by hazard ID
            synth: window.speechSynthesis,
            voicesReady: false,

            initAudio() {
                // Pre-warm speech synthesis (browsers require this to be ready)
                if (speechSynthesis.onvoiceschanged !== undefined) {
                    speechSynthesis.onvoiceschanged = () => { this.voicesReady = true; };
                }
            },

            speak(text) {
                if (!this.synth) return;
                // Cancel any ongoing speech
                this.synth.cancel();
                
                const utterThis = new SpeechSynthesisUtterance(text);
                utterThis.lang = 'en-US';
                utterThis.rate = 1.1;
                utterThis.pitch = 1.0;
                
                this.synth.speak(utterThis);
            },

            toggleTracking() {
                if (this.isTracking) {
                    this.stopTracking();
                } else {
                    this.startTracking();
                }
            },

            startTracking() {
                if (!("geolocation" in navigator)) {
                    alert("Geolocation is not supported by your browser.");
                    return;
                }

                // Initial dummy speak to unlock audio context on mobile browsers
                this.speak("Drive mode activated.");

                this.isTracking = true;
                this.statusText = 'Acquiring GPS Signal...';
                lucide.createIcons();

                this.watchId = navigator.geolocation.watchPosition(
                    (position) => {
                        this.currentLat = position.coords.latitude;
                        this.currentLng = position.coords.longitude;
                        this.statusText = 'Scanning for hazards...';
                        
                        // Fetch nearby hazards every 5 seconds maximum to save battery/bandwidth
                        const now = Date.now();
                        if (now - this.lastFetchTime > 5000) {
                            this.fetchNearbyHazards();
                            this.lastFetchTime = now;
                        } else {
                            // Even if we don't fetch, we must recalculate distance to known hazards
                            this.checkDistances();
                        }
                    },
                    (error) => {
                        console.error(error);
                        this.statusText = 'GPS Error: Please ensure location services are enabled.';
                        this.isTracking = false;
                    },
                    { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 }
                );
            },

            stopTracking() {
                if (this.watchId !== null) {
                    navigator.geolocation.clearWatch(this.watchId);
                }
                this.isTracking = false;
                this.statusText = 'System Standby';
                this.currentLat = null;
                this.currentLng = null;
                this.alertActive = false;
                lucide.createIcons();
            },

            async fetchNearbyHazards() {
                if (!this.currentLat || !this.currentLng) return;

                try {
                    const response = await fetch(`/api/hazards/nearby?latitude=${this.currentLat}&longitude=${this.currentLng}&radius=1000`);
                    const json = await response.json();
                    if (json.success) {
                        this.nearbyHazards = json.data;
                        this.checkDistances();
                    }
                } catch (e) {
                    console.error("Failed to fetch hazards", e);
                }
            },

            checkDistances() {
                // Only alert the user for road crack hazards
                const crackHazards = this.nearbyHazards.filter(h => h.model_type === 'cracks');

                if (!this.currentLat || !this.currentLng || crackHazards.length === 0) {
                    this.alertActive = false;
                    return;
                }

                let closestHazard = null;
                let minDistance = Infinity;

                for (const hazard of crackHazards) {
                    // Haversine formula implemented in JS
                    const R = 6371e3; // metres
                    const φ1 = this.currentLat * Math.PI/180;
                    const φ2 = parseFloat(hazard.latitude) * Math.PI/180;
                    const Δφ = (parseFloat(hazard.latitude)-this.currentLat) * Math.PI/180;
                    const Δλ = (parseFloat(hazard.longitude)-this.currentLng) * Math.PI/180;

                    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                              Math.cos(φ1) * Math.cos(φ2) *
                              Math.sin(Δλ/2) * Math.sin(Δλ/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

                    const distance = R * c; // in metres

                    if (distance < minDistance) {
                        minDistance = distance;
                        closestHazard = hazard;
                    }
                }

                // ALERT THRESHOLD: 50 METERS
                if (minDistance <= 50 && closestHazard) {
                    this.triggerAlert(closestHazard, Math.round(minDistance));
                } else {
                    this.alertActive = false;
                }
            },

            triggerAlert(hazard, distance) {
                this.alertActive = true;
                this.alertDistance = distance;
                
                let typeName = hazard.model_type.replace('_', ' ');
                if (hazard.model_type === 'cracks') typeName = 'road crack';
                
                this.alertMessage = `Detected ${typeName} ahead!`;

                const now = Date.now();
                const lastAlert = this.alertCooldowns[hazard.id] || 0;
                
                // Only speak if we haven't alerted for THIS specific hazard in the last 30 seconds
                if (now - lastAlert > 30000) {
                    this.speak(`Warning, ${typeName} ${distance} meters ahead. Please drive carefully.`);
                    this.alertCooldowns[hazard.id] = now;
                }
            }
        };
    }
</script>
@endsection
