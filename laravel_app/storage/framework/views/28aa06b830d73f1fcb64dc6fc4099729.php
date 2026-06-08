

<?php $__env->startSection('title', 'Fire & Smoke'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-8 max-w-4xl mx-auto">
        <div class="text-center space-y-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-500/10 mb-2 shadow-[0_0_15px_rgba(52,211,153,0.2)]">
                <i data-lucide="flame" class="w-8 h-8 text-primary-400"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-primary-100 to-primary-400">
                Combustion Detection
            </h1>
            <p class="text-base text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Detect the presence of fire, smoke plumes, and hazardous environmental combustion indicating an active emergency.
            </p>
        </div>

        <div class="relative z-10 w-full animate-fade-in-up" style="animation-delay: 100ms;">
            <?php
                $endpoint = url('/api/ml/fire_smoke/detect');
            ?>

            <?php echo $__env->make('components.upload-box', ['endpoint' => $endpoint, 'requireLocation' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/fire_smoke.blade.php ENDPATH**/ ?>