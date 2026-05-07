<?php

namespace App\Http\Controllers;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Services\CertificatePdfRenderer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicCertificateController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $client = $this->resolveClient($slug);
        $categories = $client->certificateCategories()
            ->whereNotNull('html_template')
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            abort(404, 'Δεν υπάρχει διαθέσιμο πιστοποιητικό για αυτόν τον πελάτη.');
        }

        $selected = $categories->firstWhere('slug', $request->query('cat'))
            ?? $categories->first();

        return view('certificates.public', [
            'client'         => $client,
            'categories'     => $categories,
            'selected'       => $selected,
        ]);
    }

    public function pdf(string $slug, ?string $categorySlug, CertificatePdfRenderer $renderer): Response
    {
        [$client, $category] = $this->resolveClientAndCategory($slug, $categorySlug);

        return $renderer->render($client, $category)
            ->stream($renderer->filename($client, $category), ['Attachment' => false]);
    }

    public function download(string $slug, ?string $categorySlug, CertificatePdfRenderer $renderer): Response
    {
        [$client, $category] = $this->resolveClientAndCategory($slug, $categorySlug);

        return $renderer->render($client, $category)
            ->download($renderer->filename($client, $category));
    }

    private function resolveClient(string $slug): Client
    {
        return Client::with('certificateCategories', 'customValues.field')
            ->where('url_slug', $slug)
            ->firstOrFail();
    }

    private function resolveClientAndCategory(string $slug, ?string $categorySlug): array
    {
        $client = $this->resolveClient($slug);

        $query = $client->certificateCategories()->whereNotNull('html_template');
        $category = $categorySlug
            ? $query->where('slug', $categorySlug)->first()
            : $query->first();

        if (! $category) {
            abort(404, 'Πιστοποιητικό μη διαθέσιμο.');
        }

        return [$client, $category];
    }
}
