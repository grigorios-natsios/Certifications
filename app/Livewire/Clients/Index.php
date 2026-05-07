<?php

namespace App\Livewire\Clients;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCustomField;
use App\Services\CertificatePdfRenderer;
use App\Services\ClientExcelImporter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Διαχείριση Πελατών')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(as: 'q')] public string $search = '';
    #[Url] public ?int $categoryFilter = null;
    #[Url] public string $dateFrom = '';
    #[Url] public string $dateTo = '';
    #[Url] public string $hasUrl = '';   // '' | 'yes' | 'no'
    #[Url] public array $activeCustomFilters = [];
    public array $customFilters = [];

    /** Default columns shown in the table. User can toggle others on. */
    #[Url(as: 'cols')]
    public array $visibleColumns = ['email', 'categories', 'url', 'created'];

    public array $selected = [];
    public bool $selectAll = false;

    public $importFile;

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
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }
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
        $this->dateFrom = '';
        $this->dateTo = '';
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
        if ($this->dateFrom !== '')      $count++;
        if ($this->dateTo !== '')        $count++;
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

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
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
        $this->dispatch('toast', message: 'Ο πελάτης διαγράφηκε.', type: 'success');
    }

    public function importExcel(ClientExcelImporter $importer): void
    {
        $this->validate([
            'importFile' => [
                'required',
                'file',
                'max:20480',
                static function ($_attr, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (! in_array($ext, ['xlsx', 'xls', 'csv', 'txt', 'ods'])) {
                        $fail('Επιτρέπονται μόνο αρχεία .xlsx, .xls, .csv ή .ods');
                    }
                },
            ],
        ], [], ['importFile' => 'αρχείο']);

        $extension = strtolower($this->importFile->getClientOriginalExtension());

        try {
            $stats = $importer->import(
                $this->importFile->getRealPath(),
                Auth::user()->organization_id,
                $extension
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Σφάλμα ανάγνωσης: '.$e->getMessage(), type: 'error');
            return;
        }

        $this->importFile = null;
        $this->dispatch(
            'toast',
            message: "Νέοι: {$stats['inserted']}, Ενημερώθηκαν: {$stats['updated']}, Παραλείφθηκαν: {$stats['skipped']}",
            type: 'success'
        );
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
                    ->save($dir.'/'.$client->id.'_'.$category->slug.'.pdf');
                $count++;
            }
        }

        $this->dispatch('toast', message: "Δημιουργήθηκαν $count πιστοποιητικά.", type: 'success');
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        return view('livewire.clients.index', [
            'clients'      => $this->buildQuery()->paginate(15),
            'categories'   => CertificateCategory::orderBy('name')->get(),
            'customFields' => ClientCustomField::where('organization_id', $organizationId)->get(),
            'stats'        => [
                'total'         => Client::where('organization_id', $organizationId)->count(),
                'with_category' => Client::where('organization_id', $organizationId)
                                    ->has('certificateCategories')->count(),
                'with_slug'     => Client::where('organization_id', $organizationId)
                                    ->whereNotNull('url_slug')->count(),
                'this_month'    => Client::where('organization_id', $organizationId)
                                    ->whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)->count(),
            ],
        ]);
    }
}
