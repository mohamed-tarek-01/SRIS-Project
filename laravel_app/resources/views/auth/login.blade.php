@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="flex items-center justify-center min-h-[70vh]">
    <div class="glass-panel p-8 rounded-2xl w-full max-w-md relative z-10 transition-all duration-300">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-blue-500">Welcome Back</h2>
            <p class="text-slate-400 mt-2 text-sm">Sign in to the Integrated Smart Road System</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-3 rounded-xl mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-300 ml-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full bg-dark-800/50 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/50 transition-all">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-300 ml-1">Password</label>
                <input type="password" name="password" required
                       class="w-full bg-dark-800/50 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/50 transition-all">
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-primary-500 to-blue-600 hover:from-primary-400 hover:to-blue-500 text-white font-semibold rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_25px_rgba(16,185,129,0.5)] transition-all duration-300 transform hover:-translate-y-0.5">
                Sign In
            </button>
            
            <p class="text-center text-sm text-slate-400 pt-4">
                Don't have an account? <a href="{{ route('register') }}" class="text-primary-400 hover:text-primary-300 font-medium">Register here</a>
            </p>
        </form>
    </div>
</div>
@endsection
