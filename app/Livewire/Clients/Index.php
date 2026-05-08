<?php

namespace App\Livewire\Clients;

use App\Mail\CertificateReadyMail;
use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCustomField;
use App\Services\CertificatePdfRenderer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Πελάτες')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')] public string $search = '';
    #[Url] public ?int $categoryFilter = null;
    #[Url] public string $hasUrl = '';   // '' | 'yes' | 'no'
    #[Url] public array $activeCustomFilters = [];
    public array $customFilters = [];

    /** Default columns shown in the table. User can toggle others on. */
    #[Url(as: 'cols')]
    public array $visibleColumns = ['email', 'categories', 'url', 'created'];

    public array $selected = [];
    public bool $selectAll = false;

    public ?int $confirmingDeleteId = null;

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function isColumnVisible(string $key): bool
    {
        return in_array($key, $this->visibleColumns, true);
    }

    public function getColumnDefinitionsProperty(): array
    {
        return [
            'id'         => 'ID',
            'email'      => 'Email',
            'url'        => 'URL slug',
            'external'   => 'Excel ID',
            'categories' => 'Κατηγορίες',
            'created'    => 'Ημερομηνία',
        ];
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedCategoryFilter() { $this->resetPage(); }
    public function updatedHasUrl() { $this->resetPage(); }
    public function updatedCustomFilters() { $this->resetPage(); }

    public function addCustomFilter(int $fieldId): void
    {
        if (! in_array($fieldId, $this->activeCustomFilters, true)) {
            $this->activeCustomFilters[] = $fieldId;
        }
    }

    public function removeCustomFilter(int $fieldId): void
    {
        $this->activeCustomFilters = array_values(array_filter(
            $this->activeCustomFilters, fn ($id) => $id !== $fieldId
        ));
        unset($this->customFilters[$fieldId]);
        $this->resetPage();
    }

    public function clearAllFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = null;
        $this->hasUrl = '';
        $this->customFilters = [];
        $this->activeCustomFilters = [];
        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        if ($this->search !== '')        $count++;
        if ($this->categoryFilter)       $count++;
        if ($this->hasUrl !== '')        $count++;
        foreach ($this->activeCustomFilters as $fieldId) {
            if (! empty($this->customFilters[$fieldId] ?? '')) $count++;
        }
        return $count;
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selected = $value ? $this->currentPageIds() : [];
    }

    private function currentPageIds(): array
    {
        return $this->buildQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    private function buildQuery()
    {
        $query = Client::with('certificateCategories', 'customValues')
            ->where('organization_id', Auth::user()->organization_id);

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('lastname', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('url_slug', 'like', $term);
            });
        }

        if ($this->categoryFilter) {
            $query->whereHas('certificateCategories', function ($q) {
                $q->where('certificate_categories.id', $this->categoryFilter);
            });
        }

        if ($this->hasUrl === 'yes') {
            $query->whereNotNull('url_slug')->where('url_slug', '!=', '');
        } elseif ($this->hasUrl === 'no') {
            $query->where(fn ($q) => $q->whereNull('url_slug')->orWhere('url_slug', ''));
        }

        foreach ($this->activeCustomFilters as $fieldId) {
            $value = $this->customFilters[$fieldId] ?? '';
            if ($value === '') continue;
            $query->whereHas('customValues', function ($q) use ($fieldId, $value) {
                $q->where('custom_field_id', $fieldId)
                  ->where('value', 'like', '%'.$value.'%');
            });
        }

        return $query->orderByDesc('id');
    }

    public function delete(int $id): void
    {
        Client::where('organization_id', Auth::user()->organization_id)->findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
        $this->dispatch('toast', message: 'Ο πελάτης διαγράφηκε.', type: 'success');
    }

    public function generatePdfs(CertificatePdfRenderer $renderer): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Δεν επιλέχθηκαν πελάτες.', type: 'warning');
            return;
        }

        $clients = Client::with('certificateCategories', 'customValues.field')
            ->where('organization_id', Auth::user()->organization_id)
            ->whereIn('id', $this->selected)
            ->get();

        $dir = storage_path('app/public/pdfs');
        if (! is_dir($dir)) mkdir($dir, 0777, true);

        $count = 0;
        foreach ($clients as $client) {
            foreach ($client->certificateCategories as $category) {
                if (! $category->html_template) continue;
                $renderer->render($client, $category)
                    ->save($dir.'/'.$renderer->filename($client, $category));
                $count++;
            }
        }

        $this->selected = [];
        $this->selectAll = false;

        $this->dispatch('operation-result',
            title: 'Παραγωγή PDF ολοκληρώθηκε',
            message: "Δημιουργήθηκαν $count πιστοποιητικά.",
            type: 'success'
        );
    }

    public function sendEmails(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Δεν επιλέχθηκαν πελάτες.', type: 'warning');
            return;
        }

        $clients = Client::with('certificateCategories')
            ->where('organization_id', Auth::user()->organization_id)
            ->whereIn('id', $this->selected)
            ->get();

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($clients as $client) {
            if (! $client->email || ! $client->url_slug) {
                $skipped++;
                continue;
            }

            try {
                Mail::to($client->email)->send(new CertificateReadyMail($client));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Certificate email failed', [
                    'client_id' => $client->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        $parts = ["Στάλθηκαν: $sent"];
        if ($skipped) $parts[] = "Παραλείφθηκαν: $skipped";
        if ($failed)  $parts[] = "Αποτυχίες: $failed";

        $this->selected = [];
        $this->selectAll = false;

        $this->dispatch('operation-result',
            title: 'Αποστολή email ολοκληρώθηκε',
            message: implode(' · ', $parts),
            type: $failed ? 'warning' : 'success'
        );
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        return view('livewire.clients.index', [
            'clients'      => $this->buildQuery()->paginate(15),
            'categories'   => CertificateCategory::orderBy('name')->get(),
            'customFields' => ClientCustomField::where('organization_id', $organizationId)->get(),
        ]);
    }
}
