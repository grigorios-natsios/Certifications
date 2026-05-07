<?php

namespace App\Livewire\Clients;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCustomField;
use App\Models\ClientCustomValue;
use Illuminate\Support\Facades\Auth;
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
            'selectedCategories.*' => 'exists:certificate_categories,id',
        ];
    }

    public function save()
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

        foreach ($this->customValues as $fieldId => $value) {
            if ($value === null || $value === '') continue;
            ClientCustomValue::create([
                'client_id'       => $client->id,
                'custom_field_id' => $fieldId,
                'value'           => $value,
            ]);
        }

        session()->flash('toast', ['type' => 'success', 'message' => 'Ο πελάτης δημιουργήθηκε.']);
        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.create', [
            'categories'   => CertificateCategory::orderBy('name')->get(),
            'customFields' => ClientCustomField::where('organization_id', Auth::user()->organization_id)
                ->orderBy('name')->get(),
        ]);
    }
}
