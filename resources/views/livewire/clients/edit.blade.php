<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('clients.index') }}" wire:navigate class="btn-icon" title="Πίσω">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div class="min-w-0">
                    <nav class="text-xs text-slate-500 mb-0.5 flex items-center gap-1.5">
                        <a href="{{ route('clients.index') }}" wire:navigate class="hover:text-slate-700">{{ __('Πελάτες') }}</a>
                        <span class="text-slate-300">/</span>
                        <span>{{ __('Επεξεργασία') }}</span>
                    </nav>
                    <h1 class="page-title truncate">
                        {{ trim(($client->lastname ?? '').' '.($client->name ?? '')) ?: __('Επεξεργασία Πελάτη') }}
                    </h1>
                </div>
            </div>
            @if($client->url_slug)
                <a href="/c/{{ $client->url_slug }}" target="_blank" rel="noopener" class="btn-secondary">
                    <i class="fas fa-external-link-alt text-xs"></i> {{ __('Προβολή πιστοποιητικού') }}
                </a>
            @endif
        </div>
    </x-slot>

    <form wire:submit.prevent="save">
        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                @include('livewire.clients._form-fields')

                <div class="form-section">
                    <div class="form-section-head">
                        <h2 class="section-title text-rose-700">{{ __('Επικίνδυνη Ζώνη') }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Μη αναστρέψιμες ενέργειες') }}</p>
                    </div>
                    <div class="form-section-body flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ __('Διαγραφή πελάτη') }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ __('Διαγράφονται και όλα τα συσχετισμένα στοιχεία.') }}</p>
                        </div>
                        <button type="button" wire:click="delete"
                                onclick="return confirm('Σίγουρα θέλεις να διαγράψεις αυτόν τον πελάτη; Δεν αναιρείται.')"
                                class="btn-danger">
                            {{ __('Διαγραφή') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 bg-white/95 backdrop-blur border-t border-slate-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-3">
                <a href="{{ route('clients.index') }}" wire:navigate class="btn-secondary">{{ __('Ακύρωση') }}</a>
                <button type="submit" class="btn-primary">
                    <span wire:loading.remove wire:target="save">{{ __('Αποθήκευση αλλαγών') }}</span>
                    <span wire:loading wire:target="save">{{ __('Αποθήκευση...') }}</span>
                </button>
            </div>
        </div>
    </form>
</div>
