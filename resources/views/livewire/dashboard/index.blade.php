<div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Πίνακας Ελέγχου') }}</h1>
                <p class="page-subtitle">{{ __('Συνοπτική εικόνα & εισαγωγή πελατών') }}</p>
            </div>
            <div class="toolbar">
                <a href="{{ route('clients.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 text-sm font-medium text-slate-700 transition">
                    <i class="fas fa-list text-xs"></i>
                    {{ __('Πελάτες') }}
                </a>
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

            <div class="bg-white rounded-xl border border-slate-200 p-4 hover:border-slate-300 transition-colors"
                 x-data="{ showInfo: false }">
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
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="flex items-center gap-2.5 sm:flex-shrink-0">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-sm flex-shrink-0">
                                <i class="fas fa-file-import text-white text-sm"></i>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <h2 class="text-sm font-semibold text-slate-900 tracking-tight whitespace-nowrap">{{ __('Εισαγωγή Excel / CSV') }}</h2>
                                <button type="button" @click="showInfo = !showInfo" :class="{ 'text-emerald-600': showInfo, 'text-slate-400 hover:text-slate-600': !showInfo }"
                                        class="w-5 h-5 rounded-full flex items-center justify-center transition" title="{{ __('Πληροφορίες') }}">
                                    <i class="fas fa-circle-info text-xs"></i>
                                </button>
                            </div>
                        </div>

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
                             class="relative border-2 border-dashed rounded-lg px-3 py-2 transition flex-1 min-w-0">

                            <input type="file" wire:model="importFile" x-ref="fileInput"
                                   accept=".xlsx,.xls,.csv,.ods,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv"
                                   @change="handleFile($event.target.files[0])"
                                   class="hidden">

                            <div x-show="!fileName" class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-md bg-slate-100 text-slate-500 flex items-center justify-center text-xs flex-shrink-0">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-700 truncate">
                                        <span class="font-semibold">{{ __('Σύρε αρχείο') }}</span>
                                        <span class="text-slate-500">{{ __('ή') }}</span>
                                        <span class="text-emerald-700 font-semibold">{{ __('πάτησε για επιλογή') }}</span>
                                    </p>
                                    <p class="text-[10px] text-slate-400 leading-tight mt-0.5">.xlsx · .xls · .csv · .ods · {{ __('έως 20MB') }}</p>
                                </div>
                            </div>

                            <div x-show="fileName" x-cloak class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-md bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs flex-shrink-0">
                                    <i class="fas fa-file-excel"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-slate-900 truncate" x-text="fileName"></p>
                                    <p class="text-[10px] text-slate-500 leading-tight mt-0.5">
                                        <span x-text="fileSize"></span>
                                        <span class="mx-0.5 text-slate-300">·</span>
                                        <span class="text-emerald-700 font-medium">{{ __('έτοιμο') }}</span>
                                    </p>
                                </div>
                                <button type="button" @click.stop="clearFile()"
                                        class="w-6 h-6 rounded-md text-slate-400 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition flex-shrink-0"
                                        title="{{ __('Αφαίρεση') }}">
                                    <i class="fas fa-xmark text-xs"></i>
                                </button>
                            </div>

                            <div wire:loading wire:target="importFile"
                                 class="absolute inset-0 bg-white/85 rounded-lg flex items-center justify-center text-xs text-slate-600">
                                <i class="fas fa-circle-notch fa-spin mr-2"></i> {{ __('Ανέβασμα...') }}
                            </div>
                        </div>

                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-600 hover:to-brand-500 text-white text-xs font-semibold shadow-brand transition-all flex-shrink-0"
                                :disabled="!fileName"
                                :class="{ 'opacity-50 cursor-not-allowed': !fileName }"
                                wire:loading.attr="disabled" wire:target="importExcel">
                            <i class="fas fa-upload text-[10px]" wire:loading.remove wire:target="importExcel"></i>
                            <i class="fas fa-circle-notch fa-spin text-[10px]" wire:loading wire:target="importExcel"></i>
                            <span wire:loading.remove wire:target="importExcel">{{ __('Εισαγωγή') }}</span>
                            <span wire:loading wire:target="importExcel">{{ __('Επεξεργασία...') }}</span>
                        </button>
                    </div>

                    <div x-show="showInfo" x-cloak x-collapse class="mt-3 pt-3 border-t border-slate-100">
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            {{ __('Στήλες: ID, Name, Lastname, Start date, End Date, Title, Category, Hours, URL. Νέοι προστίθενται, υπάρχοντες (κατά ID) ενημερώνονται. Όσοι λείπουν δεν διαγράφονται.') }}
                        </p>
                    </div>

                    @error('importFile') <p class="text-rose-600 text-[11px] mt-2">{{ $message }}</p> @enderror
                </form>
            </div>

            @php
                $palette = [
                    ['hex' => '#EF4444', 'dot' => 'bg-brand-500'],
                    ['hex' => '#F59E0B', 'dot' => 'bg-amber-500'],
                    ['hex' => '#0EA5E9', 'dot' => 'bg-sky-500'],
                    ['hex' => '#8B5CF6', 'dot' => 'bg-violet-500'],
                    ['hex' => '#F43F5E', 'dot' => 'bg-rose-500'],
                    ['hex' => '#14B8A6', 'dot' => 'bg-teal-500'],
                ];
                $sortedCats = $categories->sortByDesc('clients_count')->values();
                $sliceTotal = $sortedCats->sum('clients_count');

                $slices = [];
                $cumAngle = 0;
                foreach ($sortedCats as $i => $cat) {
                    if ($sliceTotal <= 0 || $cat->clients_count <= 0) continue;
                    $angle = ($cat->clients_count / $sliceTotal) * 360;
                    $start = $cumAngle;
                    $end = $cumAngle + $angle;
                    $cumAngle = $end;

                    if ($angle >= 359.999) {
                        $path = "M 50,10 A 40,40 0 1 1 49.999,10 Z";
                    } else {
                        $sRad = deg2rad($start - 90);
                        $eRad = deg2rad($end - 90);
                        $x1 = 50 + 40 * cos($sRad);
                        $y1 = 50 + 40 * sin($sRad);
                        $x2 = 50 + 40 * cos($eRad);
                        $y2 = 50 + 40 * sin($eRad);
                        $large = $angle > 180 ? 1 : 0;
                        $path = sprintf("M 50,50 L %.4f,%.4f A 40,40 0 %d 1 %.4f,%.4f Z", $x1, $y1, $large, $x2, $y2);
                    }
                    $slices[] = ['path' => $path, 'hex' => $palette[$i % count($palette)]['hex']];
                }
            @endphp

            @php
                $certPerClient = $stats['total'] > 0 ? round($stats['certifications'] / $stats['total'], 1) : 0;
                $pdfCoverage   = $stats['certifications'] > 0 ? round(($stats['pdfs_generated'] / $stats['certifications']) * 100) : 0;
                $emailsTotal   = $stats['emails_sent'];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative bg-white rounded-2xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-md transition-all overflow-hidden group">
                    <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-rose-500/10 to-brand-600/10 blur-xl group-hover:scale-110 transition-transform"></div>
                    <div class="relative flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-brand-600 flex items-center justify-center shadow-md flex-shrink-0">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-slate-500 font-semibold">{{ __('Σύνολο πελατών') }}</p>
                            <p class="text-3xl font-bold text-slate-900 tabular-nums tracking-tight leading-none mt-2">
                                {{ number_format($stats['total']) }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-2 leading-snug">
                                {{ __('Όλοι οι καταχωρημένοι πελάτες στη βάση') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-md transition-all overflow-hidden group">
                    <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-violet-500/10 to-fuchsia-600/10 blur-xl group-hover:scale-110 transition-transform"></div>
                    <div class="relative flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center shadow-md flex-shrink-0">
                            <i class="fas fa-award text-white text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-slate-500 font-semibold">{{ __('Σύνολο πιστοποιήσεων') }}</p>
                            <p class="text-3xl font-bold text-slate-900 tabular-nums tracking-tight leading-none mt-2">
                                {{ number_format($stats['certifications']) }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-2 leading-snug">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-violet-50 text-violet-700 font-semibold">
                                    <i class="fas fa-layer-group text-[9px]"></i>{{ $certPerClient }}
                                </span>
                                {{ __('πιστοποιήσεις κατά μέσο όρο ανά πελάτη') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-md transition-all overflow-hidden group">
                    <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-sky-500/10 to-indigo-600/10 blur-xl group-hover:scale-110 transition-transform"></div>
                    <div class="relative flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 flex items-center justify-center shadow-md flex-shrink-0">
                            <i class="fas fa-file-pdf text-white text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-slate-500 font-semibold">{{ __('PDF παραγμένα') }}</p>
                            <p class="text-3xl font-bold text-slate-900 tabular-nums tracking-tight leading-none mt-2">
                                {{ number_format($stats['pdfs_generated']) }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-2 leading-snug">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-sky-50 text-sky-700 font-semibold">
                                    <i class="fas fa-check text-[9px]"></i>{{ $pdfCoverage }}%
                                </span>
                                {{ __('κάλυψη επί των πιστοποιήσεων') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-md transition-all overflow-hidden group">
                    <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-emerald-500/10 to-teal-600/10 blur-xl group-hover:scale-110 transition-transform"></div>
                    <div class="relative flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md flex-shrink-0">
                            <i class="fas fa-paper-plane text-white text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-slate-500 font-semibold">{{ __('Emails απεσταλμένα') }}</p>
                            <p class="text-3xl font-bold text-slate-900 tabular-nums tracking-tight leading-none mt-2">
                                {{ number_format($emailsTotal) }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-2 leading-snug">
                                {{ __('Συνολικά emails πιστοποιητικών προς τους πελάτες') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if($sortedCats->isNotEmpty() && $sliceTotal > 0)
                <div class="bg-white rounded-2xl border border-slate-200 hover:border-slate-300 transition-colors overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center shadow-md">
                                <i class="fas fa-chart-pie text-white"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">Κατανομη ανα κατηγορια</h2>
                                <p class="text-[11px] text-slate-500 mt-0.5">Ποσοι πελατες ανηκουν σε καθε κατηγορια πιστοποιησης</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] text-slate-500 font-semibold">Καλύτερη κατηγορία</p>
                            <p class="text-sm font-bold text-slate-900 mt-0.5">
                                {{ $sortedCats->first()->name }}
                                <span class="text-slate-400 font-normal">·</span>
                                <span class="text-violet-600">{{ round(($sortedCats->first()->clients_count / $sliceTotal) * 100) }}%</span>
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-8">
                        <div class="flex flex-col lg:flex-row items-center gap-10">
                            <div class="relative flex-shrink-0">
                                <svg viewBox="0 0 100 100" class="w-72 h-72 drop-shadow-lg">
                                    @foreach($slices as $slice)
                                        <path d="{{ $slice['path'] }}" fill="{{ $slice['hex'] }}" stroke="white" stroke-width="0.6"
                                              class="hover:opacity-80 transition-opacity" />
                                    @endforeach
                                    <circle cx="50" cy="50" r="26" fill="white" />
                                    <circle cx="50" cy="50" r="26" fill="none" stroke="#f1f5f9" stroke-width="0.5" />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <span class="text-[11px] text-slate-500 font-semibold">Σύνολο</span>
                                    <span class="text-4xl font-bold text-slate-900 tabular-nums leading-none mt-1">{{ number_format($sliceTotal) }}</span>
                                    <span class="text-[10px] text-slate-400 mt-1.5">πελατες</span>
                                </div>
                            </div>

                            <ul class="flex-1 w-full space-y-2 min-w-0">
                                @foreach($sortedCats as $cat)
                                    @php
                                        $p = $palette[$loop->index % count($palette)];
                                        $pct = $sliceTotal > 0 ? ($cat->clients_count / $sliceTotal) * 100 : 0;
                                    @endphp
                                    <li>
                                        <a href="{{ route('clients.index', ['categoryFilter' => $cat->id]) }}" wire:navigate
                                           class="flex items-center gap-3 rounded-lg px-3 -mx-3 py-2 hover:bg-slate-50 transition group">
                                            <span class="w-3.5 h-3.5 rounded-md {{ $p['dot'] }} flex-shrink-0 shadow-sm"></span>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-baseline justify-between gap-3 mb-1">
                                                    <span class="text-sm font-semibold text-slate-800 truncate group-hover:text-slate-900">{{ $cat->name }}</span>
                                                    <span class="text-sm tabular-nums whitespace-nowrap">
                                                        <span class="font-bold text-slate-900">{{ number_format($cat->clients_count) }}</span>
                                                        <span class="text-slate-500 font-normal text-xs">πελάτες</span>
                                                        <span class="mx-1.5 text-slate-300">·</span>
                                                        <span class="font-semibold" style="color: {{ $p['hex'] ?? '#64748b' }}">{{ round($pct) }}%</span>
                                                        <span class="text-slate-400 font-normal text-xs">του συνόλου</span>
                                                    </span>
                                                </div>
                                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-500"
                                                         style="width: {{ max($pct, $cat->clients_count > 0 ? 2 : 0) }}%; background-color: {{ $p['hex'] }}"></div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

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
