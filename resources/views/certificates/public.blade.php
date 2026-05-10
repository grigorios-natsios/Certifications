<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Πιστοποιητικό') }} — {{ trim(($client->lastname ?? '').' '.($client->name ?? '')) }}</title>

    <x-favicon />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body, .font-sans {
            font-family: 'Manrope', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-feature-settings: 'cv11', 'ss01', 'ss03';
        }
    </style>
</head>
<body class="font-sans antialiased text-stone-800 bg-stone-50">

@php
    $pdfRecord   = $client->certificatePdfs->firstWhere('category_id', $selected->id);
    $pdfUrl      = $pdfRecord?->public_url
        ?? route('certificate.pdf', ['slug' => $client->url_slug, 'category' => $selected->slug]);
    $downloadUrl = route('certificate.download', ['slug' => $client->url_slug, 'category' => $selected->slug]);
    $shareUrl    = route('certificate.show', $client->url_slug).($selected ? '?cat='.$selected->slug : '');

    $issuedAt = $pdfRecord?->generated_at ?? now();

    $greekMonths = ['Ιανουαρίου','Φεβρουαρίου','Μαρτίου','Απριλίου','Μαΐου','Ιουνίου','Ιουλίου','Αυγούστου','Σεπτεμβρίου','Οκτωβρίου','Νοεμβρίου','Δεκεμβρίου'];
    $issuedDateGreek = $issuedAt->day.' '.$greekMonths[$issuedAt->month - 1].' '.$issuedAt->year;
    $issuedTime      = $issuedAt->format('H:i:s');

    $clientFullName = trim(($client->lastname ?? '').' '.($client->name ?? '')) ?: '—';
    $firstInitial = mb_strtoupper(mb_substr(trim($client->lastname ?? ''), 0, 1));
    $secondInitial = mb_strtoupper(mb_substr(trim($client->name ?? ''), 0, 1));
    $initials = ($firstInitial.$secondInitial) ?: '?';

    $certId = $client->external_id
        ?: 'C-'.$issuedAt->year.'-'.str_pad((string) $client->id, 4, '0', STR_PAD_LEFT);

    $org           = $client->organization;
    $orgName       = $org?->name ?: 'Lia Naoumidou';
    $orgAddress    = $org?->address ?: 'Δημαρχίας 13, Νάουσα, Ημαθία';
    $orgPhones     = $org?->phones ?: ['23320 29485', '23320 21071'];
    $orgEmail      = $org?->email ?: 'info@lianaoumidou.gr';
    $orgHours      = $org?->hours ?: 'Δευ–Παρ 09:00–21:00';
    $orgWebsite    = $org?->website_url ?: 'https://www.lianaoumidou.gr/';
    $orgFacebook   = $org?->facebook_url ?: 'https://www.facebook.com/NaoumidouTsitsi/';
    $orgInstagram  = $org?->instagram_url ?: 'https://www.instagram.com/kentro_ekpaideysis_naoumidou/';
    $orgYoutube    = $org?->youtube_url ?: 'https://www.youtube.com/channel/UCG6L7z7XlTO6r2gAOdV11CA';

    $fileSizeKb = null;
    $fileName   = ($clientFullName !== '—' ? $clientFullName : 'certificate').'.pdf';
    if ($pdfRecord && $pdfRecord->fileExists()) {
        $fileSizeKb = (int) round(filesize($pdfRecord->absolutePath()) / 1024);
    }

    $expirationValue = null;
    foreach ($client->customValues as $cv) {
        $fieldName = mb_strtolower($cv->field?->name ?? '', 'UTF-8');
        if (! str_contains($fieldName, 'λήξ')) {
            continue;
        }
        $value = trim((string) $cv->value);
        if ($value === '') {
            continue;
        }
        if ($cv->certificate_category_id === $selected->id) {
            $expirationValue = $value;
            break;
        }
        if ($cv->certificate_category_id === null && $expirationValue === null) {
            $expirationValue = $value;
        }
    }

    $displayDateGreek = $issuedDateGreek;
    if ($expirationValue) {
        $parsed = \DateTime::createFromFormat('d/m/Y', $expirationValue)
            ?: \DateTime::createFromFormat('Y-m-d', $expirationValue)
            ?: \DateTime::createFromFormat('d-m-Y', $expirationValue);
        if ($parsed) {
            $displayDateGreek = (int) $parsed->format('j').' '.$greekMonths[(int) $parsed->format('n') - 1].' '.$parsed->format('Y');
        } else {
            $displayDateGreek = $expirationValue;
        }
    }
@endphp

<div class="min-h-screen flex flex-col">

    <header class="bg-white border-b border-stone-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ $orgWebsite }}" target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-3 group">
                <x-brand-logo class="h-9 w-auto transition-opacity group-hover:opacity-80" :fallback-text="config('app.name', 'Certifications')" />
            </a>
            <span class="hidden sm:inline-flex items-center gap-1.5 pl-1.5 pr-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-emerald-50 to-emerald-100 text-emerald-700 ring-1 ring-inset ring-emerald-200/80 shadow-sm shadow-emerald-900/5">
                <span class="w-4 h-4 rounded-full bg-emerald-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5 text-white">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </span>
                {{ __('Επικυρωμένο') }}
            </span>
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 py-6 sm:py-8">

        {{-- ───── HERO ───── --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-900 text-white p-5 sm:p-6 mb-6 sm:mb-8 shadow-xl shadow-emerald-900/30">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-400/25 rounded-full blur-3xl"></div>
            <div class="absolute right-32 bottom-0 w-32 h-32 bg-amber-300/10 rounded-full blur-2xl"></div>
            <div class="relative flex items-center gap-4 sm:gap-5">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center ring-4 ring-white/10 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 sm:w-9 sm:h-9 text-white">
                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-200 mb-1">{{ __('Επαληθευμένο') }}</div>
                    <h1 class="text-lg sm:text-2xl font-bold tracking-tight mb-1 leading-tight">{{ __('Αυτό το πιστοποιητικό είναι αυθεντικό') }}</h1>
                    <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed">
                        {{ __('Έχει εκδοθεί από το') }} {{ $orgName }} {{ __('και ψηφιακά υπογραφεί την') }}
                        {{ $displayDateGreek }}, {{ __('στις') }} {{ $issuedTime }}.
                    </p>
                </div>
            </div>
        </div>

        {{-- ───── CATEGORY SWITCHER ───── --}}
        @if($categories->count() > 1)
            <div class="mb-6 flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-stone-400 uppercase tracking-wider mr-1">
                    {{ __('Κατηγορία') }}
                </span>
                @foreach($categories as $cat)
                    <a href="{{ route('certificate.show', $client->url_slug) }}?cat={{ $cat->slug }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition
                              {{ $cat->id === $selected->id
                                 ? 'bg-brand-600 text-white border-brand-600 shadow-sm shadow-brand-900/20'
                                 : 'bg-white text-stone-600 border-stone-200 hover:border-brand-300 hover:text-brand-700' }}">
                        @if($cat->id === $selected->id)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- ───── GRID ───── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: PDF VIEWER --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- File bar --}}
                <div class="flex items-center justify-between bg-white rounded-xl border border-stone-200 px-3 sm:px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- PDF badge --}}
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-sm shadow-brand-900/20 shrink-0">
                            <span class="text-[9px] font-extrabold text-white tracking-wider">PDF</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-stone-900 truncate leading-tight">{{ $fileName }}</div>
                            <div class="flex items-center gap-1.5 mt-1">
                                @if($fileSizeKb !== null)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold text-stone-600 bg-stone-100 ring-1 ring-stone-200/60">
                                        {{ number_format($fileSizeKb) }} KB
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold text-stone-600 bg-stone-100 ring-1 ring-stone-200/60">A4</span>
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200/60">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    {{ __('Έγκυρο') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-stone-600 hover:text-brand-700 hover:bg-brand-50 transition-colors shrink-0"
                       title="{{ __('Πλήρης οθόνη') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                            <path d="M15 3h6v6"></path>
                            <path d="M10 14 21 3"></path>
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        </svg>
                        <span class="hidden sm:inline">{{ __('Πλήρης οθόνη') }}</span>
                    </a>
                </div>

                {{-- PDF iframe in stone container --}}
                <div class="bg-stone-200/60 rounded-xl p-3 sm:p-6 border border-stone-200">
                    <div class="bg-white shadow-2xl shadow-brand-900/15 hover:shadow-brand-900/25 transition-shadow rounded-sm mx-auto overflow-hidden"
                         style="max-width: 720px; aspect-ratio: 1 / 1.4142;">
                        <iframe id="cert-frame"
                                src="{{ $pdfUrl }}#toolbar=0&navpanes=0&view=FitH"
                                class="w-full h-full bg-white block"
                                style="border: 0;"
                                title="{{ __('Πιστοποιητικό') }} — {{ $selected->name }}"
                                loading="eager">
                        </iframe>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <a href="{{ $downloadUrl }}"
                       class="flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-gradient-to-r from-red-700 to-red-600 hover:from-red-600 hover:to-red-500 text-white text-sm font-semibold shadow-lg shadow-red-900/20 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" x2="12" y1="15" y2="3"></line>
                        </svg>
                        {{ __('Λήψη PDF') }}
                    </a>
                    <button type="button" onclick="printCertificate()"
                            class="flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-white border border-stone-200 hover:border-stone-300 text-stone-900 text-sm font-semibold transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path>
                            <rect x="6" y="14" width="12" height="8" rx="1"></rect>
                        </svg>
                        {{ __('Εκτύπωση') }}
                    </button>
                    <button type="button"
                            onclick="shareCertificate(@js($shareUrl), @js(__('Πιστοποιητικό').' — '.$clientFullName))"
                            class="flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-white border border-stone-200 hover:border-stone-300 text-stone-900 text-sm font-semibold transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <circle cx="18" cy="5" r="3"></circle>
                            <circle cx="6" cy="12" r="3"></circle>
                            <circle cx="18" cy="19" r="3"></circle>
                            <line x1="8.59" x2="15.42" y1="13.51" y2="17.49"></line>
                            <line x1="15.41" x2="8.59" y1="6.51" y2="10.49"></line>
                        </svg>
                        {{ __('Κοινοποίηση') }}
                    </button>
                </div>

            </div>

            {{-- RIGHT: SIDEBAR --}}
            <div class="lg:col-span-1 space-y-4">

                {{-- Recipient --}}
                <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-700 ring-1 ring-brand-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <h3 class="text-[11px] uppercase tracking-[0.18em] text-stone-700 font-bold">{{ __('Παραλήπτης') }}</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 text-lg rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-semibold shadow-sm shadow-brand-900/20 ring-2 ring-white shrink-0">
                            {{ $initials }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-base font-bold text-stone-900 truncate">{{ $clientFullName }}</div>
                            <div class="mt-1.5">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    {{ $selected->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Certificate details --}}
                <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-700 ring-1 ring-brand-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <path d="m9 18 2 2 4-4"></path>
                            </svg>
                        </div>
                        <h3 class="text-[11px] uppercase tracking-[0.18em] text-stone-700 font-bold">{{ __('Στοιχεία Πιστοποιητικού') }}</h3>
                    </div>
                    <dl class="space-y-3">
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <dt class="text-stone-600 font-medium shrink-0">{{ __('Τίτλος') }}</dt>
                            <dd class="text-stone-900 font-semibold text-right">{{ $selected->name }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <dt class="text-stone-600 font-medium shrink-0">{{ __('Ημ/νία Έκδοσης') }}</dt>
                            <dd class="text-stone-900 font-semibold text-right">{{ $displayDateGreek }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <dt class="text-stone-600 font-medium shrink-0">{{ __('Ώρα Έκδοσης') }}</dt>
                            <dd class="text-stone-900 font-semibold text-right font-mono">{{ $issuedTime }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <dt class="text-stone-600 font-medium shrink-0">{{ __('Φορέας') }}</dt>
                            <dd class="text-stone-900 font-semibold text-right">{{ $orgName }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Organization details --}}
                <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-700 ring-1 ring-brand-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                <rect width="16" height="20" x="4" y="2" rx="2"></rect>
                                <path d="M9 22v-4h6v4"></path>
                                <path d="M8 6h.01"></path>
                                <path d="M16 6h.01"></path>
                                <path d="M12 6h.01"></path>
                                <path d="M12 10h.01"></path>
                                <path d="M12 14h.01"></path>
                                <path d="M16 10h.01"></path>
                                <path d="M16 14h.01"></path>
                                <path d="M8 10h.01"></path>
                                <path d="M8 14h.01"></path>
                            </svg>
                        </div>
                        <h3 class="text-[11px] uppercase tracking-[0.18em] text-stone-700 font-bold">{{ __('Στοιχεία Φορέα') }}</h3>
                    </div>
                    <dl class="space-y-3 text-xs">
                        <div class="flex items-start gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-brand-600 mt-0.5 shrink-0">
                                <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <div class="min-w-0">
                                <div class="text-stone-600 font-medium mb-0.5">{{ __('Διεύθυνση') }}</div>
                                <div class="text-stone-900 font-medium leading-snug">{{ $orgAddress }}</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-brand-600 mt-0.5 shrink-0">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <div class="min-w-0">
                                <div class="text-stone-600 font-medium mb-0.5">{{ __('Τηλέφωνο') }}</div>
                                <div class="text-stone-900 font-medium leading-snug">
                                    @foreach($orgPhones as $i => $phone)
                                        @if($i > 0)<span class="text-stone-300 mx-1">·</span>@endif
                                        <a href="tel:+30{{ preg_replace('/\D+/', '', $phone) }}" class="hover:text-brand-700">{{ $phone }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-brand-600 mt-0.5 shrink-0">
                                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                            <div class="min-w-0">
                                <div class="text-stone-600 font-medium mb-0.5">Email</div>
                                <a href="mailto:{{ $orgEmail }}" class="text-stone-900 font-medium hover:text-brand-700 break-all">{{ $orgEmail }}</a>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-brand-600 mt-0.5 shrink-0">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <div class="min-w-0">
                                <div class="text-stone-600 font-medium mb-0.5">{{ __('Ωράριο') }}</div>
                                <div class="text-stone-900 font-medium">{{ $orgHours }}</div>
                            </div>
                        </div>
                    </dl>

                    <div class="mt-4 pt-4 border-t border-stone-100 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            @if($orgWebsite)
                                <a href="{{ $orgWebsite }}" target="_blank" rel="noopener noreferrer"
                                   title="Website" aria-label="Website"
                                   class="w-7 h-7 rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100 hover:bg-brand-600 hover:text-white hover:ring-brand-600 flex items-center justify-center transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                                        <path d="M2 12h20"></path>
                                    </svg>
                                </a>
                            @endif
                            @if($orgFacebook)
                                <a href="{{ $orgFacebook }}" target="_blank" rel="noopener noreferrer"
                                   title="Facebook" aria-label="Facebook"
                                   class="w-7 h-7 rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100 hover:bg-brand-600 hover:text-white hover:ring-brand-600 flex items-center justify-center transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                                    </svg>
                                </a>
                            @endif
                            @if($orgInstagram)
                                <a href="{{ $orgInstagram }}" target="_blank" rel="noopener noreferrer"
                                   title="Instagram" aria-label="Instagram"
                                   class="w-7 h-7 rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100 hover:bg-brand-600 hover:text-white hover:ring-brand-600 flex items-center justify-center transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"></path>
                                    </svg>
                                </a>
                            @endif
                            @if($orgYoutube)
                                <a href="{{ $orgYoutube }}" target="_blank" rel="noopener noreferrer"
                                   title="YouTube" aria-label="YouTube"
                                   class="w-7 h-7 rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100 hover:bg-brand-600 hover:text-white hover:ring-brand-600 flex items-center justify-center transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <div class="text-right text-[10px] text-stone-400 leading-tight">
                            <div>{{ __('Άδεια Κ.Ξ.Γ.') }} <span class="font-mono text-stone-600">2308176</span></div>
                            <div>{{ __('Άδεια Κ.Δ.Β.Μ.') }} <span class="font-mono text-stone-600">2101537</span></div>
                        </div>
                    </div>
                </div>

                {{-- QR verification --}}
                @if($qr && $qr->image_base64)
                    <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm">
                        <div class="flex items-center gap-2.5 mb-4">
                            <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-700 ring-1 ring-brand-100 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                    <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                    <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                    <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                    <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                    <path d="M21 21v.01"></path>
                                    <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                                    <path d="M3 12h.01"></path>
                                    <path d="M12 3h.01"></path>
                                    <path d="M12 16v.01"></path>
                                    <path d="M16 12h1"></path>
                                    <path d="M21 12v.01"></path>
                                    <path d="M12 21v-1"></path>
                                </svg>
                            </div>
                            <h3 class="text-[11px] uppercase tracking-[0.18em] text-stone-700 font-bold">{{ __('Επαλήθευση') }}</h3>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="shrink-0 p-2 bg-white rounded-lg ring-1 ring-stone-200">
                                <img src="data:image/png;base64,{{ $qr->image_base64 }}"
                                     alt="{{ __('QR επαλήθευσης') }}"
                                     class="w-24 h-24 block">
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-stone-900 leading-snug mb-1">
                                    {{ __('Σαρώστε για επαλήθευση') }}
                                </div>
                                <p class="text-[11px] text-stone-500 leading-relaxed">
                                    {{ __('Με το κινητό σας, σαρώστε τον κωδικό για να ανοίξετε αυτή τη σελίδα.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Authentic banner --}}
                <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 text-white rounded-2xl p-5 shadow-lg shadow-emerald-900/20 relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-400/20 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-emerald-200">
                                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>
                            <div class="text-sm font-bold">{{ __('Αυθεντικό Πιστοποιητικό') }}</div>
                        </div>
                        <p class="text-xs text-emerald-50/90 leading-relaxed">
                            {{ __('Έχει εκδοθεί επίσημα από το') }} {{ $orgName }} {{ __('και είναι καταχωρημένο στο μητρώο μας. Μπορείτε να επαληθεύσετε την εγκυρότητά του ανά πάσα στιγμή σαρώνοντας το QR code του εγγράφου.') }}
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ───── FOOTER ───── --}}
        <div class="mt-12 pt-8 border-t border-stone-200">
            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-2 text-xs text-stone-700">
                <span class="inline-block w-8 h-px bg-gradient-to-r from-transparent to-brand-500"></span>
                <span class="font-medium">&copy; {{ date('Y') }} {{ $orgName }}</span>
                <span class="text-stone-300">·</span>
                <a href="https://www.lianaoumidou.gr/cookie-policy/" target="_blank" rel="noopener noreferrer"
                   class="font-medium hover:text-brand-700 transition-colors">
                    {{ __('Όροι Χρήσης / Πολιτική Ακύρωσης') }}
                </a>
                <span class="inline-block w-8 h-px bg-gradient-to-l from-transparent to-brand-500"></span>
            </div>
        </div>

    </main>
</div>

<script>
    function printCertificate() {
        const f = document.getElementById('cert-frame');
        if (!f) { window.print(); return; }
        try { f.contentWindow.focus(); f.contentWindow.print(); }
        catch (e) { window.open(f.src, '_blank'); }
    }
    async function shareCertificate(url, title) {
        if (navigator.share) {
            try { await navigator.share({ title: title, url: url }); return; } catch (e) {}
        }
        try {
            await navigator.clipboard.writeText(url);
            alert(@js(__('Ο σύνδεσμος αντιγράφηκε στο πρόχειρο.')));
        } catch (e) {
            window.prompt(@js(__('Αντιγράψτε τον σύνδεσμο:')), url);
        }
    }
</script>

</body>
</html>
