<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;


Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $user = Auth::user();

    // Φόρτωσε το organization και τους users του
    $organization = $user->organization()->with('users')->first();

    return view('dashboard', compact('organization'));
})->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/clients/import', [ClientController::class, 'import'])->name('clients.import');
});


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
