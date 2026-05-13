<?php

namespace App\Livewire\Clients;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCustomField;
use App\Models\ClientCustomValue;
use App\Services\CertificatePdfStore;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Επεξεργασία Πελάτη')]
class Edit extends Component
{
    public Client $client;

    public string $name = '';
    public string $lastname = '';
    public string $email = '';
    public string $urlSlug = '';
    public ?string $externalId = null;
    public array $selectedCategories = [];
    public array $customValues = [];

    public function mount(Client $client): void
    {
        abort_if($client->organization_id !== Auth::user()->organization_id, 403);

        $client->load('certificateCategories', 'customValues');
        $this->client     = $client;
        $this->name       = $client->name ?? '';
        $this->lastname   = $client->lastname ?? '';
        $this->email      = $client->email ?? '';
        $this->urlSlug    = $client->url_slug ?? '';
        $this->externalId = $client->external_id;
        $this->selectedCategories = $client->certificateCategories->pluck('id')->toArray();

        // customValues is keyed [categoryId][fieldId] => value. Legacy rows with
        // no category (NULL) are placed under key 0 and stay invisible in the
        // form, but are preserved on save.
        $this->customValues = [];
        foreach ($client->customValues as $cv) {
            $catKey = (int) ($cv->certificate_category_id ?? 0);
            $this->customValues[$catKey][$cv->custom_field_id] = $cv->value;
        }
    }

    protected function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'lastname'  => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:255',
            'urlSlug'   => 'nullable|string|max:255',
            'externalId'=> 'nullable|string|max:255',
            'selectedCategories'   => 'array',
            'selectedCategories.*' => [
                Rule::exists('certificate_categories', 'id')
                    ->where('organization_id', Auth::user()->organization_id),
            ],
        ];
    }

    public function save(QrCodeService $qrService, CertificatePdfStore $pdfStore)
    {
        $this->validate();

        $this->client->update([
            'name'        => $this->name,
            'lastname'    => $this->lastname ?: null,
            'email'       => $this->email ?: null,
            'url_slug'    => $this->urlSlug ?: null,
            'external_id' => $this->externalId ?: null,
        ]);

        $this->client->certificateCategories()->sync($this->selectedCategories);

        // Persist values per attached category. Empty fields delete the row so
        // the certificate doesn't keep stale data. Bulk-write to avoid N*M
        // queries on forms with several categories × fields.
        $now = now();
        $upserts = [];
        $emptyByCategory = [];

        foreach ($this->selectedCategories as $catId) {
            $values = $this->customValues[$catId] ?? [];
            foreach ($values as $fieldId => $value) {
                if ($value === null || $value === '') {
                    $emptyByCategory[$catId][] = $fieldId;
                    continue;
                }
                $upserts[] = [
                    'client_id'               => $this->client->id,
                    'custom_field_id'         => $fieldId,
                    'certificate_category_id' => $catId,
                    'value'                   => $value,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }
        }

        if ($upserts) {
            ClientCustomValue::upsert(
                $upserts,
                ['client_id', 'custom_field_id', 'certificate_category_id'],
                ['value', 'updated_at']
            );
        }

        foreach ($emptyByCategory as $catId => $fieldIds) {
            ClientCustomValue::where('client_id', $this->client->id)
                ->where('certificate_category_id', $catId)
                ->whereIn('custom_field_id', $fieldIds)
                ->delete();
        }

        $fresh = $this->client->fresh('certificateCategories');
        $qrService->ensureAllFor($fresh);

        // Wipe stale disk artefacts (cached + bulk, including detached
        // categories) and regenerate. Bulk PDFs come back only for combos
        // that already had a tracked bulk filename.
        $pdfStore->refreshAllForClient($fresh);

        session()->flash('toast', ['type' => 'success', 'message' => 'Ο πελάτης ενημερώθηκε.']);
        return redirect()->route('clients.index');
    }

    public function delete(CertificatePdfStore $pdfStore)
    {
        $pdfStore->pruneAllForClient($this->client);
        $this->client->delete();
        session()->flash('toast', ['type' => 'success', 'message' => 'Ο πελάτης διαγράφηκε.']);
        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.edit', [
            'categories'   => CertificateCategory::where('organization_id', Auth::user()->organization_id)
                ->orderBy('name')->get(),
            'customFields' => ClientCustomField::with('categories:id')
                ->where('organization_id', Auth::user()->organization_id)
                ->orderBy('name')->get(),
        ]);
    }
}
