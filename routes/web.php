<?php

use App\Http\Controllers\PublicCertificateController;
use App\Livewire\Categories;
use App\Livewire\Clients;
use App\Livewire\CustomFields;
use App\Livewire\Dashboard;
use App\Livewire\Users;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(Auth::check() ? route('dashboard') : route('login')));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard',             Dashboard\Index::class)->name('dashboard');

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

Route::get('/qr.png', function (Request $request, QrCodeService $qrs) {
    $data = (string) $request->query('data', '');
    if ($data === '' || mb_strlen($data) > 2048) {
        abort(400);
    }
    $png = $qrs->generatePng($data);
    if ($png === null) {
        abort(500);
    }
    return response($png, 200, [
        'Content-Type'  => 'image/png',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('qr.png');

// Backward compatibility: old /c/{slug} URLs (already-generated QRs, sent emails, printed PDFs)
// redirect to the new prefix-less URLs.
Route::redirect('/c/{slug}', '/{slug}', 301);
Route::get('/c/{slug}/{category}/preview.pdf', fn (string $slug, string $category) => redirect("/$slug/$category/preview.pdf", 301));
Route::get('/c/{slug}/{category}/download.pdf', fn (string $slug, string $category) => redirect("/$slug/$category/download.pdf", 301));

require __DIR__.'/auth.php';

// Public certificate routes — must come AFTER all explicit routes/auth includes
// because /{slug} is a wildcard that would otherwise swallow /login, /dashboard, etc.
Route::get('/{slug}',                          [PublicCertificateController::class, 'show'])
    ->where('slug', '[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?')
    ->name('certificate.show');
Route::get('/{slug}/{category?}/preview.pdf',  [PublicCertificateController::class, 'pdf'])
    ->where('slug', '[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?')
    ->name('certificate.pdf');
Route::get('/{slug}/{category?}/download.pdf', [PublicCertificateController::class, 'download'])
    ->where('slug', '[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?')
    ->name('certificate.download');
