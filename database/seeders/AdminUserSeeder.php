<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $organizationData = [
            'name'          => 'Κέντρο Εκπαίδευσης "Ναουμίδου"',
            'address'       => 'Δημαρχίας 13, Νάουσα, Ημαθία',
            'phones'        => ['23320 29485', '23320 21071'],
            'email'         => 'info@lianaoumidou.gr',
            'hours'         => 'Δευ–Παρ 09:00–21:00',
            'website_url'   => 'https://www.lianaoumidou.gr/',
            'facebook_url'  => 'https://www.facebook.com/NaoumidouTsitsi/',
            'instagram_url' => 'https://www.instagram.com/kentro_ekpaideysis_naoumidou/',
            'youtube_url'   => 'https://www.youtube.com/channel/UCG6L7z7XlTO6r2gAOdV11CA',
        ];

        $organization = Organization::query()->orderBy('id')->first();

        if ($organization) {
            $organization->fill($organizationData)->save();
        } else {
            $organization = Organization::create($organizationData);
        }

        User::firstOrCreate(
            ['email' => 'info@lianaoumidou.gr'],
            [
                'name' => 'Αναστάσης Τσίτσης',
                'password' => Hash::make('12345678'),
                'role' => UserRole::ADMIN,
                'organization_id' => $organization->id,
            ]
        );
    }
}
