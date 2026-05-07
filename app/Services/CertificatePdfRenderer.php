<?php

namespace App\Services;

use App\Models\CertificateCategory;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class CertificatePdfRenderer
{
    public function render(Client $client, CertificateCategory $category): DomPdf
    {
        $client->loadMissing(['customValues.field']);

        $html = $this->fillTemplate($category->html_template ?? '', $client, $category);

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);
    }

    public function filename(Client $client, CertificateCategory $category): string
    {
        $base = trim(($client->lastname ?? '').'_'.($client->name ?? '')) ?: 'certificate';
        $base = preg_replace('/\s+/u', '_', $base);
        return $base.'_'.$category->slug.'.pdf';
    }

    public function fillTemplate(string $html, Client $client, CertificateCategory $category): string
    {
        $fullName = trim(($client->lastname ?? '').' '.($client->name ?? ''));

        $replacements = [
            '{{full_name}}'   => e($fullName),
            '{{name}}'        => e($client->name ?? ''),
            '{{lastname}}'    => e($client->lastname ?? ''),
            '{{email}}'       => e($client->email ?? ''),
            '{{url_slug}}'    => e($client->url_slug ?? ''),
            '{{external_id}}' => e($client->external_id ?? ''),
            '{{category}}'    => e($category->name),
            '{{date}}'        => date('d/m/Y'),
            '{{public}}'      => public_path(),
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
            '/\{\{(field:[^}]+|cf_\d+|name|lastname|full_name|email|url_slug|external_id|category|date)\}\}/u',
            '',
            $output
        );

        $output = preg_replace_callback(
            '#(<img[^>]+src=["\'])(?!https?://|file://|data:|/)([^"\']+)#i',
            fn ($m) => $m[1].public_path($m[2]),
            $output
        );

        return $output;
    }
}
