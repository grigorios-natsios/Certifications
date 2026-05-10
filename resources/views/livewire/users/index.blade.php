<div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">{{ __('Χρήστες') }}</h1>
                <p class="page-subtitle">{{ __('Διαχειριστές & χρήστες του οργανισμού') }}</p>
            </div>
            <div class="toolbar">
                <a href="{{ route('users.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-600 hover:to-brand-500 text-white text-sm font-semibold shadow-brand transition-all">
                    <i class="fas fa-user-plus text-xs" aria-hidden="true"></i>
                    {{ __('Νέος χρήστης') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @php
                $activePct = $stats['total'] > 0 ? round($stats['active'] / $stats['total'] * 100) : 0;
                $inactive  = max($stats['total'] - $stats['active'], 0);
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="relative bg-white rounded-2xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-md transition-all overflow-hidden group">
                    <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-rose-500/10 to-brand-600/10 blur-xl group-hover:scale-110 transition-transform"></div>
                    <div class="relative flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-brand-600 flex items-center justify-center shadow-md flex-shrink-0">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-slate-500 font-semibold">{{ __('Σύνολο χρηστών') }}</p>
                            <p class="text-3xl font-bold text-slate-900 tabular-nums tracking-tight leading-none mt-2">
                                {{ number_format($stats['total']) }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-2 leading-snug">
                                {{ __('Όλοι οι χρήστες του οργανισμού') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-md transition-all overflow-hidden group">
                    <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-emerald-500/10 to-teal-600/10 blur-xl group-hover:scale-110 transition-transform"></div>
                    <div class="relative flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md flex-shrink-0">
                            <i class="fas fa-user-check text-white text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-slate-500 font-semibold">{{ __('Ενεργοί') }}</p>
                            <p class="text-3xl font-bold text-slate-900 tabular-nums tracking-tight leading-none mt-2">
                                {{ number_format($stats['active']) }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-2 leading-snug">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold">
                                    <i class="fas fa-check text-[9px]"></i>{{ $activePct }}%
                                </span>
                                {{ __('επί του συνόλου') }}
                                @if($inactive > 0)
                                    <span class="text-slate-300">·</span>
                                    <span class="text-slate-500">{{ $inactive }} {{ __('ανενεργοί') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-md transition-all overflow-hidden group">
                    <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-violet-500/10 to-fuchsia-600/10 blur-xl group-hover:scale-110 transition-transform"></div>
                    <div class="relative flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center shadow-md flex-shrink-0">
                            <i class="fas fa-user-plus text-white text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-slate-500 font-semibold">{{ __('Φετινός μήνας') }}</p>
                            <p class="text-3xl font-bold text-slate-900 tabular-nums tracking-tight leading-none mt-2">
                                {{ number_format($stats['this_month']) }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-2 leading-snug">
                                {{ __('Νέοι χρήστες αυτόν τον μήνα') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 hover:border-slate-300 transition-colors overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-brand-600 flex items-center justify-center shadow-md">
                            <i class="fas fa-user-shield text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 tracking-tight">{{ __('Λίστα χρηστών') }}</h2>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $users->total() }} {{ __('εγγραφές') }}</p>
                        </div>
                    </div>
                    <div class="relative w-full sm:w-72">
                        <i class="fas fa-magnifying-glass input-icon"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Αναζήτηση χρήστη ή email') }}" class="input input-with-icon">
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
                                <th>{{ __('2FA') }}</th>
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
                                            <span class="w-9 h-9 rounded-full flex items-center justify-center font-semibold text-white text-sm bg-gradient-to-br from-rose-500 to-brand-600 shadow-sm flex-shrink-0">
                                                {{ mb_strtoupper(mb_substr(trim($user->name) ?: '?', 0, 1)) }}
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
                                        @php($canToggle = auth()->user()->role === 'admin' && $user->id !== auth()->id() && $user->id !== 1)
                                        <button type="button"
                                                @if($canToggle) wire:click="toggleActive({{ $user->id }})" @else disabled @endif
                                                role="switch"
                                                aria-checked="{{ $user->is_active ? 'true' : 'false' }}"
                                                title="{{ $canToggle ? ($user->is_active ? 'Απενεργοποίηση' : 'Ενεργοποίηση') : ($user->is_active ? 'Ενεργός' : 'Ανενεργός') }}"
                                                class="relative inline-flex items-center align-middle h-5 w-9 rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 {{ $user->is_active ? 'bg-emerald-500 focus-visible:ring-emerald-500' : 'bg-slate-300 focus-visible:ring-slate-400' }} {{ $canToggle ? '' : 'opacity-60 cursor-not-allowed' }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $user->is_active ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                                        </button>
                                    </td>
                                    <td>
                                        @if($user->hasTwoFactorEnabled())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[11px] font-semibold"
                                                  title="{{ __('Ενεργό · ενεργοποιήθηκε ' . $user->two_factor_confirmed_at?->format('d/m/Y')) }}">
                                                <i class="fas fa-shield-halved text-[10px]"></i>{{ __('Ενεργό') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[11px] font-medium"
                                                  title="{{ __('Χωρίς δευτεροβάθμια ταυτοποίηση') }}">
                                                <i class="fas fa-shield text-[10px]"></i>{{ __('Ανενεργό') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-slate-500 whitespace-nowrap text-xs">{{ $user->created_at?->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap !pr-0">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('users.edit', $user->id) }}" wire:navigate class="btn-icon" title="Επεξεργασία">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <button type="button" wire:click="confirmDelete({{ $user->id }})"
                                                    @if($user->id === auth()->id() || $user->id === 1) disabled @endif
                                                    class="btn-icon-danger"
                                                    title="{{ $user->id === 1 ? 'Ο Super Admin δεν διαγράφεται' : 'Διαγραφή' }}">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
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

                <div class="px-5 py-3 border-t border-slate-100">
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
