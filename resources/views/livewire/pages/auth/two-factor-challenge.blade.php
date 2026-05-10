<?php

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $code = '';

    public function mount(): void
    {
        if (! Session::has('two_factor:user_id')) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function verify(TwoFactorAuthService $svc): void
    {
        $this->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = Session::get('two_factor:user_id');
        $user   = $userId ? User::find($userId) : null;

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            Session::forget(['two_factor:user_id', 'two_factor:remember']);
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $key = 'two-factor|'.$user->id.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        if (! $svc->verify($user->two_factor_secret, $this->code)) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages([
                'code' => __('Λάθος κωδικός. Δοκίμασε ξανά.'),
            ]);
        }

        RateLimiter::clear($key);
        $throttleKey = Str::transliterate(Str::lower($user->email).'|'.request()->ip());
        RateLimiter::clear($throttleKey);

        $remember = (bool) Session::pull('two_factor:remember', false);
        Session::forget('two_factor:user_id');

        Auth::login($user, $remember);
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function cancel(): void
    {
        Session::forget(['two_factor:user_id', 'two_factor:remember']);
        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Εισήγαγε τον 6ψήφιο κωδικό από την εφαρμογή Google Authenticator για να ολοκληρώσεις τη σύνδεση.') }}
    </div>

    <form wire:submit="verify">
        <div>
            <x-input-label for="code" :value="__('Κωδικός επαλήθευσης')" />
            <x-text-input
                wire:model="code"
                id="code"
                type="text"
                inputmode="numeric"
                maxlength="6"
                autocomplete="one-time-code"
                class="block mt-1 w-full tracking-widest text-center font-mono"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <button type="button" wire:click="cancel" class="underline text-sm text-gray-600 hover:text-gray-900">
                {{ __('Άκυρο') }}
            </button>

            <x-primary-button class="ms-3">
                {{ __('Επαλήθευση') }}
            </x-primary-button>
        </div>
    </form>
</div>
