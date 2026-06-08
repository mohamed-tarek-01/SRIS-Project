@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-12 pb-20">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-400 border border-rose-500/20 shadow-lg shadow-rose-500/5">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Smart Reminders</h1>
            </div>
            <p class="text-slate-400 font-medium max-w-xl">Never miss an oil change or license renewal again with automated alerts and custom reminders.</p>
        </div>
    </div>

    @if($vehicles->isEmpty())
        <div class="glass-panel rounded-[2.5rem] p-12 text-center space-y-6 border border-white/5">
            <div class="w-20 h-20 bg-slate-900 rounded-[2rem] flex items-center justify-center mx-auto text-slate-700">
                <i data-lucide="car" class="w-10 h-10"></i>
            </div>
            <div class="max-w-xs mx-auto">
                <h3 class="text-xl font-black text-white mb-2">No Vehicles Found</h3>
                <p class="text-slate-500 text-sm">Add a vehicle first to start setting reminders.</p>
            </div>
            <a href="{{ route('user.vehicles.index') }}" class="inline-flex px-8 py-4 bg-white text-dark-900 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-primary-500 hover:text-white transition-all">
                Go to Garage
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- New Reminder Form -->
            <div class="lg:col-span-1">
                <div class="glass-panel rounded-[2.5rem] border border-white/5 p-8 sticky top-32">
                    <h3 class="text-xl font-black text-white mb-8 flex items-center gap-3">
                        <i data-lucide="bell-plus" class="w-5 h-5 text-rose-400"></i>
                        Set Alert
                    </h3>
                    
                    <form action="{{ route('user.reminders.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Vehicle</label>
                            <select name="vehicle_id" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none">
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" class="bg-dark-800">{{ $vehicle->brand }} {{ $vehicle->model }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Reminder Title</label>
                            <input type="text" name="title" required placeholder="e.g. License Renewal" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Category</label>
                            <select name="type" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-rose-500/50 transition-all appearance-none">
                                <option value="maintenance" class="bg-dark-800">Maintenance</option>
                                <option value="insurance" class="bg-dark-800">Insurance</option>
                                <option value="license" class="bg-dark-800">License</option>
                                <option value="other" class="bg-dark-800">Other</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Due Date</label>
                            <input type="date" name="due_date" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-rose-500/50 transition-all">
                        </div>

                        <button type="submit" class="w-full py-5 bg-rose-500 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-[11px] hover:bg-rose-400 transition-all shadow-xl shadow-rose-500/10 active:scale-[0.98] mt-4">
                            Create Reminder
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reminders List -->
            <div class="lg:col-span-2 space-y-6">
                @forelse($reminders as $reminder)
                    <div class="glass-panel rounded-[2.5rem] border border-white/5 p-8 transition-all {{ $reminder->is_completed ? 'opacity-50 grayscale' : 'hover:border-rose-500/30' }}">
                        <div class="flex items-center justify-between gap-6">
                            <div class="flex items-center gap-6">
                                <div class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400">
                                    @php
                                        $icon = match($reminder->type) {
                                            'maintenance' => 'wrench',
                                            'insurance' => 'shield-check',
                                            'license' => 'file-badge',
                                            default => 'bell'
                                        };
                                    @endphp
                                    <i data-lucide="{{ $icon }}" class="w-7 h-7 {{ !$reminder->is_completed ? 'text-rose-400' : '' }}"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <h4 class="text-xl font-black text-white {{ $reminder->is_completed ? 'line-through' : '' }}">{{ $reminder->title }}</h4>
                                        <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-[9px] font-black text-slate-500 uppercase tracking-widest">
                                            {{ $reminder->type }}
                                        </span>
                                    </div>
                                    <p class="text-xs font-bold text-slate-500">
                                        Vehicle: <span class="text-slate-300">{{ $reminder->vehicle->brand }} {{ $reminder->vehicle->model }}</span>
                                        @if($reminder->due_date)
                                            • Due: <span class="text-rose-400/80">{{ \Carbon\Carbon::parse($reminder->due_date)->format('M d, Y') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <form action="{{ route('user.reminders.toggle', $reminder) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="flex items-center gap-2 px-5 py-3 rounded-xl {{ $reminder->is_completed ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-white/5 border border-white/10 text-slate-400 hover:bg-green-500/10 hover:text-green-400 hover:border-green-500/20' }} transition-all text-[10px] font-black uppercase tracking-widest">
                                        <i data-lucide="{{ $reminder->is_completed ? 'check-circle' : 'circle' }}" class="w-4 h-4"></i>
                                        {{ $reminder->is_completed ? 'Completed' : 'Mark Done' }}
                                    </button>
                                </form>
                                
                                <form action="{{ route('user.reminders.destroy', $reminder) }}" method="POST" onsubmit="return confirm('Delete reminder?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-3 rounded-xl bg-white/5 border border-white/10 text-slate-600 hover:text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/20 transition-all opacity-0 group-hover:opacity-100">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="glass-panel rounded-[2.5rem] p-20 text-center space-y-6 border border-white/5">
                        <div class="w-20 h-20 bg-slate-900 rounded-[2rem] flex items-center justify-center mx-auto text-slate-700">
                            <i data-lucide="bell-off" class="w-10 h-10"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white mb-2">No Active Reminders</h3>
                            <p class="text-slate-500 text-sm">Stay ahead of schedule by setting reminders for your car.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection
