<div>
    @php
        // Stable color per category (keyed by id) so the same category
        // always shows in the same color across rows. Tailwind needs to
        // see the literal class names, so we list them explicitly.
        $catPalette = [
            ['bg' => 'bg-sky-50',     'fg' => 'text-sky-700',     'border' => 'border-sky-200',     'dot' => 'bg-sky-500',     'hover' => 'hover:bg-sky-100'],
            ['bg' => 'bg-amber-50',   'fg' => 'text-amber-700',   'border' => 'border-amber-200',   'dot' => 'bg-amber-500',   'hover' => 'hover:bg-amber-100'],
            ['bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500', 'hover' => 'hover:bg-emerald-100'],
            ['bg' => 'bg-violet-50',  'fg' => 'text-violet-700',  'border' => 'border-violet-200',  'dot' => 'bg-violet-500',  'hover' => 'hover:bg-violet-100'],
            ['bg' => 'bg-rose-50',    'fg' => 'text-rose-700',    'border' => 'border-rose-200',    'dot' => 'bg-rose-500',    'hover' => 'hover:bg-rose-100'],
            ['bg' => 'bg-teal-50',    'fg' => 'text-teal-700',    'border' => 'border-teal-200',    'dot' => 'bg-teal-500',    'hover' => 'hover:bg-teal-100'],
        ];
        $catColor = fn ($id) => $catPalette[$id % count($catPalette)];
    @endphp

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="page-title">{{ __('Πελάτες') }}</h1>
                    @if(! empty($lastExternalId))
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-gradient-to-r from-emerald-50 to-teal-50 text-emerald-800 ring-1 ring-inset ring-emerald-200 shadow-sm"
                              title="{{ __('Πιο πρόσφατο Excel ID που καταχωρήθηκε') }}">
                            <i class="fas fa-hashtag text-[10px] text-emerald-500"></i>
                            <span class="text-emerald-700">{{ __('Τελευταίο Excel ID:') }}</span>
                            <span class="font-mono font-semibold text-emerald-900">{{ $lastExternalId }}</span>
                        </span>
                    @endif
                </div>
                <p class="page-subtitle">{{ __('Διαχείριση πελατών & παραγωγή πιστοποιητικών') }}</p>
            </div>
            <div class="toolbar">
                <a href="{{ route('clients.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-600 hover:to-brand-500 text-white text-sm font-semibold shadow-brand transition-all">
                    <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                    {{ __('Νέος πελάτης') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-baseline gap-3">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">{{ __('Λίστα πελατών') }}</h2>
                        <span class="text-xs text-slate-500"><span class="font-semibold text-slate-900">{{ $clients->total() }}</span> {{ __('εγγραφές') }}</span>
                    </div>
                    <div class="toolbar">
                        @if(count($selected))
                            <span class="text-xs font-semibold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-md ring-1 ring-inset ring-brand-200">
                                {{ count($selected) }} {{ __('επιλεγμένοι') }}
                            </span>
                        @endif

                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button" @click="open = ! open"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 text-sm font-medium text-slate-700 transition">
                                <i class="fas fa-table-columns text-xs"></i>
                                {{ __('Στήλες') }}
                                <span class="text-[10px] text-slate-500">({{ count($visibleColumns) + 2 }})</span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>
                            <div x-show="open" x-cloak x-transition.opacity
                                 class="absolute right-0 z-30 mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl max-h-96 overflow-y-auto">
                                <div class="p-2">
                                    <p class="px-2 pt-1 pb-1.5 text-[11px] text-slate-400 font-semibold uppercase tracking-wider">{{ __('Βασικές στήλες') }}</p>
                                    @foreach($this->columnDefinitions as $key => $label)
                                        <label class="flex items-center gap-2 px-2 py-1.5 text-sm rounded-md hover:bg-slate-50 cursor-pointer">
                                            <input type="checkbox" value="{{ $key }}" wire:model.live="visibleColumns"
                                                   class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            <span class="text-slate-700">{{ $label }}</span>
                                        </label>
                                    @endforeach

                                    @if($customFields->count())
                                        <p class="px-2 pt-3 pb-1.5 text-[11px] text-slate-400 font-semibold uppercase tracking-wider">{{ __('Custom πεδία') }}</p>
                                        @foreach($customFields as $field)
                                            <label class="flex items-center gap-2 px-2 py-1.5 text-sm rounded-md hover:bg-slate-50 cursor-pointer">
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

                        <button type="button" wire:click="generatePdfs"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 text-sm font-medium text-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                @if(empty($selected)) disabled @endif
                                wire:loading.attr="disabled" wire:target="generatePdfs,sendEmails,downloadPdfs">
                            <i class="fas fa-file-pdf text-xs" wire:loading.remove wire:target="generatePdfs"></i>
                            <i class="fas fa-circle-notch fa-spin text-xs" wire:loading wire:target="generatePdfs"></i>
                            <span wire:loading.remove wire:target="generatePdfs">{{ __('Παραγωγή PDF') }}</span>
                            <span wire:loading wire:target="generatePdfs">{{ __('Δημιουργία...') }}</span>
                        </button>

                        <button type="button" wire:click="downloadPdfs"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 text-sm font-medium text-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                @if(empty($selected)) disabled @endif
                                wire:loading.attr="disabled" wire:target="downloadPdfs,generatePdfs,sendEmails">
                            <i class="fas fa-file-zipper text-xs" wire:loading.remove wire:target="downloadPdfs"></i>
                            <i class="fas fa-circle-notch fa-spin text-xs" wire:loading wire:target="downloadPdfs"></i>
                            <span wire:loading.remove wire:target="downloadPdfs">{{ __('Λήψη PDF (ZIP)') }}</span>
                            <span wire:loading wire:target="downloadPdfs">{{ __('Συμπίεση...') }}</span>
                        </button>

                        <button type="button" wire:click="sendEmails"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 text-sm font-medium text-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                @if(empty($selected)) disabled @endif
                                wire:loading.attr="disabled" wire:target="sendEmails,generatePdfs,downloadPdfs">
                            <i class="fas fa-paper-plane text-xs" wire:loading.remove wire:target="sendEmails"></i>
                            <i class="fas fa-circle-notch fa-spin text-xs" wire:loading wire:target="sendEmails"></i>
                            <span wire:loading.remove wire:target="sendEmails">{{ __('Αποστολή Email') }}</span>
                            <span wire:loading wire:target="sendEmails">{{ __('Αποστολή...') }}</span>
                        </button>

                        <button type="button" wire:click="confirmBulkDelete"
                                @if(empty($selected)) disabled @endif
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-rose-200 bg-white text-rose-600 hover:bg-rose-50 hover:border-rose-300 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-trash text-xs"></i>
                            <span>{{ __('Διαγραφή') }}</span>
                        </button>
                    </div>
                </div>

                <div class="border-b border-slate-200 bg-white">
                    <div class="px-5 py-4">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <div class="relative flex-1 min-w-[260px]">
                                <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm"></i>
                                <input type="text" wire:model.live.debounce.300ms="search"
                                       placeholder="{{ __('Αναζήτηση: όνομα, επώνυμο, email, slug...') }}"
                                       class="w-full pl-10 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 focus:bg-white transition-all">
                                @if($search !== '')
                                    <button type="button" wire:click="$set('search', '')"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1"
                                            title="{{ __('Καθαρισμός') }}">
                                        <i class="fas fa-xmark text-xs"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="relative">
                                <select wire:model.live="categoryFilter"
                                        class="appearance-none pl-9 pr-9 py-2.5 rounded-lg text-sm font-medium border bg-white transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-600/20
                                               {{ $categoryFilter !== '' ? 'border-brand-600 bg-brand-50 text-brand-700' : 'border-slate-200 text-slate-700 hover:border-slate-300' }}">
                                    <option value="">{{ __('Όλες οι κατηγορίες') }}</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <i class="fas fa-tags absolute left-3 top-1/2 -translate-y-1/2 text-xs pointer-events-none {{ $categoryFilter !== '' ? 'text-brand-600' : 'text-slate-400' }}"></i>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] pointer-events-none {{ $categoryFilter !== '' ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            </div>

                            <div class="inline-flex items-stretch border rounded-lg bg-white overflow-hidden text-sm transition
                                        {{ ($createdFrom || $createdTo) ? 'border-brand-600' : 'border-slate-200' }}"
                                 title="{{ __('Ημερομηνία δημιουργίας — εύρος') }}">
                                <span class="px-2.5 py-2 bg-slate-50 text-xs font-medium text-slate-500 border-r border-slate-200 flex items-center whitespace-nowrap">
                                    <i class="fas fa-calendar-days text-[10px] mr-1.5"></i>{{ __('Από') }}
                                </span>
                                <input type="date" wire:model.live="createdFrom" class="border-0 text-sm py-1 px-2 focus:ring-0 focus:border-0 w-[140px]">
                                <span class="px-2 py-2 bg-slate-50 text-xs font-medium text-slate-500 border-x border-slate-200 flex items-center">{{ __('έως') }}</span>
                                <input type="date" wire:model.live="createdTo" class="border-0 text-sm py-1 px-2 focus:ring-0 focus:border-0 w-[140px]">
                                @if($createdFrom || $createdTo)
                                    <button type="button" wire:click="clearDateRange"
                                            class="px-2.5 text-slate-400 hover:text-rose-600 border-l border-slate-200" title="{{ __('Καθαρισμός') }}">
                                        <i class="fas fa-xmark text-xs"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = ! open"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium border bg-white transition
                                               {{ $this->activeFilterCount > 0 ? 'border-brand-600 bg-brand-50 text-brand-700' : 'border-slate-200 text-slate-700 hover:border-slate-300' }}">
                                    <i class="fas fa-sliders text-xs"></i>
                                    {{ __('Φίλτρα') }}
                                    @if($this->activeFilterCount > 0)
                                        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-brand-600 text-white text-[10px] font-semibold">{{ $this->activeFilterCount }}</span>
                                    @endif
                                    <i class="fas fa-chevron-down text-[10px] {{ $this->activeFilterCount > 0 ? 'text-brand-600' : 'text-slate-400' }}"></i>
                                </button>
                                <div x-show="open" x-cloak x-transition.opacity
                                     class="absolute right-0 z-30 mt-2 w-72 bg-white border border-slate-200 rounded-xl shadow-xl max-h-80 overflow-y-auto">
                                    <div class="p-2">
                                        <p class="px-2 py-1 text-[11px] text-slate-400 font-semibold uppercase tracking-wider">{{ __('Πεδία') }}</p>
                                        @php $availableCustom = $customFields->whereNotIn('id', $activeCustomFilters); @endphp

                                        @if($hasUrl === '')
                                            <button type="button" @click="open = false" wire:click="$set('hasUrl', 'yes')" class="w-full text-left px-2 py-1.5 text-sm rounded-md hover:bg-slate-50 flex items-center justify-between">
                                                <span>{{ __('Έχει public URL') }}</span>
                                                <span class="text-slate-400 text-xs">slug</span>
                                            </button>
                                        @endif

                                        @if($availableCustom->count() === 0 && $hasUrl !== '')
                                            <p class="px-2 py-2 text-xs text-slate-500">{{ __('Όλα τα διαθέσιμα φίλτρα είναι ενεργά.') }}</p>
                                        @endif

                                        @if($availableCustom->count())
                                            <p class="px-2 pt-2 pb-1 text-[11px] text-slate-400 font-semibold uppercase tracking-wider">{{ __('Custom πεδία') }}</p>
                                            @foreach($availableCustom as $field)
                                                <button type="button" @click="open = false" wire:click="addCustomFilter({{ $field->id }})"
                                                        class="w-full text-left px-2 py-1.5 text-sm rounded-md hover:bg-slate-50 flex items-center justify-between">
                                                    <span>{{ $field->name }}</span>
                                                    <span class="text-slate-400 text-[10px]">{{ $field->type }}</span>
                                                </button>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($search !== '' || $categoryFilter !== '' || $createdFrom || $createdTo || $hasUrl !== '' || count($activeCustomFilters))
                                <button type="button" wire:click="clearAllFilters"
                                        class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-900 transition">
                                    <i class="fas fa-xmark text-[10px]"></i>
                                    {{ __('Καθαρισμός') }}
                                </button>
                            @endif

                            <div class="ml-auto text-xs text-slate-500 font-medium whitespace-nowrap">
                                <span class="text-slate-900 font-bold">{{ $clients->total() }}</span> {{ __('εγγραφές') }}
                            </div>
                        </div>

                        @if($hasUrl !== '' || count($activeCustomFilters))
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @if($hasUrl !== '')
                                    <div class="inline-flex items-stretch border border-brand-200 rounded-lg bg-white overflow-hidden text-sm">
                                        <span class="px-2.5 py-1 bg-brand-50 text-xs font-medium text-brand-700 border-r border-brand-200 flex items-center">{{ __('Public URL') }}</span>
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
                                        <div wire:key="filter-{{ $field->id }}" class="inline-flex items-stretch border border-brand-200 rounded-lg bg-white overflow-hidden text-sm">
                                            <span class="px-2.5 py-1 bg-brand-50 text-xs font-medium text-brand-700 border-r border-brand-200 flex items-center whitespace-nowrap" title="{{ $field->name }}">
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
                            </div>
                        @endif
                    </div>
                </div>

                @php
                    $visibleCustomFields = $customFields->filter(fn($f) => in_array('cf_'.$f->id, $visibleColumns));
                    $colspan = 3 + count($visibleColumns) + $visibleCustomFields->count(); // checkbox + name + actions + visible
                @endphp

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="w-12 px-5 py-3">
                                    <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-600/30 cursor-pointer">
                                </th>
                                @if($this->isColumnVisible('id'))
                                    <th class="w-14 px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                @endif
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('Πελάτης') }}</th>
                                @if($this->isColumnVisible('email'))
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('Email') }}</th>
                                @endif
                                @if($this->isColumnVisible('url'))
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('URL') }}</th>
                                @endif
                                @if($this->isColumnVisible('external'))
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('Excel ID') }}</th>
                                @endif
                                @if($this->isColumnVisible('categories'))
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('Κατηγορίες') }}</th>
                                @endif
                                @foreach($visibleCustomFields as $field)
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                                        {{ $field->name }}
                                        @if(in_array($field->id, $activeCustomFilters))
                                            <i class="fas fa-filter text-brand-600 text-[9px] ml-1" title="Φιλτράρεται"></i>
                                        @endif
                                    </th>
                                @endforeach
                                @if($this->isColumnVisible('created'))
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('Ημ/νία') }}</th>
                                @endif
                                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('Ενέργειες') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($clients as $client)
                                @php $isSelected = in_array($client->id, $selected); @endphp
                                <tr wire:key="client-{{ $client->id }}"
                                    class="group transition-colors {{ $isSelected ? 'bg-brand-50/40' : 'hover:bg-slate-50/60' }}">
                                    <td class="px-5 py-4">
                                        <input type="checkbox" value="{{ $client->id }}" wire:model.live="selected" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-600/30 cursor-pointer">
                                    </td>
                                    @if($this->isColumnVisible('id'))
                                        <td class="px-4 py-4 text-slate-400 font-mono text-xs">{{ $client->id }}</td>
                                    @endif
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            @php
                                                $lastInitial = $client->lastname ? mb_strtoupper(mb_substr($client->lastname, 0, 1, 'UTF-8'), 'UTF-8') : '';
                                                $firstInitial = $client->name ? mb_strtoupper(mb_substr($client->name, 0, 1, 'UTF-8'), 'UTF-8') : '';
                                                $initials = $lastInitial.$firstInitial ?: '—';
                                                $palette = ['from-rose-500 to-red-600','from-amber-500 to-orange-600','from-indigo-500 to-violet-600','from-emerald-500 to-teal-600','from-sky-500 to-blue-600','from-fuchsia-500 to-pink-600'];
                                                $gradient = $palette[$client->id % count($palette)];
                                            @endphp
                                            <span class="w-9 h-9 rounded-full bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-white text-xs font-semibold shadow-sm shrink-0">{{ $initials }}</span>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-900 truncate">
                                                    {{ trim(($client->lastname ?? '').' '.($client->name ?? '')) ?: '—' }}
                                                </p>
                                                @if($client->email && ! $this->isColumnVisible('email'))
                                                    <p class="text-xs text-slate-500 truncate">{{ $client->email }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    @if($this->isColumnVisible('email'))
                                        <td class="px-4 py-4 text-sm text-slate-600">
                                            @if($client->email)
                                                <a href="mailto:{{ $client->email }}" class="hover:text-slate-900">{{ $client->email }}</a>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                    @endif
                                    @if($this->isColumnVisible('url'))
                                        <td class="px-4 py-4">
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
                                        <td class="px-4 py-4 text-slate-500 font-mono text-xs">{{ $client->external_id ?: '—' }}</td>
                                    @endif
                                    @if($this->isColumnVisible('categories'))
                                        <td class="px-4 py-4">
                                            <div class="flex flex-wrap gap-1.5">
                                                @forelse($client->certificateCategories as $cat)
                                                    @php
                                                        $hasPdf = $cat->html_template && $client->url_slug;
                                                        $c = $catColor($cat->id);
                                                    @endphp
                                                    @if($hasPdf)
                                                        <a href="{{ route('certificate.pdf', ['slug' => $client->url_slug, 'category' => $cat->slug]) }}"
                                                           target="_blank" rel="noopener"
                                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium ring-1 ring-inset transition {{ $c['bg'] }} {{ $c['fg'] }} {{ str_replace('border-', 'ring-', $c['border']) }} {{ $c['hover'] }}"
                                                           title="Άνοιγμα PDF — {{ $cat->name }}">
                                                            <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></span>
                                                            {{ $cat->name }}
                                                        </a>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium ring-1 ring-inset bg-slate-50 text-slate-600 ring-slate-200"
                                                              title="{{ $cat->html_template ? 'Λείπει URL slug' : 'Λείπει template' }}">
                                                            <i class="fas fa-circle-exclamation text-[10px] text-amber-500"></i>
                                                            {{ $cat->name }}
                                                        </span>
                                                    @endif
                                                @empty
                                                    <span class="text-slate-300">—</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    @endif
                                    @foreach($visibleCustomFields as $field)
                                        @php
                                            // A client can have multiple values for the same field (one per
                                            // attached category). Show distinct non-empty values joined.
                                            $vals = $client->customValues
                                                ->where('custom_field_id', $field->id)
                                                ->pluck('value')
                                                ->filter(fn ($v) => $v !== null && $v !== '')
                                                ->unique()
                                                ->values();
                                        @endphp
                                        <td class="px-4 py-4 text-sm text-slate-600">
                                            @if($vals->isEmpty())
                                                —
                                            @elseif($vals->count() === 1)
                                                {{ $vals->first() }}
                                            @else
                                                <span title="{{ $vals->implode(' · ') }}">{{ $vals->implode(' · ') }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    @if($this->isColumnVisible('created'))
                                        <td class="px-4 py-4 text-sm text-slate-500 whitespace-nowrap">{{ $client->created_at?->format('d/m/Y') }}</td>
                                    @endif
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                            @if($client->url_slug)
                                                <a href="{{ route('certificate.show', $client->url_slug) }}" target="_blank" rel="noopener"
                                                   class="w-8 h-8 rounded-md hover:bg-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 transition-colors"
                                                   title="Προβολή">
                                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('clients.edit', $client->id) }}" wire:navigate
                                               class="w-8 h-8 rounded-md hover:bg-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 transition-colors"
                                               title="Επεξεργασία">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <button type="button" wire:click="confirmDelete({{ $client->id }})"
                                                    class="w-8 h-8 rounded-md hover:bg-rose-50 flex items-center justify-center text-rose-500 hover:text-rose-700 transition-colors"
                                                    title="Διαγραφή">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $colspan }}" class="px-4 py-16 text-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <h3 class="mt-3 text-sm font-semibold text-slate-900">{{ __('Δεν υπάρχουν πελάτες') }}</h3>
                                        <p class="text-xs text-slate-500 mt-1">{{ __('Πρόσθεσε νέο ή εισήγαγε από Excel.') }}</p>
                                        <a href="{{ route('clients.create') }}" wire:navigate
                                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-600 hover:to-brand-500 text-white text-sm font-semibold shadow-brand transition-all mt-4">
                                            <i class="fas fa-plus text-xs"></i>
                                            {{ __('Νέος πελάτης') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($clients->total() > 0)
                    <div class="px-5 py-4 border-t border-slate-200 flex items-center justify-between flex-wrap gap-3">
                        <div class="text-xs text-slate-500">
                            {{ __('Εμφάνιση') }}
                            <span class="font-semibold text-slate-900">{{ $clients->firstItem() }}–{{ $clients->lastItem() }}</span>
                            {{ __('από') }}
                            <span class="font-semibold text-slate-900">{{ $clients->total() }}</span>
                        </div>

                        @if($clients->hasPages())
                            @php
                                $current  = $clients->currentPage();
                                $last     = $clients->lastPage();
                                $window   = 1;
                                $pages    = [];
                                if ($last <= 7) {
                                    for ($i = 1; $i <= $last; $i++) $pages[] = $i;
                                } else {
                                    $pages[] = 1;
                                    if ($current - $window > 2) $pages[] = '...';
                                    for ($i = max(2, $current - $window); $i <= min($last - 1, $current + $window); $i++) $pages[] = $i;
                                    if ($current + $window < $last - 1) $pages[] = '...';
                                    $pages[] = $last;
                                }
                            @endphp
                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="previousPage"
                                        @if($clients->onFirstPage()) disabled @endif
                                        class="w-8 h-8 rounded-md flex items-center justify-center transition-colors disabled:opacity-40 disabled:cursor-not-allowed
                                               {{ $clients->onFirstPage() ? 'text-slate-400' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </button>

                                @foreach($pages as $p)
                                    @if($p === '...')
                                        <span class="min-w-8 h-8 px-2.5 inline-flex items-center justify-center text-xs font-semibold text-slate-400">…</span>
                                    @elseif($p === $current)
                                        <button type="button" class="min-w-8 h-8 px-2.5 rounded-md text-xs font-semibold bg-slate-900 text-white">{{ $p }}</button>
                                    @else
                                        <button type="button" wire:click="gotoPage({{ $p }})"
                                                class="min-w-8 h-8 px-2.5 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">{{ $p }}</button>
                                    @endif
                                @endforeach

                                <button type="button" wire:click="nextPage"
                                        @if(! $clients->hasMorePages()) disabled @endif
                                        class="w-8 h-8 rounded-md flex items-center justify-center transition-colors disabled:opacity-40 disabled:cursor-not-allowed
                                               {{ ! $clients->hasMorePages() ? 'text-slate-400' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($confirmingBulkDelete)
        <div class="modal-backdrop" wire:key="bulk-delete-modal">
            <div class="modal-panel" @click.stop>
                <div class="modal-header">
                    <h3 class="section-title text-rose-700">
                        <i class="fas fa-triangle-exclamation mr-1.5"></i>
                        {{ __('Διαγραφή πελατών') }}
                    </h3>
                    <button type="button" wire:click="cancelBulkDelete" class="text-slate-400 hover:text-slate-600"><i class="fas fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <p class="text-sm text-slate-700">
                        {{ __('Σίγουρα θέλεις να διαγράψεις') }}
                        <strong class="text-rose-700">{{ count($selected) }}</strong>
                        {{ count($selected) === 1 ? __('πελάτη') : __('πελάτες') }};
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        {{ __('Διαγράφονται και τα cached PDFs, QR codes και custom values. Δεν υπάρχει undo.') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="cancelBulkDelete" class="btn-secondary">{{ __('Άκυρο') }}</button>
                    <button type="button" wire:click="bulkDelete"
                            wire:loading.attr="disabled" wire:target="bulkDelete"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium disabled:opacity-60">
                        <i class="fas fa-trash text-xs" wire:loading.remove wire:target="bulkDelete"></i>
                        <i class="fas fa-circle-notch fa-spin text-xs" wire:loading wire:target="bulkDelete"></i>
                        <span wire:loading.remove wire:target="bulkDelete">{{ __('Διαγραφή') }}</span>
                        <span wire:loading wire:target="bulkDelete">{{ __('Διαγραφή...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <x-confirm-delete-toast :targetId="$confirmingDeleteId"
                            message="Σίγουρα θέλεις να διαγράψεις αυτόν τον πελάτη; Τα στοιχεία του χάνονται οριστικά." />

    <div wire:loading.flex wire:target="generatePdfs,sendEmails,downloadPdfs"
         class="fixed inset-0 z-[65] bg-slate-900/40 backdrop-blur-[2px] items-center justify-center px-4"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl border border-brand-200 p-5 max-w-md w-full">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0 text-xl">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900">
                        <span wire:loading wire:target="generatePdfs">{{ __('Δημιουργία PDF σε εξέλιξη...') }}</span>
                        <span wire:loading wire:target="downloadPdfs">{{ __('Συμπίεση PDF σε ZIP...') }}</span>
                        <span wire:loading wire:target="sendEmails">{{ __('Αποστολή email σε εξέλιξη...') }}</span>
                    </p>
                    <p class="text-sm text-slate-600 mt-1">
                        <span wire:loading wire:target="generatePdfs">
                            {{ __('Παραγωγή') }} <span class="font-semibold">{{ $this->selectedPdfCount }}</span>
                            {{ $this->selectedPdfCount === 1 ? __('πιστοποιητικού') : __('πιστοποιητικών') }}
                        </span>
                        <span wire:loading wire:target="downloadPdfs">
                            {{ __('Συμπίεση') }} <span class="font-semibold">{{ $this->selectedPdfCount }}</span>
                            {{ $this->selectedPdfCount === 1 ? __('πιστοποιητικού') : __('πιστοποιητικών') }}
                        </span>
                        <span wire:loading wire:target="sendEmails">
                            {{ __('Αποστολή σε') }} <span class="font-semibold">{{ count($selected) }}</span>
                            {{ count($selected) === 1 ? __('πελάτη') : __('πελάτες') }}
                        </span>
                        — {{ __('μην κλείσεις τη σελίδα.') }}
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
        <div class="bg-white rounded-2xl shadow-2xl border pointer-events-auto p-5 max-w-md w-full"
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
