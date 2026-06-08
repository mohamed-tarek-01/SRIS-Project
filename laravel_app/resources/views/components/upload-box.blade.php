<div
    x-data="{
        loading: false,
        result: null,
        error: null,
        isDragging: false,
        fileName: '',
        selectedStationId: '',
        locationText: '',
        latitude: null,
        longitude: null,
        hasStations: {{ isset($stations) && $stations->count() ? 'true' : 'false' }},
        requireLocation: {{ isset($requireLocation) && $requireLocation ? 'true' : 'false' }},
        cameraActive: false,
        stream: null,
        capturedBlob: null,
        recordingStatus: '', // for debugging UI
        isGuardActive: false,
        guardInterval: null,

        async init() {
            window.addEventListener('station-locked', (e) => {
                this.selectedStationId = e.detail.id;
                this.hasStations = false; // Hides the dropdown
            });

            // Automatically detect location if required (for accidents/fires)
            if (this.requireLocation) {
                await this.fetchLocation();
                if (this.latitude && this.longitude) {
                    this.reverseGeocode();
                }
            }
        },

        async fetchLocation() {
            return new Promise((resolve) => {
                if ('geolocation' in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.latitude = position.coords.latitude;
                            this.longitude = position.coords.longitude;
                            resolve();
                        },
                        (error) => {
                            console.warn('Geolocation failed or denied:', error);
                            resolve(); // Continue even if denied
                        },
                        { timeout: 5000, enableHighAccuracy: true }
                    );
                } else {
                    resolve();
                }
            });
        },

        async reverseGeocode() {
            if (!this.latitude || !this.longitude) return;
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${this.latitude}&lon=${this.longitude}&zoom=18&addressdetails=1`);
                const data = await res.json();
                if (data.display_name) {
                    // Extract a shorter, more relevant address
                    const parts = data.display_name.split(',');
                    this.locationText = parts.slice(0, 3).join(',').trim();
                }
            } catch (err) {
                console.warn('Reverse geocoding failed:', err);
                this.locationText = 'Current Location';
            }
        },

        async initCamera() {
            this.recordingStatus = 'Initializing camera...';
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' },
                    audio: false
                });
                this.$refs.video.srcObject = this.stream;
                this.cameraActive = true;
                this.error = null;
                this.recordingStatus = 'Camera ready';
                // Try to get location when camera opens
                this.fetchLocation();
            } catch (err) {
                this.error = 'CAMERA ERROR: Unable to access hardware. Please ensure permissions are granted and you are on a secure (HTTPS) connection.';
                this.recordingStatus = 'Camera failed';
            }
        },

        stopCamera() {
            this.stopGuardMode();
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.cameraActive = false;
        },

        capturePhoto() {
            const video = this.$refs.video;
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
            canvas.toBlob((blob) => {
                this.capturedBlob = blob;
                this.fileName = 'camera-capture-' + Date.now() + '.jpg';
                this.stopCamera();
            }, 'image/jpeg', 0.95);
        },

        startGuardMode() {
            this.isGuardActive = true;
            this.recordingStatus = 'AI Guard Active';
            this.runGuardLoop();
        },

        stopGuardMode() {
            this.isGuardActive = false;
            this.recordingStatus = 'Guard Stopped';
        },

        async runGuardLoop() {
            if (!this.isGuardActive || !this.cameraActive) return;
            await this.captureAndAnalyze();
            // Start next frame immediately after previous one is processed
            setTimeout(() => this.runGuardLoop(), 50); 
        },

        async captureAndAnalyze() {
            if (!this.cameraActive || !this.isGuardActive) return;

            const video = this.$refs.video;
            if (!video || video.readyState !== 4) return;

            // Refresh location periodically in guard mode
            if (Math.random() < 0.1) this.fetchLocation();

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
            canvas.toBlob(async (blob) => {
                const formData = new FormData();
                formData.append('file', blob, 'guard-sample.jpg');
                if (this.selectedStationId) formData.append('station_id', this.selectedStationId);
                if (this.locationText) formData.append('location_text', this.locationText);
                if (this.latitude) formData.append('latitude', this.latitude);
                if (this.longitude) formData.append('longitude', this.longitude);

                try {
                    const res = await fetch('{{ $endpoint }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.result = data;
                    }
                } catch (err) {
                    console.error('Guard mode frame analysis failed:', err);
                }
            }, 'image/jpeg', 0.7); // Low quality for speed
        },

        async upload(e) {
            this.error = null;
            this.result = null;

            if (this.hasStations && !this.selectedStationId) {
                this.error = 'CRITICAL: Neural Gate selection required. Please select a station before initializing analysis.';
                return;
            }

            if (this.requireLocation && !this.locationText) {
                this.error = 'CRITICAL: Location Metadata Required. Please specify the camera sector or street address.';
                return;
            }

            const fileInput = this.$refs.file;
            const formData = new FormData();

            if (this.capturedBlob) {
                formData.append('file', this.capturedBlob, this.fileName);
            } else if (fileInput.files.length) {
                formData.append('file', fileInput.files[0]);
            } else {
                this.error = 'Please choose an image, video, or use the camera first.';
                return;
            }
            
            // Get precise location before uploading
            await this.fetchLocation();

            if (this.selectedStationId) {
                formData.append('station_id', this.selectedStationId);
            }

            if (this.locationText) {
                formData.append('location_text', this.locationText);
            }

            if (this.latitude) formData.append('latitude', this.latitude);
            if (this.longitude) formData.append('longitude', this.longitude);

            this.loading = true;
            try {
                const res = await fetch('{{ $endpoint }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });

                if (!res.ok) {
                    const rawText = await res.text();
                    let errorMessage;
                    try {
                        const data = JSON.parse(rawText);
                        errorMessage = data.message || data.error || `Request failed (HTTP ${res.status})`;
                    } catch (e) {
                        errorMessage = `Server returned error (HTTP ${res.status}): ${rawText.substring(0, 100)}`;
                    }
                    throw new Error(errorMessage);
                }

                // Handle streaming response if it's NDJSON
                const contentType = res.headers.get('Content-Type') || '';
                const isNDJSON = contentType.includes('ndjson');
                
                if (isNDJSON) {
                    const reader = res.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';

                    while (true) {
                        const { value, done } = await reader.read();
                        if (done) break;

                        buffer += decoder.decode(value, { stream: true });
                        const lines = buffer.split('\n');
                        buffer = lines.pop();

                        for (const line of lines) {
                            const trimmed = line.trim();
                            if (!trimmed) continue;
                            try {
                                const data = JSON.parse(trimmed);
                                if (data.event === 'frame' || data.event === 'start' || data.event === 'done') {
                                    this.result = {...data};
                                } else if (data.error) {
                                    this.error = data.error;
                                }
                            } catch (e) {
                                console.warn('Skipping malformed stream line');
                            }
                        }
                    }
                } else {
                    const data = await res.json();
                    this.result = data;
                }

            } catch (err) {
                this.error = err.message || 'Something went wrong';
            } finally {
                this.loading = false;
            }
        },
        handleFileChange() {
            const fileInput = this.$refs.file;
            this.capturedBlob = null; // Clear camera capture if user picks a file
            if (fileInput.files.length) {
                this.fileName = fileInput.files[0].name;
            } else {
                this.fileName = '';
            }
        }
    }"
    class="glass-panel border border-white/5 rounded-2xl p-6 md:p-8 flex flex-col gap-6 w-full shadow-2xl relative overflow-hidden"
>
    <!-- Background Decor -->
    <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-primary-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="flex flex-col md:flex-row gap-6 relative z-10 w-full">
        <!-- Station Selector (for OCR/Plate) -->
        @if(isset($lockedStationId) && $lockedStationId)
            <div class="hidden" x-init="selectedStationId = '{{ $lockedStationId }}'"></div>
        @elseif(isset($stations) && $stations->count() > 0)
            <div class="flex flex-col gap-3 w-full md:w-1/3 animate-fade-in-up">
                <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] ml-1">
                    Neural Gate Selection
                </label>
                <div class="relative group">
                    <select 
                        x-model="selectedStationId"
                        class="w-full bg-white/[0.03] border border-white/10 rounded-xl pl-12 pr-10 py-4 text-sm font-bold text-white appearance-none focus:outline-none focus:border-primary-500/30 focus:ring-4 focus:ring-primary-500/5 transition-all cursor-pointer"
                    >
                        <option value="" class="bg-slate-900">Select Gateway...</option>
                        @foreach($stations as $station)
                            <option value="{{ $station->id }}" class="bg-slate-900">{{ $station->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="map-pin" class="w-5 h-5 text-slate-500 group-focus-within:text-primary-400"></i>
                    </div>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500"></i>
                    </div>
                </div>
            </div>
        @elseif(isset($requireLocation) && $requireLocation)
            <!-- Manual Location Input (for fire/accident only) -->
            <div class="flex flex-col gap-3 w-full md:w-1/3 animate-fade-in-up">
                <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] ml-1">
                    Descriptive Location
                </label>
                <div class="relative group">
                    <input 
                        type="text" 
                        x-model="locationText"
                        placeholder="e.g. Near Shell Station, Gate 4"
                        class="w-full bg-white/[0.03] border border-white/10 rounded-xl pl-12 pr-5 py-4 text-sm font-bold text-white placeholder-slate-700 focus:outline-none focus:border-primary-500/30 focus:ring-4 focus:ring-primary-500/5 transition-all"
                    >
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="map-pin" class="w-5 h-5 text-slate-500 group-focus-within:text-primary-400"></i>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-1" x-show="latitude && longitude">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[8px] font-black text-emerald-500/60 uppercase tracking-widest">GPS Coordinates Locked</span>
                </div>
            </div>
        @endif

        <div class="flex flex-col gap-3 flex-grow group">
            <div class="flex items-center justify-between ml-1">
                <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">
                    Upload Source Media
                </label>
                <button 
                    @click.prevent="initCamera"
                    x-show="!cameraActive"
                    class="flex items-center gap-2 text-[10px] font-black text-primary-400 uppercase tracking-widest hover:text-white transition-colors cursor-pointer"
                >
                    <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                    Open Camera
                </button>
            </div>
            
            <!-- Drag & Drop Zone -->
            <div 
                class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-xl transition-all duration-300 overflow-hidden"
                :class="isDragging ? 'border-primary-400 bg-primary-500/10 scale-[1.01]' : 'border-slate-700 bg-white/5 hover:bg-white/10 hover:border-slate-500'"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="isDragging = false; $refs.file.files = $event.dataTransfer.files; handleFileChange()"
            >
                <!-- Camera Preview Overlay -->
                <div 
                    x-show="cameraActive" 
                    class="absolute inset-0 z-30 bg-slate-900 flex flex-col items-center justify-center overflow-hidden"
                    style="display: none;"
                >
                    <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                    
                    <!-- Guard Indicator -->
                    <div x-show="isGuardActive" class="absolute top-4 left-1/2 -translate-x-1/2 flex items-center gap-2 px-3 py-1 bg-primary-600/20 border border-primary-500/40 rounded-full animate-pulse">
                        <div class="bg-primary-500 w-2 h-2 rounded-full"></div>
                        <span class="text-[10px] font-black text-primary-100 uppercase tracking-widest">AI Guard Active</span>
                    </div>

                    <div class="absolute bottom-4 left-0 right-0 flex justify-center items-center gap-4 px-4 overflow-hidden">
                        <!-- Close Camera -->
                        <button 
                            @click.prevent="stopCamera"
                            class="p-2.5 rounded-full bg-slate-800/80 border border-white/10 text-slate-400 hover:text-white transition-all shadow-lg"
                        >
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>

                        <!-- Guard Mode & Photo Capture -->
                        <div class="flex items-center gap-4 bg-black/40 backdrop-blur-md p-1.5 rounded-full border border-white/10">
                            
                            <!-- AI Guard Toggle -->
                            <button 
                                @click.prevent="isGuardActive ? stopGuardMode() : startGuardMode()"
                                :class="isGuardActive ? 'bg-primary-500 text-white' : 'bg-white/10 text-slate-400 hover:bg-white/20'"
                                class="p-3.5 rounded-full transition-all border border-white/20 group/guard"
                                title="Toggle AI Guard"
                            >
                                <i data-lucide="shield" :class="isGuardActive ? 'animate-pulse' : ''" class="w-6 h-6 group-hover/guard:scale-110 transition-transform"></i>
                            </button>

                            <!-- Photo Capture -->
                            <button 
                                @click.prevent="capturePhoto"
                                x-show="!isGuardActive"
                                class="p-3.5 rounded-full bg-white/10 text-white hover:bg-white/20 transition-all border border-white/20 group/shutter"
                                title="Capture Photo"
                            >
                                <i data-lucide="camera" class="w-6 h-6 group-hover/shutter:scale-110 transition-transform"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- HIDDEN FILE INPUT -->
                <input
                    type="file"
                    x-ref="file"
                    @change="handleFileChange"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                    accept="image/*,video/*"
                >

                <!-- Initial State -->
                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4" x-show="!fileName && !cameraActive">
                    <div class="w-12 h-12 mb-3 bg-white/5 rounded-full flex items-center justify-center shadow-inner">
                        <i data-lucide="image" class="w-6 h-6 text-slate-400"></i>
                    </div>
                    <p class="mb-1 text-sm text-slate-300 font-medium"><span class="text-primary-400">Click to upload</span> or drag and drop</p>
                    <p class="text-xs text-slate-500">Image or Video footage (MP4, AVI, JPEG, PNG)</p>
                </div>
                
                <!-- File Selected State (Including Camera Captures) -->
                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center" x-show="fileName && !cameraActive" style="display: none;">
                    <div class="w-12 h-12 mb-3 bg-primary-500/20 rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(52,211,153,0.3)]">
                        <i data-lucide="check-circle" class="w-6 h-6 text-primary-400"></i>
                    </div>
                    <p class="text-sm font-semibold text-primary-300" x-text="fileName"></p>
                    <p class="text-xs text-slate-400 mt-1" x-text="capturedBlob ? 'Camera capture ready.' : 'Ready to process. Click to replace.'"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Button -->
    <div class="relative z-10">
        <button
            @click.prevent="upload"
            :disabled="loading || !fileName"
            class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl text-sm font-bold tracking-wide transition-all duration-300 overflow-hidden relative"
            :class="loading || !fileName ? 'bg-slate-800 text-slate-500 cursor-not-allowed border border-white/5' : 'bg-gradient-to-r from-primary-600 to-primary-400 text-white shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_30px_rgba(16,185,129,0.5)] hover:scale-105'"
        >
            <div x-show="!loading && fileName" class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
            
            <i x-show="!loading" data-lucide="play" class="w-4 h-4 relative z-10"></i>
            <span x-show="!loading" class="relative z-10">Initialize Analysis</span>
            
            <i x-show="loading" data-lucide="loader-2" class="w-5 h-5 animate-spin text-primary-300 relative z-10"></i>
            <span x-show="loading" class="relative z-10 text-primary-300">Processing Neural Network...</span>
        </button>
    </div>

    <!-- Error Alert -->
    <template x-if="error">
        <div class="flex items-start gap-4 p-5 bg-rose-500/10 border border-rose-500/20 rounded-2xl relative z-10 shadow-inner group/error">
            <div class="w-8 h-8 rounded-full bg-rose-500/20 flex items-center justify-center shrink-0 group-hover/error:bg-rose-500/30 transition-colors">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400"></i>
            </div>
            <div class="text-sm text-rose-300 font-bold uppercase tracking-wider leading-relaxed pt-1.5" x-text="error"></div>
        </div>
    </template>

    <!-- Results Area -->
    <template x-if="result">
        <div class="mt-4 relative z-10 animate-fade-in-up">
            <div class="flex items-center gap-2 mb-4">
                <div class="h-px bg-white/10 flex-grow"></div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Analysis Results</span>
                <div class="h-px bg-white/10 flex-grow"></div>
            </div>
            @include('components.result-card')
        </div>
    </template>
</div>
