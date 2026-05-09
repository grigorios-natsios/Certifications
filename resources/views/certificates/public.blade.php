<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Πιστοποιητικό') }} — {{ trim(($client->lastname ?? '').' '.($client->name ?? '')) }}</title>

    <x-favicon />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-100">

<div class="min-h-screen flex flex-col">

    <header class="bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <x-brand-logo class="h-9 w-auto" :fallback-text="config('app.name', 'Certifications')" />
            </a>
            <div class="flex items-center gap-2">
                <span class="hidden sm:inline-flex badge badge-emerald">
                    <i class="fas fa-circle-check"></i> {{ __('Επικυρωμένο') }}
                </span>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">

        <div class="mb-6 lg:mb-8">
            <p class="text-xs font-semibold text-brand-600 uppercase tracking-[0.18em] mb-2">
                {{ __('Πιστοποιητικό') }}
            </p>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight">
                {{ trim(($client->lastname ?? '').' '.($client->name ?? '')) }}
            </h1>
            <p class="mt-1.5 text-sm text-slate-500">
                {{ __('Παρακάτω βλέπεις το επίσημο πιστοποιητικό σου. Μπορείς να το κατεβάσεις σε PDF.') }}
            </p>
        </div>

        @if($categories->count() > 1)
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mr-1">
                    {{ __('Κατηγορία') }}
                </span>
                @foreach($categories as $cat)
                    <a href="{{ route('certificate.show', $client->url_slug) }}?cat={{ $cat->slug }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition
                              {{ $cat->id === $selected->id
                                 ? 'bg-slate-900 text-white border-slate-900'
                                 : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300 hover:text-slate-900' }}">
                        @if($cat->id === $selected->id)
                            <i class="fas fa-circle-check text-[10px]"></i>
                        @endif
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @php
            $pdfRecord  = $client->certificatePdfs->firstWhere('category_id', $selected->id);
            $pdfUrl     = $pdfRecord?->public_url
                ?? route('certificate.pdf', ['slug' => $client->url_slug, 'category' => $selected->slug]);
            $downloadUrl = route('certificate.download', ['slug' => $client->url_slug, 'category' => $selected->slug]);
            $issuedAt   = $pdfRecord?->generated_at?->format('d/m/Y') ?? now()->format('d/m/Y');
        @endphp

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 bg-white">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-md bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-pdf"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900 truncate text-sm">{{ $selected->name }}</p>
                        <p class="text-[11px] text-slate-500 truncate">
                            {{ __('Έκδοση') }}: {{ $issuedAt }}
                            @if($client->external_id)
                                · {{ __('Κωδικός') }}: <span class="font-mono">{{ $client->external_id }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ $pdfUrl }}"
                       target="_blank" rel="noopener"
                       class="btn-secondary text-xs py-1.5"
                       title="{{ __('Άνοιγμα σε νέα καρτέλα') }}">
                        <i class="fas fa-arrow-up-right-from-square"></i>
                        <span class="hidden sm:inline">{{ __('Νέα καρτέλα') }}</span>
                    </a>
                    <a href="{{ $downloadUrl }}"
                       class="btn-primary text-xs py-1.5">
                        <i class="fas fa-download"></i>
                        {{ __('Λήψη PDF') }}
                    </a>
                </div>
            </div>

            <div class="bg-slate-100">
                <iframe
                    src="{{ $pdfUrl }}#toolbar=0&navpanes=0&view=FitH"
                    class="w-full bg-white block"
                    style="height: 82vh; min-height: 640px; border: 0;"
                    title="{{ __('Πιστοποιητικό') }} — {{ $selected->name }}"
                    loading="eager">
                </iframe>
            </div>
        </div>

        <p class="mt-4 text-[11px] text-slate-400 text-center">
            <i class="fas fa-shield-halved mr-1"></i>
            {{ __('Το έγγραφο παράγεται απευθείας από το σύστημα και φέρει μοναδικό σύνδεσμο επαλήθευσης.') }}
        </p>

    </main>

    <footer class="border-t border-slate-200 bg-white py-4">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'Certifications') }}
        </div>
    </footer>
</div>

</body>
</html>