<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('users.index') }}" wire:navigate class="btn-icon" title="Πίσω">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div class="min-w-0">
                    <nav class="text-xs text-slate-500 mb-0.5 flex items-center gap-1.5">
                        <a href="{{ route('users.index') }}" wire:navigate class="hover:text-slate-700">{{ __('Χρήστες') }}</a>
                        <span class="text-slate-300">/</span>
                        <span>{{ __('Νέος') }}</span>
                    </nav>
                    <h1 class="page-title">{{ __('Νέος Χρήστης') }}</h1>
                </div>
            </div>
        </div>
    </x-slot>

    <form wire:submit.prevent="save">
        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                @include('livewire.users._form-fields')
            </div>
        </div>

        <div class="sticky bottom-0 bg-white/95 backdrop-blur border-t border-slate-200">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-3">
                <a href="{{ route('users.index') }}" wire:navigate class="btn-secondary">{{ __('Ακύρωση') }}</a>
                <button type="submit" class="btn-primary">
                    <span wire:loading.remove wire:target="save">{{ __('Δημιουργία χρήστη') }}</span>
                    <span wire:loading wire:target="save">{{ __('Αποθήκευση...') }}</span>
                </button>
            </div>
        </div>
    </form>
</div>
