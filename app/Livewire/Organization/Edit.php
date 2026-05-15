<?php

namespace App\Livewire\Organization;

use App\Enums\UserRole;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Επεξεργασία Οργανισμού')]
class Edit extends Component
{
    public Organization $organization;

    public string $name = '';
    public string $address = '';
    public array $phones = [];
    public string $email = '';
    public string $hours = '';
    public string $website_url = '';
    public string $facebook_url = '';
    public string $instagram_url = '';
    public string $youtube_url = '';

    public function mount(): void
    {
        abort_if(Auth::user()->role !== UserRole::ADMIN, 403);

        $this->organization = Organization::findOrFail(Auth::user()->organization_id);

        $this->name          = (string) ($this->organization->name ?? '');
        $this->address       = (string) ($this->organization->address ?? '');
        $this->phones        = (array)  ($this->organization->phones ?? []);
        $this->email         = (string) ($this->organization->email ?? '');
        $this->hours         = (string) ($this->organization->hours ?? '');
        $this->website_url   = (string) ($this->organization->website_url ?? '');
        $this->facebook_url  = (string) ($this->organization->facebook_url ?? '');
        $this->instagram_url = (string) ($this->organization->instagram_url ?? '');
        $this->youtube_url   = (string) ($this->organization->youtube_url ?? '');

        if (empty($this->phones)) {
            $this->phones = [''];
        }
    }

    protected function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'address'       => 'nullable|string|max:255',
            'phones'        => 'array',
            'phones.*'      => 'nullable|string|max:50',
            'email'         => 'nullable|email|max:255',
            'hours'         => 'nullable|string|max:255',
            'website_url'   => 'nullable|url|max:255',
            'facebook_url'  => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url'   => 'nullable|url|max:255',
        ];
    }

    public function addPhone(): void
    {
        $this->phones[] = '';
    }

    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
        if (empty($this->phones)) {
            $this->phones = [''];
        }
    }

    public function save()
    {
        $this->validate();

        $cleanedPhones = array_values(array_filter(
            array_map(fn ($p) => trim((string) $p), $this->phones),
            fn ($p) => $p !== ''
        ));

        $this->organization->update([
            'name'          => $this->name,
            'address'       => $this->address ?: null,
            'phones'        => $cleanedPhones ?: null,
            'email'         => $this->email ?: null,
            'hours'         => $this->hours ?: null,
            'website_url'   => $this->website_url ?: null,
            'facebook_url'  => $this->facebook_url ?: null,
            'instagram_url' => $this->instagram_url ?: null,
            'youtube_url'   => $this->youtube_url ?: null,
        ]);

        session()->flash('toast', ['type' => 'success', 'message' => 'Ο οργανισμός ενημερώθηκε.']);
        return redirect()->route('organization.edit');
    }

    public function render()
    {
        return view('livewire.organization.edit');
    }
}
