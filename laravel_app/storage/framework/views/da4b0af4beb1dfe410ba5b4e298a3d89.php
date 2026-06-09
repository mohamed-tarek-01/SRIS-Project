<?php $__env->startSection('title', 'My Wallet & Points'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-8">
    
    <!-- Header & Balance -->
    <div class="flex flex-col md:flex-row gap-6 items-center justify-between animate-fade-in-up">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Wallet & Points</h1>
            <p class="text-slate-400 mt-1">Manage your balance for automated toll gate payments.</p>
        </div>
        <div class="glass-panel p-6 rounded-3xl border border-primary-500/30 bg-primary-500/5 flex items-center gap-6 shadow-[0_0_30px_rgba(16,185,129,0.1)]">
            <div class="w-14 h-14 rounded-full bg-primary-500/20 flex items-center justify-center">
                <i data-lucide="coins" class="w-7 h-7 text-primary-400"></i>
            </div>
            <div>
                <p class="text-xs font-black text-primary-400/70 uppercase tracking-widest mb-1">Available Points</p>
                <p class="text-4xl font-black text-white"><?php echo e(number_format($user->balance ?? 0)); ?></p>
            </div>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="glass-panel p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 flex items-center gap-3 animate-fade-in-up">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="font-bold text-sm"><?php echo e(session('success')); ?></span>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="glass-panel p-4 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-400 flex flex-col gap-2 animate-fade-in-up">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span class="font-bold text-sm"><?php echo e($error); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Top Up Form -->
        <div class="glass-panel p-8 rounded-3xl border border-white/5 animate-fade-in-up" style="animation-delay: 100ms;">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <i data-lucide="arrow-up-circle" class="text-blue-400"></i> Top Up Balance
            </h2>
            
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-2xl p-5 mb-6 text-sm text-blue-200">
                <p class="font-bold text-white mb-2 uppercase tracking-wide text-xs">Payment Instructions</p>
                <p class="mb-3">Please transfer the desired amount via <strong>InstaPay</strong> to the following address:</p>
                <div class="bg-black/40 rounded-xl p-4 flex items-center justify-between mb-3 border border-white/5">
                    <span class="font-mono text-lg font-bold text-white tracking-wider">01012345678</span>
                    <button class="text-blue-400 hover:text-white transition-colors p-2 bg-white/5 rounded-lg">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-xs text-blue-300 font-medium"><i data-lucide="info" class="w-3 h-3 inline mr-1"></i> Conversion Rate: <strong>1 EGP = 1 Point</strong></p>
            </div>

            <form action="<?php echo e(route('user.wallet.upload')); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Requested Points (EGP)</label>
                    <input type="number" name="requested_points" min="10" max="10000" required placeholder="e.g. 50" class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Upload Transfer Receipt</label>
                    <div class="relative group">
                        <input type="file" name="receipt_image" accept="image/*" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="w-full bg-black/20 border-2 border-dashed border-white/10 rounded-xl px-4 py-8 text-center group-hover:border-blue-500/50 group-hover:bg-blue-500/5 transition-all">
                            <i data-lucide="image-plus" class="w-8 h-8 text-slate-500 mx-auto mb-3 group-hover:text-blue-400 transition-colors"></i>
                            <span class="text-sm font-bold text-slate-400 group-hover:text-blue-300">Click or drag receipt image here</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-blue-500/25 hover:scale-[1.02] transition-all flex justify-center items-center gap-2">
                    <i data-lucide="send" class="w-5 h-5"></i> Submit For Review
                </button>
            </form>
        </div>

        <!-- Request History -->
        <div class="glass-panel p-8 rounded-3xl border border-white/5 animate-fade-in-up" style="animation-delay: 200ms;">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <i data-lucide="history" class="text-slate-400"></i> Request History
            </h2>

            <?php if($receipts->count() > 0): ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receipt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-black/20 border border-white/5 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-800 overflow-hidden shrink-0 border border-white/10 relative group">
                                    <img src="<?php echo e(asset('storage/' . $receipt->image_path)); ?>" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity">
                                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                        <i data-lucide="eye" class="w-4 h-4 text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-white">+<?php echo e(number_format($receipt->requested_points)); ?> Points</p>
                                    <p class="text-xs text-slate-500"><?php echo e($receipt->created_at->format('M d, Y h:i A')); ?></p>
                                </div>
                            </div>
                            
                            <?php if($receipt->status === 'pending'): ?>
                                <span class="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider">Pending</span>
                            <?php elseif($receipt->status === 'approved'): ?>
                                <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold uppercase tracking-wider">Approved</span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-bold uppercase tracking-wider">Rejected</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="receipt" class="w-8 h-8 text-slate-600"></i>
                    </div>
                    <p class="text-slate-400 font-medium">No top-up requests yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/user/wallet/index.blade.php ENDPATH**/ ?>