<div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Καταγραφές') }}</h1>
                <p class="page-subtitle">{{ __('Λήψεις PDF και αποστολές email — έτος') }} {{ $currentYear }}.</p>
            </div>
            <div class="toolbar">
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 text-sm font-medium text-slate-700 transition">
                    <i class="fas fa-arrow-left text-xs"></i>
                    {{ __('Πίνακας') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-slate-300 transition-colors">
                <div class="px-6 py-5 border-b border-slate-100 flex items-start gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-md flex-shrink-0">
                        <i class="fas fa-clock-rotate-left text-white text-base"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">{{ __('Καταγραφές') }}</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ __('Λήψεις PDF και αποστολές email — έτος') }} {{ $currentYear }}.
                        </p>
                    </div>
                    @if($oldCount > 0)
                        <button type="button" wire:click="confirmCleanup"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 bg-white hover:bg-rose-50 text-xs font-medium text-rose-700 transition flex-shrink-0">
                            <i class="fas fa-broom text-[11px]"></i>
                            {{ __('Καθαρισμός παλιών') }}
                            <span class="ml-1 px-1.5 py-0.5 rounded-md bg-rose-100 text-rose-700 text-[10px] tabular-nums">{{ number_format($oldCount) }}</span>
                        </button>
                    @endif
                </div>

                {{-- Stats summary --}}
                <div class="px-6 py-4 grid grid-cols-2 sm:grid-cols-4 gap-3 border-b border-slate-100">
                    @php
                        $cards = [
                            ['action' => 'pdf_download',   'label' => 'Λήψεις PDF',           'icon' => 'fa-file-arrow-down', 'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700'],
                            ['action' => 'email_batch',    'label' => 'Αποστολές email',      'icon' => 'fa-paper-plane',     'bg' => 'bg-violet-50',  'fg' => 'text-violet-700'],
                            ['action' => 'client_import',  'label' => 'Εισαγωγές Excel',      'icon' => 'fa-file-import',     'bg' => 'bg-sky-50',     'fg' => 'text-sky-700'],
                            ['action' => 'client_create',  'label' => 'Νέοι πελάτες',         'icon' => 'fa-user-plus',       'bg' => 'bg-amber-50',   'fg' => 'text-amber-700'],
                        ];
                    @endphp
                    @foreach($cards as $card)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg {{ $card['bg'] }} {{ $card['fg'] }} flex items-center justify-center text-sm flex-shrink-0">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] text-slate-500 font-medium leading-none">{{ $card['label'] }}</p>
                                <p class="text-xl font-bold text-slate-900 tabular-nums tracking-tight leading-tight mt-1">
                                    {{ number_format($totals[$card['action']] ?? 0) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Filters --}}
                <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">{{ __('Αναζήτηση') }}</label>
                        <div class="relative">
                            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" wire:model.live.debounce.400ms="search"
                                   placeholder="{{ __('Όνομα, email, θέμα...') }}"
                                   class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100 transition">
                        </div>
                    </div>

                    <div class="min-w-[180px]">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">{{ __('Τύπος ενέργειας') }}</label>
                        <select wire:model.live="actionFilter"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100 transition">
                            <option value="">{{ __('Όλες') }}</option>
                            @foreach($actions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">{{ __('Από') }}</label>
                        <input type="date" wire:model.live="dateFrom"
                               class="px-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">{{ __('Έως') }}</label>
                        <input type="date" wire:model.live="dateTo"
                               class="px-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100 transition">
                    </div>

                    @if($this->activeFilterCount > 0)
                        <button type="button" wire:click="clearFilters"
                                class="px-3 py-2 text-xs text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition">
                            <i class="fas fa-xmark mr-1"></i>{{ __('Καθαρισμός φίλτρων') }} ({{ $this->activeFilterCount }})
                        </button>
                    @endif
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500">
                                <th class="text-left px-6 py-2.5 font-semibold">{{ __('Ενέργεια') }}</th>
                                <th class="text-left px-4 py-2.5 font-semibold">{{ __('Χρήστης') }}</th>
                                <th class="text-left px-4 py-2.5 font-semibold">{{ __('Θέμα') }}</th>
                                <th class="text-left px-4 py-2.5 font-semibold">{{ __('IP') }}</th>
                                <th class="text-right px-6 py-2.5 font-semibold">{{ __('Ημερομηνία') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($logs as $log)
                                @php
                                    $actionStyle = match($log->action) {
                                        'pdf_download'  => ['icon' => 'fa-file-arrow-down', 'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700'],
                                        'email_batch'   => ['icon' => 'fa-paper-plane',     'bg' => 'bg-violet-50',  'fg' => 'text-violet-700'],
                                        'client_import' => ['icon' => 'fa-file-import',     'bg' => 'bg-sky-50',     'fg' => 'text-sky-700'],
                                        'client_create' => ['icon' => 'fa-user-plus',       'bg' => 'bg-amber-50',   'fg' => 'text-amber-700'],
                                        default         => ['icon' => 'fa-circle-info',    'bg' => 'bg-slate-100',  'fg' => 'text-slate-700'],
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-medium {{ $actionStyle['bg'] }} {{ $actionStyle['fg'] }}">
                                            <i class="fas {{ $actionStyle['icon'] }} text-[10px]"></i>
                                            {{ $log->actionLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $userName  = $log->user?->name;
                                            $userEmail = $log->user?->email ?? data_get($log->meta, 'triggered_by');
                                        @endphp
                                        @if($userName || $userEmail)
                                            <p class="text-sm font-medium text-slate-900 truncate max-w-[220px]">{{ $userName ?: $userEmail }}</p>
                                            @if($userName && $userEmail)
                                                <p class="text-xs text-slate-500 truncate max-w-[220px]">{{ $userEmail }}</p>
                                            @endif
                                        @elseif($log->action === 'pdf_download')
                                            <p class="text-sm text-slate-500 italic">{{ __('Δημόσια λήψη') }}</p>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif

                                        @if($log->client_name && in_array($log->action, ['pdf_download', 'client_create'], true))
                                            <p class="text-[11px] text-slate-500 mt-1 truncate max-w-[220px]" title="{{ $log->client_name }}">
                                                <i class="fas fa-id-card text-[9px] text-slate-400 mr-1"></i>
                                                {{ __('πελάτης') }}: <span class="text-slate-700">{{ $log->client_name }}</span>
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($log->action === 'client_import')
                                            @php
                                                $imp = $log->meta ?? [];
                                                $filename = $imp['filename'] ?? $log->subject;
                                                $inserted = (int) ($imp['inserted'] ?? 0);
                                                $updated  = (int) ($imp['updated']  ?? 0);
                                                $skipped  = (int) ($imp['skipped']  ?? 0);
                                            @endphp
                                            <p class="text-sm text-slate-700 truncate max-w-[260px]" title="{{ $filename }}">
                                                <i class="fas fa-file-excel text-[11px] text-slate-400 mr-1"></i>
                                                {{ $filename }}
                                            </p>
                                            <div class="flex flex-wrap gap-1 mt-1.5">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700">
                                                    +{{ $inserted }} {{ __('νέοι') }}
                                                </span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-sky-50 text-sky-700">
                                                    ↻ {{ $updated }} {{ __('ενημερώθηκαν') }}
                                                </span>
                                                @if($skipped > 0)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                                        – {{ $skipped }} {{ __('παραλήφθηκαν') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-700 truncate block max-w-[260px]">{{ $log->subject ?: '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $ip = data_get($log->meta, 'ip'); @endphp
                                        @if($ip)
                                            <span class="text-xs text-slate-500 font-mono">{{ $ip }}</span>
                                            @if($ip === '::1' || $ip === '127.0.0.1')
                                                <span class="block text-[10px] text-slate-400">{{ __('localhost') }}</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-right tabular-nums">
                                        <span class="text-xs text-slate-500" title="{{ $log->created_at }}">
                                            {{ $log->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center">
                                        <div class="text-slate-400">
                                            <i class="fas fa-clock-rotate-left text-2xl mb-2"></i>
                                            <p class="text-sm">
                                                @if($this->activeFilterCount > 0)
                                                    {{ __('Δεν βρέθηκαν εγγραφές με τα τρέχοντα φίλτρα.') }}
                                                @else
                                                    {{ __('Δεν υπάρχουν ακόμη καταγραφές για φέτος.') }}
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="px-6 py-3 border-t border-slate-100">
                        {{ $logs->onEachSide(1)->links() }}
                    </div>
                @endif

                {{-- Cleanup confirmation modal --}}
                @if($confirmingCleanup)
                    <div class="modal-backdrop" wire:key="cleanup-logs-modal">
                        <div class="modal-panel" @click.stop>
                            <div class="modal-header">
                                <h3 class="section-title text-rose-700">
                                    <i class="fas fa-triangle-exclamation mr-1.5"></i>
                                    {{ __('Καθαρισμός παλαιών logs') }}
                                </h3>
                                <button type="button" wire:click="cancelCleanup" class="text-slate-400 hover:text-slate-600"><i class="fas fa-xmark"></i></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-sm text-slate-700">
                                    {{ __('Σίγουρα θέλεις να διαγράψεις') }}
                                    <strong class="text-rose-700">{{ number_format($oldCount) }}</strong>
                                    {{ __('εγγραφές') }}
                                    {{ __('από έτη πριν το') }}
                                    <strong>{{ $currentYear }}</strong>;
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ __('Η ενέργεια δεν αναιρείται.') }}
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" wire:click="cancelCleanup"
                                        class="px-3 py-1.5 text-sm rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-slate-700">
                                    {{ __('Άκυρο') }}
                                </button>
                                <button type="button" wire:click="cleanupOldLogs"
                                        class="px-3 py-1.5 text-sm rounded-md bg-rose-600 hover:bg-rose-700 text-white font-medium">
                                    <i class="fas fa-trash mr-1"></i>{{ __('Διαγραφή') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
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
