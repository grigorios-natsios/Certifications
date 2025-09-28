<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // μόνο για συνδεδεμένους χρήστες
    }

    // Δείχνει όλους τους users του Organization του logged-in user
    public function index()
    {
        $organization = Auth::user()->organization;
        $users = $organization->users; // παίρνει μόνο τους users του οργανισμού
        return view('users.index', compact('users'));
    }

    // Προβολή συγκεκριμένου χρήστη
    public function show(User $user)
    {
        $this->authorize('view', $user); // αν χρησιμοποιείς Policy
        return view('users.show', compact('user'));
    }

    protected function create(array $data)
    {
        $defaultOrg = \App\Models\Organization::first();

        return \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Hash::make($data['password']),
            'organization_id' => $defaultOrg->id,
        ]);
    }

}
