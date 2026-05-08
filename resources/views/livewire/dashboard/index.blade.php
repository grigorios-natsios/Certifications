<div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Πίνακας Ελέγχου') }}</h1>
                <p class="page-subtitle">{{ __('Συνοπτική εικόνα & εισαγωγή πελατών') }}</p>
            </div>
            <div class="toolbar">
                <a href="{{ route('clients.index') }}" wire:navigate class="btn-secondary">
                    <i class="fas fa-list text-xs"></i>
                    {{ __('Πελάτες') }}
                </a>
                <a href="{{ route('clients.create') }}" wire:navigate class="btn-primary">
                    {{ __('Νέος πελάτης') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @php
                $palette = [
                    ['bg' => 'bg-brand-50',  'fg' => 'text-brand-600',  'bar' => 'bg-brand-500',  'icon' => 'fa-graduation-cap'],
                    ['bg' => 'bg-amber-50',  'fg' => 'text-amber-600',  'bar' => 'bg-amber-500',  'icon' => 'fa-award'],
                    ['bg' => 'bg-sky-50',    'fg' => 'text-sky-600',    'bar' => 'bg-sky-500',    'icon' => 'fa-certificate'],
                    ['bg' => 'bg-violet-50', 'fg' => 'text-violet-600', 'bar' => 'bg-violet-500', 'icon' => 'fa-medal'],
                    ['bg' => 'bg-rose-50',   'fg' => 'text-rose-600',   'bar' => 'bg-rose-500',   'icon' => 'fa-clipboard-check'],
                    ['bg' => 'bg-teal-50',   'fg' => 'text-teal-600',   'bar' => 'bg-teal-500',   'icon' => 'fa-book-open'],
                ];
                $sortedCats = $categories->sortByDesc('clients_count')->values();
            @endphp

            <div class="card overflow-hidden">
                <div class="px-5 py-4 flex flex-wrap items-center gap-x-8 gap-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-base flex-shrink-0">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-semibold text-slate-900 tabular-nums leading-none">{{ number_format($stats['total']) }}</p>
                            <p class="text-xs text-slate-500 mt-1.5">{{ __('Σύνολο πελατών') }}</p>
                        </div>
                    </div>

                    <div class="hidden sm:block self-stretch w-px bg-slate-200"></div>

                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-base flex-shrink-0">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-semibold text-slate-900 tabular-nums leading-none">{{ number_format($stats['this_year']) }}</p>
                            <p class="text-xs text-slate-500 mt-1.5">{{ __('Νέοι το') }} {{ now()->year }}</p>
                        </div>
                    </div>
                </div>

                @if($sortedCats->isNotEmpty())
                    <div class="border-t border-slate-100"></div>

                    <div class="px-5 py-4">
                        <p class="text-xs font-semibold text-slate-500 mb-3">{{ __('Κατανομή ανά κατηγορία') }}</p>
                        <div class="space-y-2.5">
                            @foreach($sortedCats as $cat)
                                @php
                                    $p = $palette[$loop->index % count($palette)];
                                    $pct = $stats['total'] > 0 ? ($cat->clients_count / $stats['total']) * 100 : 0;
                                @endphp
                                <a href="{{ route('clients.index', ['categoryFilter' => $cat->id]) }}" wire:navigate
                                   class="block rounded-md px-2 -mx-2 py-1 hover:bg-slate-50 transition group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-md {{ $p['bg'] }} {{ $p['fg'] }} flex items-center justify-center text-xs flex-shrink-0">
                                            <i class="fas {{ $p['icon'] }}"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <span class="text-sm text-slate-700 truncate group-hover:text-slate-900">
                                                    {{ $cat->name }}
                                                </span>
                                                <span class="text-xs tabular-nums whitespace-nowrap">
                                                    <span class="font-semibold text-slate-900">{{ number_format($cat->clients_count) }}</span>
                                                    <span class="ml-1.5 text-slate-400">{{ round($pct) }}%</span>
                                                </span>
                                            </div>
                                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full {{ $p['bar'] }} rounded-full transition-all duration-300"
                                                     style="width: {{ max($pct, $cat->clients_count > 0 ? 2 : 0) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="card p-5">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-import"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="section-title">{{ __('Εισαγωγή Excel / CSV') }}</h2>
                        <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                            {{ __('Στήλες: ID, Name, Lastname, Start date, End Date, Title, Category, Hours, URL. Νέοι προστίθενται, υπάρχοντες (κατά ID) ενημερώνονται. Όσοι λείπουν δεν διαγράφονται.') }}
                        </p>
                    </div>
                </div>

                <form wire:submit.prevent="importExcel"
                      x-data="{
                          isDragging: false,
                          fileName: null,
                          fileSize: null,
                          handleFile(file) {
                              if (!file) return;
                              this.fileName = file.name;
                              this.fileSize = file.size < 1024 * 1024
                                  ? (file.size / 1024).toFixed(1) + ' KB'
                                  : (file.size / 1024 / 1024).toFixed(2) + ' MB';
                          },
                          clearFile() {
                              this.fileName = null;
                              this.fileSize = null;
                              this.$refs.fileInput.value = '';
                              $wire.set('importFile', null);
                          },
                          init() {
                              this.$watch('$wire.importFile', value => {
                                  if (!value) { this.fileName = null; this.fileSize = null; }
                              });
                          }
                      }">
                    <div @drop.prevent="isDragging = false;
                                        const f = $event.dataTransfer.files[0];
                                        if (f) {
                                            $refs.fileInput.files = $event.dataTransfer.files;
                                            $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                                            handleFile(f);
                                        }"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @click="!fileName && $refs.fileInput.click()"
                         :class="{
                             'border-emerald-400 bg-emerald-50/50': isDragging,
                             'border-slate-300 hover:border-slate-400 hover:bg-slate-50/60': !isDragging && !fileName,
                             'border-emerald-200 bg-emerald-50/30': fileName && !isDragging,
                             'cursor-pointer': !fileName,
                         }"
                         class="relative border-2 border-dashed rounded-lg p-6 transition">

                        <input type="file" wire:model="importFile" x-ref="fileInput"
                               accept=".xlsx,.xls,.csv,.ods,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv"
                               @change="handleFile($event.target.files[0])"
                               class="hidden">

                        <div x-show="!fileName" class="text-center">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 mx-auto flex items-center justify-center text-lg mb-3">
                                <i class="fas fa-cloud-arrow-up"></i>
                            </div>
                            <p class="text-sm text-slate-700">
                                <span class="font-medium">{{ __('Σύρε εδώ το αρχείο') }}</span>
                                <span class="text-slate-500">{{ __('ή') }}</span>
                                <span class="text-emerald-700 font-medium">{{ __('πάτησε για επιλογή') }}</span>
                            </p>
                            <p class="text-[11px] text-slate-500 mt-2">
                                .xlsx · .xls · .csv · .ods <span class="text-slate-400">· {{ __('έως 20MB') }}</span>
                            </p>
                        </div>

                        <div x-show="fileName" x-cloak class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-base flex-shrink-0">
                                <i class="fas fa-file-excel"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-900 truncate" x-text="fileName"></p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    <span x-text="fileSize"></span>
                                    <span class="mx-1 text-slate-300">·</span>
                                    <span class="text-emerald-700">{{ __('έτοιμο για εισαγωγή') }}</span>
                                </p>
                            </div>
                            <button type="button" @click.stop="clearFile()"
                                    class="w-8 h-8 rounded-md text-slate-400 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition flex-shrink-0"
                                    title="{{ __('Αφαίρεση') }}">
                                <i class="fas fa-xmark text-xs"></i>
                            </button>
                        </div>

                        <div wire:loading wire:target="importFile"
                             class="absolute inset-0 bg-white/85 rounded-lg flex items-center justify-center text-xs text-slate-600">
                            <i class="fas fa-circle-notch fa-spin mr-2"></i> {{ __('Ανέβασμα...') }}
                        </div>
                    </div>

                    @error('importFile') <p class="text-rose-600 text-xs mt-2">{{ $message }}</p> @enderror

                    <div class="flex justify-end mt-3">
                        <button type="submit" class="btn-primary"
                                :disabled="!fileName"
                                :class="{ 'opacity-50 cursor-not-allowed': !fileName }"
                                wire:loading.attr="disabled" wire:target="importExcel">
                            <i class="fas fa-upload text-xs" wire:loading.remove wire:target="importExcel"></i>
                            <i class="fas fa-circle-notch fa-spin text-xs" wire:loading wire:target="importExcel"></i>
                            <span wire:loading.remove wire:target="importExcel">{{ __('Εισαγωγή') }}</span>
                            <span wire:loading wire:target="importExcel">{{ __('Επεξεργασία...') }}</span>
                        </button>
                    </div>
                </form>
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
