<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;


Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $organization = $user->organization()->with('users')->first();
        return view('dashboard', compact('organization'));
    })->name('dashboard');

    Route::get('/users/data', [UserController::class, 'getUsers'])->name('users.data');
    Route::resource('users', UserController::class);

    Route::post('/clients/import', [ClientController::class, 'import'])->name('clients.import');
    Route::get('/certificates/builder', function () {
        return view('certificates.builder');
    })->name('certificates.builder');

});


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
