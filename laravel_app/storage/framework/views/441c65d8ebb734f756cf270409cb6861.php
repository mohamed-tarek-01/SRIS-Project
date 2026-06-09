<?php $__env->startSection('title', 'Payment Receipts Verification'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto space-y-8">
    
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between mb-8 animate-fade-in-up">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <i data-lucide="banknote" class="w-8 h-8 text-primary-400"></i>
                Verify Payments
            </h1>
            <p class="text-slate-400 mt-1">Review user transfer screenshots and grant toll gate points.</p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="glass-panel p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 flex items-center gap-3 animate-fade-in-up">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="font-bold text-sm"><?php echo e(session('success')); ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in-up" style="animation-delay: 100ms;">
        <?php $__empty_1 = true; $__currentLoopData = $receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receipt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="glass-panel rounded-3xl border border-white/5 overflow-hidden flex flex-col group relative <?php echo e($receipt->status === 'pending' ? 'ring-1 ring-primary-500/30' : 'opacity-75'); ?>">
                
                <!-- Receipt Image Viewer -->
                <div class="h-48 bg-slate-900 relative overflow-hidden group-hover:h-64 transition-all duration-500 cursor-pointer" onclick="window.open('<?php echo e(asset('storage/' . $receipt->image_path)); ?>', '_blank')">
                    <img src="<?php echo e(asset('storage/' . $receipt->image_path)); ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500">
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="bg-black/80 text-white text-xs font-bold px-4 py-2 rounded-full backdrop-blur-md flex items-center gap-2">
                            <i data-lucide="maximize" class="w-4 h-4"></i> View Full Image
                        </span>
                    </div>
                    
                    <?php if($receipt->status === 'pending'): ?>
                        <div class="absolute top-4 right-4 bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i> Pending
                        </div>
                    <?php elseif($receipt->status === 'approved'): ?>
                        <div class="absolute top-4 right-4 bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i> Approved
                        </div>
                    <?php else: ?>
                        <div class="absolute top-4 right-4 bg-rose-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                            <i data-lucide="x" class="w-3 h-3"></i> Rejected
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-white"><?php echo e($receipt->user->name); ?></h3>
                            <p class="text-xs text-slate-400"><?php echo e($receipt->user->email); ?></p>
                            <p class="text-[10px] text-slate-500 mt-1"><?php echo e($receipt->created_at->diffForHumans()); ?></p>
                        </div>
                        <div class="text-right">
                            <span class="block text-2xl font-black text-primary-400"><?php echo e($receipt->requested_points); ?></span>
                            <span class="text-[9px] uppercase tracking-widest text-slate-500 font-bold">Req. Points</span>
                        </div>
                    </div>

                    <?php if($receipt->status === 'pending'): ?>
                        <div class="mt-auto space-y-3 pt-4 border-t border-white/5">
                            <form action="<?php echo e(route('admin.payments.approve', $receipt)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="flex items-center gap-2 mb-3">
                                    <input type="number" name="granted_points" value="<?php echo e($receipt->requested_points); ?>" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white font-bold text-center focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/50">
                                </div>
                                <button type="submit" class="w-full bg-emerald-500/10 hover:bg-emerald-500 border border-emerald-500/20 hover:border-emerald-500 text-emerald-400 hover:text-white transition-all rounded-xl py-2.5 text-xs font-black uppercase tracking-widest flex justify-center items-center gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i> Approve & Add Points
                                </button>
                            </form>

                            <form action="<?php echo e(route('admin.payments.reject', $receipt)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-full bg-rose-500/5 hover:bg-rose-500/20 border border-transparent hover:border-rose-500/30 text-rose-400 transition-all rounded-xl py-2 text-xs font-bold flex justify-center items-center gap-2">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i> Reject Receipt
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="mt-auto pt-4 border-t border-white/5">
                            <p class="text-xs text-slate-400">
                                <span class="font-bold text-white">Admin Note:</span> <?php echo e($receipt->admin_notes); ?>

                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full text-center py-16 glass-panel rounded-3xl border border-white/5">
                <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-square" class="w-10 h-10 text-slate-600"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">All Caught Up!</h3>
                <p class="text-slate-400">There are no pending payment receipts to verify.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/payments/index.blade.php ENDPATH**/ ?>