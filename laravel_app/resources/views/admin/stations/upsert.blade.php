@extends('layouts.app')

@section('title', isset($station) ? 'Edit Toll Gate' : 'New Toll Gate')

@section('content')
<div class="flex items-center justify-center min-h-[75vh] py-10">
    <div class="glass-panel p-10 rounded-[2.5rem] w-full max-w-2xl relative z-10 border border-indigo-500/10 shadow-[0_0_50px_rgba(79,70,229,0.1)]">

        <!-- Glow -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        <!-- Header -->
        <div class="flex items-center gap-6 mb-10 relative z-10">
            <div class="w-16 h-16 rounded-[1.25rem] bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                <i data-lucide="{{ isset($station) ? 'settings' : 'plus-square' }}" class="w-8 h-8"></i>
            </div>
            <div>
                <h2 class="text-4xl font-extrabold text-white leading-tight">
                    {{ isset($station) ? 'Edit Toll Gate' : 'New Toll Gate' }}
                </h2>
                <p class="text-slate-500 text-xs font-black uppercase tracking-[0.2em] mt-1">
                    {{ isset($station) ? 'Update name, location and price' : 'Register new toll gate infrastructure' }}
                </p>
            </div>
        </div>

        <!-- Errors -->
        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-[11px] p-5 rounded-2xl mb-8 flex items-start gap-4">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                <ul class="space-y-1 font-bold uppercase tracking-wider">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ isset($station) ? route('admin.stations.update', $station) : route('admin.stations.store') }}"
              class="space-y-6 relative z-10">
            @csrf
            @if(isset($station))
                @method('PUT')
            @endif

            <!-- Gate Name -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] ml-1">Gate Name</label>
                <div class="relative">
                    <i data-lucide="info" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500"></i>
                    <input
                        type="text"
                        name="name"
                        id="station_name"
                        value="{{ old('name', $station->name ?? '') }}"
                        required
                        placeholder="e.g. Helwan Toll Gate"
                        class="w-full bg-white/[0.04] border border-white/10 rounded-2xl pl-14 pr-5 py-4 text-white placeholder-slate-600 outline-none focus:border-indigo-500/50 focus:bg-white/[0.06] transition-all font-bold text-sm">
                </div>
            </div>

            <!-- Location -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] ml-1">Location / Address</label>
                <div class="relative">
                    <i data-lucide="map-pin" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500"></i>
                    <input
                        type="text"
                        name="location"
                        id="station_location"
                        value="{{ old('location', $station->location ?? '') }}"
                        placeholder="e.g. Km 22, Alex Desert Road"
                        class="w-full bg-white/[0.04] border border-white/10 rounded-2xl pl-14 pr-5 py-4 text-white placeholder-slate-600 outline-none focus:border-indigo-500/50 focus:bg-white/[0.06] transition-all font-bold text-sm">
                </div>
            </div>

            <!-- Price -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] ml-1">Deduction Price (Points)</label>
                <div class="relative">
                    <i data-lucide="coins" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500"></i>
                    <input
                        type="number"
                        name="price"
                        id="station_price"
                        value="{{ old('price', $station->price ?? 0) }}"
                        min="0"
                        required
                        class="w-full bg-white/[0.04] border border-white/10 rounded-2xl pl-14 pr-5 py-4 text-white outline-none focus:border-indigo-500/50 focus:bg-white/[0.06] transition-all font-bold text-sm">
                </div>
            </div>

            {{-- GPS coordinates: shown read-only only when editing. On create, sent as hidden empty --}}
            @if(isset($station))
                <!-- Coordinates (Read-Only, set by GPS on creation) -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1 flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3"></i> Latitude (GPS Only)
                        </label>
                        <div class="relative">
                            <i data-lucide="crosshair" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-700"></i>
                            <input
                                type="text"
                                name="latitude"
                                value="{{ $station->latitude ?? '—' }}"
                                readonly
                                tabindex="-1"
                                class="w-full bg-black/20 border border-white/[0.03] rounded-2xl pl-14 pr-5 py-4 text-slate-600 outline-none cursor-not-allowed font-mono text-sm select-none">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] ml-1 flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3"></i> Longitude (GPS Only)
                        </label>
                        <div class="relative">
                            <i data-lucide="crosshair" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-700"></i>
                            <input
                                type="text"
                                name="longitude"
                                value="{{ $station->longitude ?? '—' }}"
                                readonly
                                tabindex="-1"
                                class="w-full bg-black/20 border border-white/[0.03] rounded-2xl pl-14 pr-5 py-4 text-slate-600 outline-none cursor-not-allowed font-mono text-sm select-none">
                        </div>
                    </div>
                </div>
                <p class="text-[10px] text-slate-600 font-bold uppercase tracking-widest text-center">
                    <i data-lucide="info" class="inline w-3 h-3 mr-1"></i>
                    GPS coordinates are locked and cannot be modified manually.
                </p>
            @else
                {{-- Hidden on create, will be filled in via the dynamic setup form on the Plate Scanner page --}}
                <input type="hidden" name="latitude" value="">
                <input type="hidden" name="longitude" value="">
            @endif

            <!-- Buttons -->
            <div class="pt-4 flex gap-4">
                <a href="{{ route('admin.stations.index') }}"
                   class="flex-1 py-4 px-6 rounded-2xl bg-white/[0.02] border border-white/5 text-slate-400 font-bold uppercase tracking-[0.2em] text-[10px] text-center hover:bg-white/5 hover:text-slate-200 transition-all">
                    Cancel
                </a>
                <button type="submit"
                    class="flex-1 py-4 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-400 hover:from-indigo-500 hover:to-indigo-300 text-white font-black uppercase tracking-[0.2em] text-[10px] shadow-[0_0_40px_rgba(79,70,229,0.2)] transition-all transform hover:-translate-y-0.5 active:scale-95 border border-white/10">
                    {{ isset($station) ? 'Save Changes' : 'Create Gate' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection