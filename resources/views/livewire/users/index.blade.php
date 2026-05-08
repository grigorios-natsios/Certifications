<div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Χρήστες') }}</h1>
                <p class="page-subtitle">{{ __('Διαχειριστές & χρήστες του οργανισμού') }}</p>
            </div>
            <a href="{{ route('users.create') }}" wire:navigate class="btn-primary">
                {{ __('Νέος χρήστης') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="stat-tile">
                    <p class="stat-tile-label">{{ __('Σύνολο') }}</p>
                    <p class="stat-tile-value">{{ number_format($stats['total']) }}</p>
                    <p class="stat-tile-meta">{{ __('χρήστες') }}</p>
                </div>
                <div class="stat-tile">
                    <p class="stat-tile-label">{{ __('Επιβεβαιωμένοι') }}</p>
                    <p class="stat-tile-value">{{ number_format($stats['verified']) }}</p>
                    <p class="stat-tile-meta">
                        @if($stats['total'])
                            {{ round($stats['verified'] / $stats['total'] * 100) }}%
                        @else 0% @endif
                    </p>
                </div>
                <div class="stat-tile">
                    <p class="stat-tile-label">{{ __('Φετινός μήνας') }}</p>
                    <p class="stat-tile-value">{{ number_format($stats['this_month']) }}</p>
                    <p class="stat-tile-meta">{{ __('νέοι') }}</p>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-head">
                    <div class="flex items-baseline gap-3">
                        <h2 class="section-title">{{ __('Λίστα χρηστών') }}</h2>
                        <span class="text-xs text-slate-500">{{ $users->total() }} {{ __('εγγραφές') }}</span>
                    </div>
                    <div class="relative">
                        <i class="fas fa-magnifying-glass input-icon"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Αναζήτηση" class="input input-with-icon">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-app">
                        <thead>
                            <tr>
                                <th class="w-14">ID</th>
                                <th>{{ __('Χρήστης') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Κατάσταση') }}</th>
                                <th>{{ __('Ημ/νία') }}</th>
                                <th class="text-right">{{ __('Ενέργειες') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($users as $user)
                                <tr wire:key="user-{{ $user->id }}">
                                    <td class="text-slate-400 font-mono text-xs">{{ $user->id }}</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="avatar w-8 h-8">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                            <div>
                                                <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                                @if($user->id === auth()->id())
                                                    <p class="text-[11px] text-slate-500">{{ __('Εσύ') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-slate-600">
                                        <a href="mailto:{{ $user->email }}" class="hover:text-slate-900">{{ $user->email }}</a>
                                    </td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="status-dot status-dot-success">{{ __('Επιβεβαιωμένος') }}</span>
                                        @else
                                            <span class="status-dot status-dot-warning">{{ __('Εκκρεμεί') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-slate-500 whitespace-nowrap text-xs">{{ $user->created_at?->format('d/m/Y') }}</td>
                                    <td class="text-right whitespace-nowrap">
                                        <a href="{{ route('users.edit', $user->id) }}" wire:navigate class="btn-icon" title="Επεξεργασία">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <button type="button" wire:click="confirmDelete({{ $user->id }})"
                                                @if($user->id === auth()->id()) disabled @endif
                                                class="btn-icon-danger" title="Διαγραφή">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-user-shield text-slate-400"></i></div>
                                            <h3 class="mt-3 text-sm font-medium text-slate-900">{{ __('Δεν υπάρχουν χρήστες') }}</h3>
                                            <p class="text-xs text-slate-500 mt-1">{{ __('Πρόσθεσε νέο χρήστη για πρόσβαση στο σύστημα.') }}</p>
                                            <a href="{{ route('users.create') }}" wire:navigate class="btn-primary mt-4 inline-flex">
                                                {{ __('Νέος χρήστης') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-slate-200">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-confirm-delete-toast :targetId="$confirmingDeleteId"
                            message="Σίγουρα θέλεις να διαγράψεις αυτόν τον χρήστη; Η πρόσβασή του θα ανακληθεί άμεσα." />

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
