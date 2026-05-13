<?php

namespace App\Livewire\CustomFields;

use App\Models\CertificateCategory;
use App\Models\ClientCustomField;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
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
    public bool $applies_to_all = true;
    public array $selectedCategories = [];

    public ?int $confirmingDeleteId = null;

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|in:text,number,date,checkbox',
            'is_required' => 'boolean',
            'applies_to_all'     => 'boolean',
            'selectedCategories' => 'array',
            'selectedCategories.*' => [
                \Illuminate\Validation\Rule::exists('certificate_categories', 'id')
                    ->where('organization_id', Auth::user()->organization_id),
            ],
        ];
    }

    public function updatedSearch() { $this->resetPage(); }

    public function updatedAppliesToAll(bool $value): void
    {
        if ($value) {
            $this->selectedCategories = [];
        }
    }

    #[On('custom-fields::create')]
    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $field = ClientCustomField::with('categories:id')
            ->where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        $this->editingId          = $field->id;
        $this->name               = $field->name;
        $this->type               = $field->type;
        $this->is_required        = (bool) $field->is_required;
        $this->applies_to_all     = (bool) $field->applies_to_all;
        $this->selectedCategories = $field->categories->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $this->showModal          = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->applies_to_all) {
            $this->selectedCategories = [];
        }

        $payload = [
            'name'            => $this->name,
            'type'            => $this->type,
            'is_required'     => $this->is_required,
            'applies_to_all'  => $this->applies_to_all,
            'organization_id' => Auth::user()->organization_id,
        ];

        if ($this->editingId) {
            $field = ClientCustomField::where('organization_id', Auth::user()->organization_id)
                ->findOrFail($this->editingId);
            $field->update($payload);
            $this->dispatch('toast', message: 'Το πεδίο ενημερώθηκε.', type: 'success');
        } else {
            $field = ClientCustomField::create($payload);
            $this->dispatch('toast', message: 'Το πεδίο δημιουργήθηκε.', type: 'success');
        }

        $field->categories()->sync($this->applies_to_all ? [] : $this->selectedCategories);

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        ClientCustomField::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
        $this->dispatch('toast', message: 'Το πεδίο διαγράφηκε.', type: 'success');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId          = null;
        $this->name               = '';
        $this->type               = 'text';
        $this->is_required        = false;
        $this->applies_to_all     = true;
        $this->selectedCategories = [];
        $this->resetErrorBag();
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        $query = ClientCustomField::with('categories')
            ->where('organization_id', $organizationId)
            ->orderByDesc('id');

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return view('livewire.custom-fields.index', [
            'fields'     => $query->paginate(10),
            'types'      => self::TYPES,
            'categories' => CertificateCategory::where('organization_id', $organizationId)
                ->orderBy('name')->get(),
        ]);
    }
}
