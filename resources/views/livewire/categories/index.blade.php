<div>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Κατηγορίες πιστοποιητικών') }}</h1>
                <p class="page-subtitle">{{ __('HTML templates που γίνονται PDF πιστοποιητικά') }}</p>
            </div>
            <button type="button" onclick="Livewire.dispatch('categories::create')" class="btn-primary">
                {{ __('Νέα κατηγορία') }}
            </button>
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
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-folder-open text-slate-400"></i></div>
                                            <h3 class="mt-3 text-sm font-medium text-slate-900">{{ __('Δεν υπάρχουν κατηγορίες') }}</h3>
                                            <p class="text-xs text-slate-500 mt-1">{{ __('Δημιούργησε την πρώτη σου κατηγορία.') }}</p>
                                            <button wire:click="openCreate" class="btn-primary mt-4 inline-flex">
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
                    categoryName: @js($editorName),
                    fields: @js($customFields->all()),
                 })"
                 x-init="init()"
                 x-on:resize.window.debounce.150ms="fitPreview()">

                <div class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-md bg-brand-600 text-white flex items-center justify-center"><i class="fas fa-code"></i></span>
                        <div class="min-w-0">
                            <h2 class="section-title truncate">{{ __('HTML Template') }}</h2>
                            <p class="text-xs text-slate-500 truncate">{{ $editorName }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span x-show="dirty" class="text-xs text-amber-600">
                            <i class="fas fa-circle text-[8px] mr-1"></i>{{ __('Μη αποθηκευμένες αλλαγές') }}
                        </span>
                        <button type="button" @click="$wire.closeEditor()" class="btn-secondary">
                            <i class="fas fa-xmark mr-1"></i>{{ __('Κλείσιμο') }}
                        </button>
                        <button type="button" @click="save()" class="btn-primary">
                            <i class="fas fa-save mr-1"></i>{{ __('Αποθήκευση') }}
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-hidden flex flex-col lg:flex-row" x-ref="splitContainer">
                    <div class="flex flex-col w-full bg-slate-50 lg:w-[var(--split-w)] lg:flex-shrink-0"
                         :style="`--split-w: ${splitPercent}%`">
                        <div class="px-4 py-2 border-b border-slate-200 bg-white flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-xs text-slate-600 font-medium">
                                <i class="fas fa-code text-slate-400"></i>
                                <span>{{ __('HTML') }}</span>
                                <span x-show="html.length" class="text-slate-400" x-text="html.length + ' ' + '{{ __('χαρακτήρες') }}'"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="formatHtml()" class="text-xs px-2 py-1 rounded hover:bg-slate-100 text-slate-600" title="{{ __('Format') }}">
                                    <i class="fas fa-indent text-[10px]"></i> {{ __('Format') }}
                                </button>
                                <button type="button" @click="copyHtml()" class="text-xs px-2 py-1 rounded hover:bg-slate-100 text-slate-600" title="{{ __('Αντιγραφή') }}">
                                    <i class="fas fa-copy text-[10px]"></i>
                                </button>
                                <button type="button" @click="resetHtml()" class="text-xs px-2 py-1 rounded hover:bg-slate-100 text-slate-600" title="{{ __('Επαναφορά') }}">
                                    <i class="fas fa-rotate-left text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                        <textarea x-ref="codeEditor"
                                  x-model="html"
                                  @input.debounce.250ms="dirty = true; refreshPreview()"
                                  @keydown.tab.prevent="handleTab($event)"
                                  spellcheck="false"
                                  class="flex-1 w-full p-4 bg-white text-slate-800 text-[13px] leading-relaxed font-mono resize-none focus:outline-none border-0"
                                  style="tab-size: 4; -moz-tab-size: 4;"
                                  placeholder="{{ __('Επικόλλησε ή γράψε HTML εδώ...') }}"></textarea>

                        <div class="border-t border-slate-200 bg-white px-3 py-2 max-h-44 overflow-y-auto">
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold mb-1.5">{{ __('Πεδία (κλικ για εισαγωγή)') }}</p>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="ph in placeholders" :key="ph.token">
                                    <button type="button" @click="insertAtCursor(ph.token)"
                                            class="text-[11px] px-2 py-0.5 rounded border border-slate-200 bg-slate-50 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 text-slate-600 font-mono transition"
                                            x-text="ph.token"
                                            :title="ph.label"></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="hidden lg:flex w-1.5 cursor-col-resize bg-slate-200 hover:bg-brand-400 active:bg-brand-500 transition-colors flex-shrink-0 items-center justify-center group"
                         @mousedown.prevent="startDrag($event)"
                         @dblclick="splitPercent = 50; $nextTick(() => fitPreview())"
                         title="{{ __('Σύρε για αλλαγή μεγέθους · διπλό κλικ για 50/50') }}">
                        <div class="w-0.5 h-10 bg-slate-400 group-hover:bg-white rounded transition-colors"></div>
                    </div>

                    <div class="flex flex-col w-full lg:flex-1 lg:min-w-0 bg-slate-100">
                        <div class="px-4 py-2 border-b border-slate-200 bg-white flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-xs text-slate-600 font-medium">
                                <i class="fas fa-eye text-slate-400"></i>
                                <span>{{ __('Προεπισκόπηση (με δείγμα δεδομένων)') }}</span>
                            </div>
                            <button type="button" @click="refreshPreview()" class="text-xs px-2 py-1 rounded hover:bg-slate-100 text-slate-600" title="{{ __('Ανανέωση') }}">
                                <i class="fas fa-rotate text-[10px]"></i>
                            </button>
                        </div>
                        <div class="flex-1 overflow-auto p-4 flex justify-center" x-ref="previewScroll">
                            <div class="flex-shrink-0"
                                 :style="`width: ${pageWidth * previewScale}px; height: ${pageHeight * previewScale}px;`">
                                <iframe x-ref="preview" sandbox="allow-same-origin"
                                        class="bg-white shadow-sm rounded border border-slate-200 block"
                                        :style="`width: ${pageWidth}px; height: ${pageHeight}px; transform: scale(${previewScale}); transform-origin: top left;`"></iframe>
                            </div>
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

    @push('scripts')
        @verbatim
        <script>
            window.htmlTemplateEditor = function ({ initialHtml, categoryName, fields }) {
                // Mirror CertificatePdfRenderer::upperNoAccents — names in the
                // final PDF are always uppercase with diacritics stripped, so
                // the preview must do the same to stay truthful.
                const upperNoAccents = (s) => {
                    if (!s) return s;
                    return s.normalize('NFD').replace(/\p{M}/gu, '').toUpperCase();
                };

                const corePlaceholders = [
                    { token: '{{full_name}}',   label: 'Πλήρες Όνομα (Επώνυμο + Όνομα)' },
                    { token: '{{lastname}}',    label: 'Επώνυμο' },
                    { token: '{{name}}',        label: 'Όνομα' },
                    { token: '{{email}}',       label: 'Email' },
                    { token: '{{url_slug}}',    label: 'URL slug' },
                    { token: '{{external_id}}', label: 'Excel ID' },
                    { token: '{{category}}',    label: 'Κατηγορία' },
                    { token: '{{date}}',        label: 'Σημερινή Ημ/νία' },
                    { token: '{{qr}}',          label: 'QR Code (img)' },
                    { token: '{{qr_url}}',      label: 'QR URL (string)' },
                ];

                const placeholders = corePlaceholders.concat(
                    (fields || []).map(f => ({
                        token: '{{field:' + f.name + '}}',
                        label: 'Custom: ' + f.name + ' (' + f.type + ')',
                    }))
                );

                return {
                    html: initialHtml || '',
                    initialHtml: initialHtml || '',
                    dirty: false,
                    categoryName,
                    fields,
                    placeholders,
                    previewScale: 1,
                    pageWidth: 794,
                    pageHeight: 1123,
                    splitPercent: 50,

                    init() {
                        const saved = parseFloat(localStorage.getItem('htmlEditorSplit'));
                        if (!isNaN(saved) && saved >= 20 && saved <= 80) this.splitPercent = saved;
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

                    detectPageSize() {
                        const m = (this.html || '').match(/@page\s*\{[^}]*size\s*:\s*[^;}]*?(landscape|portrait)/i);
                        const landscape = m && m[1].toLowerCase() === 'landscape';
                        this.pageWidth  = landscape ? 1123 : 794;
                        this.pageHeight = landscape ? 794  : 1123;
                    },

                    fitPreview() {
                        const scroll = this.$refs.previewScroll;
                        if (!scroll) return;
                        const avail = Math.max(0, scroll.clientWidth - 32);
                        this.previewScale = Math.min(1, avail / this.pageWidth);
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

                        const core = {
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

                        (this.fields || []).forEach(f => {
                            const sample = this.sampleFor(f.type, f.name);
                            const reId   = new RegExp('\\{\\{\\s*cf_' + f.id + '\\s*\\}\\}', 'g');
                            const reName = new RegExp('\\{\\{\\s*field:' + f.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*\\}\\}', 'g');
                            out = out.replace(reId, sample).replace(reName, sample);
                        });

                        const qrUrl = window.location.origin + '/demo-client?cat=' + (this.categoryName || 'demo');
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
                        this.detectPageSize();
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
                        this.dirty = true;
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
                        this.dirty = true;
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
                            this.dirty = true;
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
                        this.dirty = false;
                        this.refreshPreview();
                    },

                    save() {
                        this.$wire.saveTemplate(this.html);
                        this.initialHtml = this.html;
                        this.dirty = false;
                    },
                };
            };
        </script>
        @endverbatim
    @endpush
</div>
