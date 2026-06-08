

<?php $__env->startSection('title', 'License Plates (Geofenced)'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-8 max-w-4xl mx-auto" x-data="plateGeofencer()" x-init="initGeofence()">
        
        <div class="text-center space-y-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-500/10 mb-2 shadow-[0_0_15px_rgba(52,211,153,0.2)]">
                <i data-lucide="scan-text" class="w-8 h-8 text-primary-400"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-primary-100 to-primary-400">
                License Plate Recognition
            </h1>
            <p class="text-base text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Smart Geofenced Toll Gate. Deducts points from detected plates based on your current physical location.
            </p>
        </div>

        <!-- State 1: Loading/Checking Location -->
        <div x-show="status === 'loading'" class="glass-panel p-12 rounded-3xl border border-white/5 text-center flex flex-col items-center justify-center animate-pulse">
            <i data-lucide="loader-2" class="w-12 h-12 text-primary-400 animate-spin mb-4"></i>
            <h2 class="text-xl font-bold text-white mb-2">Acquiring GPS Signal...</h2>
            <p class="text-slate-400 text-sm">Please allow location access to verify your toll gate position.</p>
        </div>

        <!-- State 2: Error -->
        <div x-show="status === 'error'" class="glass-panel p-8 rounded-3xl border border-rose-500/30 bg-rose-500/10 text-center" style="display: none;">
            <i data-lucide="map-pin-off" class="w-12 h-12 text-rose-500 mx-auto mb-4"></i>
            <h2 class="text-xl font-bold text-rose-400 mb-2">Location Required</h2>
            <p class="text-rose-300 text-sm mb-6" x-text="errorMessage"></p>
            <button @click="initGeofence()" class="bg-rose-500/20 hover:bg-rose-500/40 border border-rose-500/50 text-white px-6 py-2 rounded-xl transition-all font-bold">
                Try Again
            </button>
        </div>

        <!-- State 3: New Station Setup -->
        <div x-show="status === 'setup'" class="glass-panel p-8 rounded-3xl border border-blue-500/30 bg-blue-500/5" style="display: none;">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-500/40">
                    <i data-lucide="map-pin" class="w-8 h-8 text-blue-400"></i>
                </div>
                <h2 class="text-2xl font-black text-white uppercase tracking-widest">New Location Detected</h2>
                <p class="text-slate-400 mt-2 text-sm">You are setting up a new toll gate. Please provide details to continue.</p>
            </div>

            <form @submit.prevent="createStation" class="space-y-6 max-w-md mx-auto">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Toll Gate Name</label>
                    <input type="text" x-model="newStation.name" required placeholder="e.g. Helwan Toll Gate" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Location Description</label>
                    <input type="text" x-model="newStation.location" placeholder="e.g. Km 22, Alex Desert Road" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Deduction Price (Points)</label>
                    <input type="number" x-model="newStation.price" min="0" required placeholder="e.g. 20" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-all">
                </div>
                
                <button type="submit" :disabled="isSubmitting" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-blue-500/25 transition-all flex justify-center items-center gap-2">
                    <i x-show="!isSubmitting" data-lucide="save" class="w-5 h-5"></i>
                    <i x-show="isSubmitting" data-lucide="loader-2" class="w-5 h-5 animate-spin"></i>
                    <span x-text="isSubmitting ? 'Saving...' : 'Save & Start Scanning'"></span>
                </button>
            </form>
        </div>

        <!-- State 4: Active / Ready -->
        <div x-show="status === 'ready'" style="display: none;">
            
            <div class="glass-panel px-6 py-4 rounded-2xl border border-emerald-500/30 bg-emerald-500/5 mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest font-black text-emerald-400/80">Active Toll Gate</p>
                        <p class="font-bold text-white">
                            <span x-text="activeStation ? activeStation.name : ''"></span>
                            <span class="text-xs text-slate-500 font-medium ml-2" x-text="activeStation && activeStation.location ? '(' + activeStation.location + ')' : ''"></span>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] uppercase tracking-widest font-black text-slate-500">Price</p>
                    <p class="font-black text-rose-400" x-text="activeStation ? '-' + activeStation.price + ' Pts' : ''"></p>
                </div>
            </div>

            <div class="relative z-10 w-full animate-fade-in-up" style="animation-delay: 100ms;">
                <?php
                    $endpoint = url('/api/ml/plate/detect');
                ?>
                <?php echo $__env->make('components.upload-box', ['endpoint' => $endpoint, 'stations' => collect()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

    </div>

    <script>
        function plateGeofencer() {
            return {
                status: 'loading', // loading, error, setup, ready
                errorMessage: '',
                activeStation: null,
                latitude: null,
                longitude: null,
                isSubmitting: false,
                newStation: {
                    name: '',
                    location: '',
                    price: ''
                },

                initGeofence() {
                    this.status = 'loading';
                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(
                            async (position) => {
                                this.latitude = position.coords.latitude;
                                this.longitude = position.coords.longitude;
                                await this.checkLocation();
                            },
                            (error) => {
                                this.status = 'error';
                                this.errorMessage = "Failed to get GPS location. It is required to determine the toll gate.";
                            },
                            { timeout: 10000, enableHighAccuracy: true }
                        );
                    } else {
                        this.status = 'error';
                        this.errorMessage = "Geolocation is not supported by your browser.";
                    }
                },

                async checkLocation() {
                    try {
                        const response = await fetch(`/api/stations/check-location?latitude=${this.latitude}&longitude=${this.longitude}`);
                        const data = await response.json();

                        if (data.success && data.data) {
                            this.activeStation = data.data;
                            this.status = 'ready';
                            this.dispatchLock();
                            setTimeout(() => lucide.createIcons(), 50);
                        } else {
                            this.status = 'setup';
                            setTimeout(() => lucide.createIcons(), 50);
                        }
                    } catch (e) {
                        this.status = 'error';
                        this.errorMessage = "Failed to communicate with server.";
                    }
                },

                async createStation() {
                    this.isSubmitting = true;
                    try {
                        const response = await fetch('/api/stations/create-dynamic', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.newStation.name,
                                price: this.newStation.price,
                                latitude: this.latitude,
                                longitude: this.longitude
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.activeStation = data.data;
                            this.status = 'ready';
                            this.dispatchLock();
                        } else {
                            alert("Failed to create station. Check inputs.");
                        }
                    } catch (e) {
                        alert("Error saving station.");
                    } finally {
                        this.isSubmitting = false;
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                },

                dispatchLock() {
                    window.dispatchEvent(new CustomEvent('station-locked', {
                        detail: { id: this.activeStation.id }
                    }));
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/plate.blade.php ENDPATH**/ ?>