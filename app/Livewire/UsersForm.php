<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UsersForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?int $organization_id = null;
    public $organizations = [];

    public function mount()
    {
        $this->organization_id = auth()->user()->organization_id;
    }

    public function createUser()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'organization_id' => ['required', 'exists:organizations,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        // Reset fields after creation
        $this->reset(['name', 'email', 'password', 'password_confirmation']);

        session()->flash('message', 'User created successfully!');
    }

    public function render()
    {
        return view('livewire.users-form');
    }
}
