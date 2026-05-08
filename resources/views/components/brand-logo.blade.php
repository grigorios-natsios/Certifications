@props(['class' => 'h-10 w-auto', 'fallbackText' => null])

@php
    $url = \App\Support\Branding::logoUrl();
@endphp

@if($url)
    <img src="{{ $url }}" alt="{{ config('app.name', 'Logo') }}" class="{{ $class }}">
@else
    <span class="w-9 h-9 rounded-md bg-brand-600 text-white flex items-center justify-center">
        <i class="fas fa-certificate"></i>
    </span>
    @if($fallbackText)
        <span class="font-semibold text-slate-800 hidden sm:inline">{{ $fallbackText }}</span>
    @endif
@endif
