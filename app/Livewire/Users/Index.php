<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
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

    public function mount(): void
    {
        abort_if(Auth::user()->role !== UserRole::ADMIN, 403);
    }

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
        if ($id === 1) {
            $this->confirmingDeleteId = null;
            $this->dispatch('toast', message: 'Ο Super Admin δεν μπορεί να διαγραφεί.', type: 'warning');
            return;
        }
        User::where('organization_id', Auth::user()->organization_id)->findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
        $this->dispatch('toast', message: 'Ο χρήστης διαγράφηκε.', type: 'success');
    }

    public function toggleActive(int $id): void
    {
        if (Auth::user()->role !== UserRole::ADMIN) {
            $this->dispatch('toast', message: 'Μόνο ο admin μπορεί να αλλάξει την κατάσταση χρηστών.', type: 'warning');
            return;
        }
        if ($id === Auth::id()) {
            $this->dispatch('toast', message: 'Δεν μπορείς να απενεργοποιήσεις τον εαυτό σου.', type: 'warning');
            return;
        }
        if ($id === 1) {
            $this->dispatch('toast', message: 'Ο Super Admin δεν απενεργοποιείται.', type: 'warning');
            return;
        }
        $user = User::where('organization_id', Auth::user()->organization_id)->findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();
        $this->dispatch('toast',
            message: $user->is_active ? 'Ο χρήστης ενεργοποιήθηκε.' : 'Ο χρήστης απενεργοποιήθηκε.',
            type: 'success'
        );
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
                'active'     => User::where('organization_id', $organizationId)
                                ->where('is_active', true)->count(),
            ],
        ]);
    }
}
