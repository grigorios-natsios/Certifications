<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Το Πιστοποιητικό σου') }} — {{ $client->lastname }} {{ $client->name }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800">

<div class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-brand-50 flex flex-col">

    <header class="bg-white/80 backdrop-blur border-b border-slate-200 sticky top-0 z-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto">
                @elseif(file_exists(public_path('images/logo.svg')))
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-10 w-auto">
                @elseif(file_exists(public_path('images/logo.jpg')))
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-10 w-auto">
                @else
                    <span class="w-9 h-9 rounded-md bg-brand-600 text-white flex items-center justify-center"><i class="fas fa-certificate"></i></span>
                    <span class="font-semibold text-slate-800 hidden sm:inline">{{ config('app.name', 'Certifications') }}</span>
                @endif
            </a>
            <a href="{{ route('certificate.download', ['slug' => $client->url_slug, 'category' => $selected->slug]) }}"
               class="btn-primary">
                <i class="fas fa-download"></i>
                <span class="hidden sm:inline">{{ __('Λήψη PDF') }}</span>
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <div class="text-center mb-8">
            <span class="badge badge-brand"><i class="fas fa-circle-check mr-1"></i> {{ __('Επικυρωμένο') }}</span>
            <h1 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900">
                {{ __('Βεβαίωση Παρακολούθησης') }}
            </h1>
            <p class="mt-2 text-slate-600">
                {{ trim(($client->lastname ?? '').' '.($client->name ?? '')) }}
            </p>
        </div>

        @if($categories->count() > 1)
            <div class="flex flex-wrap justify-center gap-2 mb-6">
                @foreach($categories as $cat)
                    <a href="{{ route('certificate.show', $client->url_slug) }}?cat={{ $cat->slug }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition border
                              {{ $cat->id === $selected->id
                                 ? 'bg-brand-600 text-white border-brand-600'
                                 : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between bg-slate-50/60">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-md bg-brand-100 text-brand-700 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-pdf"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('Κατηγορία') }}</p>
                        <p class="font-semibold text-slate-800 truncate">{{ $selected->name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('certificate.pdf', ['slug' => $client->url_slug, 'category' => $selected->slug]) }}"
                       target="_blank" rel="noopener"
                       class="btn-secondary py-1.5 text-xs">
                        <i class="fas fa-arrow-up-right-from-square"></i>
                        <span class="hidden sm:inline">{{ __('Άνοιγμα σε νέα καρτέλα') }}</span>
                    </a>
                </div>
            </div>

            <div class="bg-slate-200">
                <iframe
                    src="{{ route('certificate.pdf', ['slug' => $client->url_slug, 'category' => $selected->slug]) }}#toolbar=0&navpanes=0"
                    class="w-full bg-white"
                    style="height: 80vh; min-height: 600px; border: 0;"
                    title="{{ __('Πιστοποιητικό') }} — {{ $selected->name }}"
                    loading="eager">
                </iframe>
            </div>
        </div>

        <div class="mt-8 grid sm:grid-cols-3 gap-4 text-center">
            <div class="card p-5">
                <i class="fas fa-shield-halved text-brand-600 text-2xl"></i>
                <h3 class="mt-2 font-semibold text-slate-800">{{ __('Επίσημο Έγγραφο') }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ __('Παράγεται απευθείας από το σύστημα.') }}</p>
            </div>
            <div class="card p-5">
                <i class="fas fa-qrcode text-brand-600 text-2xl"></i>
                <h3 class="mt-2 font-semibold text-slate-800">{{ __('Επαλήθευση QR') }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ __('Κάθε πιστοποιητικό φέρει μοναδικό κωδικό.') }}</p>
            </div>
            <div class="card p-5">
                <i class="fas fa-cloud-arrow-down text-brand-600 text-2xl"></i>
                <h3 class="mt-2 font-semibold text-slate-800">{{ __('Λήψη οποτεδήποτε') }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ __('Κατέβασέ το όσες φορές χρειαστεί.') }}</p>
            </div>
        </div>

    </main>

    <footer class="border-t border-slate-200 bg-white/60 py-4">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'Certifications') }}
        </div>
    </footer>
</div>

</body>
</html>
