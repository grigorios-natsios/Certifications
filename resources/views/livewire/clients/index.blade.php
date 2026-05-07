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

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="stat-tile">
                    <p class="stat-tile-label">{{ __('Σύνολο') }}</p>
                    <p class="stat-tile-value">{{ number_format($stats['total']) }}</p>
                    <p class="stat-tile-meta">{{ __('πελάτες') }}</p>
                </div>
                <div class="stat-tile">
                    <p class="stat-tile-label">{{ __('Με κατηγορία') }}</p>
                    <p class="stat-tile-value">{{ number_format($stats['with_category']) }}</p>
                    <p class="stat-tile-meta">
                        @if($stats['total'])
                            {{ round($stats['with_category'] / $stats['total'] * 100) }}%
                        @else 0% @endif
                    </p>
                </div>
                <div class="stat-tile">
                    <p class="stat-tile-label">{{ __('Με URL') }}</p>
                    <p class="stat-tile-value">{{ number_format($stats['with_slug']) }}</p>
                    <p class="stat-tile-meta">{{ __('public links') }}</p>
                </div>
                <div class="stat-tile">
                    <p class="stat-tile-label">{{ __('Φετινός μήνας') }}</p>
                    <p class="stat-tile-value">{{ number_format($stats['this_month']) }}</p>
                    <p class="stat-tile-meta">{{ __('νέοι') }}</p>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                    <div>
                        <h2 class="section-title">{{ __('Εισαγωγή Excel / CSV') }}</h2>
                        <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                            {{ __('Στήλες: ID, Name, Lastname, Start date, End Date, Title, Category, Hours, URL. Νέοι προστίθενται, υπάρχοντες (κατά ID) ενημερώνονται. Όσοι λείπουν δεν διαγράφονται.') }}
                        </p>
                    </div>
                </div>
                <form wire:submit.prevent="importExcel" class="flex flex-col md:flex-row md:items-end gap-3">
                    <div class="flex-1">
                        <input type="file" wire:model="importFile"
                               accept=".xlsx,.xls,.csv,.ods,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv"
                               class="block w-full text-sm text-slate-600 file:me-3 file:py-1.5 file:px-3 file:rounded-md file:border file:border-slate-300 file:bg-white file:text-slate-700 hover:file:bg-slate-50 file:cursor-pointer">
                        <div class="mt-1 flex items-center gap-2 text-[11px] text-slate-500">
                            <span>{{ __('Υποστηριζόμενα:') }}</span>
                            <span class="badge badge-slate">.xlsx</span>
                            <span class="badge badge-slate">.xls</span>
                            <span class="badge badge-slate">.csv</span>
                            <span class="badge badge-slate">.ods</span>
                            <span class="text-slate-400">· {{ __('μέγιστο 20MB') }}</span>
                        </div>
                        <div wire:loading wire:target="importFile" class="text-[11px] text-slate-500 mt-1">
                            <i class="fas fa-circle-notch fa-spin"></i> {{ __('Ανέβασμα...') }}
                        </div>
                        @error('importFile') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn-secondary" wire:loading.attr="disabled" wire:target="importExcel,importFile">
                        <span wire:loading.remove wire:target="importExcel">{{ __('Εισαγωγή') }}</span>
                        <span wire:loading wire:target="importExcel">{{ __('Επεξεργασία...') }}</span>
                    </button>
                </form>
            </div>

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
                                    <p class="px-2 pt-1 pb-1.5 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ __('Βασικές στήλες') }}</p>
                                    @foreach($this->columnDefinitions as $key => $label)
                                        <label class="flex items-center gap-2 px-2 py-1.5 text-sm rounded hover:bg-slate-50 cursor-pointer">
                                            <input type="checkbox" value="{{ $key }}" wire:model.live="visibleColumns"
                                                   class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            <span class="text-slate-700">{{ $label }}</span>
                                        </label>
                                    @endforeach

                                    @if($customFields->count())
                                        <p class="px-2 pt-3 pb-1.5 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ __('Custom πεδία') }}</p>
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
                                onclick="return confirm('Να δημιουργηθούν PDF για τους επιλεγμένους;')">
                            <i class="fas fa-file-pdf text-xs"></i>
                            {{ __('Παραγωγή PDF') }}
                        </button>
                    </div>
                </div>

                <div class="border-b border-slate-200 bg-slate-50/40">
                    <div class="px-5 py-3">
                        <div class="grid gap-2 md:grid-cols-12 items-end">
                            <div class="md:col-span-5">
                                <label class="label">{{ __('Αναζήτηση') }}</label>
                                <div class="relative">
                                    <i class="fas fa-magnifying-glass input-icon"></i>
                                    <input type="text" wire:model.live.debounce.300ms="search"
                                           placeholder="Όνομα, επώνυμο, email, slug..."
                                           class="input input-with-icon bg-white">
                                </div>
                            </div>
                            <div class="md:col-span-3">
                                <label class="label">{{ __('Κατηγορία') }}</label>
                                <select wire:model.live="categoryFilter" class="input bg-white">
                                    <option value="">{{ __('Όλες') }}</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="label">{{ __('Ημ/νία από') }}</label>
                                <input type="date" wire:model.live="dateFrom" class="input bg-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="label">{{ __('Έως') }}</label>
                                <input type="date" wire:model.live="dateTo" class="input bg-white">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = ! open" class="btn-secondary text-xs py-1.5 px-2.5">
                                    <i class="fas fa-plus text-[10px]"></i>
                                    {{ __('Προσθήκη φίλτρου') }}
                                    <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                                </button>
                                <div x-show="open" x-cloak x-transition.opacity
                                     class="absolute z-30 mt-1 w-64 bg-white border border-slate-200 rounded-md shadow-lg max-h-72 overflow-y-auto">
                                    <div class="p-2">
                                        <p class="px-2 py-1 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ __('Πεδία') }}</p>
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
                                            <p class="px-2 pt-2 pb-1 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ __('Custom') }}</p>
                                            @foreach($availableCustom as $field)
                                                <button type="button" @click="open = false" wire:click="addCustomFilter({{ $field->id }})"
                                                        class="w-full text-left px-2 py-1.5 text-sm rounded hover:bg-slate-50 flex items-center justify-between">
                                                    <span>{{ $field->name }}</span>
                                                    <span class="text-slate-400 text-[10px] uppercase">{{ $field->type }}</span>
                                                </button>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($this->activeFilterCount > 0)
                                <span class="text-xs text-slate-500">
                                    {{ $this->activeFilterCount }} {{ __('ενεργό') }}{{ $this->activeFilterCount > 1 ? 'ά' : '' }} {{ __('φίλτρο') }}{{ $this->activeFilterCount > 1 ? 'α' : '' }}
                                </span>
                                <button type="button" wire:click="clearAllFilters" class="text-xs text-slate-500 hover:text-rose-600 underline underline-offset-2">
                                    {{ __('Καθαρισμός όλων') }}
                                </button>
                            @endif
                        </div>

                        @if($hasUrl !== '' || count($activeCustomFilters))
                            <div class="mt-3 grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                                @if($hasUrl !== '')
                                    <div class="flex items-stretch border border-slate-200 rounded-md bg-white overflow-hidden">
                                        <span class="px-2.5 py-1.5 bg-slate-50 text-xs text-slate-500 border-r border-slate-200 flex items-center">{{ __('Public URL') }}</span>
                                        <select wire:model.live="hasUrl" class="border-0 text-sm flex-1 focus:ring-0 focus:border-0">
                                            <option value="yes">{{ __('Έχει') }}</option>
                                            <option value="no">{{ __('Δεν έχει') }}</option>
                                        </select>
                                        <button type="button" wire:click="$set('hasUrl', '')" class="px-2 text-slate-400 hover:text-rose-600 text-sm" title="Αφαίρεση"><i class="fas fa-xmark"></i></button>
                                    </div>
                                @endif

                                @foreach($activeCustomFilters as $fieldId)
                                    @php $field = $customFields->firstWhere('id', $fieldId); @endphp
                                    @if($field)
                                        <div wire:key="filter-{{ $field->id }}" class="flex items-stretch border border-slate-200 rounded-md bg-white overflow-hidden">
                                            <span class="px-2.5 py-1.5 bg-slate-50 text-xs text-slate-500 border-r border-slate-200 flex items-center whitespace-nowrap" title="{{ $field->name }}">
                                                {{ \Illuminate\Support\Str::limit($field->name, 18) }}
                                            </span>
                                            <input
                                                type="{{ $field->type === 'number' ? 'number' : ($field->type === 'date' ? 'date' : 'text') }}"
                                                wire:model.live.debounce.400ms="customFilters.{{ $field->id }}"
                                                placeholder="{{ __('Τιμή...') }}"
                                                class="border-0 text-sm flex-1 focus:ring-0 focus:border-0 min-w-0">
                                            <button type="button" wire:click="removeCustomFilter({{ $field->id }})" class="px-2 text-slate-400 hover:text-rose-600 text-sm" title="Αφαίρεση">
                                                <i class="fas fa-xmark"></i>
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
                                            <span class="avatar w-8 h-8">
                                                {{ strtoupper(substr($client->lastname ?? $client->name ?? '?', 0, 1)) }}
                                            </span>
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
                                                <a href="/c/{{ $client->url_slug }}" target="_blank" rel="noopener" class="text-slate-700 hover:text-brand-600 font-mono text-xs">
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
                                                <span class="badge badge-slate">{{ $cat->name }}</span>
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
                                            <a href="/c/{{ $client->url_slug }}" target="_blank" rel="noopener" class="btn-icon" title="Προβολή">
                                                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('clients.edit', $client->id) }}" wire:navigate class="btn-icon" title="Επεξεργασία">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <button wire:click="delete({{ $client->id }})"
                                                onclick="return confirm('Σίγουρα θέλεις να διαγράψεις αυτόν τον πελάτη;')"
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
