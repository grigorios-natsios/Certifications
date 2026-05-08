@props([
    'targetId',
    'title'   => 'Επιβεβαίωση διαγραφής',
    'message' => 'Σίγουρα θέλεις να διαγράψεις αυτό το στοιχείο; Η ενέργεια δεν αναιρείται.',
    'confirm' => 'delete',
    'cancel'  => 'cancelDelete',
])

@if($targetId)
    <div x-data
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-6 right-6 z-[70] w-[22rem] bg-white border border-rose-200 rounded-lg shadow-xl p-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-900">{{ $title }}</p>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $message }}</p>
                <div class="flex items-center gap-2 mt-3">
                    <button type="button"
                            wire:click="{{ $confirm }}({{ $targetId }})"
                            wire:loading.attr="disabled"
                            wire:target="{{ $confirm }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-rose-600 text-white text-xs font-medium hover:bg-rose-700 transition disabled:opacity-50">
                        <i class="fas fa-trash text-[10px]" wire:loading.remove wire:target="{{ $confirm }}"></i>
                        <i class="fas fa-circle-notch fa-spin text-[10px]" wire:loading wire:target="{{ $confirm }}"></i>
                        <span>{{ __('Διαγραφή') }}</span>
                    </button>
                    <button type="button"
                            wire:click="{{ $cancel }}"
                            class="px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-100 text-xs font-medium transition">
                        {{ __('Άκυρο') }}
                    </button>
                </div>
            </div>
            <button type="button"
                    wire:click="{{ $cancel }}"
                    class="text-slate-400 hover:text-slate-600 flex-shrink-0">
                <i class="fas fa-xmark text-xs"></i>
            </button>
        </div>
    </div>
@endif
