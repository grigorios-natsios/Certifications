{{-- Shared form fields for Users Create + Edit --}}

<div class="form-section">
    <div class="form-section-head">
        <h2 class="section-title">{{ __('Στοιχεία χρήστη') }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">{{ __('Πρόσβαση στο διαχειριστικό περιβάλλον') }}</p>
    </div>
    <div class="form-section-body grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
        <div class="sm:col-span-2">
            <label class="label-plain">{{ __('Όνομα') }} <span class="text-rose-500">*</span></label>
            <input type="text" wire:model="name" class="input">
            @error('name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <label class="label-plain">{{ __('Email') }} <span class="text-rose-500">*</span></label>
            <input type="email" wire:model="email" class="input">
            @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-head">
        <h2 class="section-title">{{ __('Κωδικός πρόσβασης') }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">
            @isset($user)
                {{ __('Άφησε τα πεδία κενά για να μη αλλάξει.') }}
            @else
                {{ __('Τουλάχιστον 8 χαρακτήρες.') }}
            @endisset
        </p>
    </div>
    <div class="form-section-body grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
        <div>
            <label class="label-plain">
                {{ __('Κωδικός') }}
                @empty($user) <span class="text-rose-500">*</span> @endempty
            </label>
            <input type="password" wire:model="password" class="input" autocomplete="new-password">
            @error('password') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label-plain">{{ __('Επιβεβαίωση κωδικού') }}</label>
            <input type="password" wire:model="password_confirmation" class="input" autocomplete="new-password">
        </div>
    </div>
</div>
