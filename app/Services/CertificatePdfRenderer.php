<?php

namespace App\Services;

use App\Models\CertificateCategory;
use App\Models\Client;
use App\Models\ClientQrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class CertificatePdfRenderer
{
    public function render(Client $client, CertificateCategory $category): DomPdf
    {
        $client->loadMissing(['customValues.field']);

        $html = $this->fillTemplate($category->html_template ?? '', $client, $category);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'Roboto',
                'chroot'               => [public_path(), base_path()],
            ]);

        $this->registerCustomFonts($pdf);

        return $pdf;
    }

    /**
     * Register Roboto (and any other bundled TTFs) directly with DomPDF.
     * Goes around @font-face URL-loading quirks and ensures the font
     * is available no matter what CSS the template uses.
     */
    private function registerCustomFonts(DomPdf $pdf): void
    {
        $fontDir = public_path('fonts');
        if (! is_dir($fontDir)) {
            return;
        }

        $regular  = is_file($fontDir.DIRECTORY_SEPARATOR.'Roboto-Regular.ttf')  ? 'Roboto-Regular.ttf'  : 'Roboto-VF.ttf';
        $medium   = is_file($fontDir.DIRECTORY_SEPARATOR.'Roboto-Medium.ttf')   ? 'Roboto-Medium.ttf'   : 'Roboto-VF.ttf';
        $semibold = is_file($fontDir.DIRECTORY_SEPARATOR.'Roboto-SemiBold.ttf') ? 'Roboto-SemiBold.ttf' : 'Roboto-VF.ttf';
        $bold     = is_file($fontDir.DIRECTORY_SEPARATOR.'Roboto-Bold.ttf')     ? 'Roboto-Bold.ttf'     : 'Roboto-VF.ttf';

        $fonts = [
            ['family' => 'Roboto', 'style' => 'normal', 'weight' => 'normal', 'file' => $regular],
            ['family' => 'Roboto', 'style' => 'normal', 'weight' => 500,      'file' => $medium],
            ['family' => 'Roboto', 'style' => 'normal', 'weight' => 600,      'file' => $semibold],
            ['family' => 'Roboto', 'style' => 'normal', 'weight' => 'bold',   'file' => $bold],
        ];

        $fm = $pdf->getDomPDF()->getFontMetrics();
        foreach ($fonts as $f) {
            $absPath = $fontDir.DIRECTORY_SEPARATOR.$f['file'];
            if (! is_file($absPath)) {
                continue;
            }
            $url = str_replace('\\', '/', $absPath);
            $fm->registerFont(
                ['family' => $f['family'], 'style' => $f['style'], 'weight' => $f['weight']],
                $url
            );
        }
    }

    public function filename(Client $client, CertificateCategory $category): string
    {
        $lastname = trim($client->lastname ?? '');
        $name     = trim($client->name ?? '');
        $base = trim($lastname.' '.$name) ?: 'certificate';
        $base = preg_replace('/[\\\\\/:*?"<>|]+/u', '', $base);

        // If another client in the same org has the same lastname + name,
        // append a disambiguator (external_id if present, else url_slug).
        $hasDuplicate = Client::query()
            ->where('organization_id', $client->organization_id)
            ->where('id', '!=', $client->id)
            ->where('lastname', $client->lastname)
            ->where('name', $client->name)
            ->exists();

        if ($hasDuplicate) {
            $tag = trim((string) ($client->external_id ?: $client->url_slug ?: $client->id));
            if ($tag !== '') {
                $base .= ' - '.$tag;
            }
        }

        if ($client->certificateCategories->count() > 1) {
            $catName = trim((string) ($category->name ?? ''));
            $catName = preg_replace('/[\\\\\/:*?"<>|]+/u', '', $catName);
            if ($catName !== '') {
                $base .= ' - '.$catName;
            }
        }

        return $base.'.pdf';
    }

    /**
     * Uppercase a string and strip diacritics (Greek tonos, dialytika, etc.).
     * Used for {{name}}, {{lastname}}, {{full_name}} so certificates always
     * read e.g. "ΓΙΩΡΓΟΣ ΠΑΠΑΔΟΠΟΥΛΟΣ" regardless of how data was stored.
     */
    private function upperNoAccents(string $s): string
    {
        if ($s === '') return $s;

        $upper = mb_strtoupper($s, 'UTF-8');

        if (class_exists(\Transliterator::class)) {
            $t = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
            if ($t) return $t->transliterate($upper);
        }

        return strtr($upper, [
            'Ά' => 'Α', 'Έ' => 'Ε', 'Ή' => 'Η', 'Ί' => 'Ι',
            'Ό' => 'Ο', 'Ύ' => 'Υ', 'Ώ' => 'Ω',
            'Ϊ' => 'Ι', 'Ϋ' => 'Υ',
        ]);
    }

    public function fillTemplate(string $html, Client $client, CertificateCategory $category): string
    {
        $fullName = trim(($client->lastname ?? '').' '.($client->name ?? ''));

        $qrDisplayMm = 22;
        $qrStored = ClientQrCode::where('client_id', $client->id)
            ->where('category_id', $category->id)
            ->first();

        if (! $qrStored) {
            $qrStored = app(\App\Services\QrCodeService::class)->ensureFor($client, $category);
        }

        $certificateUrl = $qrStored?->url
            ?? ($client->url_slug ? route('certificate.show', $client->url_slug) : url('/'));

        if ($qrStored) {
            $qrBase64 = $qrStored->image_base64;
        } else {
            $png = app(\App\Services\QrCodeService::class)->generatePng($certificateUrl);
            $qrBase64 = $png ? base64_encode($png) : null;
        }

        $qrImg = $qrBase64
            ? '<img src="data:image/png;base64,'.$qrBase64
                .'" style="display:block;margin:0 auto;width:'.$qrDisplayMm.'mm;height:'.$qrDisplayMm.'mm;" alt="QR">'
            : '';

        $replacements = [
            '{{full_name}}'   => e($this->upperNoAccents($fullName)),
            '{{name}}'        => e($this->upperNoAccents($client->name ?? '')),
            '{{lastname}}'    => e($this->upperNoAccents($client->lastname ?? '')),
            '{{email}}'       => e($client->email ?? ''),
            '{{url_slug}}'    => e($client->url_slug ?? ''),
            '{{external_id}}' => e($client->external_id ?? ''),
            '{{category}}'    => e($category->name),
            '{{date}}'        => date('d/m/Y'),
            '{{public}}'      => public_path(),
            '{{qr}}'          => $qrImg,
            '{{qr_url}}'      => e($certificateUrl),
        ];

        foreach ($client->customValues as $cv) {
            $value = (string) $cv->value;

            if (optional($cv->field)->type === 'date' && $value !== '') {
                $value = date('d/m/Y', strtotime($value));
            }

            $replacements['{{cf_'.$cv->custom_field_id.'}}'] = e($value);
            if ($cv->field) {
                $replacements['{{field:'.$cv->field->name.'}}'] = e($value);
            }
        }

        $output = strtr($html, $replacements);

        $output = preg_replace(
            '/\{\{(field:[^}]+|cf_\d+|name|lastname|full_name|email|url_slug|external_id|category|date|qr|qr_url)\}\}/u',
            '',
            $output
        );

        $output = preg_replace_callback(
            '#(<img[^>]+src=["\'])(?!https?://|file://|data:|/)([^"\']+)#i',
            fn ($m) => $m[1].$this->resolveLocalImage($m[2]),
            $output
        );

        $output = preg_replace_callback(
            '#url\(\s*["\']?(?!https?://|file://|data:|/)([^"\')\s]+)["\']?\s*\)#i',
            fn ($m) => 'url('.$this->resolveLocalImage($m[1]).')',
            $output
        );

        return $output;
    }

    /**
     * Resolve a relative public asset to something DomPDF can load.
     * - Images (jpg/png/...): returned as data: URI (avoids Windows path issues).
     * - Fonts (ttf/otf):      returned as absolute file path with forward slashes
     *                         (DomPDF font loader needs a real file path, not a data URI).
     * - Anything else / missing: returned unchanged.
     */
    private function resolveLocalImage(string $path): string
    {
        $abs = public_path($path);
        if (! is_file($abs)) {
            return $path;
        }

        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));

        if (in_array($ext, ['ttf', 'otf'], true)) {
            return str_replace('\\', '/', $abs);
        }

        $imageMime = match ($ext) {
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',
            default       => null,
        };

        if ($imageMime === null) {
            return $path;
        }

        $bytes = @file_get_contents($abs);
        if ($bytes === false) {
            return $path;
        }

        return 'data:'.$imageMime.';base64,'.base64_encode($bytes);
    }
}
