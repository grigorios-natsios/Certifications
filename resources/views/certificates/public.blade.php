<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Πιστοποιητικό') }} — {{ trim(($client->lastname ?? '').' '.($client->name ?? '')) }}</title>

    <x-favicon />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

    $orgName = $client->organization?->name ?? config('app.name', 'Certifications');

    $fileSizeKb = null;
    $fileName   = $certId.'.pdf';
    if ($pdfRecord && $pdfRecord->fileExists()) {
        $fileSizeKb = (int) round(filesize($pdfRecord->absolutePath()) / 1024);
        $fileName   = basename($pdfRecord->path);
    }
@endphp

<div class="min-h-screen flex flex-col">

    <header class="bg-white border-b border-stone-200">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-brand-logo class="h-9 w-auto" :fallback-text="config('app.name', 'Certifications')" />
            </div>
            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                {{ __('Επικυρωμένο') }}
            </span>
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto w-full px-6 py-8">

        {{-- ───── HERO ───── --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 text-white p-6 mb-8 shadow-xl shadow-emerald-900/20">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-400/20 rounded-full blur-3xl"></div>
            <div class="absolute right-32 bottom-0 w-32 h-32 bg-amber-400/10 rounded-full blur-2xl"></div>
            <div class="relative flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center ring-4 ring-white/10 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-9 h-9 text-white">
                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-emerald-200">{{ __('Επαληθευμένο') }}</div>
                        <div class="flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-400/20 text-[10px] font-mono text-emerald-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>SSL
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight mb-1">{{ __('Αυτό το πιστοποιητικό είναι αυθεντικό') }}</h1>
                    <p class="text-sm text-emerald-100/90 leading-relaxed">
                        {{ __('Έχει εκδοθεί από το') }} {{ $orgName }} {{ __('και ψηφιακά υπογραφεί την') }}
                        {{ $issuedDateGreek }}, {{ __('στις') }} {{ $issuedTime }}.
                    </p>
                </div>
                <div class="hidden lg:block text-right shrink-0">
                    <div class="text-[10px] uppercase tracking-[0.18em] text-emerald-200 font-semibold mb-1">{{ __('Cert ID') }}</div>
                    <div class="font-mono text-base font-bold">{{ $certId }}</div>
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
                                 ? 'bg-stone-900 text-white border-stone-900'
                                 : 'bg-white text-stone-600 border-stone-200 hover:border-stone-300 hover:text-stone-900' }}">
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
                <div class="flex items-center justify-between bg-white rounded-xl border border-stone-200 px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-stone-500 shrink-0">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg>
                        <span class="text-sm font-semibold text-stone-900 truncate">{{ $fileName }}</span>
                        <span class="text-xs text-stone-400 hidden sm:inline">
                            ·
                            @if($fileSizeKb !== null)
                                {{ number_format($fileSizeKb) }} KB ·
                            @endif
                            A4
                        </span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
                           class="w-7 h-7 rounded hover:bg-stone-100 flex items-center justify-center"
                           title="{{ __('Πλήρης οθόνη') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-stone-600">
                                <path d="M15 3h6v6"></path>
                                <path d="M10 14 21 3"></path>
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- PDF iframe in stone container --}}
                <div class="bg-stone-200/60 rounded-xl p-4 sm:p-6 border border-stone-200">
                    <div class="bg-white shadow-2xl shadow-stone-900/15 rounded-sm mx-auto overflow-hidden"
                         style="max-width: 720px;">
                        <iframe id="cert-frame"
                                src="{{ $pdfUrl }}#toolbar=0&navpanes=0&view=FitH"
                                class="w-full bg-white block"
                                style="height: 78vh; min-height: 600px; border: 0;"
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
                    <div class="text-[10px] uppercase tracking-[0.18em] text-stone-500 font-semibold mb-3">{{ __('Παραλήπτης') }}</div>
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 text-lg rounded-full bg-gradient-to-br from-fuchsia-500 to-pink-600 flex items-center justify-center text-white font-semibold shadow-sm shrink-0">
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
                    <div class="text-[10px] uppercase tracking-[0.18em] text-stone-500 font-semibold mb-3">{{ __('Στοιχεία Πιστοποιητικού') }}</div>
                    <dl class="space-y-3">
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <dt class="text-stone-500 shrink-0">{{ __('Τίτλος') }}</dt>
                            <dd class="text-stone-900 font-semibold text-right">{{ $selected->name }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <dt class="text-stone-500 shrink-0">{{ __('Ημ/νία Έκδοσης') }}</dt>
                            <dd class="text-stone-900 font-semibold text-right">{{ $issuedDateGreek }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <dt class="text-stone-500 shrink-0">{{ __('Ώρα Έκδοσης') }}</dt>
                            <dd class="text-stone-900 font-semibold text-right font-mono">{{ $issuedTime }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <dt class="text-stone-500 shrink-0">{{ __('Φορέας') }}</dt>
                            <dd class="text-stone-900 font-semibold text-right">{{ $orgName }}</dd>
                        </div>
                        @if($client->external_id)
                            <div class="flex items-start justify-between gap-3 text-xs">
                                <dt class="text-stone-500 shrink-0">{{ __('Κωδικός') }}</dt>
                                <dd class="text-stone-900 font-semibold text-right font-mono">{{ $client->external_id }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

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
        <div class="mt-12 pt-8 border-t border-stone-200 flex items-center justify-center text-xs text-stone-500">
            <span>&copy; {{ date('Y') }} {{ $orgName }}</span>
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
