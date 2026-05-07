<?php

use App\Http\Controllers\PublicCertificateController;
use App\Livewire\Categories;
use App\Livewire\Clients;
use App\Livewire\CustomFields;
use App\Livewire\Users;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/c/{slug}',                          [PublicCertificateController::class, 'show'])->name('certificate.show');
Route::get('/c/{slug}/{category?}/preview.pdf',  [PublicCertificateController::class, 'pdf'])->name('certificate.pdf');
Route::get('/c/{slug}/{category?}/download.pdf', [PublicCertificateController::class, 'download'])->name('certificate.download');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard',             Clients\Index::class)->name('dashboard');

    Route::get('/clients',               Clients\Index::class)->name('clients.index');
    Route::get('/clients/create',        Clients\Create::class)->name('clients.create');
    Route::get('/clients/{client}/edit', Clients\Edit::class)->name('clients.edit');

    Route::get('/users',                 Users\Index::class)->name('users.index');
    Route::get('/users/create',          Users\Create::class)->name('users.create');
    Route::get('/users/{user}/edit',     Users\Edit::class)->name('users.edit');

    Route::get('/categories',            Categories\Index::class)->name('categories.index');
    Route::get('/custom-fields',         CustomFields\Index::class)->name('custom-fields.index');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
