<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Χρήστες')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')] public string $search = '';

    public ?int $confirmingDeleteId = null;

    public function updatedSearch() { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(int $id): void
    {
        if ($id === Auth::id()) {
            $this->confirmingDeleteId = null;
            $this->dispatch('toast', message: 'Δεν μπορείς να διαγράψεις τον εαυτό σου.', type: 'warning');
            return;
        }
        User::where('organization_id', Auth::user()->organization_id)->findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
        $this->dispatch('toast', message: 'Ο χρήστης διαγράφηκε.', type: 'success');
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        $query = User::where('organization_id', $organizationId)->orderByDesc('id');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        return view('livewire.users.index', [
            'users' => $query->paginate(15),
            'stats' => [
                'total'      => User::where('organization_id', $organizationId)->count(),
                'this_month' => User::where('organization_id', $organizationId)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->count(),
                'verified'   => User::where('organization_id', $organizationId)
                                ->whereNotNull('email_verified_at')->count(),
            ],
        ]);
    }
}
