<?php

namespace App\Livewire\Clients;

use App\Jobs\GenerateClientPdfs;
use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCustomField;
use App\Models\ClientCustomValue;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Νέος Πελάτης')]
class Create extends Component
{
    public string $name = '';
    public string $lastname = '';
    public string $email = '';
    public string $urlSlug = '';
    public ?string $externalId = null;
    public array $selectedCategories = [];
    public array $customValues = [];

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

    public function save(QrCodeService $qrService)
    {
        $this->validate();

        $client = Client::create([
            'name'           => $this->name,
            'lastname'       => $this->lastname ?: null,
            'email'          => $this->email ?: null,
            'url_slug'       => $this->urlSlug ?: null,
            'external_id'    => $this->externalId ?: null,
            'organization_id'=> Auth::user()->organization_id,
        ]);

        $client->certificateCategories()->sync($this->selectedCategories);

        // Custom values are scoped per category (per certificate). Only persist
        // values that belong to a category the client is actually attached to.
        $now = now();
        $rows = [];
        foreach ($this->selectedCategories as $catId) {
            $values = $this->customValues[$catId] ?? [];
            foreach ($values as $fieldId => $value) {
                if ($value === null || $value === '') continue;
                $rows[] = [
                    'client_id'               => $client->id,
                    'custom_field_id'         => $fieldId,
                    'certificate_category_id' => $catId,
                    'value'                   => $value,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }
        }
        if ($rows) {
            ClientCustomValue::insert($rows);
        }

        $qrService->ensureAllFor($client->fresh('certificateCategories'));

        GenerateClientPdfs::dispatch($client->id, invalidateFirst: false);

        session()->flash('toast', ['type' => 'success', 'message' => 'Ο πελάτης δημιουργήθηκε.']);
        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.create', [
            'categories'   => CertificateCategory::where('organization_id', Auth::user()->organization_id)
                ->orderBy('name')->get(),
            'customFields' => ClientCustomField::with('categories:id')
                ->where('organization_id', Auth::user()->organization_id)
                ->orderBy('name')->get(),
        ]);
    }
}
