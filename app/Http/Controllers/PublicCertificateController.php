<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientQrCode;
use App\Services\CertificatePdfRenderer;
use App\Services\CertificatePdfStore;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        ActivityLog::record(ActivityLog::ACTION_CERTIFICATE_VIEW, [
            'organization_id' => $client->organization_id,
            'user_id'         => Auth::id(),
            'client_id'       => $client->id,
            'client_name'     => trim(($client->lastname ?? '').' '.($client->name ?? '')),
            'client_email'    => $client->email,
            'subject'         => $selected->name,
            'meta'            => [
                'ip'         => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'category_id' => $selected->id,
            ],
        ]);

        $qr = ClientQrCode::where('client_id', $client->id)
            ->where('category_id', $selected->id)
            ->first()
            ?? app(QrCodeService::class)->ensureFor($client, $selected);

        return view('certificates.public', [
            'client'         => $client,
            'categories'     => $categories,
            'selected'       => $selected,
            'qr'             => $qr,
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

    public function download(Request $request, string $slug, ?string $categorySlug, CertificatePdfStore $store, CertificatePdfRenderer $renderer): Response
    {
        [$client, $category] = $this->resolveClientAndCategory($slug, $categorySlug);

        $path = $store->ensure($client, $category);

        ActivityLog::record(ActivityLog::ACTION_PDF_DOWNLOAD, [
            'organization_id' => $client->organization_id,
            'user_id'         => Auth::id(),
            'client_id'       => $client->id,
            'client_name'     => trim(($client->lastname ?? '').' '.($client->name ?? '')),
            'client_email'    => $client->email,
            'subject'         => $category->name,
            'meta'            => [
                'ip'         => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'category_id' => $category->id,
            ],
        ]);

        return response()->download($path, $renderer->filename($client, $category), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function resolveClient(string $slug): Client
    {
        return Client::with('organization', 'certificateCategories', 'customValues.field', 'certificatePdfs')
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
