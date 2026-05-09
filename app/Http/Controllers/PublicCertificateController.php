<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\CertificatePdfRenderer;
use App\Services\CertificatePdfStore;
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

    public function pdf(string $slug, ?string $categorySlug, CertificatePdfStore $store, CertificatePdfRenderer $renderer): Response
    {
        [$client, $category] = $this->resolveClientAndCategory($slug, $categorySlug);

        $path = $store->ensure($client, $category);

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$renderer->filename($client, $category).'"',
        ]);
    }

    public function download(string $slug, ?string $categorySlug, CertificatePdfStore $store, CertificatePdfRenderer $renderer): Response
    {
        [$client, $category] = $this->resolveClientAndCategory($slug, $categorySlug);

        $path = $store->ensure($client, $category);

        return response()->download($path, $renderer->filename($client, $category), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function resolveClient(string $slug): Client
    {
        return Client::with('certificateCategories', 'customValues.field', 'certificatePdfs')
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
