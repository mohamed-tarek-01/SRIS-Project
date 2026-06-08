@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-12 pb-20">
    <!-- Header Section -->
    <div class="space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-400 border border-amber-500/20 shadow-xl shadow-amber-500/5">
            <i data-lucide="shield-alert" class="w-8 h-8"></i>
        </div>
        <h1 class="text-4xl font-black text-white tracking-tight uppercase">Traffic Fines Reference</h1>
        <p class="text-slate-400 font-medium italic">Official reference guide for traffic rules and penalties in Egypt.</p>
    </div>

    <!-- My Issued Fines Section -->
    <div class="space-y-6">
        <h2 class="text-2xl font-black text-white uppercase tracking-wider flex items-center gap-3">
            <i data-lucide="history" class="w-6 h-6 text-rose-500"></i> My Violation History
        </h2>
        
        <div class="glass-panel rounded-[2.5rem] border border-white/5 overflow-hidden">
            @if($userFines->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-slate-500 border-b border-white/[0.05] text-[10px] font-black uppercase tracking-[0.1em] bg-white/[0.02]">
                                <th class="px-8 py-4">Date & Location</th>
                                <th class="px-8 py-4">Plate Number</th>
                                <th class="px-8 py-4">Reason</th>
                                <th class="px-8 py-4">Amount</th>
                                <th class="px-8 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.03]">
                            @foreach($userFines as $fine)
                                <tr class="text-slate-300 hover:bg-white/[0.02] transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-white">{{ $fine->created_at->format('M d, Y') }}</div>
                                        <div class="text-[10px] text-slate-500 uppercase font-black">{{ $fine->station ? $fine->station->name : 'Manual' }}</div>
                                    </td>
                                    <td class="px-8 py-5 font-mono text-sm tracking-widest text-primary-400">{{ $fine->plate_number }}</td>
                                    <td class="px-8 py-5 text-xs">{{ $fine->reason }}</td>
                                    <td class="px-8 py-5">
                                        <span class="font-black text-rose-400">{{ number_format($fine->amount, 2) }} EGP</span>
                                    </td>
                                    <td class="px-8 py-5">
                                        @if($fine->status === 'pending')
                                            <span class="px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[9px] font-black uppercase tracking-widest">Unpaid</span>
                                        @else
                                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[9px] font-black uppercase tracking-widest">Paid</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-16 text-center">
                    <div class="w-20 h-20 bg-emerald-500/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-500/20">
                        <i data-lucide="shield-check" class="w-10 h-10 text-emerald-400"></i>
                    </div>
                    <h3 class="text-xl font-black text-white mb-2 uppercase">No Violations Found</h3>
                    <p class="text-slate-400 text-sm max-w-xs mx-auto">Great job! Your record is clean. Keep up the safe driving.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Fines Table Reference -->
    <div class="space-y-6 pt-8 border-t border-white/5">
        <h2 class="text-2xl font-black text-slate-500 uppercase tracking-wider">General Reference</h2>
        <div class="glass-panel rounded-[2rem] border border-white/5 overflow-hidden">
            <div class="p-6 border-b border-white/5 bg-white/[0.01]">
                <h3 class="text-sm font-black text-white uppercase tracking-widest">Standard Traffic Penalties</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-500 border-b border-white/[0.05] text-[9px] font-black uppercase tracking-[0.1em] bg-white/[0.02]">
                            <th class="px-8 py-3">Violation</th>
                            <th class="px-8 py-3">Penalty (EGP)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.03]">
                        @php
                            $refFines = [
                                ['v' => 'Exceeding Speed Limit', 'p' => '300 - 1500'],
                                ['v' => 'Driving without License', 'p' => '1000 - 2000'],
                                ['v' => 'Insufficient Points for Toll', 'p' => '1000'],
                                ['v' => 'Wrong Way Driving', 'p' => '1000 - 3000'],
                            ];
                        @endphp
                        @foreach($refFines as $rfine)
                            <tr class="text-slate-400 hover:bg-white/[0.01] transition-colors text-xs">
                                <td class="px-8 py-4 font-bold">{{ $rfine['v'] }}</td>
                                <td class="px-8 py-4 font-black text-amber-500/80">{{ $rfine['p'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Disclaimer -->
    <div class="p-6 bg-white/5 border border-white/10 rounded-3xl opacity-50">
        <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest leading-relaxed">
            * Disclaimer: These figures are for reference only and may change according to the latest amendments in the Egyptian Traffic Law. Always check with official authorities.
        </p>
    </div>
</div>
@endsection
