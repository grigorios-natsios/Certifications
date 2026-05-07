<div>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
        <style>
            .gjs-block { min-height: auto !important; padding: 8px !important; }
            .gjs-block-label { font-size: 11px !important; }
            .gjs-pn-views .gjs-pn-btn { padding: 6px 8px !important; }
            .cert-placeholder-block {
                background: #fef2f2; border: 1px dashed #dc2626;
                color: #b91c1c; padding: 4px 8px; border-radius: 4px;
                font-size: 13px; display: inline-block;
            }
        </style>
    @endpush

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Κατηγορίες πιστοποιητικών') }}</h1>
                <p class="page-subtitle">{{ __('HTML templates που γίνονται PDF πιστοποιητικά') }}</p>
            </div>
            <button wire:click="openCreate" class="btn-primary">
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
                                        <button wire:click="openEditor({{ $cat->id }})" class="btn-secondary text-xs px-2.5 py-1.5">
                                            {{ __('Σχεδίαση') }}
                                        </button>
                                        <button wire:click="openEdit({{ $cat->id }})" class="btn-icon" title="Μετονομασία"><i class="fas fa-pen text-xs"></i></button>
                                        <button wire:click="delete({{ $cat->id }})"
                                                onclick="return confirm('Σίγουρα θέλεις να διαγράψεις αυτή την κατηγορία;')"
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
                 x-data="certificateEditor({
                    initialHtml: @js($editorTemplate),
                    fields: @js($customFields->all()),
                 })"
                 x-init="$nextTick(() => mount($refs.canvas))">

                <div class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-md bg-brand-600 text-white flex items-center justify-center"><i class="fas fa-pen-ruler"></i></span>
                        <div class="min-w-0">
                            <h2 class="section-title truncate">{{ __('Σχεδίαση Template') }}</h2>
                            <p class="text-xs text-slate-500 truncate">{{ $editorName }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <button type="button" @click="$wire.closeEditor()" class="btn-secondary">
                            <i class="fas fa-xmark mr-1"></i>{{ __('Κλείσιμο') }}
                        </button>
                        <button type="button" @click="save()" class="btn-primary">
                            <i class="fas fa-save mr-1"></i>{{ __('Αποθήκευση') }}
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-hidden" wire:ignore>
                    <div x-ref="canvas" class="h-full w-full"></div>
                </div>
            </div>
        @endif
    </div>

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
        <script src="https://unpkg.com/grapesjs"></script>
        @verbatim
        <script>
            window.certificateEditor = function ({ initialHtml, fields }) {
                return {
                    editor: null,
                    initialHtml,
                    fields,
                    mount(el) {
                        if (this.editor) return;

                        const blocks = this.buildBlocks();
                        const starter = this.initialHtml && this.initialHtml.trim().length
                            ? this.initialHtml
                            : `<div class="cert-page" style="position:relative;width:794px;min-height:1123px;background:#fff;padding:60px 80px;font-family:'DejaVu Sans',Arial,sans-serif;color:#1a1a1a;"><h1 style="text-align:center;font-size:48px;letter-spacing:8px;">ΒΕΒΑΙΩΣΗ</h1><p style="text-align:center;">Σύρε στοιχεία από αριστερά για να φτιάξεις το πιστοποιητικό σου.</p></div>`;

                        this.editor = grapesjs.init({
                            container: el,
                            height: '100%',
                            width: 'auto',
                            fromElement: false,
                            storageManager: false,
                            components: starter,
                            canvas: {
                                styles: [],
                                scripts: [],
                            },
                            deviceManager: {
                                devices: [
                                    { id: 'a4p', name: 'A4 Portrait', width: '794px',  widthMedia: '' },
                                    { id: 'a4l', name: 'A4 Landscape', width: '1123px', widthMedia: '' },
                                ],
                            },
                            blockManager: {
                                blocks: blocks,
                            },
                        });

                        try { this.editor.setDevice('A4 Portrait'); } catch (e) { /* ignore */ }
                    },

                    buildBlocks() {
                        const placeholderStyle = "background:#fef2f2;border:1px dashed #dc2626;color:#b91c1c;padding:2px 8px;border-radius:4px;font-weight:600;";
                        const list = [];

                        list.push({
                            id: 'cert-page', label: '<i class="fa fa-file"></i><div>A4 Σελίδα</div>', category: 'Σελίδα',
                            content: `<div class="cert-page" style="position:relative;width:794px;min-height:1123px;background:#fff;padding:60px 80px;font-family:'DejaVu Sans',Arial,sans-serif;"></div>`,
                        });

                        const corePlaceholders = [
                            { value: 'full_name', label: 'Πλήρες Όνομα (Επώνυμο + Όνομα)' },
                            { value: 'lastname',  label: 'Επώνυμο' },
                            { value: 'name',      label: 'Όνομα' },
                            { value: 'email',     label: 'Email' },
                            { value: 'url_slug',  label: 'URL slug' },
                            { value: 'external_id', label: 'Excel ID' },
                            { value: 'category',  label: 'Κατηγορία' },
                            { value: 'date',      label: 'Σημερινή Ημ/νία' },
                        ];
                        corePlaceholders.forEach(p => {
                            list.push({
                                id: 'ph-' + p.value, label: p.label, category: 'Πεδία',
                                content: `<span style="${placeholderStyle}">{{${p.value}}}</span>`,
                            });
                        });

                        (this.fields || []).forEach(f => {
                            list.push({
                                id: 'cf-' + f.id, label: f.name, category: 'Custom Πεδία',
                                content: `<span style="${placeholderStyle}">{{field:${f.name}}}</span>`,
                            });
                        });

                        list.push({
                            id: 'heading', label: 'Τίτλος', category: 'Κείμενο',
                            content: `<h1 style="text-align:center;font-size:48px;letter-spacing:8px;font-weight:700;margin:0;">ΒΕΒΑΙΩΣΗ</h1>`,
                        });
                        list.push({
                            id: 'subheading', label: 'Υπότιτλος', category: 'Κείμενο',
                            content: `<h2 style="text-align:center;font-size:24px;letter-spacing:6px;font-weight:300;margin:8px 0 0;">ΠΑΡΑΚΟΛΟΥΘΗΣΗΣ</h2>`,
                        });
                        list.push({
                            id: 'paragraph', label: 'Παράγραφος', category: 'Κείμενο',
                            content: `<p style="text-align:center;font-size:14px;margin:18px 0;">Νέο κείμενο...</p>`,
                        });
                        list.push({
                            id: 'legal', label: 'Νομικό κείμενο', category: 'Κείμενο',
                            content: `<p style="text-align:justify;font-size:11px;line-height:1.6;margin:24px 60px;color:#374151;">Σύμφωνα με το ΦΕΚ ...</p>`,
                        });

                        list.push({
                            id: 'image', label: 'Εικόνα', category: 'Στοιχεία',
                            content: { type: 'image', style: { 'max-width': '200px' } },
                            activate: true,
                        });
                        list.push({
                            id: 'logo-row', label: 'Σειρά Λογότυπων', category: 'Στοιχεία',
                            content: `<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:40px;"><img src="images/logos/eoppep.png" style="height:60px;"/><img src="images/logos/abm.png" style="height:60px;"/></div>`,
                        });
                        list.push({
                            id: 'signature', label: 'Υπογραφή', category: 'Στοιχεία',
                            content: `<div style="text-align:center;margin-top:24px;"><div style="border-top:1px solid #475569;width:220px;margin:0 auto;padding-top:8px;"><p style="font-size:14px;font-weight:700;margin:0;">Όνομα Υπογράφοντα</p><p style="font-size:12px;color:#475569;margin:2px 0 0;">Ρόλος / Θέση</p></div></div>`,
                        });
                        list.push({
                            id: 'qr', label: 'QR Placeholder', category: 'Στοιχεία',
                            content: `<div style="display:inline-block;width:100px;height:100px;border:1px solid #cbd5e1;"></div>`,
                        });
                        list.push({
                            id: 'kdvm', label: 'Γραμμή ΚΔΒΜ', category: 'Στοιχεία',
                            content: `<p style="text-align:center;font-size:10px;color:#6b7280;letter-spacing:1px;margin-top:6px;">Α. Α. ΚΔΒΜ: <span style="${placeholderStyle}">{{field:Αριθμός ΚΔΒΜ}}</span></p>`,
                        });

                        list.push({
                            id: 'spacer', label: 'Κενό', category: 'Layout',
                            content: `<div style="height:40px;"></div>`,
                        });
                        list.push({
                            id: 'two-col', label: '2 στήλες', category: 'Layout',
                            content: `<div style="display:flex;gap:20px;margin:16px 0;"><div style="flex:1;">Στήλη 1</div><div style="flex:1;">Στήλη 2</div></div>`,
                        });

                        return list;
                    },

                    save() {
                        if (!this.editor) return;
                        const html = this.editor.getHtml();
                        const css  = this.editor.getCss();
                        const full = '<style>' + css + '</style>\n' + html;
                        this.$wire.saveTemplate(full);
                    },
                };
            };
        </script>
        @endverbatim
    @endpush
</div>
