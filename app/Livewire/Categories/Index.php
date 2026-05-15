<?php

namespace App\Livewire\Categories;

use App\Jobs\RegenerateCategoryPdfs;
use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCustomField;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
    public string $editorOrientation = 'portrait';

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
        $cat = $this->scoped()->findOrFail($id);
        $this->editingId = $cat->id;
        $this->name      = $cat->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $cat = $this->scoped()->findOrFail($this->editingId);
            $nameChanged = $cat->name !== $this->name;
            $cat->update(['name' => $this->name]);
            if ($nameChanged) {
                $this->dispatchCategoryRegen($cat);
            }
            $this->dispatch('toast', message: 'Η κατηγορία ενημερώθηκε.', type: 'success');
        } else {
            $cat = CertificateCategory::create([
                'name'            => $this->name,
                'slug'            => Str::slug($this->name) ?: null,
                'organization_id' => Auth::user()->organization_id,
            ]);
            $this->dispatch('toast', message: 'Η κατηγορία δημιουργήθηκε.', type: 'success');
            $this->editingId = $cat->id;
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $this->scoped()->findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
        $this->dispatch('toast', message: 'Η κατηγορία διαγράφηκε.', type: 'success');
    }

    private function scoped()
    {
        return CertificateCategory::where('organization_id', Auth::user()->organization_id);
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
        $cat = $this->scoped()->findOrFail($id);
        $this->editorCategoryId  = $cat->id;
        $this->editorName        = $cat->name;
        $this->editorTemplate    = $cat->html_template ?? '';
        $this->editorOrientation = $cat->orientation === 'landscape' ? 'landscape' : 'portrait';
        $this->showEditor        = true;
    }

    public function saveTemplate(string $html, ?string $orientation = null): void
    {
        if (! $this->editorCategoryId) return;

        $normalized = $orientation === 'landscape' ? 'landscape' : 'portrait';

        $cat = $this->scoped()->findOrFail($this->editorCategoryId);
        $changed = ($cat->html_template ?? '') !== $html
            || ($cat->orientation ?? 'portrait') !== $normalized;

        $cat->update([
            'html_template' => $html,
            'orientation'   => $normalized,
        ]);

        $this->editorOrientation = $normalized;

        if ($changed) {
            $this->dispatchCategoryRegen($cat);
        }

        $this->dispatch('toast', message: 'Το template αποθηκεύτηκε.', type: 'success');
        $this->closeEditor();
    }

    /**
     * Queue a per-client PDF regen for every client attached to this category.
     * Runs after template / orientation / name changes so cached + bulk PDFs
     * stop reflecting the previous state.
     */
    private function dispatchCategoryRegen(CertificateCategory $category): void
    {
        $category->clients()->pluck('clients.id')->each(function (int $clientId) use ($category) {
            RegenerateCategoryPdfs::dispatch($clientId, $category->id);
        });
    }

    public function closeEditor(): void
    {
        $this->showEditor        = false;
        $this->editorCategoryId  = null;
        $this->editorName        = '';
        $this->editorTemplate    = '';
        $this->editorOrientation = 'portrait';
    }

    /**
     * Returns the merged custom-field values for a single client, scoped to
     * the currently-open editor category. Mirrors the precedence used by the
     * PDF renderer (category-scoped value wins over legacy NULL-category).
     */
    public function fetchClientPreviewData(int $clientId): array
    {
        if (! $this->editorCategoryId) {
            return [];
        }

        $client = Client::with(['customValues.field'])
            ->where('organization_id', Auth::user()->organization_id)
            ->findOrFail($clientId);

        $catId = $this->editorCategoryId;

        $values = $client->customValues
            ->filter(fn ($cv) => $cv->certificate_category_id === $catId
                || $cv->certificate_category_id === null)
            ->groupBy('custom_field_id')
            ->map(fn ($group) => $group
                ->sortByDesc(fn ($cv) => $cv->certificate_category_id !== null)
                ->first());

        $fieldsById   = [];
        $fieldsByName = [];

        foreach ($values as $cv) {
            $raw = (string) $cv->value;

            if (optional($cv->field)->type === 'date' && $raw !== '') {
                $ts = strtotime($raw);
                if ($ts !== false) {
                    $raw = date('d/m/Y', $ts);
                }
            }

            $fieldsById[(int) $cv->custom_field_id] = $raw;
            if ($cv->field) {
                $fieldsByName[$cv->field->name] = $raw;
            }
        }

        return [
            'id'             => $client->id,
            'name'           => $client->name ?? '',
            'lastname'       => $client->lastname ?? '',
            'full_name'      => trim(($client->lastname ?? '').' '.($client->name ?? '')),
            'email'          => $client->email ?? '',
            'url_slug'       => $client->url_slug ?? '',
            'external_id'    => $client->external_id ?? '',
            'fields_by_id'   => $fieldsById,
            'fields_by_name' => $fieldsByName,
        ];
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;
        $query = $this->scoped()->orderByDesc('id');

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        // Custom-field palette: when the editor is open for a specific category,
        // surface only fields that apply to that category. Otherwise show all.
        $fieldsQuery = ClientCustomField::where('organization_id', $organizationId);

        if ($this->editorCategoryId) {
            $catId = $this->editorCategoryId;
            $fieldsQuery->where(function ($q) use ($catId) {
                $q->where('applies_to_all', true)
                  ->orWhereHas('categories', fn ($qq) => $qq->where('certificate_categories.id', $catId));
            });
        }

        // Client picker: only clients attached to this category. Those are the
        // ones with category-scoped custom values, so the preview is meaningful.
        $clientList = [];
        if ($this->showEditor && $this->editorCategoryId) {
            $clientList = Client::whereHas('certificateCategories',
                    fn ($q) => $q->where('certificate_categories.id', $this->editorCategoryId))
                ->where('organization_id', $organizationId)
                ->orderBy('lastname')->orderBy('name')
                ->limit(500)
                ->get(['id', 'name', 'lastname'])
                ->map(fn ($c) => [
                    'id'        => $c->id,
                    'full_name' => trim(($c->lastname ?? '').' '.($c->name ?? '')) ?: ('#'.$c->id),
                ])
                ->values()
                ->all();
        }

        return view('livewire.categories.index', [
            'categories'   => $query->paginate(10),
            'customFields' => $fieldsQuery->orderBy('name')->get(['id', 'name', 'type']),
            'clientList'   => $clientList,
        ]);
    }
}
