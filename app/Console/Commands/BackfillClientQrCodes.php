<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\QrCodeService;
use Illuminate\Console\Command;

class BackfillClientQrCodes extends Command
{
    protected $signature = 'clients:backfill-qrs {--client=}';
    protected $description = 'Generate any missing QR codes for existing clients (immutable, never overwrites).';

    public function handle(QrCodeService $qrService): int
    {
        $query = Client::with('certificateCategories')
            ->whereNotNull('url_slug')
            ->where('url_slug', '!=', '');

        if ($id = $this->option('client')) {
            $query->where('id', $id);
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('No eligible clients.');
            return self::SUCCESS;
        }

        $this->info("Backfilling QR codes for {$total} clients...");
        $bar = $this->output->createProgressBar($total);

        $created = 0;
        $query->chunk(50, function ($clients) use ($qrService, $bar, &$created) {
            foreach ($clients as $client) {
                $created += $qrService->ensureAllFor($client);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Done. Created {$created} new QR codes.");

        return self::SUCCESS;
    }
}
