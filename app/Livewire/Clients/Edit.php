<?php

namespace App\Livewire\Clients;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCustomField;
use App\Models\ClientCustomValue;
use App\Services\CertificatePdfStore;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Auth;
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
        $this->customValues = $client->customValues->mapWithKeys(
            fn ($v) => [$v->custom_field_id => $v->value]
        )->toArray();
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
            'selectedCategories.*' => 'exists:certificate_categories,id',
        ];
    }

    public function save(QrCodeService $qrService, CertificatePdfStore $pdfStore)
    {
        $this->validate();

        $this->client->load('certificateCategories');
        $previousCategories = $this->client->certificateCategories;

        $this->client->update([
            'name'        => $this->name,
            'lastname'    => $this->lastname ?: null,
            'email'       => $this->email ?: null,
            'url_slug'    => $this->urlSlug ?: null,
            'external_id' => $this->externalId ?: null,
        ]);

        $this->client->certificateCategories()->sync($this->selectedCategories);

        foreach ($this->customValues as $fieldId => $value) {
            if ($value === null || $value === '') continue;
            ClientCustomValue::updateOrCreate(
                ['client_id' => $this->client->id, 'custom_field_id' => $fieldId],
                ['value' => $value]
            );
        }

        $fresh = $this->client->fresh('certificateCategories');
        $qrService->ensureAllFor($fresh);

        // Drop cached PDFs for both the previous and current category sets — manual
        // edits may have changed any rendered field, so the next view regenerates.
        foreach ($previousCategories as $cat) {
            $pdfStore->invalidate($this->client, $cat);
        }
        $pdfStore->invalidate($fresh);

        session()->flash('toast', ['type' => 'success', 'message' => 'Ο πελάτης ενημερώθηκε.']);
        return redirect()->route('clients.index');
    }

    public function delete()
    {
        $this->client->delete();
        session()->flash('toast', ['type' => 'success', 'message' => 'Ο πελάτης διαγράφηκε.']);
        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.edit', [
            'categories'   => CertificateCategory::orderBy('name')->get(),
            'customFields' => ClientCustomField::where('organization_id', Auth::user()->organization_id)
                ->orderBy('name')->get(),
        ]);
    }
}
