<?php

namespace App\Livewire\Clients;

use App\Jobs\SendCertificateEmailJob;
use App\Mail\BulkEmailReportMail;
use App\Models\ActivityLog;
use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCustomField;
use App\Services\CertificatePdfRenderer;
use App\Services\CertificatePdfStore;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
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
    #[Url(as: 'from')] public ?string $createdFrom = null;
    #[Url(as: 'to')]   public ?string $createdTo   = null;
    #[Url] public array $activeCustomFilters = [];
    public array $customFilters = [];

    /** Default columns shown in the table. User can toggle others on. */
    #[Url(as: 'cols')]
    public array $visibleColumns = ['email', 'categories', 'url', 'created'];

    public array $selected = [];
    public bool $selectAll = false;

    public ?int $confirmingDeleteId = null;
    public bool $confirmingBulkDelete = false;

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Δεν επιλέχθηκαν πελάτες.', type: 'warning');
            return;
        }
        $this->confirmingBulkDelete = true;
    }

    public function cancelBulkDelete(): void
    {
        $this->confirmingBulkDelete = false;
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
    public function updatedCreatedFrom() { $this->resetPage(); }
    public function updatedCreatedTo()   { $this->resetPage(); }

    public function clearDateRange(): void
    {
        $this->createdFrom = null;
        $this->createdTo   = null;
        $this->resetPage();
    }
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
        $this->createdFrom = null;
        $this->createdTo   = null;
        $this->customFilters = [];
        $this->activeCustomFilters = [];
        $this->resetPage();
    }

    public function getSelectedPdfCountProperty(): int
    {
        if (empty($this->selected)) return 0;

        return Client::query()
            ->where('organization_id', Auth::user()->organization_id)
            ->whereIn('id', $this->selected)
            ->withCount(['certificateCategories as pdf_count' => function ($q) {
                $q->whereNotNull('html_template')->where('html_template', '!=', '');
            }])
            ->get()
            ->sum('pdf_count');
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        if ($this->search !== '')        $count++;
        if ($this->categoryFilter)       $count++;
        if ($this->hasUrl !== '')        $count++;
        if ($this->createdFrom)          $count++;
        if ($this->createdTo)            $count++;
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

        if ($this->createdFrom) {
            $query->whereDate('created_at', '>=', $this->createdFrom);
        }
        if ($this->createdTo) {
            $query->whereDate('created_at', '<=', $this->createdTo);
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

    public function delete(int $id, CertificatePdfStore $pdfStore): void
    {
        $client = Client::with('certificateCategories')
            ->where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);
        $pdfStore->pruneAllForClient($client);
        $client->delete();

        $this->confirmingDeleteId = null;
        $this->dispatch('toast', message: 'Ο πελάτης διαγράφηκε.', type: 'success');
    }

    public function bulkDelete(CertificatePdfStore $pdfStore): void
    {
        if (empty($this->selected)) {
            $this->confirmingBulkDelete = false;
            return;
        }

        $clients = Client::with('certificateCategories')
            ->where('organization_id', Auth::user()->organization_id)
            ->whereIn('id', $this->selected)
            ->get();

        $count = 0;
        foreach ($clients as $client) {
            $pdfStore->pruneAllForClient($client); // delete cached + bulk PDF files
            $client->delete();                     // FK cascade handles DB rows
            $count++;
        }

        $this->selected = [];
        $this->selectAll = false;
        $this->confirmingBulkDelete = false;

        $this->dispatch('toast',
            message: $count === 1 ? 'Ο πελάτης διαγράφηκε.' : "Διαγράφηκαν $count πελάτες.",
            type: 'success'
        );
    }

    public function generatePdfs(CertificatePdfStore $pdfStore): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Δεν επιλέχθηκαν πελάτες.', type: 'warning');
            return;
        }

        $clients = Client::with('certificateCategories', 'customValues.field')
            ->where('organization_id', Auth::user()->organization_id)
            ->whereIn('id', $this->selected)
            ->get();

        $count = 0;
        foreach ($clients as $client) {
            foreach ($client->certificateCategories as $category) {
                if ($pdfStore->generateBulk($client, $category) !== null) {
                    $count++;
                }
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

    public function downloadPdfs(CertificatePdfRenderer $renderer, CertificatePdfStore $store)
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Δεν επιλέχθηκαν πελάτες.', type: 'warning');
            return null;
        }

        $clients = Client::with('certificateCategories', 'customValues.field')
            ->where('organization_id', Auth::user()->organization_id)
            ->whereIn('id', $this->selected)
            ->get();

        $zipPath = tempnam(sys_get_temp_dir(), 'certs_');
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            $this->dispatch('toast', message: 'Σφάλμα δημιουργίας ZIP.', type: 'error');
            return null;
        }

        // Use a name set to disambiguate identical human-readable filenames
        // across different client+category combinations.
        $usedNames = [];
        $count = 0;
        foreach ($clients as $client) {
            foreach ($client->certificateCategories as $category) {
                if (! $category->html_template) continue;
                $pdfPath = $store->ensure($client, $category);
                $name = $renderer->filename($client, $category);
                if (isset($usedNames[$name])) {
                    $name = pathinfo($name, PATHINFO_FILENAME).' ('.(++$usedNames[$name]).').pdf';
                } else {
                    $usedNames[$name] = 1;
                }
                $zip->addFile($pdfPath, $name);
                $count++;
            }
        }
        $zip->close();

        if ($count === 0) {
            @unlink($zipPath);
            $this->dispatch('toast', message: 'Δεν υπάρχουν πιστοποιητικά για τους επιλεγμένους πελάτες.', type: 'warning');
            return null;
        }

        $this->selected = [];
        $this->selectAll = false;

        $filename = 'pistopoiitika-'.date('Y-m-d_His').'.zip';

        return response()->streamDownload(function () use ($zipPath) {
            readfile($zipPath);
            @unlink($zipPath);
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
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

        $reportRecipient = Auth::user()->organization?->email ?: Auth::user()->email;

        if (! $reportRecipient) {
            $this->dispatch('toast',
                message: 'Δεν βρέθηκε email οργανισμού για την αναφορά μαζικής αποστολής.',
                type: 'error'
            );
            return;
        }

        $jobs = [];
        $skipped = 0;
        $delaySeconds = 0;

        foreach ($clients as $client) {
            if (! $client->email || ! $client->url_slug) {
                $skipped++;
                continue;
            }

            $jobs[] = (new SendCertificateEmailJob($client->id, $reportRecipient))
                ->delay(now()->addSeconds($delaySeconds));
            $delaySeconds += 15;
        }

        $queued = count($jobs);

        if ($queued === 0) {
            $this->selected = [];
            $this->selectAll = false;
            $this->dispatch('toast',
                message: "Δεν στάλθηκε κανένα email — οι επιλεγμένοι πελάτες δεν έχουν email/URL ($skipped παραλείφθηκαν).",
                type: 'warning'
            );
            return;
        }

        $batch = Bus::batch($jobs)
            ->name('Bulk Certificate Emails')
            ->allowFailures()
            ->finally(function (Batch $batch) use ($reportRecipient) {
                try {
                    Mail::to($reportRecipient)
                        ->later(now()->addSeconds(20), new BulkEmailReportMail($batch->id));
                } catch (\Throwable $e) {
                    Log::error('Bulk email report send failed', [
                        'batch_id' => $batch->id,
                        'error'    => $e->getMessage(),
                    ]);
                }
            })
            ->dispatch();

        ActivityLog::record(ActivityLog::ACTION_EMAIL_BATCH, [
            'organization_id' => Auth::user()->organization_id,
            'user_id'         => Auth::id(),
            'subject'         => "$queued emails — αναφορά στο $reportRecipient",
            'meta'            => [
                'batch_id'         => $batch->id,
                'total_jobs'       => $queued,
                'skipped'          => $skipped,
                'report_recipient' => $reportRecipient,
                'triggered_by'     => Auth::user()->email,
            ],
        ]);

        $this->selected = [];
        $this->selectAll = false;

        $parts = ["Στην ουρά: $queued"];
        if ($skipped) $parts[] = "Παραλείφθηκαν: $skipped";

        $this->dispatch('operation-result',
            title: 'Τα email προστέθηκαν στην ουρά αποστολής',
            message: implode(' · ', $parts)." — όταν ολοκληρωθεί η αποστολή, θα λάβετε αναφορά στο $reportRecipient με τα emails που στάλθηκαν και τυχόν αποτυχίες.",
            type: 'success'
        );
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        $lastExternalId = Client::where('organization_id', $organizationId)
            ->whereNotNull('external_id')
            ->where('external_id', '!=', '')
            ->orderByDesc('id')
            ->value('external_id');

        return view('livewire.clients.index', [
            'clients'        => $this->buildQuery()->paginate(15),
            'categories'     => CertificateCategory::where('organization_id', $organizationId)
                ->orderBy('name')->get(),
            'customFields'   => ClientCustomField::where('organization_id', $organizationId)->get(),
            'lastExternalId' => $lastExternalId,
        ]);
    }
}
