<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('dashboard') }}" wire:navigate class="btn-icon" title="Πίσω">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div class="min-w-0">
                    <nav class="text-xs text-slate-500 mb-0.5 flex items-center gap-1.5">
                        <span>{{ __('Ρυθμίσεις') }}</span>
                        <span class="text-slate-300">/</span>
                        <span>{{ __('Οργανισμός') }}</span>
                    </nav>
                    <h1 class="page-title truncate">{{ $organization->name }}</h1>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <form id="org-edit-form" wire:submit.prevent="save" class="space-y-6">
                <div class="form-section">
                    <div class="form-section-head">
                        <h2 class="section-title">{{ __('Στοιχεία οργανισμού') }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Βασικές πληροφορίες επικοινωνίας') }}</p>
                    </div>
                    <div class="form-section-body grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div class="sm:col-span-2">
                            <label class="label-plain">{{ __('Όνομα') }} <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" class="input">
                            @error('name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="label-plain">{{ __('Διεύθυνση') }}</label>
                            <input type="text" wire:model="address" class="input" placeholder="π.χ. Λεωφ. Παράδειγμα 1, Αθήνα">
                            @error('address') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label-plain">{{ __('Email') }}</label>
                            <input type="email" wire:model="email" class="input" placeholder="info@example.com">
                            @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label-plain">{{ __('Ωράριο') }}</label>
                            <input type="text" wire:model="hours" class="input" placeholder="π.χ. Δευ-Παρ 09:00-17:00">
                            @error('hours') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-head">
                        <h2 class="section-title">{{ __('Τηλέφωνα') }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Μπορείς να προσθέσεις περισσότερα του ενός') }}</p>
                    </div>
                    <div class="form-section-body space-y-3">
                        @foreach($phones as $index => $phone)
                            <div class="flex items-center gap-2" wire:key="phone-{{ $index }}">
                                <input type="text" wire:model="phones.{{ $index }}" class="input flex-1" placeholder="π.χ. +30 210 1234567">
                                <button type="button" wire:click="removePhone({{ $index }})" class="btn-icon text-rose-600 hover:bg-rose-50" title="{{ __('Αφαίρεση') }}">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                                @error('phones.'.$index) <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                        <button type="button" wire:click="addPhone" class="btn-secondary">
                            <i class="fas fa-plus text-xs mr-1"></i> {{ __('Προσθήκη τηλεφώνου') }}
                        </button>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-head">
                        <h2 class="section-title">{{ __('Σύνδεσμοι') }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Ιστοσελίδα και κοινωνικά δίκτυα') }}</p>
                    </div>
                    <div class="form-section-body grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div class="sm:col-span-2">
                            <label class="label-plain">{{ __('Ιστοσελίδα') }}</label>
                            <input type="url" wire:model="website_url" class="input" placeholder="https://example.com">
                            @error('website_url') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label-plain"><i class="fab fa-facebook text-slate-400 mr-1"></i>{{ __('Facebook') }}</label>
                            <input type="url" wire:model="facebook_url" class="input" placeholder="https://facebook.com/...">
                            @error('facebook_url') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label-plain"><i class="fab fa-instagram text-slate-400 mr-1"></i>{{ __('Instagram') }}</label>
                            <input type="url" wire:model="instagram_url" class="input" placeholder="https://instagram.com/...">
                            @error('instagram_url') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label-plain"><i class="fab fa-youtube text-slate-400 mr-1"></i>{{ __('YouTube') }}</label>
                            <input type="url" wire:model="youtube_url" class="input" placeholder="https://youtube.com/...">
                            @error('youtube_url') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </form>

            @if(auth()->user()->isAdmin())
                <div class="form-section border-rose-200">
                    <div class="form-section-head">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-lg bg-rose-600 text-white flex items-center justify-center"><i class="fas fa-building-circle-xmark"></i></span>
                            <div>
                                <h2 class="section-title text-rose-700">{{ __('Διαγραφή Οργανισμού') }}</h2>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Μη αναστρέψιμη ενέργεια') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <livewire:profile.delete-organization-form />
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="sticky bottom-0 bg-white/95 backdrop-blur border-t border-slate-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-3">
            <a href="{{ route('dashboard') }}" wire:navigate class="btn-secondary">{{ __('Ακύρωση') }}</a>
            <button type="submit" form="org-edit-form" class="btn-primary">
                <span wire:loading.remove wire:target="save">{{ __('Αποθήκευση αλλαγών') }}</span>
                <span wire:loading wire:target="save">{{ __('Αποθήκευση...') }}</span>
            </button>
        </div>
    </div>
</div>
