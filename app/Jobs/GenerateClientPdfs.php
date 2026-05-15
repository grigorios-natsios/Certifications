<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\CertificatePdfStore;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateClientPdfs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public int $clientId,
        public bool $invalidateFirst = false,
    ) {}

    public function handle(QrCodeService $qrService, CertificatePdfStore $pdfStore): void
    {
        $client = Client::with('certificateCategories', 'customValues.field')->find($this->clientId);
        if (! $client) {
            return;
        }

        $qrService->ensureAllFor($client);
        $client->load('certificateCategories', 'customValues.field');

        // Wipe + collect previously-bulk categories so we know which to regen
        // human-named files for. Fresh imports have nothing to wipe.
        $bulkCategoryIds = $this->invalidateFirst
            ? $pdfStore->wipeAllForClient($client)
            : [];

        foreach ($client->certificateCategories as $category) {
            if (! $category->html_template) continue;

            $path = $pdfStore->generate($client, $category);

            // If the client got deleted while we were rendering, the file we
            // just wrote is an orphan (pruneAllForClient already ran at delete
            // time and can't run again). Drop it and bail out — any remaining
            // categories would be orphans too, and the next upsertRow call
            // would FK-violate anyway.
            if (! Client::whereKey($this->clientId)->exists()) {
                @unlink($path);
                return;
            }

            if (in_array($category->id, $bulkCategoryIds, true)) {
                $bulkFilename = $pdfStore->generateBulk($client, $category);

                if (! Client::whereKey($this->clientId)->exists()) {
                    if ($bulkFilename) {
                        @unlink($pdfStore->bulkDir().DIRECTORY_SEPARATOR.$bulkFilename);
                    }
                    return;
                }
            }
        }
    }
}
