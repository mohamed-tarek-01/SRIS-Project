<nav class="fixed top-4 left-1/2 -translate-x-1/2 w-[95%] max-w-7xl z-50 transition-all duration-300">
    <div class="glass-panel rounded-2xl px-4 py-3 flex items-center justify-between shadow-[0_8px_32px_rgba(45,212,191,0.05)] border border-white/5 backdrop-blur-xl">
        
        <!-- Logo Section -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group relative px-2">
            <!-- Glow effect behind logo -->
            <div class="absolute -inset-2 bg-primary-500/20 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition duration-500"></div>
            
            <!-- Custom SVG Logo -->
            <svg class="w-8 h-8 relative z-10 transform group-hover:scale-110 transition duration-500" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#34d399" />
                        <stop offset="100%" stop-color="#3b82f6" />
                    </linearGradient>
                    <linearGradient id="logoGradDark" x1="0%" y1="100%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#059669" />
                        <stop offset="100%" stop-color="#1d4ed8" />
                    </linearGradient>
                </defs>
                <!-- Outer Tech Hexagon -->
                <polygon points="50,5 90,25 90,75 50,95 10,75 10,25" stroke="url(#logoGrad)" stroke-width="4" stroke-dasharray="8 4" class="animate-[spin_20s_linear_infinite]" fill="rgba(52, 211, 153, 0.05)"/>
                <!-- Inner Shield/Road shape -->
                <path d="M50 20 L75 35 L75 65 L50 80 L25 65 L25 35 Z" fill="url(#logoGradDark)" opacity="0.8"/>
                <!-- Road lines -->
                <path d="M50 35 L40 70 M50 35 L60 70" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                <circle cx="50" cy="45" r="4" fill="#fff" class="animate-pulse-glow" />
            </svg>
            
            <div class="flex flex-col relative z-10">
                <span class="text-lg font-bold tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-primary-300 to-blue-400">
                    Smart Road
                </span>
                <span class="text-[0.65rem] uppercase tracking-[0.2em] text-slate-400 font-medium -mt-1 hidden sm:block">
                    Integrated System
                </span>
            </div>
        </a>

        <!-- Navigation Links -->
        <div class="flex items-center gap-1 overflow-x-auto no-scrollbar mask-fade-edges pb-1 sm:pb-0">
            @auth
                @php
                    $allLinks = [
                        ['route' => 'plate', 'icon' => 'scan-text', 'label' => 'Plates', 'role' => 'admin'],
                        ['route' => 'car_dashboard', 'icon' => 'gauge', 'label' => 'Lights', 'role' => 'user'],
                        ['route' => 'cracks', 'icon' => 'map', 'label' => 'Cracks', 'role' => 'admin'],
                        ['route' => 'accident', 'icon' => 'car-front', 'label' => 'Accident', 'role' => 'admin'],
                        ['route' => 'fire_smoke', 'icon' => 'flame', 'label' => 'Fire', 'role' => 'admin'],
                        ['route' => 'traffic', 'icon' => 'signpost', 'label' => 'Signs', 'role' => 'user'],
                        ['route' => 'user.vehicles.index', 'icon' => 'car', 'label' => 'My Cars', 'role' => 'user'],
                        ['route' => 'user.fuel.index', 'icon' => 'fuel', 'label' => 'Fuel', 'role' => 'user'],
                        ['route' => 'user.maintenance.index', 'icon' => 'wrench', 'label' => 'Service', 'role' => 'user'],
                        ['route' => 'user.reminders.index', 'icon' => 'calendar-clock', 'label' => 'Alerts', 'role' => 'user'],
                        ['route' => 'user.documents.index', 'icon' => 'file-text', 'label' => 'Docs', 'role' => 'user'],
                        ['route' => 'user.trips.index', 'icon' => 'map-pin', 'label' => 'Trips', 'role' => 'user'],
                        ['route' => 'user.stations', 'icon' => 'map', 'label' => 'Stations', 'role' => 'user'],
                        ['route' => 'user.fines', 'icon' => 'shield-alert', 'label' => 'Fines', 'role' => 'user'],
                        ['route' => 'user.wallet.index', 'icon' => 'wallet', 'label' => 'Wallet', 'role' => 'user'],
                        ['route' => 'vehicles', 'icon' => 'car', 'label' => 'Detect', 'role' => 'admin'],
                        ['route' => 'car_damage', 'icon' => 'wrench', 'label' => 'Damage', 'role' => 'admin'],
                        ['route' => 'admin.users.index', 'icon' => 'users', 'label' => 'Users', 'role' => 'admin'],
                        ['route' => 'admin.stations.index', 'icon' => 'map-pin', 'label' => 'Stations', 'role' => 'admin'],
                        ['route' => 'admin.payments.index', 'icon' => 'banknote', 'label' => 'Payments', 'role' => 'admin'],
                        ['route' => 'user.drive.index', 'icon' => 'navigation', 'label' => 'Drive Mode', 'role' => 'user'],
                    ];

                    $userRole = auth()->user()->role;
                    $links = array_filter($allLinks, function($link) use ($userRole) {
                        return $link['role'] === $userRole;
                    });
                @endphp
                
                @foreach($links as $link)
                    <a href="{{ route($link['route']) }}" 
                       class="group relative flex items-center gap-1.5 px-2.5 sm:px-3 py-2 rounded-lg transition-all duration-300 text-xs font-medium
                              {{ request()->routeIs($link['route']) 
                                 ? 'text-primary-300 bg-primary-500/10 border border-primary-500/20' 
                                 : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/10' }}">
                        <i data-lucide="{{ $link['icon'] }}" class="w-3.5 h-3.5 shrink-0 {{ request()->routeIs($link['route']) ? 'animate-pulse-glow' : '' }}"></i>
                        <span class="whitespace-nowrap hidden xl:block">{{ $link['label'] }}</span>
                    </a>
                @endforeach
            @endauth
        </div>

        <!-- User Actions -->
        <div class="flex items-center gap-2">
            @auth
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-[11px] font-bold text-white leading-none">{{ auth()->user()->name }}</span>
                    <span class="text-[9px] text-primary-400 uppercase tracking-tighter">{{ auth()->user()->role }}</span>
                </div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/20 transition-all font-bold text-[11px] group/logout">
                        <i data-lucide="log-out" class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform"></i>
                        <span class="hidden sm:block">Logout</span>
                    </button>
                </form>

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('dashboard') }}" class="relative group/bell">
                        <div class="p-2 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-red-400 hover:bg-red-500/10 hover:border-red-500/20 transition-all">
                            <i data-lucide="bell" class="w-4 h-4 {{ $unreadAlerts > 0 ? 'animate-bounce' : '' }}"></i>
                            @if($unreadAlerts > 0)
                                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-600 text-white text-[8px] font-black rounded-full flex items-center justify-center border-2 border-slate-950 shadow-[0_0_10px_rgba(220,38,38,0.5)] animate-pulse">
                                    {{ $unreadAlerts }}
                                </span>
                            @endif
                        </div>
                        <!-- Tooltip -->
                        <div class="absolute top-full right-0 mt-2 w-40 p-2.5 bg-slate-900/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-2xl opacity-0 translate-y-1 pointer-events-none group-hover/bell:opacity-100 group-hover/bell:translate-y-0 transition-all z-50">
                            <p class="text-[9px] font-black text-white uppercase tracking-widest">{{ $unreadAlerts }} Active Alerts</p>
                            <p class="text-[8px] text-slate-500 font-bold mt-1 leading-tight">Click to view dashboard</p>
                        </div>
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-primary-500/10 border border-primary-500/20 text-primary-400 text-sm font-semibold hover:bg-primary-500 hover:text-white transition-all duration-300">
                    Sign In
                </a>
            @endauth
        </div>
    </div>
</nav>

<style>
    /* Utility to fade out edges of scrolling nav on small screens */
    .mask-fade-edges {
        mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

