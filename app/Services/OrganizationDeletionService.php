<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientCertificatePdf;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class OrganizationDeletionService
{
    public function __construct(private readonly CertificatePdfStore $pdfStore) {}

    /**
     * Delete an organization and every artifact tied to it:
     *  - bulk_email_results rows (no FK, would otherwise leak emails)
     *  - the organization itself (DB cascades → users, clients, certificates,
     *    custom fields, activity logs, qr codes, certificate_pdfs, custom values)
     *  - every PDF on disk: cached files keyed by client id, bulk files
     *    addressed by their tracked filename (captured before the cascade),
     *    plus a name-pattern fallback for files whose tracking row was lost
     *  - template images uploaded under public/uploads/templates/{org_id}/
     *
     * Bulk filenames are snapshotted before the transaction so the cascade
     * deleting client_certificate_pdfs doesn't strand the disk artefacts.
     */
    public function delete(Organization $organization): void
    {
        $clients = Client::with('certificateCategories')
            ->where('organization_id', $organization->id)
            ->get();

        $clientIds = $clients->pluck('id')->all();
        $orgId     = $organization->id;

        $bulkFilenames = ClientCertificatePdf::whereIn('client_id', $clientIds)
            ->whereNotNull('bulk_filename')
            ->pluck('bulk_filename')
            ->all();

        DB::transaction(function () use ($organization, $clientIds) {
            if (! empty($clientIds)) {
                DB::table('bulk_email_results')->whereIn('client_id', $clientIds)->delete();
            }
            $organization->delete();
        });

        $bulkDir = $this->pdfStore->bulkDir();
        foreach ($bulkFilenames as $filename) {
            @unlink($bulkDir.DIRECTORY_SEPARATOR.$filename);
        }

        foreach ($clients as $client) {
            $this->pdfStore->pruneAllForClient($client);
        }

        $this->pruneTemplateImages($orgId);
    }

    private function pruneTemplateImages(int $orgId): void
    {
        $dir = public_path('uploads'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$orgId);
        if (! is_dir($dir)) return;

        foreach (scandir($dir) ?: [] as $file) {
            if ($file === '.' || $file === '..') continue;
            @unlink($dir.DIRECTORY_SEPARATOR.$file);
        }
        @rmdir($dir);
    }
}
