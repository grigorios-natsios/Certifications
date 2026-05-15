<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-2xl text-slate-800 leading-tight">{{ __('Προφίλ') }}</h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="card p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200">
                    <span class="w-10 h-10 rounded-lg bg-brand-600 text-white flex items-center justify-center"><i class="fas fa-user-pen"></i></span>
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('Στοιχεία Προφίλ') }}</h2>
                </div>
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="card p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200">
                    <span class="w-10 h-10 rounded-lg bg-brand-600 text-white flex items-center justify-center"><i class="fas fa-key"></i></span>
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('Αλλαγή Κωδικού') }}</h2>
                </div>
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="card p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200">
                    <span class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center"><i class="fas fa-shield-halved"></i></span>
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('Επαλήθευση Δύο Βημάτων') }}</h2>
                </div>
                <div class="max-w-xl">
                    <livewire:profile.two-factor-authentication-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
