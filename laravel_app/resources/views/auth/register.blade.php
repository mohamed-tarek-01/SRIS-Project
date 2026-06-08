@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="flex items-center justify-center min-h-[70vh] py-8">
    <div class="glass-panel p-8 rounded-2xl w-full max-w-lg relative z-10 transition-all duration-300">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-blue-500">Create Account</h2>
            <p class="text-slate-400 mt-2 text-sm">Register your vehicle with the Smart Road System</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-3 rounded-xl mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-300 ml-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full bg-dark-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/50 transition-all">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-300 ml-1">National ID</label>
                <input type="text" name="national_id" value="{{ old('national_id') }}" required
                       placeholder="14 digit ID"
                       class="w-full bg-dark-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/50 transition-all">
                <p class="text-[11px] text-slate-500 ml-1 mt-1">Must be registered with the Traffic Authority to proceed.</p>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-300 ml-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full bg-dark-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/50 transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-300 ml-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full bg-dark-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/50 transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-300 ml-1">Confirm</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full bg-dark-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/50 transition-all">
                </div>
            </div>

            <button type="submit" class="w-full mt-4 py-3 px-4 bg-gradient-to-r from-primary-500 to-blue-600 hover:from-primary-400 hover:to-blue-500 text-white font-semibold rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_25px_rgba(16,185,129,0.5)] transition-all duration-300 transform hover:-translate-y-0.5">
                Register & Verify Vehicle
            </button>
            
            <p class="text-center text-sm text-slate-400 pt-3">
                Already have an account? <a href="{{ route('login') }}" class="text-primary-400 hover:text-primary-300 font-medium">Sign in</a>
            </p>
        </form>
    </div>
</div>
@endsection
