<div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Πελάτες') }}</h1>
                <p class="page-subtitle">{{ __('Διαχείριση πελατών & παραγωγή πιστοποιητικών') }}</p>
            </div>
            <div class="toolbar">
                <a href="{{ route('clients.create') }}" wire:navigate class="btn-primary">
                    {{ __('Νέος πελάτης') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            <div class="section-card">
                <div class="section-card-head">
                    <div class="flex items-baseline gap-3">
                        <h2 class="section-title">{{ __('Λίστα πελατών') }}</h2>
                        <span class="text-xs text-slate-500">{{ $clients->total() }} {{ __('εγγραφές') }}</span>
                    </div>
                    <div class="toolbar">
                        @if(count($selected))
                            <span class="text-xs text-slate-600">{{ count($selected) }} {{ __('επιλεγμένοι') }}</span>
                        @endif

                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button" @click="open = ! open" class="btn-secondary">
                                <i class="fas fa-table-columns text-xs"></i>
                                {{ __('Στήλες') }}
                                <span class="text-[10px] text-slate-500">({{ count($visibleColumns) + 2 }})</span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>
                            <div x-show="open" x-cloak x-transition.opacity
                                 class="absolute right-0 z-30 mt-1 w-64 bg-white border border-slate-200 rounded-md shadow-lg max-h-96 overflow-y-auto">
                                <div class="p-2">
                                    <p class="px-2 pt-1 pb-1.5 text-[11px] text-slate-400 font-semibold">{{ __('Βασικές στήλες') }}</p>
                                    @foreach($this->columnDefinitions as $key => $label)
                                        <label class="flex items-center gap-2 px-2 py-1.5 text-sm rounded hover:bg-slate-50 cursor-pointer">
                                            <input type="checkbox" value="{{ $key }}" wire:model.live="visibleColumns"
                                                   class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            <span class="text-slate-700">{{ $label }}</span>
                                        </label>
                                    @endforeach

                                    @if($customFields->count())
                                        <p class="px-2 pt-3 pb-1.5 text-[11px] text-slate-400 font-semibold">{{ __('Custom πεδία') }}</p>
                                        @foreach($customFields as $field)
                                            <label class="flex items-center gap-2 px-2 py-1.5 text-sm rounded hover:bg-slate-50 cursor-pointer">
                                                <input type="checkbox" value="cf_{{ $field->id }}" wire:model.live="visibleColumns"
                                                       class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                <span class="text-slate-700">{{ $field->name }}</span>
                                            </label>
                                        @endforeach
                                    @endif

                                    <div class="border-t border-slate-100 mt-2 pt-2 px-2 flex items-center justify-between">
                                        <button type="button" wire:click="$set('visibleColumns', ['email','categories','url','created'])"
                                                class="text-xs text-slate-500 hover:text-slate-700">{{ __('Προεπιλογή') }}</button>
                                        <button type="button" wire:click="$set('visibleColumns', [])"
                                                class="text-xs text-slate-500 hover:text-rose-600">{{ __('Καθαρισμός') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" wire:click="generatePdfs" class="btn-secondary"
                                @if(empty($selected)) disabled @endif
                                wire:loading.attr="disabled" wire:target="generatePdfs,sendEmails">
                            <i class="fas fa-file-pdf text-xs" wire:loading.remove wire:target="generatePdfs"></i>
                            <i class="fas fa-circle-notch fa-spin text-xs" wire:loading wire:target="generatePdfs"></i>
                            <span wire:loading.remove wire:target="generatePdfs">{{ __('Παραγωγή PDF') }}</span>
                            <span wire:loading wire:target="generatePdfs">{{ __('Δημιουργία...') }}</span>
                        </button>

                        <button type="button" wire:click="sendEmails" class="btn-secondary"
                                @if(empty($selected)) disabled @endif
                                wire:loading.attr="disabled" wire:target="sendEmails,generatePdfs">
                            <i class="fas fa-paper-plane text-xs" wire:loading.remove wire:target="sendEmails"></i>
                            <i class="fas fa-circle-notch fa-spin text-xs" wire:loading wire:target="sendEmails"></i>
                            <span wire:loading.remove wire:target="sendEmails">{{ __('Αποστολή Email') }}</span>
                            <span wire:loading wire:target="sendEmails">{{ __('Αποστολή...') }}</span>
                        </button>
                    </div>
                </div>

                <div class="border-b border-slate-200 bg-white">
                    <div class="px-5 py-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="relative flex-1 min-w-[240px]">
                                <i class="fas fa-magnifying-glass input-icon"></i>
                                <input type="text" wire:model.live.debounce.300ms="search"
                                       placeholder="{{ __('Αναζήτηση: όνομα, επώνυμο, email, slug...') }}"
                                       class="input input-with-icon bg-white">
                                @if($search !== '')
                                    <button type="button" wire:click="$set('search', '')"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1"
                                            title="{{ __('Καθαρισμός') }}">
                                        <i class="fas fa-xmark text-xs"></i>
                                    </button>
                                @endif
                            </div>

                            <select wire:model.live="categoryFilter" class="input bg-white w-auto min-w-[180px]">
                                <option value="">{{ __('Όλες οι κατηγορίες') }}</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>

                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = ! open" class="btn-secondary">
                                    <i class="fas fa-sliders text-xs"></i>
                                    {{ __('Φίλτρα') }}
                                    @if($this->activeFilterCount > 0)
                                        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-brand-600 text-white text-[10px] font-semibold">{{ $this->activeFilterCount }}</span>
                                    @endif
                                    <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                                </button>
                                <div x-show="open" x-cloak x-transition.opacity
                                     class="absolute right-0 z-30 mt-1 w-72 bg-white border border-slate-200 rounded-md shadow-lg max-h-80 overflow-y-auto">
                                    <div class="p-2">
                                        <p class="px-2 py-1 text-[11px] text-slate-400 font-semibold">{{ __('Πεδία') }}</p>
                                        @php $availableCustom = $customFields->whereNotIn('id', $activeCustomFilters); @endphp

                                        @if($hasUrl === '')
                                            <button type="button" @click="open = false" wire:click="$set('hasUrl', 'yes')" class="w-full text-left px-2 py-1.5 text-sm rounded hover:bg-slate-50 flex items-center justify-between">
                                                <span>{{ __('Έχει public URL') }}</span>
                                                <span class="text-slate-400 text-xs">slug</span>
                                            </button>
                                        @endif

                                        @if($availableCustom->count() === 0 && $hasUrl !== '')
                                            <p class="px-2 py-2 text-xs text-slate-500">{{ __('Όλα τα διαθέσιμα φίλτρα είναι ενεργά.') }}</p>
                                        @endif

                                        @if($availableCustom->count())
                                            <p class="px-2 pt-2 pb-1 text-[11px] text-slate-400 font-semibold">{{ __('Custom πεδία') }}</p>
                                            @foreach($availableCustom as $field)
                                                <button type="button" @click="open = false" wire:click="addCustomFilter({{ $field->id }})"
                                                        class="w-full text-left px-2 py-1.5 text-sm rounded hover:bg-slate-50 flex items-center justify-between">
                                                    <span>{{ $field->name }}</span>
                                                    <span class="text-slate-400 text-[10px]">{{ $field->type }}</span>
                                                </button>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($hasUrl !== '' || count($activeCustomFilters))
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @if($hasUrl !== '')
                                    <div class="inline-flex items-stretch border border-slate-200 rounded-md bg-white overflow-hidden text-sm">
                                        <span class="px-2.5 py-1 bg-slate-50 text-xs text-slate-500 border-r border-slate-200 flex items-center">{{ __('Public URL') }}</span>
                                        <select wire:model.live="hasUrl" class="border-0 text-sm py-1 pr-7 focus:ring-0 focus:border-0">
                                            <option value="yes">{{ __('Έχει') }}</option>
                                            <option value="no">{{ __('Δεν έχει') }}</option>
                                        </select>
                                        <button type="button" wire:click="$set('hasUrl', '')" class="px-2 text-slate-400 hover:text-rose-600" title="{{ __('Αφαίρεση') }}">
                                            <i class="fas fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                @endif

                                @foreach($activeCustomFilters as $fieldId)
                                    @php $field = $customFields->firstWhere('id', $fieldId); @endphp
                                    @if($field)
                                        <div wire:key="filter-{{ $field->id }}" class="inline-flex items-stretch border border-slate-200 rounded-md bg-white overflow-hidden text-sm">
                                            <span class="px-2.5 py-1 bg-slate-50 text-xs text-slate-500 border-r border-slate-200 flex items-center whitespace-nowrap" title="{{ $field->name }}">
                                                {{ \Illuminate\Support\Str::limit($field->name, 18) }}
                                            </span>
                                            <input
                                                type="{{ $field->type === 'number' ? 'number' : ($field->type === 'date' ? 'date' : 'text') }}"
                                                wire:model.live.debounce.400ms="customFilters.{{ $field->id }}"
                                                placeholder="{{ __('Τιμή...') }}"
                                                class="border-0 text-sm py-1 px-2 focus:ring-0 focus:border-0 w-36 min-w-0">
                                            <button type="button" wire:click="removeCustomFilter({{ $field->id }})" class="px-2 text-slate-400 hover:text-rose-600" title="{{ __('Αφαίρεση') }}">
                                                <i class="fas fa-xmark text-xs"></i>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach

                                <button type="button" wire:click="clearAllFilters" class="text-xs text-slate-500 hover:text-rose-600 underline underline-offset-2 ml-auto">
                                    {{ __('Καθαρισμός όλων') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                @php
                    $visibleCustomFields = $customFields->filter(fn($f) => in_array('cf_'.$f->id, $visibleColumns));
                    $colspan = 3 + count($visibleColumns) + $visibleCustomFields->count(); // checkbox + name + actions + visible
                @endphp

                <div class="overflow-x-auto">
                    <table class="table-app">
                        <thead>
                            <tr>
                                <th class="w-10">
                                    <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                </th>
                                @if($this->isColumnVisible('id'))
                                    <th class="w-14">ID</th>
                                @endif
                                <th>{{ __('Πελάτης') }}</th>
                                @if($this->isColumnVisible('email'))
                                    <th>{{ __('Email') }}</th>
                                @endif
                                @if($this->isColumnVisible('url'))
                                    <th>{{ __('URL') }}</th>
                                @endif
                                @if($this->isColumnVisible('external'))
                                    <th>{{ __('Excel ID') }}</th>
                                @endif
                                @if($this->isColumnVisible('categories'))
                                    <th>{{ __('Κατηγορίες') }}</th>
                                @endif
                                @foreach($visibleCustomFields as $field)
                                    <th class="whitespace-nowrap">
                                        {{ $field->name }}
                                        @if(in_array($field->id, $activeCustomFilters))
                                            <i class="fas fa-filter text-brand-600 text-[9px] ml-1" title="Φιλτράρεται"></i>
                                        @endif
                                    </th>
                                @endforeach
                                @if($this->isColumnVisible('created'))
                                    <th>{{ __('Ημ/νία') }}</th>
                                @endif
                                <th class="text-right">{{ __('Ενέργειες') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($clients as $client)
                                <tr wire:key="client-{{ $client->id }}">
                                    <td>
                                        <input type="checkbox" value="{{ $client->id }}" wire:model.live="selected" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    </td>
                                    @if($this->isColumnVisible('id'))
                                        <td class="text-slate-400 font-mono text-xs">{{ $client->id }}</td>
                                    @endif
                                    <td>
                                        <div class="flex items-center gap-3">
                                            @php
                                                $lastInitial = $client->lastname ? mb_strtoupper(mb_substr($client->lastname, 0, 1, 'UTF-8'), 'UTF-8') : '';
                                                $firstInitial = $client->name ? mb_strtoupper(mb_substr($client->name, 0, 1, 'UTF-8'), 'UTF-8') : '';
                                                $initials = $lastInitial.$firstInitial ?: '—';
                                            @endphp
                                            <span class="avatar w-8 h-8">{{ $initials }}</span>
                                            <div class="min-w-0">
                                                <p class="font-medium text-slate-900 truncate">
                                                    {{ trim(($client->lastname ?? '').' '.($client->name ?? '')) ?: '—' }}
                                                </p>
                                                @if($client->email && ! $this->isColumnVisible('email'))
                                                    <p class="text-[11px] text-slate-500 truncate">{{ $client->email }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    @if($this->isColumnVisible('email'))
                                        <td class="text-slate-600">
                                            @if($client->email)
                                                <a href="mailto:{{ $client->email }}" class="hover:text-slate-900">{{ $client->email }}</a>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                    @endif
                                    @if($this->isColumnVisible('url'))
                                        <td>
                                            @if($client->url_slug)
                                                <a href="{{ route('certificate.show', $client->url_slug) }}" target="_blank" rel="noopener" class="text-slate-700 hover:text-brand-600 font-mono text-xs">
                                                    {{ $client->url_slug }}
                                                </a>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                    @endif
                                    @if($this->isColumnVisible('external'))
                                        <td class="text-slate-500 font-mono text-xs">{{ $client->external_id ?: '—' }}</td>
                                    @endif
                                    @if($this->isColumnVisible('categories'))
                                        <td>
                                            @forelse($client->certificateCategories as $cat)
                                                @php
                                                    $hasPdf = $cat->html_template && $client->url_slug;
                                                @endphp
                                                @if($hasPdf)
                                                    <a href="{{ route('certificate.pdf', ['slug' => $client->url_slug, 'category' => $cat->slug]) }}"
                                                       target="_blank" rel="noopener"
                                                       class="badge badge-emerald hover:bg-emerald-100 transition"
                                                       title="Άνοιγμα PDF — {{ $cat->name }}">
                                                        <i class="fas fa-file-pdf text-[10px]"></i>
                                                        {{ $cat->name }}
                                                    </a>
                                                @else
                                                    <span class="badge badge-slate" title="{{ $cat->html_template ? 'Λείπει URL slug' : 'Λείπει template' }}">
                                                        <i class="fas fa-circle-exclamation text-[10px] text-amber-500"></i>
                                                        {{ $cat->name }}
                                                    </span>
                                                @endif
                                            @empty
                                                <span class="text-slate-300">—</span>
                                            @endforelse
                                        </td>
                                    @endif
                                    @foreach($visibleCustomFields as $field)
                                        @php $val = optional($client->customValues->firstWhere('custom_field_id', $field->id))->value; @endphp
                                        <td class="text-slate-600">{{ $val ?: '—' }}</td>
                                    @endforeach
                                    @if($this->isColumnVisible('created'))
                                        <td class="text-slate-500 whitespace-nowrap text-xs">{{ $client->created_at?->format('d/m/Y') }}</td>
                                    @endif
                                    <td class="text-right whitespace-nowrap">
                                        @if($client->url_slug)
                                            <a href="{{ route('certificate.show', $client->url_slug) }}" target="_blank" rel="noopener" class="btn-icon" title="Προβολή">
                                                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('clients.edit', $client->id) }}" wire:navigate class="btn-icon" title="Επεξεργασία">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <button type="button" wire:click="confirmDelete({{ $client->id }})"
                                                class="btn-icon-danger" title="Διαγραφή">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $colspan }}">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-users text-slate-400"></i></div>
                                            <h3 class="mt-3 text-sm font-medium text-slate-900">{{ __('Δεν υπάρχουν πελάτες') }}</h3>
                                            <p class="text-xs text-slate-500 mt-1">{{ __('Πρόσθεσε νέο ή εισήγαγε από Excel.') }}</p>
                                            <a href="{{ route('clients.create') }}" wire:navigate class="btn-primary mt-4 inline-flex">
                                                {{ __('Νέος πελάτης') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-slate-200">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-confirm-delete-toast :targetId="$confirmingDeleteId"
                            message="Σίγουρα θέλεις να διαγράψεις αυτόν τον πελάτη; Τα στοιχεία του χάνονται οριστικά." />

    <div wire:loading.flex wire:target="generatePdfs,sendEmails"
         class="fixed inset-0 z-[65] bg-slate-900/40 backdrop-blur-[2px] items-center justify-center px-4"
         style="display: none;">
        <div class="bg-white rounded-lg shadow-2xl border border-brand-200 p-5 max-w-md w-full">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0 text-xl">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900">
                        <span wire:loading wire:target="generatePdfs">{{ __('Δημιουργία PDF σε εξέλιξη...') }}</span>
                        <span wire:loading wire:target="sendEmails">{{ __('Αποστολή email σε εξέλιξη...') }}</span>
                    </p>
                    <p class="text-sm text-slate-600 mt-1">
                        {{ __('Επεξεργασία') }} {{ count($selected) }} {{ count($selected) === 1 ? __('εγγραφής') : __('εγγραφών') }} — {{ __('μην κλείσεις τη σελίδα.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{ result: null, dismiss() { this.result = null; } }"
         x-on:operation-result.window="result = $event.detail; setTimeout(() => result = null, 5000)"
         x-show="result" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[68] flex items-center justify-center pointer-events-none px-4">
        <div class="bg-white rounded-lg shadow-2xl border pointer-events-auto p-5 max-w-md w-full"
             :class="result?.type === 'warning' ? 'border-amber-200' : 'border-emerald-200'"
             @click.outside="dismiss()">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 text-xl"
                     :class="result?.type === 'warning' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'">
                    <i class="fas" :class="result?.type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-check'"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900" x-text="result?.title"></p>
                    <p class="text-sm text-slate-600 mt-1" x-text="result?.message"></p>
                </div>
                <button type="button" @click="dismiss()" class="text-slate-400 hover:text-slate-600 flex-shrink-0">
                    <i class="fas fa-xmark text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    <div x-data="{ items: @js(session()->get('toast') ? [array_merge(['id' => time()], session()->pull('toast'))] : []) }"
         x-on:toast.window="items.push({ id: Date.now(), ...$event.detail }); setTimeout(() => items.shift(), 3500)"
         class="fixed top-4 right-4 z-[60] space-y-2">
        <template x-for="t in items" :key="t.id">
            <div class="px-4 py-2.5 rounded-md shadow-md border bg-white text-sm flex items-center gap-2"
                 :class="{ 'border-emerald-200 text-emerald-800': t.type === 'success', 'border-rose-200 text-rose-800': t.type === 'error', 'border-amber-200 text-amber-800': t.type === 'warning' }">
                <span class="w-1.5 h-1.5 rounded-full"
                      :class="{ 'bg-emerald-500': t.type === 'success', 'bg-rose-500': t.type === 'error', 'bg-amber-500': t.type === 'warning' }"></span>
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>
</div>
