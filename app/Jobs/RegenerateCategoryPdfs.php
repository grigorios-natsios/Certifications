<?php

namespace App\Jobs;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Services\CertificatePdfStore;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegenerateCategoryPdfs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public int $clientId,
        public int $categoryId,
    ) {}

    public function handle(QrCodeService $qrService, CertificatePdfStore $pdfStore): void
    {
        $client = Client::with('certificateCategories', 'customValues.field')->find($this->clientId);
        $category = CertificateCategory::find($this->categoryId);

        if (! $client || ! $category) {
            return;
        }

        if (! $client->certificateCategories->contains('id', $category->id)) {
            return;
        }

        $qrService->ensureFor($client, $category);

        $pdfStore->refreshClientCategory($client, $category);
    }
}
