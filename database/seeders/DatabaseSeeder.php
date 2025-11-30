<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Utiliser RealisticDataSeeder pour des données réalistes
        // ou les seeders originaux pour des données de test complètes
        $this->call([
            RealisticDataSeeder::class,
        ]);
    }
}
