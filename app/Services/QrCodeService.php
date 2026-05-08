<?php

namespace App\Services;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientQrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;
use Throwable;

class QrCodeService
{
    /**
     * Idempotently ensure a QR code exists for (client, category).
     * Existing records are NEVER overwritten — once locked, always locked.
     * Returns the QR record, or null if it could not be generated yet.
     */
    public function ensureFor(Client $client, CertificateCategory $category): ?ClientQrCode
    {
        $existing = ClientQrCode::where('client_id', $client->id)
            ->where('category_id', $category->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        if (empty($client->url_slug)) {
            return null;
        }

        $url = $this->buildUrl($client, $category);
        $png = $this->generatePng($url);

        if ($png === null) {
            return null;
        }

        return ClientQrCode::create([
            'client_id'    => $client->id,
            'category_id'  => $category->id,
            'url'          => $url,
            'image_base64' => base64_encode($png),
        ]);
    }

    /**
     * Ensure QRs for all categories currently attached to the client.
     * Safe to call multiple times — only missing pairs are generated.
     */
    public function ensureAllFor(Client $client): int
    {
        if (empty($client->url_slug)) {
            return 0;
        }

        $client->loadMissing('certificateCategories');

        $created = 0;
        foreach ($client->certificateCategories as $category) {
            $before = ClientQrCode::where('client_id', $client->id)
                ->where('category_id', $category->id)
                ->exists();

            if (! $before && $this->ensureFor($client, $category)) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Generate a PNG for an arbitrary URL. Useful for ad-hoc rendering
     * when no record exists yet (e.g. during PDF rendering).
     */
    public function generatePng(string $targetUrl): ?string
    {
        try {
            $result = (new Builder(
                writer: new PngWriter(),
                data: $targetUrl,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: 320,
                margin: 0,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
            ))->build();

            return $result->getString();
        } catch (Throwable $e) {
            Log::error('QR generation failed', [
                'url'   => $targetUrl,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function buildUrl(Client $client, CertificateCategory $category): string
    {
        $base = route('certificate.show', $client->url_slug);
        return $category->slug
            ? $base.'?cat='.urlencode($category->slug)
            : $base;
    }
}
