<?php

namespace App\Livewire\CustomFields;

use App\Models\ClientCustomField;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Προσαρμοσμένα Πεδία')]
class Index extends Component
{
    use WithPagination;

    public const TYPES = [
        'text'     => 'Κείμενο',
        'number'   => 'Αριθμός',
        'date'     => 'Ημερομηνία',
        'checkbox' => 'Checkbox',
    ];

    #[Url(as: 'q')] public string $search = '';

    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $type = 'text';
    public bool $is_required = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:text,number,date,checkbox',
            'is_required' => 'boolean',
        ];
    }

    public function updatedSearch() { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $field = ClientCustomField::where('organization_id', Auth::user()->organization_id)->findOrFail($id);
        $this->editingId   = $field->id;
        $this->name        = $field->name;
        $this->type        = $field->type;
        $this->is_required = (bool) $field->is_required;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $payload = [
            'name'            => $this->name,
            'type'            => $this->type,
            'is_required'     => $this->is_required,
            'organization_id' => Auth::user()->organization_id,
        ];

        if ($this->editingId) {
            ClientCustomField::where('organization_id', Auth::user()->organization_id)
                ->findOrFail($this->editingId)->update($payload);
            $this->dispatch('toast', message: 'Το πεδίο ενημερώθηκε.', type: 'success');
        } else {
            ClientCustomField::create($payload);
            $this->dispatch('toast', message: 'Το πεδίο δημιουργήθηκε.', type: 'success');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        ClientCustomField::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Το πεδίο διαγράφηκε.', type: 'success');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->name        = '';
        $this->type        = 'text';
        $this->is_required = false;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = ClientCustomField::where('organization_id', Auth::user()->organization_id)
            ->orderByDesc('id');

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return view('livewire.custom-fields.index', [
            'fields' => $query->paginate(10),
            'types'  => self::TYPES,
        ]);
    }
}
