@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-12 pb-20">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-orange-500/10 flex items-center justify-center text-orange-400 border border-orange-500/20 shadow-lg shadow-orange-500/5">
                    <i data-lucide="fuel" class="w-6 h-6"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Fuel Intelligence</h1>
            </div>
            <p class="text-slate-400 font-medium max-w-xl">Track your fuel consumption, monitor costs, and analyze efficiency over time.</p>
        </div>
    </div>

    @if($vehicles->isEmpty())
        <div class="glass-panel rounded-[2.5rem] p-12 text-center space-y-6 border border-white/5">
            <div class="w-20 h-20 bg-slate-900 rounded-[2rem] flex items-center justify-center mx-auto text-slate-700">
                <i data-lucide="car" class="w-10 h-10"></i>
            </div>
            <div class="max-w-xs mx-auto">
                <h3 class="text-xl font-black text-white mb-2">No Vehicles Found</h3>
                <p class="text-slate-500 text-sm">You need to add a vehicle before you can log fuel consumption.</p>
            </div>
            <a href="{{ route('user.vehicles.index') }}" class="inline-flex px-8 py-4 bg-white text-dark-900 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-primary-500 hover:text-white transition-all">
                Go to Garage
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Quick Add Form -->
            <div class="lg:col-span-1">
                <div class="glass-panel rounded-[2.5rem] border border-white/5 p-8 sticky top-32">
                    <h3 class="text-xl font-black text-white mb-8 flex items-center gap-3">
                        <i data-lucide="plus-circle" class="w-5 h-5 text-orange-400"></i>
                        New Entry
                    </h3>
                    
                    <form action="{{ route('user.fuel.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Vehicle</label>
                            <select name="vehicle_id" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-orange-500/50 transition-all appearance-none">
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" class="bg-dark-800">{{ $vehicle->brand }} {{ $vehicle->model }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Liters</label>
                                <input type="number" name="liters" step="0.01" required placeholder="0.00" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-orange-500/50 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Cost (EGP)</label>
                                <input type="number" name="cost" step="0.01" required placeholder="0.00" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-orange-500/50 transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Odometer (km)</label>
                            <input type="number" name="odometer" required placeholder="Current mileage" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-orange-500/50 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Station Name</label>
                            <input type="text" name="station_name" placeholder="e.g. ExxonMobil" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-orange-500/50 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Date</label>
                            <input type="date" name="fill_date" required value="{{ date('Y-m-d') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-orange-500/50 transition-all">
                        </div>

                        <button type="submit" class="w-full py-5 bg-orange-500 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-[11px] hover:bg-orange-400 transition-all shadow-xl shadow-orange-500/10 active:scale-[0.98] mt-4">
                            Log Fuel Entry
                        </button>
                    </form>
                </div>
            </div>

            <!-- History Table -->
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-panel rounded-[2.5rem] border border-white/5 overflow-hidden">
                    <div class="p-8 border-b border-white/5 bg-white/[0.01]">
                        <h3 class="text-xl font-black text-white flex items-center gap-3">
                            <i data-lucide="history" class="w-5 h-5 text-slate-500"></i>
                            Recent History
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-slate-500 border-b border-white/[0.05] text-[10px] font-black uppercase tracking-[0.1em] bg-white/[0.02]">
                                    <th class="px-8 py-4">Vehicle</th>
                                    <th class="px-8 py-4">Amount</th>
                                    <th class="px-8 py-4">Odometer</th>
                                    <th class="px-8 py-4">Station</th>
                                    <th class="px-8 py-4">Date</th>
                                    <th class="px-8 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.03]">
                                @forelse($fuelLogs as $log)
                                    <tr class="text-slate-300 hover:bg-white/[0.02] transition-colors group">
                                        <td class="px-8 py-5">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-white">{{ $log->vehicle->brand }}</span>
                                                <span class="text-[10px] text-slate-500 uppercase tracking-tighter">{{ $log->vehicle->model }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex flex-col">
                                                <span class="font-black text-orange-400">{{ number_format($log->liters, 1) }} L</span>
                                                <span class="text-[10px] text-slate-500">{{ number_format($log->cost, 2) }} EGP</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 font-mono text-xs">
                                            {{ number_format($log->odometer) }} km
                                        </td>
                                        <td class="px-8 py-5 text-xs font-medium text-slate-400">
                                            {{ $log->station_name ?? '—' }}
                                        </td>
                                        <td class="px-8 py-5 text-xs text-slate-500 font-bold">
                                            {{ \Carbon\Carbon::parse($log->fill_date)->format('M d, Y') }}
                                        </td>
                                        <td class="px-8 py-5">
                                            <form action="{{ route('user.fuel.destroy', $log) }}" method="POST" onsubmit="return confirm('Delete this entry?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-600 hover:text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/20 transition-all opacity-0 group-hover:opacity-100">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-8 py-12 text-center text-slate-500 text-xs font-bold uppercase tracking-widest">
                                            No fuel logs recorded yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
