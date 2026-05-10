<?php

namespace App\Livewire\ActivityLogs;

use App\Models\ActivityLog as ActivityLogModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Καταγραφές')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'lq')] public string $search = '';
    #[Url(as: 'la')] public string $actionFilter = '';
    #[Url(as: 'lfrom')] public ?string $dateFrom = null;
    #[Url(as: 'lto')] public ?string $dateTo = null;

    public bool $confirmingCleanup = false;

    public function updatedSearch() { $this->resetPage(); }
    public function updatedActionFilter() { $this->resetPage(); }
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->actionFilter = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    public function confirmCleanup(): void
    {
        $this->confirmingCleanup = true;
    }

    public function cancelCleanup(): void
    {
        $this->confirmingCleanup = false;
    }

    public function cleanupOldLogs(): void
    {
        $startOfYear = now()->startOfYear();

        $deleted = ActivityLogModel::where('organization_id', Auth::user()->organization_id)
            ->where('created_at', '<', $startOfYear)
            ->delete();

        $this->confirmingCleanup = false;
        $this->resetPage();

        $this->dispatch('toast',
            message: $deleted === 0
                ? 'Δεν υπάρχουν παλαιότερα logs για διαγραφή.'
                : "Διαγράφηκαν $deleted εγγραφές από προηγούμενα έτη.",
            type: $deleted === 0 ? 'warning' : 'success',
        );
    }

    private function buildQuery()
    {
        $query = ActivityLogModel::with(['client', 'user'])
            ->where('organization_id', Auth::user()->organization_id);

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('client_name', 'like', $term)
                  ->orWhere('client_email', 'like', $term)
                  ->orWhere('subject', 'like', $term);
            });
        }

        if ($this->actionFilter !== '' && array_key_exists($this->actionFilter, ActivityLogModel::ACTIONS)) {
            $query->where('action', $this->actionFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->orderByDesc('id');
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        if ($this->search !== '')      $count++;
        if ($this->actionFilter !== '') $count++;
        if ($this->dateFrom)            $count++;
        if ($this->dateTo)              $count++;
        return $count;
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;
        $startOfYear = now()->startOfYear();

        $totals = ActivityLogModel::where('organization_id', $organizationId)
            ->where('created_at', '>=', $startOfYear)
            ->selectRaw('action, COUNT(*) as c')
            ->groupBy('action')
            ->pluck('c', 'action')
            ->toArray();

        $oldCount = ActivityLogModel::where('organization_id', $organizationId)
            ->where('created_at', '<', $startOfYear)
            ->count();

        return view('livewire.activity-logs.index', [
            'logs'        => $this->buildQuery()->paginate(15),
            'actions'     => ActivityLogModel::ACTIONS,
            'totals'      => $totals,
            'oldCount'    => $oldCount,
            'currentYear' => now()->year,
        ]);
    }
}
