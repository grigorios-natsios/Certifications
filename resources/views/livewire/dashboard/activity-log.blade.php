<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-slate-300 transition-colors">
    <div class="px-6 py-5 border-b border-slate-100 flex items-start gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-md flex-shrink-0">
            <i class="fas fa-clock-rotate-left text-white text-base"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-base font-bold text-slate-900 tracking-tight">{{ __('Ιστορικό ενεργειών') }}</h2>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Λήψεις PDF, ανοίγματα σελίδας πιστοποιητικού και αποστολές email — έτος') }} {{ $currentYear }}.
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
    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-3 border-b border-slate-100">
        @php
            $cards = [
                ['action' => 'pdf_download',     'label' => 'Λήψεις PDF',          'icon' => 'fa-file-arrow-down',  'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700'],
                ['action' => 'certificate_view', 'label' => 'Ανοίγματα σελίδας',   'icon' => 'fa-eye',              'bg' => 'bg-sky-50',     'fg' => 'text-sky-700'],
                ['action' => 'email_batch',      'label' => 'Αποστολές email',     'icon' => 'fa-paper-plane',      'bg' => 'bg-violet-50',  'fg' => 'text-violet-700'],
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
                    <th class="text-left px-4 py-2.5 font-semibold">{{ __('Πελάτης') }}</th>
                    <th class="text-left px-4 py-2.5 font-semibold">{{ __('Θέμα') }}</th>
                    <th class="text-left px-4 py-2.5 font-semibold">{{ __('IP') }}</th>
                    <th class="text-right px-6 py-2.5 font-semibold">{{ __('Ημερομηνία') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    @php
                        $actionStyle = match($log->action) {
                            'pdf_download'     => ['icon' => 'fa-file-arrow-down', 'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700'],
                            'certificate_view' => ['icon' => 'fa-eye',             'bg' => 'bg-sky-50',     'fg' => 'text-sky-700'],
                            'email_batch'      => ['icon' => 'fa-paper-plane',     'bg' => 'bg-violet-50',  'fg' => 'text-violet-700'],
                            default            => ['icon' => 'fa-circle-info',    'bg' => 'bg-slate-100',  'fg' => 'text-slate-700'],
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
                            @if($log->client_name || $log->client_email)
                                <p class="text-sm font-medium text-slate-900 truncate max-w-[220px]">{{ $log->client_name ?: '—' }}</p>
                                @if($log->client_email)
                                    <p class="text-xs text-slate-500 truncate max-w-[220px]">{{ $log->client_email }}</p>
                                @endif
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-slate-700 truncate block max-w-[260px]">{{ $log->subject ?: '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-slate-500 font-mono">{{ data_get($log->meta, 'ip', '—') }}</span>
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
