<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Νέος Χρήστης')]
class Create extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        abort_if(Auth::user()->role !== 'admin', 403);
    }

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function save()
    {
        $this->validate();

        User::create([
            'name'            => $this->name,
            'email'           => $this->email,
            'password'        => Hash::make($this->password),
            'organization_id' => Auth::user()->organization_id,
        ]);

        session()->flash('toast', ['type' => 'success', 'message' => 'Ο χρήστης δημιουργήθηκε.']);
        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.create');
    }
}
