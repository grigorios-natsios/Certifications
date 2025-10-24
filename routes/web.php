<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;


Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Route::get('/dashboard', function () {
    //     $user = Auth::user();
    //     $organization = $user->organization()->with('users')->first();
    //     return view('dashboard', compact('organization'));
    // })->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::get('dashboard', [ClientController::class, 'index'])->name('dashboard');
    Route::get('data', [ClientController::class, 'datatable'])->name('clients.data');
    Route::post('generate-pdfs', [ClientController::class, 'generateForClients'])->name('clients.generate-pdfs');


    Route::get('/users/data', [UserController::class, 'getUsers'])->name('users.data');
    Route::resource('users', UserController::class);

    Route::get('/certificate-categories', function () {
        return view('certificate-categories');
    })->name('certificate-categories.index');

    Route::post('/clients/import', [ClientController::class, 'import'])->name('clients.import');
    Route::get('/certificates/builder', function () {
        return view('certificates.builder');
    })->name('certificates.builder');

});


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
