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

        if ($this->invalidateFirst) {
            $pdfStore->invalidate($client);
        }

        $pdfStore->regenerateAll($client);
    }
}
