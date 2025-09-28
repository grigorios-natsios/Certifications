<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $user = Auth::user();

    // Φόρτωσε το organization και τους users του
    $organization = $user->organization()->with('users')->first();

    return view('dashboard', compact('organization'));
})->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
