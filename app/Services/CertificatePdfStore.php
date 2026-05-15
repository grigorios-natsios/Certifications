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

    public function bulkDir(): string
    {
        return storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'pdfs');
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
     * Delete bulk (human-named) PDFs for this client using the filenames
     * tracked in the DB. Reliable across renames because we don't recompute
     * the filename from current data — we use what was actually written.
     * Nullifies bulk_filename on tracked rows that survive (the row itself
     * stays for cached PDF tracking).
     */
    public function deleteBulkPdfs(Client $client): void
    {
        $rows = ClientCertificatePdf::where('client_id', $client->id)
            ->whereNotNull('bulk_filename')
            ->get();

        foreach ($rows as $row) {
            @unlink($this->bulkDir().DIRECTORY_SEPARATOR.$row->bulk_filename);
        }

        ClientCertificatePdf::where('client_id', $client->id)
            ->whereNotNull('bulk_filename')
            ->update(['bulk_filename' => null]);
    }

    /**
     * Generate the human-named "bulk" PDF for one client+category, write it
     * to disk, and record the filename on the corresponding tracking row so
     * it can be cleaned up later regardless of subsequent renames.
     * Returns the filename written, or null if the category has no template.
     */
    public function generateBulk(Client $client, CertificateCategory $category): ?string
    {
        if (! $category->html_template) {
            return null;
        }

        $filename = $this->renderer->filename($client, $category);
        $dir = $this->bulkDir();
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $this->renderer->render($client, $category)->save($dir.DIRECTORY_SEPARATOR.$filename);

        $publicUrl = $client->url_slug
            ? route('certificate.pdf', ['slug' => $client->url_slug, 'category' => $category->slug])
            : null;

        ClientCertificatePdf::updateOrCreate(
            ['client_id' => $client->id, 'category_id' => $category->id],
            [
                'path'          => $this->relativePath($client, $category),
                'public_url'    => $publicUrl,
                'bulk_filename' => $filename,
                'generated_at'  => now(),
            ]
        );

        return $filename;
    }

    /**
     * Delete every disk artefact for an entire category — both cached and
     * bulk PDFs across all clients. Called from CertificateCategory's
     * deleting event; the FK cascade clears the DB rows afterwards.
     */
    public function deleteAllForCategory(CertificateCategory $category): void
    {
        $rows = ClientCertificatePdf::where('category_id', $category->id)->get();

        foreach ($rows as $row) {
            $cached = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR
                .str_replace('/', DIRECTORY_SEPARATOR, $row->path));
            @unlink($cached);

            if ($row->bulk_filename) {
                @unlink($this->bulkDir().DIRECTORY_SEPARATOR.$row->bulk_filename);
            }
        }
    }

    /**
     * Refresh a single (client, category) pair. Used after a category's
     * template / orientation / name changes, so each client's PDF for that
     * category reflects the new state. Bulk PDF is only re-created if a
     * bulk filename was tracked for this pair before the wipe.
     */
    public function refreshClientCategory(Client $client, CertificateCategory $category): void
    {
        if (! $category->html_template) {
            $this->invalidate($client, $category);
            return;
        }

        $row = ClientCertificatePdf::where('client_id', $client->id)
            ->where('category_id', $category->id)
            ->first();
        $hadBulk = $row && $row->bulk_filename;

        if ($hadBulk) {
            @unlink($this->bulkDir().DIRECTORY_SEPARATOR.$row->bulk_filename);
        }
        @unlink($this->cachePath($client, $category));
        if ($row) {
            $row->delete();
        }

        $this->generate($client, $category);
        if ($hadBulk) {
            $this->generateBulk($client, $category);
        }
    }

    /**
     * Refresh every PDF artefact for a client. Used after edits so cached and
     * bulk files reflect the latest data. Bulk PDFs are only re-created for
     * (client, category) combos that already had a tracked bulk filename —
     * we never auto-promote a category to bulk that the user never asked for.
     * Also cleans up disk files for categories that were detached during the
     * edit (their tracking rows linger because the pivot has no FK cascade
     * back into client_certificate_pdfs).
     */
    public function refreshAllForClient(Client $client): void
    {
        $client->loadMissing('certificateCategories');
        $bulkCategoryIds = $this->wipeAllForClient($client);

        foreach ($client->certificateCategories as $cat) {
            if (! $cat->html_template) continue;
            $this->generate($client, $cat);
            if (in_array($cat->id, $bulkCategoryIds, true)) {
                $this->generateBulk($client, $cat);
            }
        }
    }

    /**
     * Wipe phase of a refresh: delete every cached + bulk PDF file tracked for
     * this client and clear the DB rows. Returns the category IDs that had a
     * bulk file before the wipe, so the caller can regenerate just those bulks.
     */
    public function wipeAllForClient(Client $client): array
    {
        // Snapshot all PDF tracking before we wipe — covers attached and
        // detached categories alike (latter would otherwise leak files).
        $rows = ClientCertificatePdf::where('client_id', $client->id)->get();
        $bulkCategoryIds = $rows->whereNotNull('bulk_filename')
            ->pluck('category_id')
            ->all();

        foreach ($rows as $row) {
            @unlink(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR
                .str_replace('/', DIRECTORY_SEPARATOR, $row->path)));
            if ($row->bulk_filename) {
                @unlink($this->bulkDir().DIRECTORY_SEPARATOR.$row->bulk_filename);
            }
        }

        ClientCertificatePdf::where('client_id', $client->id)->delete();

        return $bulkCategoryIds;
    }

    /**
     * Aggressive disk cleanup for a client about to disappear (org deletion).
     * Cache dir is id-based so a glob catches everything — including files for
     * detached categories or removed templates that the renderer-reconstruction
     * path would miss. Bulk dir is name-based so we filter scandir entries by
     * the client's current "{lastname} {name}" prefix, which catches renamed
     * disambiguators and added/removed category suffixes.
     *
     * Disk-only — DB rows are handled by FK cascade. Safe to call after the
     * client row has been deleted.
     */
    public function pruneAllForClient(Client $client): void
    {
        foreach (glob($this->cacheDir().DIRECTORY_SEPARATOR.$client->id.'_*.pdf') ?: [] as $file) {
            @unlink($file);
        }

        $base = trim(($client->lastname ?? '').' '.($client->name ?? ''));
        $base = preg_replace('/[\\\\\/:*?"<>|]+/u', '', (string) $base);
        if ($base === '') {
            return;
        }

        $bulkDir = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'pdfs');
        if (! is_dir($bulkDir)) {
            return;
        }

        $exact  = $base.'.pdf';
        $prefix = $base.' - ';
        foreach (scandir($bulkDir) ?: [] as $entry) {
            if (! str_ends_with($entry, '.pdf')) continue;
            if ($entry === $exact || str_starts_with($entry, $prefix)) {
                @unlink($bulkDir.DIRECTORY_SEPARATOR.$entry);
            }
        }
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
