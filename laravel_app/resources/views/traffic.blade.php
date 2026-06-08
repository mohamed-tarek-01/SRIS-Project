@extends('layouts.app')

@section('title', 'Traffic Signs')

@section('content')
    <div class="space-y-8 max-w-4xl mx-auto">
        <div class="text-center space-y-4">
            <div
                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-500/10 mb-2 shadow-[0_0_15px_rgba(52,211,153,0.2)]">
                <i data-lucide="signpost" class="w-8 h-8 text-primary-400"></i>
            </div>
            <h1
                class="text-3xl md:text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-primary-100 to-primary-400">
                Traffic Sign Classification
            </h1>
            <p class="text-base text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Recognize various traffic control signs (speed limits, yields, stops) and interpret their regulatory
                meaning.
            </p>
        </div>

        <div class="relative z-10 w-full animate-fade-in-up" style="animation-delay: 100ms;">
            @php
                $endpoint = url('/api/ml/traffic/detect');
            @endphp

            @include('components.upload-box', ['endpoint' => $endpoint])
        </div>
    </div>
@endsection