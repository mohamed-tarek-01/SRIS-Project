@extends('layouts.app')

@section('title', 'Toll Station Management')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400 font-sans tracking-tight">Station Management</h1>
            <p class="text-slate-400 mt-1 text-sm font-light">Configure and monitor physical toll collection gateways throughout the logistics network.</p>
        </div>
        <a href="{{ route('admin.stations.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-white/10 to-white/[0.05] hover:from-white/20 hover:to-white/10 text-white font-semibold rounded-xl border border-white/10 shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-95 group">
            <i data-lucide="plus-circle" class="w-5 h-5 group-hover:rotate-90 transition-transform duration-500"></i>
            <span>Register New Station</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-primary-500/10 border border-primary-500/20 text-primary-400 p-4 rounded-xl flex items-center gap-3 animate-fade-in-up shadow-[0_0_20px_rgba(16,185,129,0.05)]">
            <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="glass-panel rounded-2xl overflow-hidden border border-white/5 relative">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/[0.03] border-b border-white/10 text-slate-400">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] w-1/3">Station Name</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] w-1/3">Physical Location</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.05]">
                    @foreach($stations as $station)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center text-indigo-400 border border-white/5 group-hover:border-indigo-500/30 transition-all duration-300">
                                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-sm font-bold text-white tracking-wide group-hover:text-indigo-400 transition-colors">{{ $station->name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2 text-slate-400">
                                    <span class="text-xs font-medium">{{ $station->location ?? 'Global Network' }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-3 opacity-40 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.stations.edit', $station) }}" 
                                       class="p-2.5 rounded-xl bg-white/5 border border-white/5 text-slate-400 hover:text-white hover:bg-white/10 transition-all" 
                                       title="Edit Metadata">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.stations.destroy', $station) }}" method="POST" onsubmit="return confirm('WARNING: Deleting this station will dissociate all linked transaction history. Proceed?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2.5 rounded-xl bg-white/5 border border-white/5 text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all" 
                                                title="Revoke Gateway">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-8 py-5 bg-white/[0.01] border-t border-white/10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-slate-500 text-[10px] font-bold uppercase tracking-widest italic">
                    Catalog Cluster: Layer 01 - {{ $stations->count() }} entities active
                </div>
                <div>
                    {{ $stations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
