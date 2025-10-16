<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Αν δεν υπάρχει organization, δημιούργησέ το
        $organization = Organization::firstOrCreate(
            ['name' => 'Naoumidou'],
        );

        // Δημιούργησε admin user μόνο αν δεν υπάρχει ήδη
        User::firstOrCreate(
            ['email' => 'grigoriosnatsio@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'organization_id' => $organization->id,
            ]
        );
    }
}
