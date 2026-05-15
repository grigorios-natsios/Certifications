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
     * Known system field aliases (lowercase). Anything not listed here is
     * treated as a custom field whose name matches the header verbatim.
     */
    private const SYSTEM_HEADERS = [
        'id'         => 'external_id',
        'name'       => 'name',
        'lastname'   => 'lastname',
        'last name'  => 'lastname',
        'email'      => 'email',
        'category'   => 'category',
        'url'        => 'url_slug',
        'slug'       => 'url_slug',
    ];

    /**
     * English/legacy aliases for well-known custom fields (lowercase header →
     * custom field name in DB). Lets old import templates keep working.
     */
    private const CUSTOM_FIELD_ALIASES = [
        'start date' => 'Περίοδος Έναρξης',
        'startdate'  => 'Περίοδος Έναρξης',
        'end date'   => 'Περίοδος Λήξης',
        'enddate'    => 'Περίοδος Λήξης',
        'title'      => 'Αντικείμενο Προγράμματος',
        'subject'    => 'Αντικείμενο Προγράμματος',
        'hours'      => 'Διάρκεια (ώρες)',
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
        $customFields = ClientCustomField::with('categories:id')
            ->where('organization_id', $organizationId)
            ->get()->keyBy(fn ($f) => mb_strtolower($f->name));

        $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'pdfs_queued' => 0, 'custom_skipped' => 0, 'unknown_categories' => []];
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

                $category = $this->syncCategory($client, $data, $categories, $stats);
                $this->syncCustomValues($client, $data, $customFields, $organizationId, $category, $stats);

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
            $raw = trim((string) $value);
            if ($raw === '') continue;
            $key = mb_strtolower($raw);

            if (isset(self::SYSTEM_HEADERS[$key])) {
                $map[$col] = self::SYSTEM_HEADERS[$key];
                continue;
            }

            // Known custom-field aliases resolve to the canonical DB name.
            if (isset(self::CUSTOM_FIELD_ALIASES[$key])) {
                $map[$col] = 'cf:' . self::CUSTOM_FIELD_ALIASES[$key];
                continue;
            }

            // Anything else is treated as a custom field whose name equals the
            // raw header. Matching against ClientCustomField is done
            // case-insensitively in syncCustomValues.
            $map[$col] = 'cf:' . $raw;
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

        if (! $isDate) {
            return (string) $value;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                // fall through
            }
        }

        $str = trim((string) $value);
        if ($str === '') return '';

        // European formats first — PHP's strtotime treats `/` as US M/D/Y,
        // so "28/03/2025" would otherwise fail and get stored as raw text.
        foreach (['d/m/Y', 'd/m/y', 'd-m-Y', 'd-m-y', 'd.m.Y', 'd.m.y', 'Y-m-d', 'Y/m/d'] as $fmt) {
            $dt = \DateTime::createFromFormat('!'.$fmt, $str);
            if ($dt !== false && $dt->format($fmt) === $str) {
                return $dt->format('Y-m-d');
            }
        }

        $ts = strtotime($str);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return $str;
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

    private function syncCategory(Client $client, array $data, $categories, array &$stats): ?CertificateCategory
    {
        if (empty($data['category'])) return null;

        $raw = trim($data['category']);
        $key = mb_strtolower($raw);
        $category = $categories->get($key);
        if (! $category) {
            // Surface typos like "klarkκ" instead of silently dropping the row's
            // custom values — the user can't tell from stats alone what went wrong.
            if (! in_array($raw, $stats['unknown_categories'], true)) {
                $stats['unknown_categories'][] = $raw;
            }
            return null;
        }

        $client->certificateCategories()->syncWithoutDetaching([$category->id]);
        return $category;
    }

    private function syncCustomValues(Client $client, array $data, $customFields, int $organizationId, ?CertificateCategory $category, array &$stats): void
    {
        // Custom values are per-certificate (per category). Without a resolved
        // category we have no certificate to attach them to, so skip the row's
        // custom values rather than orphan them.
        if (! $category) {
            $stats['custom_skipped'] += count($data['custom'] ?? []);
            return;
        }

        foreach (($data['custom'] ?? []) as $name => $value) {
            $key   = mb_strtolower($name);
            $field = $customFields->get($key);
            if (! $field) {
                $field = ClientCustomField::firstOrCreate(
                    ['organization_id' => $organizationId, 'name' => $name],
                    ['type' => 'text', 'applies_to_all' => false, 'is_required' => false]
                );
                $field->setRelation('categories', $field->categories()->get(['certificate_categories.id']));
                $customFields->put($key, $field);
            }

            // Field scope is inferred from the categories the field appears
            // under across imports: a same-named header in a new category
            // extends the field's scope instead of being silently dropped (or
            // worse — creating a duplicate field). Fields with applies_to_all
            // already cover every category so we leave their scope alone.
            if (! $field->applies_to_all && ! $field->categories->contains('id', $category->id)) {
                $field->categories()->attach($category->id);
                $field->setRelation('categories', $field->categories->push((object) ['id' => $category->id]));
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
