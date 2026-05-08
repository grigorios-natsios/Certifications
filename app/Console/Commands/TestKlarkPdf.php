<?php

namespace App\Console\Commands;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Services\CertificatePdfRenderer;
use Illuminate\Console\Command;

class TestKlarkPdf extends Command
{
    protected $signature = 'klark:test-pdf {--client=}';
    protected $description = 'Render a klark certificate PDF and verify single-page output.';

    public function handle(CertificatePdfRenderer $renderer): int
    {
        $category = CertificateCategory::where('slug', 'klark')->first();
        if (! $category) {
            $this->error('No klark category found.');
            return self::FAILURE;
        }
        if (empty($category->html_template)) {
            $this->error('Klark template is empty. Run: php artisan db:seed --class=KlarkTemplateSeeder');
            return self::FAILURE;
        }

        $clientQuery = Client::with('customValues.field')
            ->whereHas('certificateCategories', fn ($q) => $q->where('slug', 'klark'))
            ->whereNotNull('url_slug')
            ->where('url_slug', '!=', '');

        if ($id = $this->option('client')) {
            $clientQuery->where('id', $id);
        }

        $client = $clientQuery->first();
        if (! $client) {
            $this->error('No klark client with url_slug. Pass --client=ID to override.');
            return self::FAILURE;
        }

        $this->info("Client:   {$client->lastname} {$client->name}  (slug={$client->url_slug})");
        $this->info("Category: {$category->name}");
        $this->newLine();

        $filled = $renderer->fillTemplate($category->html_template, $client, $category);

        // Inlined images become data: URIs — count them by mime + first bytes
        $jpegCount = preg_match_all('#data:image/jpeg;base64#', $filled);
        $pngCount  = preg_match_all('#data:image/png;base64#',  $filled);

        $checks = [
            'data:image/jpeg;base64'      => 'background image (jpeg inlined)',
            'data:image/png;base64'       => 'PNG images inlined (logos, sig, QR)',
            'ΒΕΒΑΙΩΣΗ'                    => 'title text',
            'ΠΑΡΑΚΟΛΟΥΘΗΣΗΣ'              => 'subtitle',
            'ΒΕΒΑΙΩΝΕΤΑΙ ΟΤΙ Ο/Η'         => 'header',
            $client->lastname             => 'lastname',
            $client->name                 => 'name',
        ];

        $this->info('Content checks:');
        $allPresent = true;
        foreach ($checks as $needle => $label) {
            $found = str_contains($filled, $needle);
            $this->line(($found ? '  <fg=green>✓</>' : '  <fg=red>✗</>')." {$label}");
            if (! $found) $allPresent = false;
        }
        $this->line("  <fg=cyan>JPEGs inlined:</> {$jpegCount}    <fg=cyan>PNGs inlined:</> {$pngCount}");
        $this->newLine();

        $pdf = $renderer->render($client, $category);
        $bytes = $pdf->output();

        $dir = storage_path('app/test-pdfs');
        if (! is_dir($dir)) mkdir($dir, 0777, true);
        $filePath = $dir.'/klark-test.pdf';
        file_put_contents($filePath, $bytes);

        // Count actual page objects ( /Type /Page  not /Pages )
        $pageCount = preg_match_all('#/Type\s*/Page(?![s/A-Za-z])#', $bytes);
        $size = strlen($bytes);

        $this->info("PDF size: ".number_format($size).' bytes');
        $this->info("Saved:    {$filePath}");
        $this->newLine();

        if ($pageCount === 1) {
            $this->info("<fg=green>✓ Single page</> ({$pageCount})");
        } else {
            $this->error("✗ Multi-page output: {$pageCount} pages — content overflows A4.");
        }

        return ($pageCount === 1 && $allPresent) ? self::SUCCESS : self::FAILURE;
    }
}
