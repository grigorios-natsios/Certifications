<?php

use App\Enums\UserRole;
use App\Livewire\Actions\Logout;
use App\Models\Organization;
use App\Services\OrganizationDeletionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $confirmText = '';

    public function deleteOrganization(Logout $logout, OrganizationDeletionService $deleter)
    {
        $user = Auth::user();

        if (! $user || $user->role !== UserRole::ADMIN) {
            $this->dispatch('toast', message: 'Μόνο ο admin μπορεί να διαγράψει τον οργανισμό.', type: 'warning');
            return;
        }

        if (trim($this->confirmText) !== 'DELETE') {
            $this->dispatch('toast', message: 'Πληκτρολογήστε «DELETE» (κεφαλαία) για επιβεβαίωση.', type: 'warning');
            return;
        }

        $organization = Organization::findOrFail($user->organization_id);

        $deleter->delete($organization);

        $logout();

        return redirect('/');
    }
}; ?>

<section class="space-y-6">
    <header>
        <p class="text-sm text-slate-600">
            {{ __('Διαγράφει οριστικά τον οργανισμό σας μαζί με όλους τους χρήστες, πελάτες, πιστοποιητικά, QR codes, αρχεία PDF, προσαρμοσμένα πεδία και καταγραφές. Η ενέργεια δεν αναιρείται.') }}
        </p>
    </header>

    <div x-data="{
        text: @entangle('confirmText'),
        loading: false,
        async submit() {
            if (this.text.trim() !== 'DELETE' || this.loading) return;
            this.loading = true;
            try {
                await $wire.call('deleteOrganization');
            } catch (e) {
                console.error('deleteOrganization failed', e);
            } finally {
                this.loading = false;
            }
        }
    }">
        <button
            type="button"
            x-on:click="text = ''; $dispatch('open-modal', 'confirm-organization-deletion')"
            class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-500 active:bg-rose-700 border border-transparent rounded-md font-semibold text-sm text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition"
        >{{ __('Διαγραφή οργανισμού') }}</button>

        <x-modal name="confirm-organization-deletion" focusable>
            <div class="p-6">
                <h2 class="text-lg font-medium text-slate-900">
                    {{ __('Είστε σίγουρος ότι θέλετε να διαγράψετε τον οργανισμό;') }}
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    {{ __('Όλα τα δεδομένα του οργανισμού — πελάτες, πιστοποιητικά, QR codes, αρχεία PDF, χρήστες, προσαρμοσμένα πεδία και καταγραφές — θα διαγραφούν οριστικά. Θα αποσυνδεθείτε αυτόματα.') }}
                </p>

                <p class="mt-4 text-sm text-slate-700">
                    {{ __('Για επιβεβαίωση πληκτρολογήστε') }}
                    <span class="font-semibold text-rose-700">DELETE</span>
                    {{ __('με κεφαλαία.') }}
                </p>

                <div class="mt-3">
                    <input
                        x-model="text"
                        x-on:keydown.enter.prevent="submit()"
                        type="text"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500"
                        placeholder="DELETE"
                        autocomplete="off"
                    />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        x-on:click="$dispatch('close')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-md font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition"
                    >
                        {{ __('Άκυρο') }}
                    </button>

                    <button
                        type="button"
                        x-on:click="submit()"
                        x-bind:disabled="text.trim() !== 'DELETE' || loading"
                        class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-500 active:bg-rose-700 border border-transparent rounded-md font-semibold text-sm text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!loading">{{ __('Διαγραφή οργανισμού') }}</span>
                        <span x-show="loading">{{ __('Διαγραφή...') }}</span>
                    </button>
                </div>
            </div>
        </x-modal>
    </div>
</section>
