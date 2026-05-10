<?php

namespace App\Services;

use App\Models\Client;
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
     *  - cached + bulk PDFs on disk for every client (aggressive glob/scandir
     *    cleanup catches stragglers from renames or detached categories)
     *
     * DB deletes run inside a transaction first; disk cleanup runs after a
     * successful commit so a DB rollback can never leave us with vanished
     * files but live rows. Disk cleanup is best-effort — the in-memory client
     * collection survives the DB delete and provides id/name/categories.
     */
    public function delete(Organization $organization): void
    {
        $clients = Client::with('certificateCategories')
            ->where('organization_id', $organization->id)
            ->get();

        $clientIds = $clients->pluck('id')->all();

        DB::transaction(function () use ($organization, $clientIds) {
            if (! empty($clientIds)) {
                DB::table('bulk_email_results')->whereIn('client_id', $clientIds)->delete();
            }
            $organization->delete();
        });

        foreach ($clients as $client) {
            $this->pdfStore->pruneAllForClient($client);
        }
    }
}
