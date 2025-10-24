<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Κάλεσε εδώ όποια seeders θέλεις να εκτελούνται
        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}
