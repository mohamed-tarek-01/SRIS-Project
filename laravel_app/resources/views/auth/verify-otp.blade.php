@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#0f172a] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-[#1e293b] p-8 rounded-2xl shadow-2xl border border-gray-800">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                Verify OTP
            </h2>
            <p class="mt-2 text-center text-sm text-gray-400">
                We've sent a 6-digit code to your email.
            </p>
        </div>
        <form class="mt-8 space-y-6" action="{{ route('verify.otp') }}" method="POST">
            @csrf
            <div>
                <label for="otp" class="sr-only">OTP Code</label>
                <input id="otp" name="otp" type="text" required class="appearance-none rounded-xl relative block w-full px-3 py-4 border border-gray-700 bg-gray-900 placeholder-gray-500 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm text-center text-2xl tracking-widest" placeholder="123456">
                @error('otp')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-lg">
                    Verify
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
