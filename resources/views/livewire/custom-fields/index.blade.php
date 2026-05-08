<div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Προσαρμοσμένα πεδία') }}</h1>
                <p class="page-subtitle">{{ __('Επιπλέον πεδία πελατών (ΑΦΜ, Τηλέφωνο, ώρες κ.λπ.)') }}</p>
            </div>
            <button wire:click="openCreate" class="btn-primary">
                {{ __('Νέο πεδίο') }}
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="section-card">
                <div class="section-card-head">
                    <div class="flex items-center gap-3">
                        <h2 class="section-title">{{ __('Πεδία') }}</h2>
                        <span class="badge badge-slate">{{ $fields->total() }}</span>
                    </div>
                    <div class="relative">
                        <i class="fas fa-magnifying-glass input-icon"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Αναζήτηση..." class="input input-with-icon">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-app">
                        <thead>
                            <tr>
                                <th class="w-14">ID</th>
                                <th>{{ __('Όνομα') }}</th>
                                <th>{{ __('Τύπος') }}</th>
                                <th>{{ __('Απαραίτητο') }}</th>
                                <th class="text-right">{{ __('Ενέργειες') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($fields as $field)
                                <tr wire:key="field-{{ $field->id }}">
                                    <td class="text-slate-400 font-mono text-xs">{{ $field->id }}</td>
                                    <td>
                                        <span class="font-medium text-slate-900">{{ $field->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-slate">{{ $types[$field->type] ?? $field->type }}</span>
                                    </td>
                                    <td>
                                        @if($field->is_required)
                                            <span class="status-dot status-dot-brand">{{ __('Απαραίτητο') }}</span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right whitespace-nowrap">
                                        <button wire:click="openEdit({{ $field->id }})" class="btn-icon" title="Επεξεργασία"><i class="fas fa-pen text-xs"></i></button>
                                        <button type="button" wire:click="confirmDelete({{ $field->id }})"
                                                class="btn-icon-danger" title="Διαγραφή"><i class="fas fa-trash text-xs"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-list-check text-slate-400"></i></div>
                                            <h3 class="mt-3 text-sm font-medium text-slate-900">{{ __('Δεν υπάρχουν προσαρμοσμένα πεδία') }}</h3>
                                            <p class="text-xs text-slate-500 mt-1">{{ __('Δημιούργησε επιπλέον πεδία (π.χ. Διάρκεια, ΚΔΒΜ).') }}</p>
                                            <button wire:click="openCreate" class="btn-primary mt-4 inline-flex">
                                                {{ __('Νέο πεδίο') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-slate-200">
                    {{ $fields->links() }}
                </div>
            </div>
        </div>

        @if($showModal)
            <div class="modal-backdrop" wire:key="field-modal">
                <div class="modal-panel" @click.stop>
                    <div class="modal-header">
                        <h3 class="section-title">{{ $editingId ? __('Επεξεργασία πεδίου') : __('Νέο πεδίο') }}</h3>
                        <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600"><i class="fas fa-xmark"></i></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div>
                                <label class="label">{{ __('Όνομα') }}</label>
                                <input type="text" wire:model="name" class="input" autofocus>
                                @error('name') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">{{ __('Τύπος') }}</label>
                                <select wire:model="type" class="input">
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" wire:model="is_required" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm text-slate-700">{{ __('Απαραίτητο') }}</span>
                            </label>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="closeModal" class="btn-secondary">{{ __('Άκυρο') }}</button>
                            <button type="submit" class="btn-primary">{{ __('Αποθήκευση') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <x-confirm-delete-toast :targetId="$confirmingDeleteId"
                            message="Σίγουρα θέλεις να διαγράψεις αυτό το πεδίο; Οι τιμές που έχουν αποθηκευτεί στους πελάτες θα χαθούν." />

    <div x-data="{ items: [] }"
         x-on:toast.window="items.push({ id: Date.now(), ...$event.detail }); setTimeout(() => items.shift(), 3500)"
         class="fixed top-4 right-4 z-[60] space-y-2">
        <template x-for="t in items" :key="t.id">
            <div class="px-4 py-2 rounded-md shadow text-sm text-white"
                 :class="{ 'bg-brand-600': t.type === 'success', 'bg-rose-600': t.type === 'error', 'bg-amber-500': t.type === 'warning' }">
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>
</div>
