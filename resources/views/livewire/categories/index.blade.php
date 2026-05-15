<div>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Κατηγορίες πιστοποιητικών') }}</h1>
                <p class="page-subtitle">{{ __('HTML templates που γίνονται PDF πιστοποιητικά') }}</p>
            </div>
            <div class="toolbar">
                <button type="button" onclick="Livewire.dispatch('categories::create')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-600 hover:to-brand-500 text-white text-sm font-semibold shadow-brand transition-all">
                    <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                    {{ __('Νέα κατηγορία') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="section-card">
                <div class="section-card-head">
                    <div class="flex items-center gap-3">
                        <h2 class="section-title">{{ __('Κατηγορίες') }}</h2>
                        <span class="badge badge-slate">{{ $categories->total() }}</span>
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
                                <th>{{ __('Template') }}</th>
                                <th>{{ __('Διάταξη') }}</th>
                                <th>{{ __('Ημ/νία') }}</th>
                                <th class="text-right">{{ __('Ενέργειες') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($categories as $cat)
                                <tr wire:key="cat-{{ $cat->id }}">
                                    <td class="text-slate-400 font-mono text-xs">{{ $cat->id }}</td>
                                    <td>
                                        <span class="font-medium text-slate-900">{{ $cat->name }}</span>
                                    </td>
                                    <td>
                                        @if($cat->html_template)
                                            <span class="status-dot status-dot-success">{{ __('Ενεργό') }}</span>
                                        @else
                                            <span class="status-dot status-dot-warning">{{ __('Κενό') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($cat->orientation ?? 'portrait') === 'landscape')
                                            <span class="badge badge-slate" title="Landscape"><i class="fas fa-image text-[10px] mr-1"></i>{{ __('Οριζόντιο') }}</span>
                                        @else
                                            <span class="badge badge-slate" title="Portrait"><i class="fas fa-file text-[10px] mr-1"></i>{{ __('Κάθετο') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-slate-500 whitespace-nowrap text-xs">{{ $cat->created_at?->format('d/m/Y') }}</td>
                                    <td class="text-right whitespace-nowrap">
                                        <button wire:click="openEditor({{ $cat->id }})"
                                                class="btn-primary text-xs px-3 py-1.5 gap-1.5 shadow-sm hover:shadow ring-1 ring-brand-700/20">
                                            <i class="fas fa-pen-ruler text-[11px]"></i>
                                            {{ __('Σχεδίαση') }}
                                        </button>
                                        <button wire:click="openEdit({{ $cat->id }})" class="btn-icon" title="Μετονομασία"><i class="fas fa-pen text-xs"></i></button>
                                        <button type="button" wire:click="confirmDelete({{ $cat->id }})"
                                                class="btn-icon-danger" title="Διαγραφή"><i class="fas fa-trash text-xs"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-folder-open text-slate-400"></i></div>
                                            <h3 class="mt-3 text-sm font-medium text-slate-900">{{ __('Δεν υπάρχουν κατηγορίες') }}</h3>
                                            <p class="text-xs text-slate-500 mt-1">{{ __('Δημιούργησε την πρώτη σου κατηγορία.') }}</p>
                                            <button wire:click="openCreate"
                                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-600 hover:to-brand-500 text-white text-sm font-semibold shadow-brand transition-all">
                                                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                                                {{ __('Νέα κατηγορία') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-slate-200">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>

        @if($showModal)
            <div class="modal-backdrop" wire:key="cat-modal">
                <div class="modal-panel" @click.stop>
                    <div class="modal-header">
                        <h3 class="section-title">{{ $editingId ? __('Μετονομασία Κατηγορίας') : __('Νέα Κατηγορία') }}</h3>
                        <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600"><i class="fas fa-xmark"></i></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div>
                                <label class="label">{{ __('Όνομα') }}</label>
                                <input type="text" wire:model="name" class="input" autofocus>
                                @error('name') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <p class="text-xs text-slate-500">{{ __('Για τη σχεδίαση του template πάτησε «Σχεδίαση» από τη λίστα.') }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="closeModal" class="btn-secondary">{{ __('Άκυρο') }}</button>
                            <button type="submit" class="btn-primary">{{ __('Αποθήκευση') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($showEditor)
            <div class="fixed inset-0 z-[55] bg-slate-100 flex flex-col"
                 wire:key="editor-{{ $editorCategoryId }}"
                 x-data="htmlTemplateEditor({
                    initialHtml: @js($editorTemplate),
                    initialOrientation: @js($editorOrientation),
                    categoryName: @js($editorName),
                    fields: @js($customFields->all()),
                    clients: @js($clientList),
                 })"
                 x-init="init()"
                 x-on:resize.window.debounce.150ms="fitPreview()">

                {{-- ============ TOP TOOLBAR ============ --}}
                <div class="bg-white border-b border-slate-200 px-4 py-2.5 flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-8 h-8 rounded-md bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center shadow-sm">
                            <i class="fas fa-pen-ruler text-[13px]"></i>
                        </span>
                        <div class="min-w-0 leading-tight">
                            <h2 class="text-sm font-semibold text-slate-900 truncate">{{ $editorName }}</h2>
                            <p class="text-[11px] text-slate-500 truncate">{{ __('Σχεδιαστής Template') }}</p>
                        </div>
                        <span x-show="dirty" x-cloak
                              class="inline-flex items-center gap-1 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-full pl-1.5 pr-2 py-0.5 whitespace-nowrap"
                              title="{{ __('Μη αποθηκευμένες αλλαγές') }}">
                            <i class="fas fa-circle text-[6px]"></i>
                            <span class="hidden sm:inline">{{ __('Μη αποθηκευμένες αλλαγές') }}</span>
                            <span class="sm:hidden">{{ __('Αλλαγές') }}</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Orientation segmented control --}}
                        <div class="inline-flex rounded-md border border-slate-200 bg-slate-50 p-0.5" role="group">
                            <button type="button" @click="setOrientation('portrait')"
                                    :class="orientation === 'portrait' ? 'bg-white text-brand-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:text-slate-700'"
                                    class="text-[11px] px-2 py-1 rounded inline-flex items-center gap-1.5 transition"
                                    title="{{ __('Κάθετο (A4 portrait)') }}">
                                <i class="fas fa-file text-[11px]"></i>
                                <span class="hidden md:inline">{{ __('Κάθετο') }}</span>
                            </button>
                            <button type="button" @click="setOrientation('landscape')"
                                    :class="orientation === 'landscape' ? 'bg-white text-brand-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:text-slate-700'"
                                    class="text-[11px] px-2 py-1 rounded inline-flex items-center gap-1.5 transition"
                                    title="{{ __('Οριζόντιο (A4 landscape)') }}">
                                <i class="fas fa-image text-[11px]"></i>
                                <span class="hidden md:inline">{{ __('Οριζόντιο') }}</span>
                            </button>
                        </div>

                        <div class="h-6 w-px bg-slate-200"></div>

                        <button type="button" @click="requestClose()"
                                class="text-xs px-3 py-1.5 rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 inline-flex items-center gap-1.5">
                            <i class="fas fa-xmark text-[11px]"></i>
                            <span class="hidden sm:inline">{{ __('Κλείσιμο') }}</span>
                        </button>
                        <button type="button" @click="save()"
                                class="text-xs px-3 py-1.5 rounded-md bg-brand-600 hover:bg-brand-700 text-white inline-flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-save text-[11px]"></i>
                            <span>{{ __('Αποθήκευση') }}</span>
                        </button>
                    </div>
                </div>

                {{-- ============ SPLIT BODY ============ --}}
                <div class="flex-1 overflow-hidden flex flex-col lg:flex-row" x-ref="splitContainer">

                    {{-- ===== LEFT: CODE EDITOR ===== --}}
                    <div class="flex flex-col w-full bg-slate-50 lg:w-[var(--split-w)] lg:flex-shrink-0"
                         :style="`--split-w: ${splitPercent}%`">

                        {{-- Code header --}}
                        <div class="px-3 py-2 border-b border-slate-200 bg-white flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-xs text-slate-600">
                                <i class="fas fa-code text-slate-400"></i>
                                <span class="font-medium">{{ __('HTML') }}</span>
                                <span class="text-slate-300">·</span>
                                <span class="text-slate-400 tabular-nums" x-text="lineCount + ' ' + (lineCount === 1 ? '{{ __('γραμμή') }}' : '{{ __('γραμμές') }}')"></span>
                                <span x-show="html.length" class="text-slate-300">·</span>
                                <span x-show="html.length" class="text-slate-400 tabular-nums" x-text="html.length + ' ' + '{{ __('χαρακτήρες') }}'"></span>
                            </div>
                            <div class="flex items-center gap-0.5">
                                <button type="button" @click="openImageLibrary()" class="text-[11px] px-2 py-1 rounded hover:bg-slate-100 text-slate-600 inline-flex items-center gap-1" title="{{ __('Φωτογραφίες') }}">
                                    <i class="fas fa-image text-[10px]"></i>
                                    <span class="hidden md:inline">{{ __('Φωτογραφίες') }}</span>
                                </button>
                                <div class="w-px h-4 bg-slate-200 mx-0.5"></div>
                                <button type="button" @click="formatHtml()" class="text-[11px] px-2 py-1 rounded hover:bg-slate-100 text-slate-600 inline-flex items-center gap-1" title="{{ __('Αυτόματη μορφοποίηση') }}">
                                    <i class="fas fa-indent text-[10px]"></i>
                                    <span class="hidden md:inline">{{ __('Format') }}</span>
                                </button>
                                <button type="button" @click="copyHtml()" class="text-[11px] px-2 py-1 rounded hover:bg-slate-100 text-slate-600" title="{{ __('Αντιγραφή κώδικα') }}">
                                    <i class="fas fa-copy text-[10px]"></i>
                                </button>
                                <button type="button" @click="resetHtml()" class="text-[11px] px-2 py-1 rounded hover:bg-slate-100 text-slate-600" title="{{ __('Επαναφορά στην αποθηκευμένη έκδοση') }}">
                                    <i class="fas fa-rotate-left text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Code surface: gutter + (pre overlay + textarea) --}}
                        <div class="relative flex-1 overflow-hidden bg-white code-editor-wrap">
                            <div class="code-editor-gutter" x-ref="gutter">
                                <template x-for="n in lineCount" :key="n">
                                    <div x-text="n"></div>
                                </template>
                            </div>
                            <div class="code-editor-body">
                                <pre aria-hidden="true" x-ref="codeHighlight"
                                     class="code-editor-surface code-editor-pre"><code class="language-markup" x-html="highlightedHtml"></code></pre>
                                <textarea x-ref="codeEditor"
                                          x-model="html"
                                          @input="onCodeInput()"
                                          @scroll="syncScroll()"
                                          @keydown.tab.prevent="handleTab($event)"
                                          spellcheck="false"
                                          class="code-editor-surface code-editor-textarea"
                                          placeholder="{{ __('Επικόλλησε ή γράψε HTML εδώ...') }}"></textarea>
                            </div>

                            {{-- Empty state overlay with starter samples --}}
                            <div x-show="!html.trim()" x-cloak
                                 class="absolute inset-0 z-[3] flex items-center justify-center bg-white/95 pointer-events-none p-6">
                                <div class="text-center max-w-sm w-full pointer-events-auto">
                                    <div class="w-14 h-14 mx-auto rounded-full bg-brand-50 text-brand-500 flex items-center justify-center">
                                        <i class="fas fa-file-code text-2xl"></i>
                                    </div>
                                    <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ __('Κενό template') }}</h3>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Διάλεξε δείγμα για να ξεκινήσεις γρήγορα ή γράψε από το μηδέν.') }}</p>
                                    <div class="mt-5 grid grid-cols-1 gap-2">
                                        <button type="button" @click="loadSample('diploma')"
                                                class="px-3 py-2.5 rounded-lg border border-slate-200 bg-white hover:border-brand-300 hover:bg-brand-50 text-left transition group">
                                            <div class="flex items-center gap-2.5">
                                                <i class="fas fa-award text-brand-500 group-hover:text-brand-700"></i>
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-800">{{ __('Βεβαίωση παρακολούθησης') }}</div>
                                                    <div class="text-[11px] text-slate-500">{{ __('Κλασικό κάθετο layout · όνομα · QR') }}</div>
                                                </div>
                                            </div>
                                        </button>
                                        <button type="button" @click="loadSample('award')"
                                                class="px-3 py-2.5 rounded-lg border border-slate-200 bg-white hover:border-brand-300 hover:bg-brand-50 text-left transition group">
                                            <div class="flex items-center gap-2.5">
                                                <i class="fas fa-trophy text-brand-500 group-hover:text-brand-700"></i>
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-800">{{ __('Επίσημο πιστοποιητικό') }}</div>
                                                    <div class="text-[11px] text-slate-500">{{ __('Οριζόντιο με border · κομψό') }}</div>
                                                </div>
                                            </div>
                                        </button>
                                        <button type="button" @click="loadSample('minimal')"
                                                class="px-3 py-2.5 rounded-lg border border-slate-200 bg-white hover:border-brand-300 hover:bg-brand-50 text-left transition group">
                                            <div class="flex items-center gap-2.5">
                                                <i class="fas fa-file-lines text-brand-500 group-hover:text-brand-700"></i>
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-800">{{ __('Minimal') }}</div>
                                                    <div class="text-[11px] text-slate-500">{{ __('Skeleton HTML για ελεύθερη σχεδίαση') }}</div>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                    <button type="button" @click="$refs.codeEditor.focus()"
                                            class="mt-4 text-[11px] text-slate-400 hover:text-slate-600 underline-offset-2 hover:underline">
                                        {{ __('Ή ξεκίνα από κενό') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Placeholders panel --}}
                        <div class="border-t border-slate-200 bg-white">
                            <div class="px-3 py-2 flex items-center gap-2 border-b border-slate-200">
                                <i class="fas fa-bolt text-amber-500 text-[11px]"></i>
                                <span class="text-xs text-slate-800 font-semibold">{{ __('Πεδία') }}</span>
                                <div class="ml-auto relative flex-1 max-w-[180px]">
                                    <i class="fas fa-magnifying-glass absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                    <input type="text" x-model="placeholderQuery"
                                           placeholder="{{ __('Αναζήτηση πεδίου...') }}"
                                           class="w-full pl-6 pr-2 py-1 text-[11px] text-slate-800 border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-brand-300">
                                </div>
                            </div>
                            <div class="px-3 py-2 max-h-48 overflow-y-auto space-y-3">
                                <template x-for="group in filteredGroups()" :key="group.key">
                                    <div>
                                        <div class="text-[11px] text-slate-700 font-semibold mb-1.5 flex items-center gap-1.5">
                                            <i :class="group.icon" class="text-slate-500 text-[10px]"></i>
                                            <span x-text="group.label"></span>
                                            <span class="text-slate-300">·</span>
                                            <span class="text-slate-500 tabular-nums" x-text="group.items.length"></span>
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="ph in group.items" :key="ph.token">
                                                <button type="button" @click="insertAtCursor(ph.token)"
                                                        class="text-[11px] pl-2 pr-1 py-0.5 rounded border border-slate-300 bg-white hover:bg-brand-50 hover:border-brand-300 hover:text-brand-700 text-slate-800 font-mono transition inline-flex items-center gap-1.5"
                                                        :title="ph.label + (ph.preview ? ' · τιμή: ' + ph.preview : '')">
                                                    <span x-text="ph.token"></span>
                                                    <span x-show="ph.preview"
                                                          class="text-[10px] px-1 py-px rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-sans truncate max-w-[120px]"
                                                          x-text="ph.preview"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="filteredGroups().length === 0" x-cloak
                                     class="text-center text-[11px] text-slate-500 py-3">
                                    {{ __('Δεν βρέθηκε αντίστοιχο πεδίο.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Drag handle --}}
                    <div class="hidden lg:flex w-1.5 cursor-col-resize bg-slate-200 hover:bg-brand-400 active:bg-brand-500 transition-colors flex-shrink-0 items-center justify-center group"
                         @mousedown.prevent="startDrag($event)"
                         @dblclick="splitPercent = 50; $nextTick(() => fitPreview())"
                         title="{{ __('Σύρε για αλλαγή μεγέθους · διπλό κλικ για 50/50') }}">
                        <div class="w-0.5 h-10 bg-slate-400 group-hover:bg-white rounded transition-colors"></div>
                    </div>

                    {{-- ===== RIGHT: PREVIEW ===== --}}
                    <div class="flex flex-col w-full lg:flex-1 lg:min-w-0 preview-pane-bg">
                        {{-- Preview header --}}
                        <div class="px-3 py-2 border-b border-slate-200 bg-white flex items-center justify-between gap-3 flex-wrap">
                            <div class="flex items-center gap-2 text-xs text-slate-600 min-w-0">
                                <i class="fas fa-eye text-slate-400"></i>
                                <span class="font-medium">{{ __('Προεπισκόπηση') }}</span>
                                <span class="text-slate-300">·</span>
                                <span class="text-slate-400" x-show="!selectedClient">{{ __('Δείγμα') }}</span>
                                <span class="text-brand-700 font-semibold truncate" x-show="selectedClient" x-text="selectedClient ? selectedClient.full_name : ''"></span>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                {{-- Zoom segmented control --}}
                                <div class="inline-flex rounded-md border border-slate-200 bg-slate-50 p-0.5" role="group">
                                    <template x-for="z in zoomOptions" :key="z.value">
                                        <button type="button" @click="setZoom(z.value)"
                                                :class="zoomMode === z.value ? 'bg-white text-brand-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:text-slate-700'"
                                                class="text-[11px] px-1.5 py-1 rounded inline-flex items-center transition min-w-[28px] justify-center"
                                                :title="z.title"
                                                x-text="z.label"></button>
                                    </template>
                                </div>

                                {{-- Client picker --}}
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button type="button" @click="open = !open"
                                            class="text-[11px] px-2 py-1 rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 inline-flex items-center gap-1.5"
                                            :class="selectedClient ? 'ring-1 ring-brand-300 text-brand-700 border-brand-200 bg-brand-50/50' : ''">
                                        <i class="fas fa-user text-[10px]"></i>
                                        <span class="max-w-[140px] truncate" x-text="selectedClient ? selectedClient.full_name : '{{ __('Επιλογή πελάτη') }}'"></span>
                                        <i class="fas fa-chevron-down text-[9px] text-slate-400"></i>
                                    </button>

                                    <div x-show="open" x-transition.opacity x-cloak
                                         class="absolute right-0 mt-1 w-72 bg-white border border-slate-200 rounded-md shadow-lg z-10">
                                        <div class="p-2 border-b border-slate-100">
                                            <input type="text" x-model="clientQuery"
                                                   placeholder="{{ __('Αναζήτηση πελάτη...') }}"
                                                   class="w-full px-2 py-1 text-xs border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-brand-300">
                                        </div>
                                        <div class="max-h-64 overflow-y-auto">
                                            <template x-if="selectedClient">
                                                <button type="button" @click="clearClient(); open = false"
                                                        class="w-full px-3 py-1.5 text-xs text-left text-slate-500 hover:bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                                                    <i class="fas fa-rotate-left text-[10px]"></i>
                                                    <span>{{ __('Επαναφορά σε δείγμα') }}</span>
                                                </button>
                                            </template>
                                            <template x-for="c in filteredClients()" :key="c.id">
                                                <button type="button" @click="selectClient(c.id); open = false"
                                                        class="w-full px-3 py-1.5 text-xs text-left text-slate-700 hover:bg-brand-50 hover:text-brand-700 truncate"
                                                        x-text="c.full_name"></button>
                                            </template>
                                            <div x-show="filteredClients().length === 0" x-cloak
                                                 class="px-3 py-3 text-xs text-slate-400 text-center">
                                                {{ __('Δεν βρέθηκαν πελάτες') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" @click="refreshPreview()" class="text-[11px] px-2 py-1 rounded hover:bg-slate-100 text-slate-500" title="{{ __('Ανανέωση προεπισκόπησης') }}">
                                    <i class="fas fa-rotate text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Preview canvas --}}
                        <div class="flex-1 overflow-auto p-6 flex justify-center items-start" x-ref="previewScroll">
                            <div class="flex-shrink-0 preview-paper"
                                 :style="`width: ${pageWidth * previewScale}px; height: ${pageHeight * previewScale}px;`">
                                <iframe x-ref="preview" sandbox="allow-same-origin"
                                        class="block bg-white"
                                        :style="`width: ${pageWidth}px; height: ${pageHeight}px; transform: scale(${previewScale}); transform-origin: top left;`"></iframe>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ IMAGE LIBRARY MODAL ============ --}}
                <div x-show="showImagesModal" x-cloak
                     class="fixed inset-0 z-[70] bg-slate-900/50 flex items-center justify-center p-4"
                     x-transition.opacity
                     @keydown.escape.window="showImagesModal = false">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[85vh] flex flex-col"
                         @click.outside="showImagesModal = false">
                        <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-image text-brand-600"></i>
                                <h3 class="text-sm font-semibold text-slate-900">{{ __('Φωτογραφίες') }}</h3>
                                <span class="text-xs text-slate-400" x-text="'(' + images.length + ')'"></span>
                            </div>
                            <button type="button" @click="showImagesModal = false" class="text-slate-400 hover:text-slate-600">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>

                        {{-- Upload dropzone --}}
                        <div class="p-5 border-b border-slate-100">
                            <label
                                @dragover.prevent="imagesDragOver = true"
                                @dragleave.prevent="imagesDragOver = false"
                                @drop.prevent="imagesDragOver = false; handleImageDrop($event)"
                                :class="imagesDragOver ? 'border-brand-400 bg-brand-50/50' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                class="block border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition">
                                <input type="file" class="hidden" accept="image/png,image/jpeg,image/gif,image/webp,image/svg+xml"
                                       @change="handleImageInput($event)" multiple>
                                <i class="fas fa-cloud-arrow-up text-2xl text-slate-400"></i>
                                <p class="mt-2 text-sm font-medium text-slate-700">
                                    <span x-show="!imagesUploading">{{ __('Σύρε εικόνες εδώ ή κάνε κλικ για επιλογή') }}</span>
                                    <span x-show="imagesUploading" x-cloak>
                                        <i class="fas fa-spinner fa-spin mr-1"></i>{{ __('Ανέβασμα...') }}
                                    </span>
                                </p>
                                <p class="text-[11px] text-slate-500 mt-1">{{ __('PNG, JPG, GIF, WEBP, SVG · έως 5MB') }}</p>
                            </label>
                        </div>

                        {{-- Image grid --}}
                        <div class="flex-1 overflow-y-auto p-5">
                            <div x-show="images.length === 0 && !imagesLoading" x-cloak
                                 class="text-center py-10">
                                <i class="fas fa-images text-3xl text-slate-300"></i>
                                <p class="mt-3 text-sm text-slate-500">{{ __('Δεν έχεις ανεβάσει φωτογραφίες ακόμα.') }}</p>
                            </div>
                            <div x-show="imagesLoading" x-cloak class="text-center py-10 text-slate-400">
                                <i class="fas fa-spinner fa-spin text-2xl"></i>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                <template x-for="img in images" :key="img.name">
                                    <div class="group relative rounded-md border border-slate-200 bg-slate-50 overflow-hidden hover:border-brand-300 transition">
                                        <div class="aspect-square flex items-center justify-center bg-white">
                                            <img :src="img.url" :alt="img.name" class="max-w-full max-h-full object-contain">
                                        </div>
                                        <div class="px-2 py-1 border-t border-slate-100 text-[11px] text-slate-600 truncate" x-text="img.name"></div>
                                        <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                            <button type="button" @click="insertImage(img.url)"
                                                    class="text-[11px] px-2.5 py-1 rounded bg-white text-slate-900 hover:bg-brand-50 inline-flex items-center gap-1.5"
                                                    title="{{ __('Εισαγωγή στο template') }}">
                                                <i class="fas fa-plus text-[10px]"></i>
                                                <span>{{ __('Εισαγωγή') }}</span>
                                            </button>
                                            <button type="button" @click="copyImageUrl(img.url)"
                                                    class="w-7 h-7 rounded bg-white text-slate-600 hover:bg-slate-100 inline-flex items-center justify-center"
                                                    title="{{ __('Αντιγραφή URL') }}">
                                                <i class="fas fa-copy text-[10px]"></i>
                                            </button>
                                            <button type="button" @click="deleteImage(img.name)"
                                                    class="w-7 h-7 rounded bg-white text-rose-600 hover:bg-rose-50 inline-flex items-center justify-center"
                                                    title="{{ __('Διαγραφή') }}">
                                                <i class="fas fa-trash text-[10px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 text-[11px] text-slate-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ __('Κλικ στο «Εισαγωγή» βάζει την εικόνα στη θέση του cursor στο HTML.') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <x-confirm-delete-toast :targetId="$confirmingDeleteId"
                            message="Σίγουρα θέλεις να διαγράψεις αυτή την κατηγορία; Το template της θα χαθεί οριστικά." />

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

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism.min.css">
        <style>
            /* ===== Code editor with line gutter ===== */
            .code-editor-wrap {
                display: flex;
                align-items: stretch;
            }
            .code-editor-gutter {
                flex-shrink: 0;
                width: 44px;
                padding: 1rem 0.5rem 1rem 0;
                text-align: right;
                background: #f8fafc;
                color: #cbd5e1;
                font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
                font-size: 12px;
                line-height: 1.55;
                border-right: 1px solid #e2e8f0;
                user-select: none;
                overflow: hidden;
            }
            .code-editor-gutter > div {
                font-variant-numeric: tabular-nums;
            }
            .code-editor-body {
                position: relative;
                flex: 1;
                min-width: 0;
            }
            /* Overlay editor: textarea + highlighted <pre> share identical metrics so the caret aligns with the rendered tokens. */
            .code-editor-surface {
                position: absolute;
                inset: 0;
                margin: 0;
                padding: 1rem;
                font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
                font-size: 13px;
                line-height: 1.55;
                tab-size: 4;
                -moz-tab-size: 4;
                white-space: pre;
                word-break: normal;
                overflow: auto;
                border: 0;
            }
            .code-editor-pre {
                background: #fff;
                color: #0f172a;
                pointer-events: none;
                z-index: 1;
            }
            .code-editor-pre code {
                font: inherit;
                white-space: inherit;
                word-break: inherit;
                background: transparent;
            }
            .code-editor-textarea {
                background: transparent;
                color: transparent;
                caret-color: #0f172a;
                resize: none;
                z-index: 2;
            }
            .code-editor-textarea:focus { outline: none; }
            .code-editor-textarea::selection { background: rgba(59, 130, 246, 0.25); color: transparent; }
            .code-editor-textarea::placeholder { color: #94a3b8; }

            /* ===== Preview canvas ===== */
            .preview-pane-bg {
                background:
                    radial-gradient(circle at 50% 0%, #f1f5f9 0%, transparent 60%),
                    linear-gradient(180deg, #e2e8f0 0%, #cbd5e1 100%);
            }
            .preview-paper {
                border-radius: 4px;
                background: #ffffff;
                box-shadow:
                    0 1px 2px rgba(15, 23, 42, 0.04),
                    0 8px 24px -8px rgba(15, 23, 42, 0.18),
                    0 24px 48px -16px rgba(15, 23, 42, 0.12);
                overflow: hidden;
                transition: box-shadow 0.2s ease;
            }
            .preview-paper:hover {
                box-shadow:
                    0 1px 2px rgba(15, 23, 42, 0.05),
                    0 12px 32px -8px rgba(15, 23, 42, 0.22),
                    0 32px 64px -16px rgba(15, 23, 42, 0.15);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-core.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-markup.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-css.min.js"></script>
        @verbatim
        <script>
            window.htmlTemplateEditor = function ({ initialHtml, initialOrientation, categoryName, fields, clients }) {
                // Mirror CertificatePdfRenderer::upperNoAccents — names in the
                // final PDF are always uppercase with diacritics stripped, so
                // the preview must do the same to stay truthful.
                const upperNoAccents = (s) => {
                    if (!s) return s;
                    return s.normalize('NFD').replace(/\p{M}/gu, '').toUpperCase();
                };

                const escapeHtml = (s) => String(s ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');

                const placeholderGroups = [
                    {
                        key: 'client',
                        label: 'Πελάτης',
                        icon: 'fas fa-user',
                        items: [
                            { token: '{{full_name}}',   label: 'Πλήρες Όνομα (Επώνυμο + Όνομα)' },
                            { token: '{{lastname}}',    label: 'Επώνυμο' },
                            { token: '{{name}}',        label: 'Όνομα' },
                            { token: '{{email}}',       label: 'Email' },
                            { token: '{{url_slug}}',    label: 'URL slug' },
                            { token: '{{external_id}}', label: 'Excel ID' },
                        ],
                    },
                    {
                        key: 'certificate',
                        label: 'Πιστοποιητικό',
                        icon: 'fas fa-certificate',
                        items: [
                            { token: '{{category}}', label: 'Κατηγορία' },
                            { token: '{{date}}',    label: 'Σημερινή ημερομηνία' },
                            { token: '{{qr}}',      label: 'QR Code (img tag)' },
                            { token: '{{qr_url}}',  label: 'QR URL (string)' },
                        ],
                    },
                    {
                        key: 'custom',
                        label: 'Custom πεδία',
                        icon: 'fas fa-list',
                        items: (fields || []).map(f => ({
                            token: '{{field:' + f.name + '}}',
                            label: f.name + ' (' + f.type + ')',
                        })),
                    },
                ];

                const samples = {
                    diploma:
`<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="utf-8">
<style>
@page { size: A4 portrait; margin: 0; }
body { font-family: 'Roboto', sans-serif; margin: 0; padding: 80px 60px; text-align: center; color: #1e293b; }
.eyebrow { font-size: 12px; letter-spacing: 6px; color: #64748b; margin-bottom: 8px; }
.title { font-size: 44px; font-weight: 700; color: #0f172a; margin: 0 0 8px; }
.divider { width: 60px; height: 3px; background: #0f172a; margin: 24px auto; }
.intro { font-size: 14px; color: #475569; margin-bottom: 30px; }
.name { font-size: 36px; font-weight: 600; margin: 20px 0; color: #0f172a; }
.category { font-size: 18px; color: #334155; margin: 30px 0; }
.date { font-size: 13px; color: #64748b; margin-top: 60px; }
.qr { margin-top: 30px; }
</style>
</head>
<body>
  <div class="eyebrow">ΒΕΒΑΙΩΣΗ</div>
  <h1 class="title">ΠΑΡΑΚΟΛΟΥΘΗΣΗΣ</h1>
  <div class="divider"></div>
  <p class="intro">Πιστοποιείται ότι ο/η</p>
  <div class="name">{{full_name}}</div>
  <p class="intro">παρακολούθησε με επιτυχία το πρόγραμμα</p>
  <div class="category">{{category}}</div>
  <div class="date">Ημερομηνία: {{date}}</div>
  <div class="qr">{{qr}}</div>
</body>
</html>`,

                    award:
`<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="utf-8">
<style>
@page { size: A4 landscape; margin: 0; }
body { font-family: 'Roboto', sans-serif; margin: 0; padding: 40px; color: #1e293b; }
.frame { border: 10px solid #0f172a; padding: 40px 60px; text-align: center; height: calc(100% - 100px); position: relative; }
.frame::after { content: ''; position: absolute; inset: 8px; border: 1px solid #94a3b8; pointer-events: none; }
.eyebrow { font-size: 14px; letter-spacing: 10px; color: #64748b; margin-bottom: 16px; }
.title { font-size: 56px; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: 4px; }
.intro { font-size: 14px; color: #475569; margin-top: 30px; }
.name { font-size: 44px; font-weight: 600; margin: 20px 0; color: #0f172a; }
.category { font-size: 20px; color: #334155; margin: 20px 0; }
.footer { display: flex; justify-content: space-between; align-items: end; margin-top: 50px; font-size: 12px; color: #64748b; }
.qr img { width: 18mm !important; height: 18mm !important; }
</style>
</head>
<body>
  <div class="frame">
    <div class="eyebrow">CERTIFICATE</div>
    <h1 class="title">ΠΙΣΤΟΠΟΙΗΤΙΚΟ</h1>
    <p class="intro">Απονέμεται στον/στην</p>
    <div class="name">{{full_name}}</div>
    <p class="intro">για την επιτυχή ολοκλήρωση του προγράμματος</p>
    <div class="category">{{category}}</div>
    <div class="footer">
      <div>Ημερομηνία: {{date}}</div>
      <div class="qr">{{qr}}</div>
    </div>
  </div>
</body>
</html>`,

                    minimal:
`<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="utf-8">
<style>
@page { size: A4 portrait; margin: 20mm; }
body { font-family: 'Roboto', sans-serif; color: #1e293b; }
</style>
</head>
<body>
  <h1>{{category}}</h1>
  <p>Όνομα: {{full_name}}</p>
  <p>Ημερομηνία: {{date}}</p>
  {{qr}}
</body>
</html>`,
                };

                return {
                    html: initialHtml || '',
                    initialHtml: initialHtml || '',
                    highlightedHtml: '',
                    dirty: false,
                    orientation: initialOrientation === 'landscape' ? 'landscape' : 'portrait',
                    initialOrientation: initialOrientation === 'landscape' ? 'landscape' : 'portrait',
                    categoryName,
                    fields,
                    placeholderGroups,
                    placeholderQuery: '',
                    samples,
                    lineCount: 1,
                    previewScale: 1,
                    zoomMode: 'fit',
                    zoomOptions: [
                        { value: 'fit',  label: 'Auto', title: 'Προσαρμογή στο διαθέσιμο πλάτος' },
                        { value: 0.5,    label: '50%',  title: '50%' },
                        { value: 0.75,   label: '75%',  title: '75%' },
                        { value: 1,      label: '100%', title: 'Πραγματικό μέγεθος' },
                    ],
                    pageWidth: 794,
                    pageHeight: 1123,
                    splitPercent: 50,
                    _previewTimer: null,
                    clients: clients || [],
                    clientQuery: '',
                    selectedClient: null,

                    images: [],
                    imagesLoading: false,
                    imagesUploading: false,
                    imagesDragOver: false,
                    showImagesModal: false,

                    init() {
                        const saved = parseFloat(localStorage.getItem('htmlEditorSplit'));
                        if (!isNaN(saved) && saved >= 20 && saved <= 80) this.splitPercent = saved;
                        const savedZoom = localStorage.getItem('htmlEditorZoom');
                        if (savedZoom) {
                            const parsed = savedZoom === 'fit' ? 'fit' : parseFloat(savedZoom);
                            if (parsed === 'fit' || [0.5, 0.75, 1].includes(parsed)) this.zoomMode = parsed;
                        }
                        this.applyPageSize();
                        this.updateHighlight();
                        this.updateLineCount();
                        this.$nextTick(() => {
                            this.refreshPreview();
                            if (this.zoomMode !== 'fit') this.previewScale = this.zoomMode;
                        });
                    },

                    recomputeDirty() {
                        this.dirty = this.html !== this.initialHtml
                            || this.orientation !== this.initialOrientation;
                    },

                    setOrientation(value) {
                        const next = value === 'landscape' ? 'landscape' : 'portrait';
                        if (next === this.orientation) return;
                        this.orientation = next;
                        this.recomputeDirty();
                        this.applyPageSize();
                        this.$nextTick(() => {
                            this.refreshPreview();
                            this.fitPreview();
                        });
                    },

                    requestClose() {
                        if (this.dirty) {
                            if (! confirm('Έχεις μη αποθηκευμένες αλλαγές. Θέλεις σίγουρα να κλείσεις χωρίς αποθήκευση;')) {
                                return;
                            }
                        }
                        this.$wire.closeEditor();
                    },

                    applyPageSize() {
                        const landscape = this.orientation === 'landscape';
                        this.pageWidth  = landscape ? 1123 : 794;
                        this.pageHeight = landscape ? 794  : 1123;
                    },

                    onCodeInput() {
                        this.recomputeDirty();
                        this.updateHighlight();
                        this.updateLineCount();
                        if (this._previewTimer) clearTimeout(this._previewTimer);
                        this._previewTimer = setTimeout(() => this.refreshPreview(), 250);
                    },

                    updateLineCount() {
                        this.lineCount = ((this.html || '').match(/\n/g) || []).length + 1;
                    },

                    updateHighlight() {
                        const raw = this.html || '';
                        let out;
                        if (typeof Prism !== 'undefined' && Prism.languages && Prism.languages.markup) {
                            out = Prism.highlight(raw, Prism.languages.markup, 'markup');
                        } else {
                            out = escapeHtml(raw);
                        }
                        this.highlightedHtml = out + (out.endsWith('\n') ? '' : '\n');
                    },

                    syncScroll() {
                        const ta = this.$refs.codeEditor;
                        if (!ta) return;
                        if (this.$refs.codeHighlight) {
                            this.$refs.codeHighlight.scrollTop  = ta.scrollTop;
                            this.$refs.codeHighlight.scrollLeft = ta.scrollLeft;
                        }
                        if (this.$refs.gutter) {
                            this.$refs.gutter.scrollTop = ta.scrollTop;
                        }
                    },

                    /**
                     * Returns the placeholder palette grouped + filtered by the
                     * current search. When a client is selected, each item also
                     * carries a `preview` string so the user can see what value
                     * will replace the token in the live preview.
                     */
                    filteredGroups() {
                        const q = (this.placeholderQuery || '').trim().toLowerCase();
                        const c = this.selectedClient;

                        const today = new Date();
                        const dd = String(today.getDate()).padStart(2, '0');
                        const mm = String(today.getMonth() + 1).padStart(2, '0');
                        const yy = today.getFullYear();

                        const corePreview = (token) => {
                            if (!c) return '';
                            switch (token) {
                                case '{{full_name}}':   return upperNoAccents(c.full_name || '');
                                case '{{lastname}}':    return upperNoAccents(c.lastname  || '');
                                case '{{name}}':        return upperNoAccents(c.name      || '');
                                case '{{email}}':       return c.email       || '';
                                case '{{url_slug}}':    return c.url_slug    || '';
                                case '{{external_id}}': return c.external_id || '';
                                case '{{category}}':    return this.categoryName || '';
                                case '{{date}}':        return dd + '/' + mm + '/' + yy;
                            }
                            return '';
                        };

                        const fieldsByName = c && c.fields_by_name ? c.fields_by_name : null;
                        const customPreview = (token) => {
                            if (!fieldsByName) return '';
                            const name = token.replace(/^\{\{\s*field:/, '').replace(/\s*\}\}$/, '');
                            return fieldsByName.hasOwnProperty(name) ? (fieldsByName[name] || '') : '';
                        };

                        return this.placeholderGroups
                            .map(g => ({
                                ...g,
                                items: g.items
                                    .map(it => ({
                                        ...it,
                                        preview: g.key === 'custom' ? customPreview(it.token) : corePreview(it.token),
                                    }))
                                    .filter(it => !q
                                        || it.token.toLowerCase().includes(q)
                                        || (it.label || '').toLowerCase().includes(q)),
                            }))
                            .filter(g => g.items.length > 0);
                    },

                    setZoom(value) {
                        this.zoomMode = value;
                        localStorage.setItem('htmlEditorZoom', String(value));
                        if (value === 'fit') {
                            this.fitPreview();
                        } else {
                            this.previewScale = value;
                        }
                    },

                    fitPreview() {
                        if (this.zoomMode !== 'fit') return;
                        const scroll = this.$refs.previewScroll;
                        if (!scroll) return;
                        const avail = Math.max(0, scroll.clientWidth - 48);
                        this.previewScale = Math.min(1, avail / this.pageWidth);
                    },

                    loadSample(key) {
                        const tpl = this.samples[key];
                        if (!tpl) return;
                        if (this.html.trim() && !confirm('Θα αντικατασταθεί το τρέχον template. Συνέχεια;')) return;
                        this.html = tpl;
                        // Match orientation to the sample's @page setting
                        const m = tpl.match(/@page[^{]*\{[^}]*size\s*:\s*[^;}]*?(landscape|portrait)/i);
                        if (m) this.orientation = m[1].toLowerCase();
                        this.applyPageSize();
                        this.recomputeDirty();
                        this.updateHighlight();
                        this.updateLineCount();
                        this.$nextTick(() => this.refreshPreview());
                    },

                    startDrag(e) {
                        const container = this.$refs.splitContainer;
                        if (!container) return;
                        const rect = container.getBoundingClientRect();
                        const startX = e.clientX;
                        const startPct = this.splitPercent;

                        const move = (ev) => {
                            const dx = ev.clientX - startX;
                            let next = startPct + (dx / rect.width) * 100;
                            next = Math.max(20, Math.min(80, next));
                            this.splitPercent = next;
                            this.$nextTick(() => this.fitPreview());
                        };
                        const up = () => {
                            document.removeEventListener('mousemove', move);
                            document.removeEventListener('mouseup', up);
                            document.body.style.userSelect = '';
                            document.body.style.cursor = '';
                            localStorage.setItem('htmlEditorSplit', String(this.splitPercent));
                        };
                        document.addEventListener('mousemove', move);
                        document.addEventListener('mouseup', up);
                        document.body.style.userSelect = 'none';
                        document.body.style.cursor = 'col-resize';
                    },

                    filteredClients() {
                        const q = (this.clientQuery || '').trim().toLowerCase();
                        if (!q) return this.clients.slice(0, 200);
                        return this.clients
                            .filter(c => (c.full_name || '').toLowerCase().includes(q))
                            .slice(0, 200);
                    },

                    async selectClient(id) {
                        try {
                            const data = await this.$wire.fetchClientPreviewData(id);
                            if (data && data.id) {
                                this.selectedClient = data;
                                this.refreshPreview();
                            }
                        } catch (e) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'error', message: 'Δεν φορτώθηκαν τα δεδομένα του πελάτη' }
                            }));
                        }
                    },

                    clearClient() {
                        this.selectedClient = null;
                        this.refreshPreview();
                    },

                    // ===== Image library =====
                    csrfToken() {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        return meta ? meta.getAttribute('content') : '';
                    },

                    async openImageLibrary() {
                        this.showImagesModal = true;
                        await this.loadImages();
                    },

                    async loadImages() {
                        this.imagesLoading = true;
                        try {
                            const res = await fetch('/template-images', {
                                headers: { 'Accept': 'application/json' },
                                credentials: 'same-origin',
                            });
                            if (!res.ok) throw new Error('load failed');
                            const data = await res.json();
                            this.images = data.images || [];
                        } catch (e) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'error', message: 'Δεν φόρτωσαν οι φωτογραφίες' }
                            }));
                        } finally {
                            this.imagesLoading = false;
                        }
                    },

                    async uploadImageFile(file) {
                        const fd = new FormData();
                        fd.append('file', file);
                        const res = await fetch('/template-images', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken(),
                                'Accept': 'application/json',
                            },
                            body: fd,
                        });
                        if (!res.ok) {
                            let msg = 'Αποτυχία ανεβάσματος';
                            try {
                                const err = await res.json();
                                if (err && err.message) msg = err.message;
                            } catch (e) { /* ignore */ }
                            throw new Error(msg);
                        }
                        return res.json();
                    },

                    async handleImageInput(event) {
                        const files = Array.from(event.target.files || []);
                        event.target.value = '';
                        await this.uploadMany(files);
                    },

                    async handleImageDrop(event) {
                        const files = Array.from(event.dataTransfer?.files || [])
                            .filter(f => f.type.startsWith('image/'));
                        await this.uploadMany(files);
                    },

                    async uploadMany(files) {
                        if (!files.length) return;
                        this.imagesUploading = true;
                        let okCount = 0;
                        let errMsg = '';
                        for (const f of files) {
                            try {
                                await this.uploadImageFile(f);
                                okCount++;
                            } catch (e) {
                                errMsg = e.message || 'Αποτυχία';
                            }
                        }
                        this.imagesUploading = false;
                        await this.loadImages();
                        if (okCount > 0) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'success', message: okCount + ' εικόνα/ες ανέβηκαν' }
                            }));
                        }
                        if (errMsg) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'error', message: errMsg }
                            }));
                        }
                    },

                    async deleteImage(name) {
                        if (!confirm('Διαγραφή της φωτογραφίας;')) return;
                        try {
                            const res = await fetch('/template-images/' + encodeURIComponent(name), {
                                method: 'DELETE',
                                credentials: 'same-origin',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrfToken(),
                                    'Accept': 'application/json',
                                },
                            });
                            if (!res.ok) throw new Error('delete failed');
                            await this.loadImages();
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'success', message: 'Διαγράφηκε' }
                            }));
                        } catch (e) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'error', message: 'Αποτυχία διαγραφής' }
                            }));
                        }
                    },

                    insertImage(url) {
                        const snippet = '<img src="' + url + '" style="display:block;max-width:100%;margin:0 auto;" alt="">';
                        this.insertAtCursor(snippet);
                        this.showImagesModal = false;
                    },

                    copyImageUrl(url) {
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(url);
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'success', message: 'URL αντιγράφηκε' }
                            }));
                        }
                    },

                    sampleFor(type, name) {
                        if (type === 'date')   return '01/01/2026';
                        if (type === 'number') return '40';
                        return name || 'Δείγμα';
                    },

                    buildSampleHtml() {
                        let out = this.html || '';
                        const today = new Date();
                        const dd = String(today.getDate()).padStart(2, '0');
                        const mm = String(today.getMonth() + 1).padStart(2, '0');
                        const yy = today.getFullYear();

                        const c = this.selectedClient;

                        const core = c ? {
                            full_name:   upperNoAccents(c.full_name || ''),
                            lastname:    upperNoAccents(c.lastname  || ''),
                            name:        upperNoAccents(c.name      || ''),
                            email:       c.email       || '',
                            url_slug:    c.url_slug    || '',
                            external_id: c.external_id || '',
                            category:    this.categoryName || '',
                            date:        dd + '/' + mm + '/' + yy,
                        } : {
                            full_name:   upperNoAccents('Παπαδόπουλος Γιώργος'),
                            lastname:    upperNoAccents('Παπαδόπουλος'),
                            name:        upperNoAccents('Γιώργος'),
                            email:       'demo@example.com',
                            url_slug:    'demo-client',
                            external_id: 'EX-001',
                            category:    this.categoryName || 'Δείγμα',
                            date:        dd + '/' + mm + '/' + yy,
                        };

                        Object.entries(core).forEach(([k, v]) => {
                            const re = new RegExp('\\{\\{\\s*' + k + '\\s*\\}\\}', 'g');
                            out = out.replace(re, v);
                        });

                        const fieldsById   = (c && c.fields_by_id)   ? c.fields_by_id   : null;
                        const fieldsByName = (c && c.fields_by_name) ? c.fields_by_name : null;

                        (this.fields || []).forEach(f => {
                            let valueById = '';
                            let valueByName = '';
                            if (fieldsById && fieldsById.hasOwnProperty(f.id)) {
                                valueById = fieldsById[f.id];
                            } else if (!c) {
                                valueById = this.sampleFor(f.type, f.name);
                            }
                            if (fieldsByName && fieldsByName.hasOwnProperty(f.name)) {
                                valueByName = fieldsByName[f.name];
                            } else if (!c) {
                                valueByName = this.sampleFor(f.type, f.name);
                            }

                            const reId   = new RegExp('\\{\\{\\s*cf_' + f.id + '\\s*\\}\\}', 'g');
                            const reName = new RegExp('\\{\\{\\s*field:' + f.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*\\}\\}', 'g');
                            out = out.replace(reId, valueById).replace(reName, valueByName);
                        });

                        const slug  = (c && c.url_slug) ? c.url_slug : 'demo-client';
                        const qrUrl = window.location.origin + '/' + slug;
                        const qrImg = '<img src="' + window.location.origin + '/qr.png?data='
                                    + encodeURIComponent(qrUrl)
                                    + '" style="display:block;margin:0 auto;width:22mm;height:22mm;">';
                        out = out.replace(/\{\{\s*qr\s*\}\}/g, qrImg);
                        out = out.replace(/\{\{\s*qr_url\s*\}\}/g, qrUrl);

                        out = out.replace(/\{\{(?:field:[^}]+|cf_\d+|name|lastname|full_name|email|url_slug|external_id|category|date|public|qr|qr_url)\}\}/g, '');

                        const origin = window.location.origin;
                        out = out.replace(/(<img[^>]+src=["'])(?!https?:\/\/|data:|\/\/|\/)([^"']+)/gi, '$1' + origin + '/$2');

                        return out;
                    },

                    refreshPreview() {
                        const iframe = this.$refs.preview;
                        if (!iframe) return;
                        const doc = iframe.contentDocument || iframe.contentWindow.document;
                        doc.open();
                        doc.write(this.buildSampleHtml());
                        doc.close();
                        this.$nextTick(() => this.fitPreview());
                    },

                    insertAtCursor(token) {
                        const ta = this.$refs.codeEditor;
                        if (!ta) return;
                        const start = ta.selectionStart;
                        const end   = ta.selectionEnd;
                        this.html = this.html.slice(0, start) + token + this.html.slice(end);
                        this.recomputeDirty();
                        this.updateHighlight();
                        this.updateLineCount();
                        this.$nextTick(() => {
                            ta.focus();
                            ta.selectionStart = ta.selectionEnd = start + token.length;
                            this.refreshPreview();
                        });
                    },

                    handleTab(e) {
                        const ta = this.$refs.codeEditor;
                        const start = ta.selectionStart;
                        const end   = ta.selectionEnd;
                        const indent = '    ';
                        this.html = this.html.slice(0, start) + indent + this.html.slice(end);
                        this.recomputeDirty();
                        this.updateHighlight();
                        this.updateLineCount();
                        this.$nextTick(() => {
                            ta.selectionStart = ta.selectionEnd = start + indent.length;
                        });
                    },

                    formatHtml() {
                        try {
                            let html = this.html;
                            html = html.replace(/>\s+</g, '>\n<');
                            const lines = html.split('\n');
                            let depth = 0;
                            const formatted = lines.map(line => {
                                const trimmed = line.trim();
                                if (!trimmed) return '';
                                if (trimmed.startsWith('</')) depth = Math.max(0, depth - 1);
                                const out = '    '.repeat(depth) + trimmed;
                                if (trimmed.startsWith('<') && !trimmed.startsWith('</') && !trimmed.endsWith('/>') && !/<(br|hr|img|input|meta|link)\b/i.test(trimmed) && !/<\/[a-z]+>$/i.test(trimmed)) {
                                    depth++;
                                }
                                return out;
                            }).filter(l => l !== '').join('\n');
                            this.html = formatted;
                            this.recomputeDirty();
                            this.updateHighlight();
                            this.updateLineCount();
                            this.refreshPreview();
                        } catch (e) { /* ignore */ }
                    },

                    copyHtml() {
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(this.html);
                            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Αντιγράφηκε στο clipboard' } }));
                        }
                    },

                    resetHtml() {
                        if (!confirm('Επαναφορά στην τελευταία αποθηκευμένη έκδοση;')) return;
                        this.html = this.initialHtml;
                        this.orientation = this.initialOrientation;
                        this.dirty = false;
                        this.applyPageSize();
                        this.updateHighlight();
                        this.updateLineCount();
                        this.refreshPreview();
                    },

                    save() {
                        this.$wire.saveTemplate(this.html, this.orientation);
                        this.initialHtml = this.html;
                        this.initialOrientation = this.orientation;
                        this.dirty = false;
                    },
                };
            };
        </script>
        @endverbatim
    @endpush
</div>
