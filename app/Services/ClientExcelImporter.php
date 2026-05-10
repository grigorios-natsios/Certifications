<?php

namespace App\Services;

use App\Jobs\GenerateClientPdfs;
use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientCustomField;
use App\Models\ClientCustomValue;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ClientExcelImporter
{
    public function __construct(
        private readonly QrCodeService $qrService,
        private readonly CertificatePdfStore $pdfStore,
    ) {}

    /**
     * Excel header → handler key. Header lookup is case-insensitive.
     */
    private const HEADERS = [
        'id'         => 'external_id',
        'name'       => 'name',
        'lastname'   => 'lastname',
        'last name'  => 'lastname',
        'email'      => 'email',
        'category'   => 'category',
        'url'        => 'url_slug',
        'slug'       => 'url_slug',
        'start date' => 'cf:Περίοδος Έναρξης',
        'startdate'  => 'cf:Περίοδος Έναρξης',
        'end date'   => 'cf:Περίοδος Λήξης',
        'enddate'    => 'cf:Περίοδος Λήξης',
        'title'      => 'cf:Αντικείμενο Προγράμματος',
        'subject'    => 'cf:Αντικείμενο Προγράμματος',
        'hours'      => 'cf:Διάρκεια (ώρες)',
    ];

    public function import(string $path, int $organizationId, ?string $extension = null): array
    {
        $sheet = $this->loadSheet($path, $extension);
        $rows  = $sheet->toArray(null, true, true, true);
        if (empty($rows)) {
            return ['inserted' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $headerRow = array_shift($rows);
        $columnMap = $this->buildColumnMap($headerRow);

        $categories = CertificateCategory::where('organization_id', $organizationId)
            ->get()->keyBy(fn ($c) => mb_strtolower($c->name));
        $customFields = ClientCustomField::where('organization_id', $organizationId)
            ->get()->keyBy('name');

        $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'pdfs_queued' => 0];
        $touched = []; // [client_id => ['was_new' => bool, 'before' => string|null]]

        DB::transaction(function () use ($rows, $columnMap, $categories, $customFields, $organizationId, &$stats, &$touched) {
            foreach ($rows as $row) {
                $data = $this->mapRow($row, $columnMap);
                if ($this->isEmptyRow($data)) {
                    continue;
                }
                if (empty($data['name']) && empty($data['lastname']) && empty($data['external_id'])) {
                    $stats['skipped']++;
                    continue;
                }

                $existing = $this->findExisting($data, $organizationId);
                $fingerprintBefore = $existing ? $this->fingerprint($existing) : null;

                $client = $this->upsertClient($data, $organizationId, $stats, $existing);

                $category = $this->syncCategory($client, $data, $categories);
                $this->syncCustomValues($client, $data, $customFields, $organizationId, $category);

                if (! isset($touched[$client->id])) {
                    $touched[$client->id] = [
                        'was_new' => $existing === null,
                        'before'  => $fingerprintBefore,
                    ];
                }
            }
        });

        foreach ($touched as $clientId => $info) {
            if ($info['was_new']) {
                GenerateClientPdfs::dispatch($clientId, invalidateFirst: false);
                $stats['pdfs_queued']++;
                continue;
            }

            $client = Client::with('certificateCategories', 'customValues')->find($clientId);
            if (! $client) continue;

            if ($this->fingerprint($client) !== $info['before']) {
                GenerateClientPdfs::dispatch($clientId, invalidateFirst: true);
                $stats['pdfs_queued']++;
            }
        }

        return $stats;
    }

    private function findExisting(array $data, int $organizationId): ?Client
    {
        if (empty($data['external_id'])) return null;
        return Client::with('certificateCategories', 'customValues')
            ->where('organization_id', $organizationId)
            ->where('external_id', (string) $data['external_id'])
            ->first();
    }

    /**
     * Stable hash of every field that affects the rendered PDF. If this changes
     * between two imports of the same client, the cached PDF is invalidated.
     */
    private function fingerprint(Client $client): string
    {
        $client->loadMissing('certificateCategories', 'customValues');

        $payload = [
            'name'        => $client->name,
            'lastname'    => $client->lastname,
            'email'       => $client->email,
            'url_slug'    => $client->url_slug,
            'external_id' => $client->external_id,
            'categories'  => $client->certificateCategories->pluck('id')->sort()->values()->all(),
            'custom'      => $client->customValues
                ->sortBy(fn ($v) => [(int) $v->certificate_category_id, (int) $v->custom_field_id])
                ->map(fn ($v) => [
                    (int) $v->custom_field_id,
                    (int) ($v->certificate_category_id ?? 0),
                    (string) $v->value,
                ])
                ->values()
                ->all(),
        ];

        return md5(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    private function loadSheet(string $path, ?string $extension)
    {
        $readerType = match (strtolower($extension ?? '')) {
            'xlsx' => 'Xlsx',
            'xls'  => 'Xls',
            'csv'  => 'Csv',
            'txt'  => 'Csv',
            'ods'  => 'Ods',
            default => null,
        };

        if ($readerType) {
            $reader = IOFactory::createReader($readerType);
            if ($readerType === 'Csv') {
                $reader->setDelimiter($this->detectCsvDelimiter($path));
                $reader->setEnclosure('"');
                $reader->setInputEncoding('UTF-8');
            }
            return $reader->load($path)->getActiveSheet();
        }

        return IOFactory::load($path)->getActiveSheet();
    }

    private function detectCsvDelimiter(string $path): string
    {
        $handle = @fopen($path, 'r');
        if (! $handle) return ',';
        $line = fgets($handle, 4096) ?: '';
        fclose($handle);
        $candidates = [',' => 0, ';' => 0, "\t" => 0];
        foreach (array_keys($candidates) as $d) {
            $candidates[$d] = substr_count($line, $d);
        }
        arsort($candidates);
        return array_key_first($candidates) ?: ',';
    }

    private function buildColumnMap(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $col => $value) {
            $key = mb_strtolower(trim((string) $value));
            if ($key === '') continue;
            if (isset(self::HEADERS[$key])) {
                $map[$col] = self::HEADERS[$key];
            }
        }
        return $map;
    }

    private function mapRow(array $row, array $columnMap): array
    {
        $data = ['custom' => []];
        foreach ($columnMap as $col => $field) {
            $raw = $row[$col] ?? null;
            if ($raw === null || $raw === '') continue;

            $value = is_string($raw) ? trim($raw) : $raw;

            if (str_starts_with($field, 'cf:')) {
                $name = substr($field, 3);
                $data['custom'][$name] = $this->normalizeValue($name, $value);
            } else {
                $data[$field] = (string) $value;
            }
        }
        return $data;
    }

    private function normalizeValue(string $fieldName, mixed $value): string
    {
        $isDate = str_contains(mb_strtolower($fieldName), 'περίοδος')
              || str_contains(mb_strtolower($fieldName), 'date');

        if ($isDate) {
            if (is_numeric($value)) {
                try {
                    return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // fall through
                }
            }
            $ts = strtotime((string) $value);
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }

        return (string) $value;
    }

    private function isEmptyRow(array $data): bool
    {
        $hasAny = ! empty($data['external_id'])
               || ! empty($data['name'])
               || ! empty($data['lastname'])
               || ! empty($data['email'])
               || ! empty($data['custom']);
        return ! $hasAny;
    }

    private function upsertClient(array $data, int $organizationId, array &$stats, ?Client $existing = null): Client
    {
        $payload = [
            'name'     => $data['name']     ?? null,
            'lastname' => $data['lastname'] ?? null,
            'email'    => $data['email']    ?? null,
            'url_slug' => $data['url_slug'] ?? null,
            'organization_id' => $organizationId,
        ];

        $payload = array_filter($payload, fn ($v) => $v !== null);

        if ($existing) {
            $existing->update($payload);
            $stats['updated']++;
            return $existing;
        }

        $payload['external_id'] = $data['external_id'] ?? null;
        $client = Client::create($payload);
        $stats['inserted']++;
        return $client;
    }

    private function syncCategory(Client $client, array $data, $categories): ?CertificateCategory
    {
        if (empty($data['category'])) return null;

        $key = mb_strtolower(trim($data['category']));
        $category = $categories->get($key);
        if (! $category) return null;

        $client->certificateCategories()->syncWithoutDetaching([$category->id]);
        return $category;
    }

    private function syncCustomValues(Client $client, array $data, $customFields, int $organizationId, ?CertificateCategory $category): void
    {
        // Custom values are per-certificate (per category). Without a resolved
        // category we have no certificate to attach them to, so skip the row's
        // custom values rather than orphan them.
        if (! $category) return;

        foreach (($data['custom'] ?? []) as $name => $value) {
            $field = $customFields->get($name);
            if (! $field) {
                $field = ClientCustomField::firstOrCreate(
                    ['organization_id' => $organizationId, 'name' => $name],
                    ['type' => 'text']
                );
                $customFields->put($name, $field);
            }

            ClientCustomValue::updateOrCreate(
                [
                    'client_id'               => $client->id,
                    'custom_field_id'         => $field->id,
                    'certificate_category_id' => $category->id,
                ],
                ['value' => (string) $value]
            );
        }
    }
}
