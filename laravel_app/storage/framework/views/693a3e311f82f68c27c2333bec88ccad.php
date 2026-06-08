<?php $__env->startSection('title', 'System Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-12">
        <!-- Dashboard Header -->
        <div class="relative z-10 text-center max-w-3xl mx-auto space-y-4">
            <h1
                class="text-4xl md:text-5xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-br from-white via-slate-200 to-slate-500 pb-2">
                Ai-Powered Road Analytics
            </h1>
            <p class="text-lg text-slate-400 font-light leading-relaxed">
                Welcome to the Integrated Smart Road System. Select a machine learning module below to analyze imagery for
                specialized detections, ranging from license plate recognition to traffic accident monitoring.
            </p>
        </div>

        <!-- AI Analytics Section -->
        <div class="space-y-6 relative z-10">
            <!-- Analytics Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 border border-indigo-500/20">
                        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-white tracking-tight uppercase">AI Analytics</h2>
                        <p class="text-[9px] text-slate-500 font-bold uppercase tracking-[0.25em]">Model Usage Statistics
                        </p>
                    </div>
                </div>
                <span
                    class="text-[10px] font-black text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1.5 rounded-lg uppercase tracking-widest">
                    <?php echo e($totalPredictions); ?> Total Predictions
                </span>
            </div>

            <!-- Models Usage Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php $__currentLoopData = $predictionsByModel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modelStat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $modelNames = [
                            'plate' => 'License Plates',
                            'cracks' => 'Road Cracks',
                            'accident' => 'Accident Alert',
                            'fire_smoke' => 'Fire & Smoke',
                            'traffic' => 'Traffic Signs',
                            'vehicles' => 'Vehicles',
                            'car_damage' => 'Car Damage',
                            'dashboard' => 'Dashboard Lights',
                        ];
                        $colors = [
                            'plate' => ['bg' => 'bg-blue-500/10', 'text' => 'text-blue-400', 'border' => 'border-blue-500/20', 'icon' => 'scan-text'],
                            'cracks' => ['bg' => 'bg-rose-500/10', 'text' => 'text-rose-400', 'border' => 'border-rose-500/20', 'icon' => 'map'],
                            'accident' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'border' => 'border-red-500/20', 'icon' => 'car-front'],
                            'fire_smoke' => ['bg' => 'bg-orange-500/10', 'text' => 'text-orange-400', 'border' => 'border-orange-500/20', 'icon' => 'flame'],
                            'traffic' => ['bg' => 'bg-teal-500/10', 'text' => 'text-teal-400', 'border' => 'border-teal-500/20', 'icon' => 'signpost'],
                            'vehicles' => ['bg' => 'bg-purple-500/10', 'text' => 'text-purple-400', 'border' => 'border-purple-500/20', 'icon' => 'car'],
                            'car_damage' => ['bg' => 'bg-slate-500/10', 'text' => 'text-slate-400', 'border' => 'border-slate-500/20', 'icon' => 'wrench'],
                            'dashboard' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'icon' => 'gauge'],
                        ];
                        $color = $colors[$modelStat->model_type] ?? ['bg' => 'bg-white/5', 'text' => 'text-white', 'border' => 'border-white/10', 'icon' => 'activity'];
                    ?>
                    <div
                        class="glass-panel p-5 rounded-2xl border <?php echo e($color['border']); ?> relative overflow-hidden group hover:shadow-lg transition-all flex flex-col h-full">
                        <div class="flex items-start justify-between mb-4">
                            <div
                                class="w-10 h-10 rounded-lg <?php echo e($color['bg']); ?> flex items-center justify-center border <?php echo e($color['border']); ?>">
                                <i data-lucide="<?php echo e($color['icon']); ?>" class="w-5 h-5 <?php echo e($color['text']); ?>"></i>
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-[10px] font-black <?php echo e($color['text']); ?> uppercase tracking-widest block"><?php echo e($modelStat->count); ?>x</span>
                                <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest">Analyses</span>
                            </div>
                        </div>
                        <h3 class="text-sm font-black text-white mb-2">
                            <?php echo e($modelNames[$modelStat->model_type] ?? $modelStat->model_type); ?></h3>

                        <?php
                            $descriptions = [
                                'plate' => 'Detect plates and read alphanumeric characters via OCR.',
                                'cracks' => 'Monitor surface anomalies, potholes, and cracking.',
                                'accident' => 'Detect vehicular collisions and road incidents.',
                                'fire_smoke' => 'Spot active fires or dense smoke signatures.',
                                'traffic' => 'Classify regulatory, warning, and informational signs.',
                                'vehicles' => 'Classify vehicle types and count traffic flow.',
                                'car_damage' => 'Assess external vehicle damage for safety.',
                                'dashboard' => 'Identify warning indicators on instrument panels.',
                            ];
                            $route = $modelStat->model_type === 'dashboard' ? 'car_dashboard' : $modelStat->model_type;
                        ?>

                        <p class="text-[10px] text-slate-500 leading-relaxed mb-4 flex-1">
                            <?php echo e($descriptions[$modelStat->model_type] ?? 'Real-time AI analysis for road safety.'); ?>

                        </p>

                        <div class="flex items-center justify-between pt-4 border-t border-white/5 mt-auto">
                            <div class="flex flex-col">
                                <span class="text-[8px] text-slate-500 font-black uppercase tracking-widest">Confidence</span>
                                <span
                                    class="text-[11px] font-black <?php echo e($color['text']); ?>"><?php echo e(round($modelStat->avg_confidence ?? 0, 1)); ?>%</span>
                            </div>
                            <a href="<?php echo e(Route::has($route) ? route($route) : '#'); ?>"
                                class="px-3 py-1.5 rounded-lg <?php echo e($color['bg']); ?> <?php echo e($color['text']); ?> text-[9px] font-black uppercase tracking-widest hover:brightness-125 transition-all flex items-center gap-1">
                                Launch <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if(auth()->user()->role !== 'admin'): ?>
                    <!-- Car Management Analytics -->
                    <div
                        class="glass-panel p-5 rounded-2xl border border-orange-500/20 relative overflow-hidden group hover:shadow-lg transition-all flex flex-col h-full">
                        <div class="flex items-start justify-between mb-4">
                            <div
                                class="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center border border-orange-500/20">
                                <i data-lucide="banknote" class="w-5 h-5 text-orange-400"></i>
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-[10px] font-black text-orange-400 uppercase tracking-widest block"><?php echo e(number_format($carTotalExpenses)); ?></span>
                                <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest">EGP Total</span>
                            </div>
                        </div>
                        <h3 class="text-sm font-black text-white mb-2">Car Expenses</h3>
                        <p class="text-[10px] text-slate-500 leading-relaxed mb-4 flex-1">
                            Consolidated tracking of your fuel purchases and maintenance service costs.
                        </p>
                        <div class="flex items-center justify-between pt-4 border-t border-white/5 mt-auto">
                            <div class="flex flex-col">
                                <span class="text-[8px] text-slate-500 font-black uppercase tracking-widest">Type</span>
                                <span class="text-[11px] font-black text-orange-400">Financial</span>
                            </div>
                            <a href="<?php echo e(route('user.fuel.index')); ?>"
                                class="px-3 py-1.5 rounded-lg bg-orange-500/10 text-orange-400 text-[9px] font-black uppercase tracking-widest hover:brightness-125 transition-all flex items-center gap-1">
                                Manage <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </div>

                    <div
                        class="glass-panel p-5 rounded-2xl border border-teal-500/20 relative overflow-hidden group hover:shadow-lg transition-all flex flex-col h-full">
                        <div class="flex items-start justify-between mb-4">
                            <div
                                class="w-10 h-10 rounded-lg bg-teal-500/10 flex items-center justify-center border border-teal-500/20">
                                <i data-lucide="zap" class="w-5 h-5 text-teal-400"></i>
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-[10px] font-black text-teal-400 uppercase tracking-widest block"><?php echo e($avgFleetEfficiency); ?></span>
                                <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest">KM/L Avg</span>
                            </div>
                        </div>
                        <h3 class="text-sm font-black text-white mb-2">Fleet Efficiency</h3>
                        <p class="text-[10px] text-slate-500 leading-relaxed mb-4 flex-1">
                            Real-time calculation of your average fuel economy across all registered vehicles.
                        </p>
                        <div class="flex items-center justify-between pt-4 border-t border-white/5 mt-auto">
                            <div class="flex flex-col">
                                <span class="text-[8px] text-slate-500 font-black uppercase tracking-widest">Status</span>
                                <span class="text-[11px] font-black text-teal-400">Optimized</span>
                            </div>
                            <a href="<?php echo e(route('user.vehicles.index')); ?>"
                                class="px-3 py-1.5 rounded-lg bg-teal-500/10 text-teal-400 text-[9px] font-black uppercase tracking-widest hover:brightness-125 transition-all flex items-center gap-1">
                                Garage <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Admin Alert Distribution Plot (Spanning 2 columns to replace removed cards) -->
                    <div class="glass-panel p-5 rounded-2xl border border-red-500/20 relative overflow-hidden group hover:shadow-lg transition-all flex flex-col h-full lg:col-span-2">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center border border-red-500/20">
                                <i data-lucide="pie-chart" class="w-5 h-5 text-red-400"></i>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-black text-red-400 uppercase tracking-widest block"><?php echo e($alertsCountByType->sum('count')); ?>x Incidents</span>
                                <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest">System-wide Alerts</span>
                            </div>
                        </div>
                        
                        <div class="flex flex-row items-center gap-8 flex-1">
                            <div class="w-1/3 aspect-square max-h-[120px] relative">
                                <canvas id="alertsDistributionChart"></canvas>
                            </div>
                            <div class="flex-1 space-y-3">
                                <h3 class="text-sm font-black text-white">Alerts Distribution</h3>
                                <p class="text-[10px] text-slate-500 leading-relaxed">
                                    Real-time monitoring of road incidents across all active stations and camera sectors.
                                </p>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php $__currentLoopData = $alertsCountByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alertStat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full <?php echo e($alertStat->type === 'Fire' ? 'bg-red-500' : ($alertStat->type === 'Accident' ? 'bg-orange-500' : 'bg-purple-500')); ?>"></div>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase"><?php echo e($alertStat->type); ?>: <?php echo e($alertStat->count); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-white/5 mt-auto">
                            <div class="flex flex-col">
                                <span class="text-[8px] text-slate-500 font-black uppercase tracking-widest">Status</span>
                                <span class="text-[11px] font-black text-red-400">Security Gate Active</span>
                            </div>
                            <div class="px-3 py-1.5 rounded-lg bg-red-500/10 text-red-400 text-[9px] font-black uppercase tracking-widest flex items-center gap-1">
                                Live <div class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(auth()->user()->role === 'admin'): ?>
            <!-- Live System Monitor (Admins Only) -->
            <div class="mb-8 animate-fade-in-up" x-data="{ alertPage: 1, totalAlerts: <?php echo e($latestAlerts->count()); ?> }">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500 border border-red-500/20">
                            <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-white tracking-tight uppercase">Live System Monitor</h2>
                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-[0.25em]">Real-time Incident Feed</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1 bg-white/5 rounded-lg border border-white/10 p-1 mr-2" x-show="totalAlerts > 8">
                            <button @click="if(alertPage > 1) alertPage--" :disabled="alertPage === 1" class="p-1.5 rounded-md hover:bg-white/10 disabled:opacity-20 disabled:cursor-not-allowed transition-all">
                                <i data-lucide="chevron-left" class="w-3 h-3 text-white"></i>
                            </button>
                            <span class="text-[8px] font-black text-slate-500 px-2 uppercase tracking-widest" x-text="'Page ' + alertPage"></span>
                            <button @click="if(alertPage * 8 < totalAlerts) alertPage++" :disabled="alertPage * 8 >= totalAlerts" class="p-1.5 rounded-md hover:bg-white/10 disabled:opacity-20 disabled:cursor-not-allowed transition-all">
                                <i data-lucide="chevron-right" class="w-3 h-3 text-white"></i>
                            </button>
                        </div>
                        <?php $unreadCount = \App\Models\SystemAlert::where('is_read', false)->count(); ?>
                        <?php if($unreadCount > 0): ?>
                            <span class="text-[9px] font-black text-red-400 bg-red-500/10 border border-red-500/20 px-2 py-1 rounded-lg uppercase tracking-widest">
                                <?php echo e($unreadCount); ?> Unresolved
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 min-h-[160px]">
                    <?php $__empty_1 = true; $__currentLoopData = $latestAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $colors = [
                                'Fire'       => ['bg' => 'bg-red-500/10',    'text' => 'text-red-400',    'border' => 'border-red-500/20',    'icon' => 'flame'],
                                'Smoke'      => ['bg' => 'bg-slate-500/10',  'text' => 'text-slate-400',  'border' => 'border-slate-500/20',  'icon' => 'wind'],
                                'Accident'   => ['bg' => 'bg-orange-500/10', 'text' => 'text-orange-400', 'border' => 'border-orange-500/20', 'icon' => 'car'],
                                'Fake Plate' => ['bg' => 'bg-purple-500/10', 'text' => 'text-purple-400', 'border' => 'border-purple-500/20', 'icon' => 'user-minus'],
                            ];
                            $c = $colors[$alert->type] ?? ['bg' => 'bg-white/5', 'text' => 'text-white', 'border' => 'border-white/10', 'icon' => 'alert-circle'];
                            
                            $lat = $alert->details['latitude'] ?? null;
                            $lng = $alert->details['longitude'] ?? null;
                            $mapUrl = (isset($lat) && isset($lng) && is_numeric($lat) && is_numeric($lng)) 
                                ? "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}" 
                                : "https://www.google.com/maps/search/?api=1&query=" . urlencode($alert->location_text);
                        ?>
                        <a href="<?php echo e($mapUrl); ?>" target="_blank" 
                           x-show="<?php echo e($index); ?> >= (alertPage - 1) * 8 && <?php echo e($index); ?> < alertPage * 8"
                           x-transition:enter="transition ease-out duration-300"
                           x-transition:enter-start="opacity-0 translate-x-4"
                           x-transition:enter-end="opacity-100 translate-x-0"
                           class="glass-panel p-4 rounded-2xl border <?php echo e($c['border']); ?> relative overflow-hidden group hover:shadow-lg transition-all duration-300 hover:bg-white/5">
                            <div class="flex items-start justify-between mb-2">
                                <span class="<?php echo e($c['bg']); ?> <?php echo e($c['text']); ?> px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-widest border <?php echo e($c['border']); ?>">
                                    <?php echo e($alert->type); ?>

                                </span>
                                <div class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse mt-1"></div>
                            </div>
                            <div class="flex items-center gap-1.5 mt-2">
                                <i data-lucide="<?php echo e($c['icon']); ?>" class="w-3.5 h-3.5 <?php echo e($c['text']); ?> shrink-0"></i>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-bold text-white truncate group-hover:text-primary-400 transition-colors"><?php echo e($alert->location_text); ?></p>
                                    <?php if($alert->type === 'Fake Plate' && isset($alert->details['plate_text'])): ?>
                                        <p class="text-[9px] font-black text-purple-400 mt-0.5 tracking-wider">
                                            <span class="bg-purple-500/10 px-1 rounded border border-purple-500/20"><?php echo e($alert->details['plate_text']); ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-1.5">
                                <p class="text-[8px] text-slate-600 font-bold uppercase tracking-wider"><?php echo e($alert->created_at->diffForHumans()); ?></p>
                                <i data-lucide="external-link" class="w-3 h-3 text-slate-600 group-hover:text-primary-400 opacity-0 group-hover:opacity-100 transition-all"></i>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-span-full py-8 text-center glass-panel rounded-2xl border border-white/5 bg-white/[0.01]">
                            <i data-lucide="shield-check" class="w-6 h-6 text-slate-700 mx-auto mb-2"></i>
                            <p class="text-slate-600 text-[9px] font-black uppercase tracking-[0.2em]">No active incidents.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if(auth()->user()->role === 'user'): ?>
            <!-- User Financial Overview (Automated Toll System) -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 relative z-10 animate-fade-in-up">
                <!-- Balance Card -->
                <div
                    class="glass-panel p-8 rounded-3xl flex flex-col justify-between border-l-4 border-l-primary-500 shadow-[0_10px_40px_rgba(16,185,129,0.05)] relative overflow-hidden group">
                    <div
                        class="absolute -top-10 -right-10 w-32 h-32 bg-primary-500/5 rounded-full blur-3xl group-hover:bg-primary-500/10 transition-all duration-700">
                    </div>
                    <div>
                        <div class="flex items-center gap-2 text-slate-400 mb-3">
                            <i data-lucide="zap" class="w-4 h-4 text-primary-400"></i>
                            <h2 class="text-[10px] font-black uppercase tracking-[0.2em]">Points</h2>
                        </div>
                        <div class="text-5xl font-black text-white tracking-tighter">
                            <span class="text-primary-400"><?php echo e(number_format($user->balance, 0)); ?></span>
                        </div>
                    </div>
                    <div
                        class="mt-6 flex items-center gap-2 p-3 bg-white/[0.02] border border-white/5 rounded-2xl text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                        <i data-lucide="shield-check" class="w-4 h-4 text-primary-500"></i>
                        Automated Deduction Active
                    </div>
                </div>

                <!-- Recent History Table -->
                <div class="md:col-span-2 glass-panel p-8 rounded-3xl border border-white/5 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <i data-lucide="activity" class="w-4 h-4 text-indigo-400"></i>
                            <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Recent Toll Activity
                            </h2>
                        </div>
                        <span class="text-[9px] text-slate-600 font-bold uppercase tracking-widest italic cursor-help"
                            title="Last 5 verified gateway crossings">Verified Crossings</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="text-slate-500 border-b border-white/[0.05] text-[10px] font-black uppercase tracking-[0.1em]">
                                    <th class="pb-3 pl-2">Location</th>
                                    <th class="pb-3">Vehicle Plate</th>
                                    <th class="pb-3 text-center">Amount (EGP)</th>
                                    <th class="pb-3 text-right pr-2">Gateway Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.03]">
                                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="text-slate-300 hover:bg-white/[0.01] transition-colors">
                                        <td class="py-3 pl-2 text-xs font-bold"><?php echo e($tx->station_name); ?></td>
                                        <td class="py-3 text-[11px] font-mono tracking-wider text-indigo-300">
                                            <?php echo e($tx->plate_number); ?></td>
                                        <td class="py-3 text-center text-sm font-black"><?php echo e(number_format($tx->amount, 0)); ?>.00</td>
                                        <td class="py-3 text-right pr-2">
                                            <span
                                                class="<?php echo e($tx->status === 'success' ? 'bg-primary-500/10 text-primary-400 border-primary-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'); ?> font-black uppercase text-[8px] tracking-[0.15em] border px-2 py-0.5 rounded-md">
                                                <?php echo e($tx->status); ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4"
                                            class="py-10 text-center text-slate-600 text-xs font-medium uppercase tracking-[0.2em]">
                                            No recent system activity detected</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Car Expense Distribution Chart -->
                <div
                    class="glass-panel p-6 rounded-3xl border border-white/5 relative overflow-hidden flex flex-col items-center justify-center">
                    <div class="flex items-center gap-2 text-slate-400 mb-6 self-start">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-orange-400"></i>
                        <h2 class="text-[10px] font-black uppercase tracking-[0.2em]">Expense Mix</h2>
                    </div>
                    <div class="w-full aspect-square max-w-[140px] relative">
                        <canvas id="dashboardExpenseChart"></canvas>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="text-center">
                                <span
                                    class="text-[10px] font-black text-white block leading-none"><?php echo e(number_format($carTotalExpenses)); ?></span>
                                <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest">EGP</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 w-full space-y-2">
                        <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-widest">
                            <span class="flex items-center gap-2 text-orange-400"><span
                                    class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Fuel</span>
                            <span class="text-white"><?php echo e(number_format($fuelTotal)); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-widest">
                            <span class="flex items-center gap-2 text-indigo-400"><span
                                    class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Service</span>
                            <span class="text-white"><?php echo e(number_format($maintenanceTotal)); ?></span>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Predictions Log -->
        <?php if($recentPredictions->isNotEmpty()): ?>
            <div class="relative z-10" x-data="{ 
                imageModalOpen: false, 
                resultsModalOpen: false,
                selectedPred: null,
                predPage: 1,
                totalPreds: <?php echo e($recentPredictions->count()); ?>,
                showImage(pred) {
                    this.selectedPred = pred;
                    this.imageModalOpen = true;
                },
                showResults(pred) {
                    this.selectedPred = pred;
                    this.resultsModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                }
            }">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500 border border-purple-500/20">
                            <i data-lucide="history" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-white tracking-tight uppercase">Recent Predictions</h2>
                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-[0.25em]">Latest Analysis Results</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1 bg-white/5 rounded-lg border border-white/10 p-1" x-show="totalPreds > 8">
                        <button @click="if(predPage > 1) predPage--" :disabled="predPage === 1" class="p-1.5 rounded-md hover:bg-white/10 disabled:opacity-20 disabled:cursor-not-allowed transition-all">
                            <i data-lucide="chevron-left" class="w-3 h-3 text-white"></i>
                        </button>
                        <span class="text-[8px] font-black text-slate-500 px-2 uppercase tracking-widest" x-text="'Page ' + predPage"></span>
                        <button @click="if(predPage * 8 < totalPreds) predPage++" :disabled="predPage * 8 >= totalPreds" class="p-1.5 rounded-md hover:bg-white/10 disabled:opacity-20 disabled:cursor-not-allowed transition-all">
                            <i data-lucide="chevron-right" class="w-3 h-3 text-white"></i>
                        </button>
                    </div>
                </div>

                <div class="glass-panel rounded-2xl border border-white/5 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="text-slate-500 border-b border-white/[0.05] text-[10px] font-black uppercase tracking-[0.1em] bg-white/[0.02]">
                                    <th class="px-4 py-3">Visual</th>
                                    <th class="px-4 py-3">Model</th>
                                    <th class="px-4 py-3">Results</th>
                                    <?php if(auth()->user()->role === 'admin'): ?>
                                        <th class="px-4 py-3">User</th>
                                    <?php endif; ?>
                                    <th class="px-4 py-3 text-center">Confidence</th>
                                    <th class="px-4 py-3">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.03]">
                                <?php $__currentLoopData = $recentPredictions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pred): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $modelNames = [
                                            'plate' => 'License Plates',
                                            'cracks' => 'Road Cracks',
                                            'accident' => 'Accident',
                                            'fire_smoke' => 'Fire & Smoke',
                                            'traffic' => 'Traffic Signs',
                                            'vehicles' => 'Vehicles',
                                            'car_damage' => 'Car Damage',
                                            'dashboard' => 'Dashboard',
                                        ];
                                        $colors = [
                                            'plate' => 'text-blue-400',
                                            'cracks' => 'text-rose-400',
                                            'accident' => 'text-red-400',
                                            'fire_smoke' => 'text-orange-400',
                                            'traffic' => 'text-teal-400',
                                            'vehicles' => 'text-purple-400',
                                            'car_damage' => 'text-slate-400',
                                            'dashboard' => 'text-amber-400',
                                        ];

                                        // Prepare clean summary and list
                                        $summary = 'No results';
                                        $detailsList = [];
                                        if (isset($pred->prediction_result['detections']) && count($pred->prediction_result['detections']) > 0) {
                                            $counts = [];
                                            foreach ($pred->prediction_result['detections'] as $d) {
                                                $label = $d['label'] ?? $d['class'] ?? 'item';
                                                $counts[$label] = ($counts[$label] ?? 0) + 1;
                                            }
                                            $summaryArr = [];
                                            foreach ($counts as $label => $count) {
                                                $text = $count . 'x ' . ucfirst(str_replace('_', ' ', $label));
                                                $summaryArr[] = $text;
                                                $detailsList[] = ['label' => ucfirst(str_replace('_', ' ', $label)), 'count' => $count];
                                            }
                                            $summary = implode(', ', $summaryArr);
                                        } elseif (isset($pred->prediction_result['class_name'])) {
                                            $summary = ucfirst($pred->prediction_result['class_name']);
                                            $detailsList[] = ['label' => $summary, 'count' => 1];
                                        } elseif (isset($pred->prediction_result['plates']) && count($pred->prediction_result['plates']) > 0) {
                                            $summary = 'Plate: ' . ($pred->prediction_result['plates'][0]['plate_text'] ?? 'Unknown');
                                            $detailsList[] = ['label' => 'License Plate', 'value' => ($pred->prediction_result['plates'][0]['plate_text'] ?? 'Unknown'), 'count' => 1];
                                        }

                                        $clientData = [
                                            'id' => $pred->id,
                                            'model' => $modelNames[$pred->model_type] ?? $pred->model_type,
                                            'image' => $pred->image_path ? asset('storage/' . $pred->image_path) : null,
                                            'details' => $detailsList,
                                            'confidence' => number_format($pred->confidence_score, 1) . '%',
                                            'timestamp' => $pred->created_at->format('M d, Y H:i'),
                                        ];
                                    ?>
                                    <tr class="text-slate-300 hover:bg-white/[0.02] transition-colors" 
                                        x-show="<?php echo e($index); ?> >= (predPage - 1) * 8 && <?php echo e($index); ?> < predPage * 8"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0">
                                        <td class="px-4 py-3">
                                            <?php if($pred->image_path): ?>
                                                <div @click="showImage(<?php echo e(json_encode($clientData)); ?>)"
                                                    class="w-12 h-12 rounded-lg bg-slate-800 border border-white/10 overflow-hidden group/img relative cursor-pointer">
                                                    <img src="<?php echo e(asset('storage/' . $pred->image_path)); ?>" alt="Prediction"
                                                        class="w-full h-full object-cover">
                                                    <div
                                                        class="absolute inset-0 bg-black/40 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center">
                                                        <i data-lucide="maximize-2" class="w-4 h-4 text-white"></i>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div
                                                    class="w-12 h-12 rounded-lg bg-slate-800/50 border border-white/5 flex items-center justify-center">
                                                    <i data-lucide="image-off" class="w-4 h-4 text-slate-700"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col">
                                                <span class="font-semibold <?php echo e($colors[$pred->model_type] ?? 'text-white'); ?>">
                                                    <?php echo e($modelNames[$pred->model_type] ?? $pred->model_type); ?>

                                                </span>
                                                <span
                                                    class="text-[9px] text-slate-500 uppercase tracking-tighter"><?php echo e(ucfirst($pred->input_type)); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-[11px] cursor-pointer group"
                                            @click="showResults(<?php echo e(json_encode($clientData)); ?>)">
                                            <div class="max-w-[200px] truncate font-medium text-slate-400 flex items-center gap-2">
                                                <?php echo e($summary); ?>

                                                <i data-lucide="external-link"
                                                    class="w-3 h-3 text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                            </div>
                                        </td>
                                        <?php if(auth()->user()->role === 'admin'): ?>
                                            <td class="px-4 py-3 text-[11px]">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="w-5 h-5 rounded-full bg-indigo-500/10 flex items-center justify-center text-[10px] text-indigo-400 border border-indigo-500/20">
                                                        <?php echo e(substr($pred->user->name, 0, 1)); ?>

                                                    </div>
                                                    <?php echo e($pred->user->name); ?>

                                                </div>
                                            </td>
                                        <?php endif; ?>
                                        <td class="px-4 py-3 text-center">
                                            <?php if($pred->confidence_score): ?>
                                                <div class="flex flex-col items-center">
                                                    <span
                                                        class="font-bold <?php echo e($pred->confidence_score >= 80 ? 'text-green-400' : ($pred->confidence_score >= 60 ? 'text-yellow-400' : 'text-red-400')); ?>">
                                                        <?php echo e(number_format($pred->confidence_score, 1)); ?>%
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-500">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-[11px] text-slate-400">
                                            <?php echo e($pred->created_at->format('M d, H:i')); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 1. Full Image Modal -->
                <template x-teleport="body">
                    <div x-show="imageModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-10"
                        x-cloak>
                        <div x-show="imageModalOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" @click="imageModalOpen = false"
                            class="fixed inset-0 bg-black/90 backdrop-blur-md"></div>

                        <div x-show="imageModalOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95" class="relative max-w-full max-h-full">
                            <button @click="imageModalOpen = false"
                                class="absolute -top-12 right-0 text-white/50 hover:text-white transition-colors flex items-center gap-2 text-xs uppercase font-black tracking-widest">
                                Close <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                            <img :src="selectedPred ? selectedPred.image : ''"
                                class="max-w-full max-h-[85vh] rounded-2xl shadow-2xl border border-white/10 object-contain">
                        </div>
                    </div>
                </template>

                <!-- 2. Clean Results Modal -->
                <template x-teleport="body">
                    <div x-show="resultsModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
                        <div x-show="resultsModalOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" @click="resultsModalOpen = false"
                            class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

                        <div x-show="resultsModalOpen" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95 translateY(20px)"
                            x-transition:enter-end="opacity-100 scale-100 translateY(0)"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100 translateY(0)"
                            x-transition:leave-end="opacity-0 scale-95 translateY(20px)"
                            class="relative w-full max-w-md glass-panel rounded-3xl border border-white/10 p-8 shadow-2xl bg-dark-800 flex flex-col max-h-[85vh]">
                            <div class="flex justify-between items-start mb-8 shrink-0">
                                <div>
                                    <h3 class="text-xl font-black text-white" x-text="selectedPred ? selectedPred.model : ''">
                                    </h3>
                                    <p class="text-[9px] text-slate-500 font-bold uppercase tracking-[0.2em]"
                                        x-text="selectedPred ? selectedPred.timestamp : ''"></p>
                                </div>
                                <button @click="resultsModalOpen = false"
                                    class="text-slate-500 hover:text-white transition-colors">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <div class="space-y-3 mb-8 overflow-y-auto pr-2 custom-scrollbar flex-1">
                                <template x-for="item in (selectedPred ? selectedPred.details : [])">
                                    <div
                                        class="flex items-center justify-between p-4 bg-white/[0.03] border border-white/5 rounded-2xl group hover:border-indigo-500/30 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400 border border-indigo-500/20 group-hover:bg-indigo-500/20">
                                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-200" x-text="item.label"></p>
                                                <p x-if="item.value" class="text-[10px] text-indigo-400 font-mono"
                                                    x-text="item.value"></p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-black text-white bg-white/5 px-3 py-1 rounded-full"
                                            x-text="item.count + 'x'"></span>
                                    </div>
                                </template>
                            </div>

                            <div
                                class="flex items-center justify-between p-4 bg-primary-500/5 border border-primary-500/10 rounded-2xl shrink-0">
                                <span class="text-[10px] font-black text-primary-400 uppercase tracking-widest">Average
                                    Confidence</span>
                                <span class="text-lg font-black text-primary-400"
                                    x-text="selectedPred ? selectedPred.confidence : ''"></span>
                            </div>

                            <button @click="resultsModalOpen = false"
                                class="w-full mt-8 py-4 bg-white text-dark-900 rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-slate-200 transition-all active:scale-[0.98] shrink-0">
                                Close Analysis
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Alerts Distribution Chart (Admin Only)
            const alertsCtx = document.getElementById('alertsDistributionChart');
            if (alertsCtx) {
                new Chart(alertsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($alertsCountByType->pluck('type')); ?>,
                        datasets: [{
                            data: <?php echo json_encode($alertsCountByType->pluck('count')); ?>,
                            backgroundColor: [
                                '#ef4444', // Red for Fire
                                '#f97316', // Orange for Accident
                                '#a855f7', // Purple for Fake Plate
                                '#6366f1'  // Indigo fallback
                            ],
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            const ctx = document.getElementById('dashboardExpenseChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Fuel', 'Maintenance'],
                        datasets: [{
                            data: [<?php echo e($fuelTotal); ?>, <?php echo e($maintenanceTotal); ?>],
                            backgroundColor: ['#f97316', '#6366f1'],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '80%',
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/dashboard.blade.php ENDPATH**/ ?>