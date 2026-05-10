<?php

namespace Database\Seeders;

use App\Models\CertificateCategory;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CertificateCategorySeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = Organization::query()->orderBy('id')->value('id');
        if (! $organizationId) {
            return;
        }

        // Templates are filled in by dedicated seeders (e.g. KlarkTemplateSeeder)
        // so we always have a single source of truth per template.
        $categories = ['tsitsis-euro', 'tsitsis', 'naoumidou-euro', 'naoumidou', 'klark'];

        foreach ($categories as $name) {
            CertificateCategory::updateOrCreate(
                ['organization_id' => $organizationId, 'name' => $name],
                [
                    'slug'          => Str::slug($name),
                    'html_template' => null,
                ]
            );
        }
    }
}
