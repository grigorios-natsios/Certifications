<?php

use App\Livewire\Actions\Logout;
use App\Models\Organization;
use App\Services\OrganizationDeletionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $confirmName = '';

    public function deleteOrganization(Logout $logout, OrganizationDeletionService $deleter): void
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            $this->dispatch('toast', message: 'Μόνο ο admin μπορεί να διαγράψει τον οργανισμό.', type: 'warning');
            return;
        }

        if ($user->id === 1) {
            $this->dispatch('toast', message: 'Ο οργανισμός του Super Admin δεν διαγράφεται.', type: 'warning');
            return;
        }

        $organization = Organization::findOrFail($user->organization_id);

        $this->validate([
            'confirmName' => ['required', 'string'],
        ]);

        if (trim($this->confirmName) !== $organization->name) {
            $this->addError('confirmName', 'Το όνομα δεν ταιριάζει με τον οργανισμό.');
            return;
        }

        $deleter->delete($organization);

        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header>
        <p class="text-sm text-slate-600">
            {{ __('Διαγράφει οριστικά τον οργανισμό σας μαζί με όλους τους χρήστες, πελάτες, πιστοποιητικά, QR codes, αρχεία PDF, προσαρμοσμένα πεδία και καταγραφές. Η ενέργεια δεν αναιρείται.') }}
        </p>
    </header>

    @php($organization = \App\Models\Organization::find(auth()->user()->organization_id))

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-organization-deletion')"
    >{{ __('Διαγραφή Οργανισμού') }}</x-danger-button>

    <x-modal name="confirm-organization-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteOrganization" class="p-6">
            <h2 class="text-lg font-medium text-slate-900">
                {{ __('Είστε σίγουρος ότι θέλετε να διαγράψετε τον οργανισμό;') }}
            </h2>

            <p class="mt-2 text-sm text-slate-600">
                {{ __('Όλα τα δεδομένα του οργανισμού — πελάτες, πιστοποιητικά, QR codes, αρχεία PDF, χρήστες, προσαρμοσμένα πεδία και καταγραφές — θα διαγραφούν οριστικά. Θα αποσυνδεθείτε αυτόματα.') }}
            </p>

            <p class="mt-4 text-sm text-slate-700">
                {{ __('Για επιβεβαίωση πληκτρολογήστε το όνομα του οργανισμού:') }}
                <span class="font-semibold text-slate-900">{{ $organization?->name }}</span>
            </p>

            <div class="mt-3">
                <x-input-label for="confirmName" :value="__('Όνομα οργανισμού')" class="sr-only" />
                <x-text-input
                    wire:model="confirmName"
                    id="confirmName"
                    name="confirmName"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="{{ $organization?->name }}"
                    autocomplete="off"
                />
                <x-input-error :messages="$errors->get('confirmName')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Άκυρο') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Διαγραφή Οργανισμού') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
