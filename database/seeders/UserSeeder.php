<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin principal
        User::factory()->admin()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@ecole.test',
            'password' => Hash::make('admin1234'),
        ]);

        // Bibliothécaire
        User::factory()->bibliothecaire()->create([
            'name' => 'Bibliothécaire',
            'email' => 'biblio@ecole.test',
            'password' => Hash::make('biblio1234'),
        ]);

        // Profs avec des noms réalistes
        User::factory()->prof()->create([
            'name' => 'Professeur Ahmed Benali',
            'email' => 'prof@ecole.test',
            'password' => Hash::make('prof1234'),
        ]);
        
        User::factory()->prof()->create([
            'name' => 'Professeur Fatima Alami',
            'email' => 'f.alami@ecole.test',
            'password' => Hash::make('prof1234'),
        ]);
        
        User::factory()->prof()->create([
            'name' => 'Professeur Youssef Idrissi',
            'email' => 'y.idrissi@ecole.test',
            'password' => Hash::make('prof1234'),
        ]);

        // Étudiants IL (30 étudiants)
        User::factory()->count(30)->etudiant('IL')->create();
        
        // Étudiants ADIA (30 étudiants)
        User::factory()->count(30)->etudiant('ADIA')->create();
    }
}


