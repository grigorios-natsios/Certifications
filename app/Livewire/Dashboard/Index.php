<?php

namespace App\Livewire\Dashboard;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Services\ClientExcelImporter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Πίνακας Ελέγχου')]
class Index extends Component
{
    use WithFileUploads;

    public $importFile;

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

        $parts = [
            "Νέοι: {$stats['inserted']}",
            "Ενημερώθηκαν: {$stats['updated']}",
            "Παραλείφθηκαν: {$stats['skipped']}",
        ];
        $pdfsQueued = $stats['pdfs_queued'] ?? 0;
        if ($pdfsQueued) $parts[] = "PDF στην ουρά: {$pdfsQueued} (παράγονται στο παρασκήνιο)";

        $this->dispatch(
            'toast',
            message: implode(', ', $parts),
            type: 'success'
        );
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        $categories = CertificateCategory::withCount(['clients' => fn ($q) => $q->where('organization_id', $organizationId)])
            ->orderBy('name')
            ->get();

        return view('livewire.dashboard.index', [
            'categories' => $categories,
            'stats'      => [
                'total'     => Client::where('organization_id', $organizationId)->count(),
                'this_year' => Client::where('organization_id', $organizationId)
                                ->whereYear('created_at', now()->year)->count(),
            ],
        ]);
    }
}
