@extends('layouts.app')

@section('title', 'Payment Receipts Verification')

@section('content')
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

    @if(session('success'))
        <div class="glass-panel p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 flex items-center gap-3 animate-fade-in-up">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in-up" style="animation-delay: 100ms;">
        @forelse($receipts as $receipt)
            <div class="glass-panel rounded-3xl border border-white/5 overflow-hidden flex flex-col group relative {{ $receipt->status === 'pending' ? 'ring-1 ring-primary-500/30' : 'opacity-75' }}">
                
                <!-- Receipt Image Viewer -->
                <div class="h-48 bg-slate-900 relative overflow-hidden group-hover:h-64 transition-all duration-500 cursor-pointer" onclick="window.open('{{ asset('storage/' . $receipt->image_path) }}', '_blank')">
                    <img src="{{ asset('storage/' . $receipt->image_path) }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500">
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="bg-black/80 text-white text-xs font-bold px-4 py-2 rounded-full backdrop-blur-md flex items-center gap-2">
                            <i data-lucide="maximize" class="w-4 h-4"></i> View Full Image
                        </span>
                    </div>
                    
                    @if($receipt->status === 'pending')
                        <div class="absolute top-4 right-4 bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i> Pending
                        </div>
                    @elseif($receipt->status === 'approved')
                        <div class="absolute top-4 right-4 bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i> Approved
                        </div>
                    @else
                        <div class="absolute top-4 right-4 bg-rose-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                            <i data-lucide="x" class="w-3 h-3"></i> Rejected
                        </div>
                    @endif
                </div>

                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ $receipt->user->name }}</h3>
                            <p class="text-xs text-slate-400">{{ $receipt->user->email }}</p>
                            <p class="text-[10px] text-slate-500 mt-1">{{ $receipt->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <span class="block text-2xl font-black text-primary-400">{{ $receipt->requested_points }}</span>
                            <span class="text-[9px] uppercase tracking-widest text-slate-500 font-bold">Req. Points</span>
                        </div>
                    </div>

                    @if($receipt->status === 'pending')
                        <div class="mt-auto space-y-3 pt-4 border-t border-white/5">
                            <form action="{{ route('admin.payments.approve', $receipt) }}" method="POST">
                                @csrf
                                <div class="flex items-center gap-2 mb-3">
                                    <input type="number" name="granted_points" value="{{ $receipt->requested_points }}" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white font-bold text-center focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/50">
                                </div>
                                <button type="submit" class="w-full bg-emerald-500/10 hover:bg-emerald-500 border border-emerald-500/20 hover:border-emerald-500 text-emerald-400 hover:text-white transition-all rounded-xl py-2.5 text-xs font-black uppercase tracking-widest flex justify-center items-center gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i> Approve & Add Points
                                </button>
                            </form>

                            <form action="{{ route('admin.payments.reject', $receipt) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-rose-500/5 hover:bg-rose-500/20 border border-transparent hover:border-rose-500/30 text-rose-400 transition-all rounded-xl py-2 text-xs font-bold flex justify-center items-center gap-2">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i> Reject Receipt
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="mt-auto pt-4 border-t border-white/5">
                            <p class="text-xs text-slate-400">
                                <span class="font-bold text-white">Admin Note:</span> {{ $receipt->admin_notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 glass-panel rounded-3xl border border-white/5">
                <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-square" class="w-10 h-10 text-slate-600"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">All Caught Up!</h3>
                <p class="text-slate-400">There are no pending payment receipts to verify.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
