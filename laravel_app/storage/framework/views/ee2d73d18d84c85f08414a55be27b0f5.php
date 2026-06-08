<div class="glass-panel border-t-4 border-t-primary-500 rounded-xl p-5 md:p-6 text-sm text-slate-100 shadow-xl relative overflow-hidden">
    <!-- Subtle background glow -->
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-primary-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="flex flex-col gap-6 relative z-10">

        <!-- Detection Status Badge (Accident) -->
        <template x-if="result && result.accident_detected !== undefined">
            <div :class="result.accident_detected ? 'bg-red-500/10 border-red-500/30' : 'bg-emerald-500/10 border-emerald-500/30'"
                 class="flex items-center gap-3 p-4 rounded-xl border">
                <i :data-lucide="result.accident_detected ? 'alert-triangle' : 'shield-check'"
                   :class="result.accident_detected ? 'text-red-400' : 'text-emerald-400'"
                   class="w-5 h-5 shrink-0"></i>
                <span :class="result.accident_detected ? 'text-red-300' : 'text-emerald-300'"
                      class="text-sm font-black uppercase tracking-widest"
                      x-text="result.accident_detected ? '⚠ Accident Detected!' : '✓ Road Clear — No Accident'">
                </span>
                <template x-if="result.alert_triggered">
                    <span class="ml-auto text-[9px] font-black text-red-400 bg-red-900/20 border border-red-500/30 px-2 py-1 rounded-lg uppercase tracking-widest animate-pulse">
                        System Alert Logged
                    </span>
                </template>
            </div>
        </template>

        <!-- Detection Status Badge (Fire/Smoke) -->
        <template x-if="result && (result.fire_detected !== undefined || result.smoke_detected !== undefined)">
            <div :class="(result.fire_detected || result.smoke_detected) ? 'bg-orange-500/10 border-orange-500/30' : 'bg-emerald-500/10 border-emerald-500/30'"
                 class="flex items-center gap-3 p-4 rounded-xl border">
                <i :data-lucide="result.fire_detected ? 'flame' : result.smoke_detected ? 'wind' : 'shield-check'"
                   :class="(result.fire_detected || result.smoke_detected) ? 'text-orange-400' : 'text-emerald-400'"
                   class="w-5 h-5 shrink-0"></i>
                <span :class="(result.fire_detected || result.smoke_detected) ? 'text-orange-300' : 'text-emerald-300'"
                      class="text-sm font-black uppercase tracking-widest"
                      x-text="result.fire_detected ? '🔥 Fire Detected!' : result.smoke_detected ? '💨 Smoke Detected!' : '✓ No Fire or Smoke'">
                </span>
                <template x-if="result.alert_triggered">
                    <span class="ml-auto text-[9px] font-black text-orange-400 bg-orange-900/20 border border-orange-500/30 px-2 py-1 rounded-lg uppercase tracking-widest animate-pulse">
                        System Alert Logged
                    </span>
                </template>
            </div>
        </template>


        <template x-if="result && result.event === 'frame'">
            <div class="mb-2 p-4 bg-white/5 rounded-lg border border-white/10">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs text-primary-400 font-bold uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="cpu" class="w-4 h-4 animate-pulse-glow"></i> Processing Stream Loop
                    </span>
                    <span class="text-xs font-medium text-slate-300 font-mono" x-text="`${result.frame_index + 1} frames` "></span>
                </div>
                <div class="w-full bg-slate-800/50 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-primary-600 to-primary-400 h-1.5 rounded-full transition-all duration-300 shadow-[0_0_10px_rgba(52,211,153,0.5)]" 
                         :style="`width: ${Math.min(100, ((result.frame_index + 1) / 30) * 100)}%` "></div>
                </div>
            </div>
        </template>


        <!-- Detection Count Badge (Vehicles/Traffic) -->
        <template x-if="result && result.detections && result.detections.length > 0">
            <div class="bg-primary-500/10 border-primary-500/30 flex items-center gap-3 p-4 rounded-xl border">
                <i data-lucide="calculator" class="text-primary-400 w-5 h-5 shrink-0"></i>
                <span class="text-primary-300 text-sm font-black uppercase tracking-widest"
                      x-text="`${result.detections.length} ${result.model.includes('vehicles') ? 'Vehicles' : 'Objects'} Detected` ">
                </span>
            </div>
        </template>

        <!-- Display Processed Output Image (Dashboard & Plates) -->
        <template x-if="result && result.processed_image">
            <div class="space-y-4">
                <template x-if="result.num_plates > 0 || (result.plates && result.plates.length > 0)">
                    <div class="flex flex-col items-center justify-center p-8 bg-black/20 rounded-2xl border border-white/5 shadow-inner animate-fade-in-up gap-4">
                        <template x-for="(plate, index) in result.plates" :key="index">
                            <div class="w-full max-w-sm">
                                <div class="license-plate-card mb-2">
                                    <span class="license-plate-label">License Plate Recognition</span>
                                    <span class="license-plate-text" x-text="plate.plate_text"></span>
                                </div>
                            </div>
                        </template>
                        <div class="flex items-center gap-2 text-primary-400 font-bold text-xs uppercase tracking-widest mt-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            OCR Analysis Complete
                        </div>
                    </div>
                </template>

                <h3 class="font-bold flex items-center gap-2 text-primary-100" x-text="result.event === 'frame' ? 'Latest Frame Output' : 'Processed Visual Output'">
                    <i data-lucide="image" class="w-5 h-5 text-primary-400"></i>
                </h3>
                <div class="rounded-xl overflow-hidden border border-white/10 shadow-lg relative group">
                    <div class="absolute inset-0 bg-primary-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    <img :src="`data:image/jpeg;base64,${result.processed_image}`" alt="Processed Output Image" class="w-full max-w-full h-auto object-cover">
                </div>
            </div>
        </template>

        <!-- Display Processed Output Video -->
        <template x-if="result && result.processed_video">
            <div class="space-y-3">
                <h3 class="font-bold flex items-center gap-2 text-primary-100">
                    <i data-lucide="video" class="w-5 h-5 text-primary-400"></i> Processed Video Output
                </h3>
                <div class="rounded-xl overflow-hidden border border-white/10 shadow-lg relative group">
                    <div class="absolute inset-0 bg-primary-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    <video controls autoplay loop class="w-full max-w-full h-auto bg-black">
                        <source :src="`data:${result.video_mime || 'video/webm'};base64,${result.processed_video}`" :type="result.video_mime || 'video/webm'">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </template>

        <!-- Display Crack/Pothole Segmentation Mask Image -->
        <template x-if="result && result.segmentation_image">
            <div class="space-y-3">
                <h3 class="font-bold flex items-center gap-2 text-primary-100" x-text="result.event === 'frame' ? 'Frame Segmentation' : 'Semantic Segmentation Mask'">
                    <i data-lucide="layers" class="w-5 h-5 text-primary-400"></i>
                </h3>
                <div class="rounded-xl overflow-hidden border border-white/10 shadow-lg relative group">
                    <div class="absolute inset-0 bg-primary-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    <img :src="`data:image/jpeg;base64,${result.segmentation_image}`" alt="Segmentation Result" class="w-full max-w-full h-auto object-cover">
                </div>
            </div>
        </template>
        
        <!-- Display Crack/Pothole Detection Bounding Boxes -->
        <template x-if="result && result.detection_image">
            <div class="space-y-3">
                <h3 class="font-bold flex items-center gap-2 text-primary-100" x-text="result.event === 'frame' ? 'Frame Detection Boxes' : 'Detection Output'">
                    <i data-lucide="focus" class="w-5 h-5 text-primary-400"></i>
                </h3>
                <div class="rounded-xl overflow-hidden border border-white/10 shadow-lg relative group">
                    <div class="absolute inset-0 bg-primary-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    <img :src="`data:image/jpeg;base64,${result.detection_image}`" alt="Detection Result" class="w-full max-w-full h-auto object-cover">
                </div>
            </div>
        </template>

        <!-- Raw JSON Telemetry (Hidden by default, available in console) -->
        <div x-init="console.log('ML Result Data:', result)" class="hidden"></div>
    </div>
</div>

<?php /**PATH /var/www/resources/views/components/result-card.blade.php ENDPATH**/ ?>