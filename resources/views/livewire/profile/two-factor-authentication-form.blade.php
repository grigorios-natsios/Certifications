<?php

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $showingSetup = false;
    public ?string $pendingSecret = null;
    public ?string $pendingOtpUrl = null;
    public string $enableCode = '';

    public bool $showingDisable = false;
    public string $disablePassword = '';

    public function startSetup(TwoFactorAuthService $svc): void
    {
        $user = Auth::user();
        if ($user->hasTwoFactorEnabled()) {
            return;
        }

        $this->pendingSecret = $svc->generateSecret();
        $this->pendingOtpUrl = $svc->otpAuthUrl($user, $this->pendingSecret);
        $this->enableCode    = '';
        $this->showingSetup  = true;
    }

    public function cancelSetup(): void
    {
        $this->reset(['showingSetup', 'pendingSecret', 'pendingOtpUrl', 'enableCode']);
        $this->resetErrorBag();
    }

    public function confirmEnable(TwoFactorAuthService $svc): void
    {
        $this->validate([
            'enableCode' => ['required', 'string'],
        ]);

        if (! $this->pendingSecret || ! $svc->verify($this->pendingSecret, $this->enableCode)) {
            throw ValidationException::withMessages([
                'enableCode' => __('Ο κωδικός δεν είναι σωστός. Δοκίμασε ξανά.'),
            ]);
        }

        $user = Auth::user();
        $user->forceFill([
            'two_factor_secret'       => $this->pendingSecret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->cancelSetup();
        $this->dispatch('two-factor-enabled');
    }

    public function openDisable(): void
    {
        $this->showingDisable = true;
        $this->disablePassword = '';
        $this->resetErrorBag();
    }

    public function cancelDisable(): void
    {
        $this->showingDisable = false;
        $this->disablePassword = '';
        $this->resetErrorBag();
    }

    public function confirmDisable(): void
    {
        $this->validate([
            'disablePassword' => ['required', 'string'],
        ]);

        if (! Hash::check($this->disablePassword, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'disablePassword' => __('Λάθος κωδικός.'),
            ]);
        }

        Auth::user()->forceFill([
            'two_factor_secret'       => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->cancelDisable();
        $this->dispatch('two-factor-disabled');
    }

    public function with(): array
    {
        return [
            'enabled' => Auth::user()->hasTwoFactorEnabled(),
        ];
    }
}; ?>

<section>
    <header>
        <h2 class="text-base font-medium text-slate-700">
            {{ __('Επαλήθευση δύο βημάτων (2FA)') }}
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            {{ __('Πρόσθεσε ένα επιπλέον επίπεδο ασφάλειας στον λογαριασμό σου με Google Authenticator ή άλλη εφαρμογή TOTP.') }}
        </p>
    </header>

    @if ($enabled && ! $showingDisable)
        <div class="mt-6 flex items-center gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200">
            <i class="fas fa-shield-halved text-emerald-600"></i>
            <div class="flex-1">
                <p class="text-sm font-medium text-emerald-800">{{ __('Η επαλήθευση δύο βημάτων είναι ενεργή.') }}</p>
                <p class="text-xs text-emerald-700 mt-0.5">{{ __('Θα σου ζητείται ένας 6ψήφιος κωδικός σε κάθε σύνδεση.') }}</p>
            </div>
            <button type="button" wire:click="openDisable" class="text-sm font-medium text-rose-600 hover:text-rose-700">
                {{ __('Απενεργοποίηση') }}
            </button>
        </div>

        <x-action-message class="mt-3" on="two-factor-enabled">
            {{ __('Ενεργοποιήθηκε.') }}
        </x-action-message>
    @endif

    @if ($enabled && $showingDisable)
        <div class="mt-6 p-4 rounded-lg bg-rose-50 border border-rose-200 space-y-4">
            <p class="text-sm text-rose-800">
                {{ __('Επιβεβαίωσε τον κωδικό σου για να απενεργοποιήσεις το 2FA.') }}
            </p>
            <div>
                <x-input-label for="disable_password" :value="__('Κωδικός')" />
                <x-text-input
                    wire:model="disablePassword"
                    id="disable_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                />
                <x-input-error :messages="$errors->get('disablePassword')" class="mt-2" />
            </div>
            <div class="flex items-center gap-3">
                <x-danger-button wire:click="confirmDisable">{{ __('Απενεργοποίηση') }}</x-danger-button>
                <x-secondary-button wire:click="cancelDisable">{{ __('Άκυρο') }}</x-secondary-button>
            </div>
        </div>
    @endif

    @if (! $enabled && ! $showingSetup)
        <div class="mt-6">
            <x-primary-button wire:click="startSetup">
                <i class="fas fa-shield-halved me-2"></i>
                {{ __('Ενεργοποίηση 2FA') }}
            </x-primary-button>
            <x-action-message class="ms-3" on="two-factor-disabled">
                {{ __('Απενεργοποιήθηκε.') }}
            </x-action-message>
        </div>
    @endif

    @if (! $enabled && $showingSetup)
        <div class="mt-6 p-4 sm:p-6 rounded-lg bg-slate-50 border border-slate-200 space-y-5">
            <div>
                <p class="text-sm text-slate-700">
                    <strong>{{ __('Βήμα 1.') }}</strong>
                    {{ __('Άνοιξε την εφαρμογή Google Authenticator (ή παρόμοια) και σκάναρε το παρακάτω QR code.') }}
                </p>
                <div class="mt-3 flex flex-col sm:flex-row items-start gap-4">
                    <div class="bg-white p-3 rounded-lg border border-slate-200">
                        <img
                            src="{{ route('qr.png', ['data' => $pendingOtpUrl]) }}"
                            alt="2FA QR code"
                            class="w-44 h-44"
                        />
                    </div>
                    <div class="text-xs text-slate-600 space-y-2">
                        <p>{{ __('Αν δεν μπορείς να σκανάρεις, εισήγαγε χειροκίνητα αυτό το κλειδί στην εφαρμογή:') }}</p>
                        <code class="block bg-white p-2 rounded border border-slate-200 font-mono text-slate-800 break-all">{{ $pendingSecret }}</code>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-sm text-slate-700 mb-2">
                    <strong>{{ __('Βήμα 2.') }}</strong>
                    {{ __('Εισήγαγε τον 6ψήφιο κωδικό που εμφανίζει η εφαρμογή για επιβεβαίωση.') }}
                </p>
                <x-input-label for="enable_code" :value="__('Κωδικός επαλήθευσης')" />
                <x-text-input
                    wire:model="enableCode"
                    id="enable_code"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    autocomplete="one-time-code"
                    class="mt-1 block w-40 tracking-widest text-center font-mono"
                />
                <x-input-error :messages="$errors->get('enableCode')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-primary-button wire:click="confirmEnable">{{ __('Ενεργοποίηση') }}</x-primary-button>
                <x-secondary-button wire:click="cancelSetup">{{ __('Άκυρο') }}</x-secondary-button>
            </div>
        </div>
    @endif
</section>
