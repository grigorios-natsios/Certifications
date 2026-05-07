<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Certifications') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-100 via-brand-50 to-slate-100 relative overflow-hidden">
            <div aria-hidden="true" class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-brand-200/40 blur-3xl"></div>
            <div aria-hidden="true" class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-brand-300/30 blur-3xl"></div>

            <div class="relative z-10 flex flex-col items-center">
                <a href="/" wire:navigate class="flex items-center gap-3 mb-6">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16 w-auto">
                    @elseif(file_exists(public_path('images/logo.svg')))
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-16 w-auto">
                    @elseif(file_exists(public_path('images/logo.jpg')))
                        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-16 w-auto">
                    @else
                        <x-application-logo class="w-14 h-14" />
                        <span class="text-2xl font-bold text-slate-800">{{ config('app.name', 'Certifications') }}</span>
                    @endif
                </a>
            </div>

            <div class="relative z-10 w-full sm:max-w-md mt-2 px-8 py-8 bg-white shadow-xl rounded-2xl border border-slate-200">
                {{ $slot }}
            </div>

            <p class="relative z-10 mt-6 text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'Certifications') }}
            </p>
        </div>
    </body>
</html>
