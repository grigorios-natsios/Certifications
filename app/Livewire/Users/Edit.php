<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Επεξεργασία Χρήστη')]
class Edit extends Component
{
    public User $user;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(User $user): void
    {
        abort_if(Auth::user()->role !== UserRole::ADMIN, 403);
        abort_if($user->organization_id !== Auth::user()->organization_id, 403);

        $this->user  = $user;
        $this->name  = $user->name;
        $this->email = $user->email;
    }

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }

    public function save()
    {
        $this->validate();

        $payload = [
            'name'  => $this->name,
            'email' => $this->email,
        ];
        if (! empty($this->password)) {
            $payload['password'] = Hash::make($this->password);
        }

        $this->user->update($payload);

        session()->flash('toast', ['type' => 'success', 'message' => 'Ο χρήστης ενημερώθηκε.']);
        return redirect()->route('users.index');
    }

    public function delete()
    {
        if ($this->user->id === Auth::id()) {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Δεν μπορείς να διαγράψεις τον εαυτό σου.']);
            return;
        }

        $this->user->delete();
        session()->flash('toast', ['type' => 'success', 'message' => 'Ο χρήστης διαγράφηκε.']);
        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.edit');
    }
}
