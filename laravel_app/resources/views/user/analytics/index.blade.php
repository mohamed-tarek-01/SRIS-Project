@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-12 pb-20">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-teal-500/10 flex items-center justify-center text-teal-400 border border-teal-500/20 shadow-lg shadow-teal-500/5">
                    <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Expense Analytics</h1>
            </div>
            <p class="text-slate-400 font-medium max-w-xl">Visualize your vehicle spending and analyze fuel efficiency through intelligent data insights.</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-panel rounded-[2.5rem] p-8 border border-white/5 space-y-4">
            <div class="flex items-center gap-3 text-orange-400">
                <i data-lucide="fuel" class="w-5 h-5"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">Fuel Expenses</span>
            </div>
            <p class="text-4xl font-black text-white leading-none">{{ number_format($fuelTotal) }} <span class="text-sm text-slate-500 font-medium">EGP</span></p>
            <div class="h-1 w-full bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-orange-500" style="width: {{ $totalExpenses > 0 ? ($fuelTotal / $totalExpenses) * 100 : 0 }}%"></div>
            </div>
        </div>

        <div class="glass-panel rounded-[2.5rem] p-8 border border-white/5 space-y-4">
            <div class="flex items-center gap-3 text-indigo-400">
                <i data-lucide="wrench" class="w-5 h-5"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">Maintenance</span>
            </div>
            <p class="text-4xl font-black text-white leading-none">{{ number_format($maintenanceTotal) }} <span class="text-sm text-slate-500 font-medium">EGP</span></p>
            <div class="h-1 w-full bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-500" style="width: {{ $totalExpenses > 0 ? ($maintenanceTotal / $totalExpenses) * 100 : 0 }}%"></div>
            </div>
        </div>

        <div class="glass-panel rounded-[2.5rem] p-8 bg-gradient-to-br from-teal-500/10 to-blue-500/10 border border-teal-500/20 space-y-4 shadow-2xl shadow-teal-500/5">
            <div class="flex items-center gap-3 text-teal-400">
                <i data-lucide="calculator" class="w-5 h-5"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">Total Investment</span>
            </div>
            <p class="text-4xl font-black text-white leading-none">{{ number_format($totalExpenses) }} <span class="text-sm text-slate-500 font-medium">EGP</span></p>
            <p class="text-[10px] text-teal-400/60 font-bold uppercase tracking-widest">Lifetime vehicle cost</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Chart Section -->
        <div class="glass-panel rounded-[2.5rem] border border-white/5 p-8 flex flex-col h-[450px]">
            <h3 class="text-xl font-black text-white mb-8 flex items-center gap-3">
                <i data-lucide="pie-chart" class="w-5 h-5 text-slate-500"></i>
                Spending Distribution
            </h3>
            <div class="flex-1 relative flex items-center justify-center">
                <canvas id="expenseChart"></canvas>
            </div>
        </div>

        <!-- Fuel Efficiency Section -->
        <div class="space-y-6">
            <div class="glass-panel rounded-[2.5rem] border border-white/5 p-8">
                <h3 class="text-xl font-black text-white mb-8 flex items-center gap-3">
                    <i data-lucide="zap" class="w-5 h-5 text-teal-400"></i>
                    Fuel Efficiency Intelligence
                </h3>
                
                <div class="space-y-6">
                    @foreach($vehicles as $vehicle)
                        <div class="flex items-center justify-between p-6 bg-white/[0.03] border border-white/5 rounded-3xl">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-white/10 flex items-center justify-center">
                                    <i data-lucide="car" class="w-6 h-6 text-slate-400"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-white">{{ $vehicle->brand }} {{ $vehicle->model }}</h4>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $vehicle->plate_number }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @php $eff = $efficiencyData[$vehicle->id] ?? 0; @endphp
                                <p class="text-2xl font-black {{ $eff > 12 ? 'text-green-400' : ($eff > 8 ? 'text-yellow-400' : 'text-rose-400') }}">
                                    {{ $eff > 0 ? $eff : '—' }}
                                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest ml-1">km/L</span>
                                </p>
                                <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest">Avg Efficiency</p>
                            </div>
                        </div>
                    @endforeach

                    @if($vehicles->isEmpty())
                        <p class="text-center text-slate-500 text-xs py-10 uppercase tracking-[0.2em] font-black">No vehicle data to analyze</p>
                    @endif
                </div>
            </div>

            <!-- Intelligence Tip -->
            <div class="bg-primary-500/10 border border-primary-500/20 rounded-[2.5rem] p-8 flex gap-6 items-start">
                <div class="w-12 h-12 rounded-2xl bg-primary-500/20 flex items-center justify-center text-primary-400 shrink-0 border border-primary-500/30">
                    <i data-lucide="lightbulb" class="w-6 h-6"></i>
                </div>
                <div class="space-y-2">
                    <h4 class="font-black text-white uppercase tracking-widest text-xs">AI Efficiency Tip</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">Based on your patterns, maintaining a steady speed of 80-90 km/h and regular tire pressure checks could improve your efficiency by up to <span class="text-primary-400 font-black">15%</span>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('expenseChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Fuel', 'Maintenance'],
                datasets: [{
                    data: [{{ $fuelTotal }}, {{ $maintenanceTotal }}],
                    backgroundColor: ['#f97316', '#6366f1'],
                    borderWidth: 0,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#94a3b8',
                            font: { family: 'Inter', weight: 'bold', size: 12 },
                            padding: 20
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
