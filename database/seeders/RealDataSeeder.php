<?php

namespace Database\Seeders;

use App\Models\Livre;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RealDataSeeder extends Seeder
{
    /**
     * Seed avec de vraies données minimales pour le fonctionnement
     */
    public function run(): void
    {
        // S'assurer que admin et bibliothécaire existent
        $admin = User::firstOrCreate(
            ['email' => 'admin@ecole.test'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
                'filiere' => null,
            ]
        );

        $biblio = User::firstOrCreate(
            ['email' => 'biblio@ecole.test'],
            [
                'name' => 'Bibliothécaire',
                'password' => Hash::make('biblio1234'),
                'role' => 'bibliothecaire',
                'filiere' => null,
            ]
        );

        // Ajouter quelques livres réels pour commencer
        $livresReels = [
            [
                'titre' => 'Introduction à la Programmation',
                'auteur' => 'Jean Dupont',
                'isbn' => '978-2-1234-5678-9',
                'quantite' => 10,
                'description' => 'Un guide complet pour apprendre les bases de la programmation.',
                'disponible_numerique' => false,
            ],
            [
                'titre' => 'Base de Données : Concepts et Applications',
                'auteur' => 'Marie Martin',
                'isbn' => '978-2-1234-5679-0',
                'quantite' => 8,
                'description' => 'Apprenez les fondamentaux des bases de données relationnelles et SQL.',
                'disponible_numerique' => false,
            ],
            [
                'titre' => 'Algorithmes et Structures de Données',
                'auteur' => 'Pierre Durand',
                'isbn' => '978-2-1234-5680-1',
                'quantite' => 5,
                'description' => 'Une approche pratique des algorithmes classiques en informatique.',
                'disponible_numerique' => false,
            ],
        ];

        foreach ($livresReels as $livre) {
            Livre::firstOrCreate(
                ['isbn' => $livre['isbn']],
                $livre
            );
        }

        $this->command->info('Données réelles minimales ajoutées.');
        $this->command->info('Vous pouvez maintenant ajouter vos propres données via l\'interface admin.');
    }
}

