@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-12 pb-20">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-primary-500/10 flex items-center justify-center text-primary-400 border border-primary-500/20 shadow-lg shadow-primary-500/5">
                    <i data-lucide="lightbulb" class="w-6 h-6"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Pro Tips</h1>
            </div>
            <p class="text-slate-400 font-medium max-w-xl">Expert advice to help you save money on fuel and keep your vehicle in showroom condition.</p>
        </div>
    </div>

    <!-- Tips Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($tips as $tip)
            <div class="glass-panel rounded-[2.5rem] border border-white/5 p-8 space-y-6 hover:border-primary-500/30 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-primary-500/10 border border-primary-500/20 flex items-center justify-center text-primary-400 group-hover:scale-110 transition-transform duration-500">
                    <i data-lucide="sparkles" class="w-7 h-7"></i>
                </div>
                <div class="space-y-3">
                    <h3 class="text-xl font-black text-white leading-tight uppercase italic tracking-tighter">{{ $tip['title'] }}</h3>
                    <p class="text-sm text-slate-500 leading-relaxed font-medium">{{ $tip['desc'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
