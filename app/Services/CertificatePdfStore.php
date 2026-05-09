<?php

namespace App\Services;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCertificatePdf;

class CertificatePdfStore
{
    public function __construct(private readonly CertificatePdfRenderer $renderer) {}

    public function cacheDir(): string
    {
        return storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'pdfs'.DIRECTORY_SEPARATOR.'cache');
    }

    /**
     * Stable cache path keyed by IDs so renames of the client don't orphan files.
     */
    public function cachePath(Client $client, CertificateCategory $category): string
    {
        return $this->cacheDir().DIRECTORY_SEPARATOR.$client->id.'_'.$category->id.'.pdf';
    }

    /**
     * Path relative to storage/app/public — used for DB column and asset() URLs.
     */
    public function relativePath(Client $client, CertificateCategory $category): string
    {
        return 'pdfs/cache/'.$client->id.'_'.$category->id.'.pdf';
    }

    public function exists(Client $client, CertificateCategory $category): bool
    {
        return is_file($this->cachePath($client, $category));
    }

    /**
     * Return the cached PDF path, generating it on first access.
     * Also backfills the DB record if the file exists but the row is missing.
     */
    public function ensure(Client $client, CertificateCategory $category): string
    {
        $path = $this->cachePath($client, $category);
        if (! is_file($path)) {
            $this->generate($client, $category);
            return $path;
        }

        $hasRow = ClientCertificatePdf::where('client_id', $client->id)
            ->where('category_id', $category->id)
            ->exists();
        if (! $hasRow) {
            $this->upsertRow($client, $category, generatedAt: date('Y-m-d H:i:s', filemtime($path)));
        }

        return $path;
    }

    /**
     * Force (re)generation, overwriting any existing cached file and DB row.
     */
    public function generate(Client $client, CertificateCategory $category): string
    {
        $path = $this->cachePath($client, $category);
        if (! $category->html_template) {
            return $path;
        }

        $dir = dirname($path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $this->renderer->render($client, $category)->save($path);

        $this->upsertRow($client, $category, generatedAt: now());

        return $path;
    }

    public function invalidate(Client $client, ?CertificateCategory $category = null): void
    {
        if ($category) {
            @unlink($this->cachePath($client, $category));
            ClientCertificatePdf::where('client_id', $client->id)
                ->where('category_id', $category->id)
                ->delete();
            return;
        }

        $client->loadMissing('certificateCategories');
        foreach ($client->certificateCategories as $cat) {
            @unlink($this->cachePath($client, $cat));
        }
        ClientCertificatePdf::where('client_id', $client->id)->delete();
    }

    /**
     * Generate cached PDFs for every renderable category of the client.
     * Returns the number of files actually written.
     */
    public function regenerateAll(Client $client): int
    {
        $client->loadMissing('certificateCategories');
        $count = 0;
        foreach ($client->certificateCategories as $category) {
            if (! $category->html_template) continue;
            $this->generate($client, $category);
            $count++;
        }
        return $count;
    }

    private function upsertRow(Client $client, CertificateCategory $category, mixed $generatedAt): void
    {
        $publicUrl = $client->url_slug
            ? route('certificate.pdf', ['slug' => $client->url_slug, 'category' => $category->slug])
            : null;

        ClientCertificatePdf::updateOrCreate(
            ['client_id' => $client->id, 'category_id' => $category->id],
            [
                'path'         => $this->relativePath($client, $category),
                'public_url'   => $publicUrl,
                'generated_at' => $generatedAt,
            ]
        );
    }
}
