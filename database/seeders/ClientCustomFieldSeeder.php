<?php

namespace Database\Seeders;

use App\Models\ClientCustomField;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class ClientCustomFieldSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::firstOrCreate(['name' => 'Naoumidou']);

        $defaults = [
            ['name' => 'Αντικείμενο Προγράμματος', 'type' => 'text',   'is_required' => false],
            ['name' => 'Διάρκεια (ώρες)',          'type' => 'number', 'is_required' => false],
            ['name' => 'Περίοδος Έναρξης',         'type' => 'date',   'is_required' => false],
            ['name' => 'Περίοδος Λήξης',           'type' => 'date',   'is_required' => false],
            ['name' => 'Αριθμός ΚΔΒΜ',             'type' => 'text',   'is_required' => false],
        ];

        foreach ($defaults as $field) {
            ClientCustomField::firstOrCreate(
                ['organization_id' => $organization->id, 'name' => $field['name']],
                ['type' => $field['type'], 'is_required' => $field['is_required']]
            );
        }
    }
}
