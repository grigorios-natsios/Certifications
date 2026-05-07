<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Certifications') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans text-slate-800">
        <div class="relative min-h-screen bg-gradient-to-br from-slate-100 via-white to-brand-50 overflow-hidden">
            <div aria-hidden="true" class="absolute -top-40 -left-40 w-[28rem] h-[28rem] rounded-full bg-brand-200/40 blur-3xl"></div>
            <div aria-hidden="true" class="absolute -bottom-40 -right-40 w-[28rem] h-[28rem] rounded-full bg-brand-300/30 blur-3xl"></div>

            <header class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 py-6 flex items-center justify-between">
                <a href="/" class="flex items-center gap-3">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-11 w-auto">
                    @elseif(file_exists(public_path('images/logo.svg')))
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-11 w-auto">
                    @elseif(file_exists(public_path('images/logo.jpg')))
                        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-11 w-auto">
                    @else
                        <x-application-logo class="w-11 h-11" />
                        <span class="text-xl font-bold">{{ config('app.name', 'Certifications') }}</span>
                    @endif
                </a>
                @if (Route::has('login'))
                    <nav class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-primary">
                                <i class="fas fa-arrow-right-to-bracket"></i> {{ __('Πίνακας Ελέγχου') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-secondary">{{ __('Σύνδεση') }}</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-primary">{{ __('Εγγραφή') }}</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 pt-14 pb-24">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="badge badge-brand"><i class="fas fa-bolt mr-1"></i> {{ __('Νέα έκδοση') }}</span>
                        <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold leading-tight text-slate-900">
                            {{ __('Παραγωγή Πιστοποιητικών σε ένα κλικ.') }}
                        </h1>
                        <p class="mt-5 text-lg text-slate-600 max-w-xl">
                            {{ __('Διαχειρίσου πελάτες, κατηγορίες και custom πεδία. Δημιούργησε επαγγελματικά PDF certificates μαζικά, με σχεδιασμό που ταιριάζει στο brand σου.') }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn-primary"><i class="fas fa-gauge-high"></i> {{ __('Πήγαινε στο Dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="btn-primary"><i class="fas fa-arrow-right-to-bracket"></i> {{ __('Ξεκίνα τώρα') }}</a>
                            @endauth
                        </div>

                        <dl class="mt-12 grid grid-cols-3 gap-6 max-w-md">
                            <div>
                                <dt class="text-3xl font-bold text-brand-600">PDF</dt>
                                <dd class="text-xs text-slate-500 uppercase tracking-wide">{{ __('Παραγωγή') }}</dd>
                            </div>
                            <div>
                                <dt class="text-3xl font-bold text-brand-600">CSV</dt>
                                <dd class="text-xs text-slate-500 uppercase tracking-wide">{{ __('Εισαγωγή') }}</dd>
                            </div>
                            <div>
                                <dt class="text-3xl font-bold text-brand-600">∞</dt>
                                <dd class="text-xs text-slate-500 uppercase tracking-wide">{{ __('Πελάτες') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-tr from-brand-500/30 to-transparent rounded-3xl blur-2xl"></div>
                        <div class="relative card p-8 rotate-1 hover:rotate-0 transition-transform">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-lg bg-brand-600 text-white flex items-center justify-center"><i class="fas fa-certificate"></i></span>
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('Πιστοποιητικό') }}</p>
                                        <p class="font-semibold">{{ __('Πρόγραμμα Voucher 2026') }}</p>
                                    </div>
                                </div>
                                <span class="badge badge-brand">PDF</span>
                            </div>
                            <ul class="space-y-3 text-sm">
                                <li class="flex items-center gap-3 text-slate-700"><i class="fas fa-check-circle text-brand-600"></i> {{ __('Custom HTML template ανά κατηγορία') }}</li>
                                <li class="flex items-center gap-3 text-slate-700"><i class="fas fa-check-circle text-brand-600"></i> {{ __('Μαζική παραγωγή PDF') }}</li>
                                <li class="flex items-center gap-3 text-slate-700"><i class="fas fa-check-circle text-brand-600"></i> {{ __('Custom πεδία πελατών') }}</li>
                                <li class="flex items-center gap-3 text-slate-700"><i class="fas fa-check-circle text-brand-600"></i> {{ __('Φιλτράρισμα & αναζήτηση') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="relative z-10 border-t border-slate-200 bg-white/60 backdrop-blur">
                <div class="max-w-7xl mx-auto px-6 lg:px-8 py-6 text-center text-xs text-slate-500">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Certifications') }} &middot; Laravel v{{ Illuminate\Foundation\Application::VERSION }}
                </div>
            </footer>
        </div>
    </body>
</html>
