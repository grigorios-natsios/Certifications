<?php

namespace App\Livewire\Categories;

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
#[Title('Κατηγορίες Πιστοποιητικών')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')] public string $search = '';

    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';

    public bool $showEditor = false;
    public ?int $editorCategoryId = null;
    public string $editorName = '';
    public string $editorTemplate = '';

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
            'name' => 'required|string|max:255',
        ];
    }

    public function updatedSearch() { $this->resetPage(); }

    #[On('categories::create')]
    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $cat = CertificateCategory::findOrFail($id);
        $this->editingId = $cat->id;
        $this->name      = $cat->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            CertificateCategory::findOrFail($this->editingId)->update(['name' => $this->name]);
            $this->dispatch('toast', message: 'Η κατηγορία ενημερώθηκε.', type: 'success');
        } else {
            $cat = CertificateCategory::create(['name' => $this->name]);
            $this->dispatch('toast', message: 'Η κατηγορία δημιουργήθηκε.', type: 'success');
            $this->editingId = $cat->id;
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        CertificateCategory::findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
        $this->dispatch('toast', message: 'Η κατηγορία διαγράφηκε.', type: 'success');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name      = '';
        $this->resetErrorBag();
    }

    public function openEditor(int $id): void
    {
        $cat = CertificateCategory::findOrFail($id);
        $this->editorCategoryId = $cat->id;
        $this->editorName       = $cat->name;
        $this->editorTemplate   = $cat->html_template ?? '';
        $this->showEditor       = true;
    }

    public function saveTemplate(string $html): void
    {
        if (! $this->editorCategoryId) return;

        CertificateCategory::findOrFail($this->editorCategoryId)
            ->update(['html_template' => $html]);

        $this->dispatch('toast', message: 'Το template αποθηκεύτηκε.', type: 'success');
        $this->closeEditor();
    }

    public function closeEditor(): void
    {
        $this->showEditor       = false;
        $this->editorCategoryId = null;
        $this->editorName       = '';
        $this->editorTemplate   = '';
    }

    public function render()
    {
        $query = CertificateCategory::orderByDesc('id');

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return view('livewire.categories.index', [
            'categories'   => $query->paginate(10),
            'customFields' => ClientCustomField::where('organization_id', Auth::user()->organization_id)
                ->orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }
}
