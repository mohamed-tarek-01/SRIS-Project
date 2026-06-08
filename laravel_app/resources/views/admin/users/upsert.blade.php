@extends('layouts.app')

@section('title', isset($user) ? 'Modifying User Profile' : 'Identity Provisioning')

@section('content')
<div class="flex items-center justify-center min-h-[75vh] py-10">
    <div class="glass-panel p-10 rounded-[2.5rem] w-full max-w-2xl relative z-10 transition-all duration-700 hover:shadow-[0_40px_80px_rgba(0,0,0,0.6)] border border-white/5 group">
        
        <!-- Decoration background glow -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-primary-500/20 transition-all duration-1000"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-indigo-500/20 transition-all duration-1000"></div>

        <div class="flex items-center gap-6 mb-10 relative z-10">
            <div class="w-16 h-16 rounded-[1.25rem] bg-gradient-to-br from-primary-500/20 to-indigo-500/20 border border-white/10 flex items-center justify-center text-primary-400 shadow-inner group-hover:scale-110 transition-transform duration-500">
                <i data-lucide="{{ isset($user) ? 'user-cog' : 'user-plus' }}" class="w-9 h-9"></i>
            </div>
            <div>
                <h2 class="text-4xl font-extrabold text-white leading-tight tracking-[calc(-0.025em)]">
                    {{ isset($user) ? 'Access Config' : 'New Identity' }}
                </h2>
                <p class="text-slate-500 text-xs font-black uppercase tracking-[0.2em] mt-1">
                    {{ isset($user) ? 'Identity manipulation cluster' : 'Manual directory enrollment' }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-8 relative z-10">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif
            
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-[11px] p-5 rounded-3xl mb-8 flex items-start gap-4 animate-fade-in-up">
                    <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                    </div>
                    <ul class="space-y-1 pt-1.5 uppercase font-bold tracking-wider leading-relaxed">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- Name -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] ml-2">Verified Full Name</label>
                    <div class="relative group/input">
                        <div class="absolute inset-0 bg-primary-500/0 rounded-2xl group-focus-within/input:bg-primary-500/5 transition-all duration-300"></div>
                        <i data-lucide="user" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-600 transition-colors group-focus-within/input:text-primary-400"></i>
                        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                               class="w-full bg-white/[0.02] border border-white/5 rounded-2xl pl-14 pr-5 py-4 text-white placeholder-slate-700 outline-none focus:border-primary-500/30 transition-all font-bold text-sm tracking-wide">
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] ml-2">Comm Vector (Email)</label>
                    <div class="relative group/input">
                        <div class="absolute inset-0 bg-primary-500/0 rounded-2xl group-focus-within/input:bg-primary-500/5 transition-all duration-300"></div>
                        <i data-lucide="mail" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-600 transition-colors group-focus-within/input:text-primary-400"></i>
                        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                               class="w-full bg-white/[0.02] border border-white/5 rounded-2xl pl-14 pr-5 py-4 text-white placeholder-slate-700 outline-none focus:border-primary-500/30 transition-all font-mono text-sm">
                    </div>
                </div>

                <!-- National ID -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] ml-2">Registry Key (Nat. ID)</label>
                    <div class="relative group/input">
                        <i data-lucide="fingerprint" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-600 transition-colors group-focus-within/input:text-primary-400"></i>
                        <input type="text" name="national_id" value="{{ old('national_id', $user->national_id ?? '') }}"
                               placeholder="14-digit numeric key"
                               class="w-full bg-white/[0.02] border border-white/5 rounded-2xl pl-14 pr-5 py-4 text-white placeholder-slate-700 outline-none focus:border-primary-500/30 transition-all font-bold text-sm">
                    </div>
                </div>

                <!-- Role -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] ml-2">Access Privilege Level</label>
                    <div class="relative group/input">
                        <i data-lucide="shield-check" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-600 transition-colors group-focus-within/input:text-primary-400"></i>
                        <select name="role" required
                                class="w-full bg-white/[0.02] border border-white/5 rounded-2xl pl-14 pr-5 py-4 text-white appearance-none outline-none focus:border-primary-500/30 transition-all font-bold text-sm cursor-pointer">
                            <option value="user" {{ old('role', $user->role ?? '') === 'user' ? 'selected' : '' }} class="bg-slate-900">Standard User Entity</option>
                            <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }} class="bg-slate-900">System Administrator</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-600 pointer-events-none group-focus-within/input:rotate-180 transition-transform"></i>
                    </div>
                </div>


                <!-- Balance -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] ml-2">Wallet Liquidity (Balance)</label>
                    <div class="relative group/input">
                        <i data-lucide="banknote" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-600 transition-colors group-focus-within/input:text-primary-400"></i>
                        <input type="number" name="balance" step="0.01" value="{{ old('balance', $user->balance ?? '200.00') }}" required
                               class="w-full bg-white/[0.02] border border-white/5 rounded-2xl pl-14 pr-5 py-4 text-white placeholder-slate-700 outline-none focus:border-primary-500/30 transition-all font-bold text-sm">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] ml-2">Security Cipher (Pass)</label>
                    <div class="relative group/input">
                        <i data-lucide="lock-keyhole" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-600 transition-colors group-focus-within/input:text-primary-400"></i>
                        <input type="password" name="password" {{ isset($user) ? '' : 'required' }}
                               placeholder="{{ isset($user) ? 'Null to keep existing' : 'Provisioning key' }}"
                               class="w-full bg-white/[0.02] border border-white/5 rounded-2xl pl-14 pr-5 py-4 text-white placeholder-slate-800 outline-none focus:border-primary-500/30 transition-all font-mono text-sm leading-none tracking-widest">
                    </div>
                </div>
            </div>

            <div class="pt-10 flex gap-5">
                <a href="{{ route('admin.users.index') }}" class="flex-1 py-4.5 px-6 rounded-2xl bg-white/[0.01] border border-white/5 text-slate-500 font-black uppercase tracking-[0.3em] text-[10px] text-center hover:bg-white/5 hover:text-slate-300 transition-all duration-300 active:scale-95 leading-none flex items-center justify-center">
                    Abort Setup
                </a>
                <button type="submit" class="flex-1 py-4.5 px-6 rounded-2xl bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white font-black uppercase tracking-[0.3em] text-[10px] shadow-[0_0_40px_rgba(16,185,129,0.2)] transition-all transform hover:-translate-y-1 active:scale-95 leading-none border border-white/10">
                    {{ isset($user) ? 'Execute Manifest' : 'Commit Provisioning' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
