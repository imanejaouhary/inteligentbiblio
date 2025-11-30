<?php

namespace Database\Seeders;

use App\Models\Livre;
use Illuminate\Database\Seeder;

class LivreSeeder extends Seeder
{
    public function run(): void
    {
        // Créer 50 livres pour avoir plus de variété
        Livre::factory()->count(50)->create();
        
        // Ajouter quelques livres spécifiques avec des données réalistes
        $livresSpecifiques = [
            [
                'titre' => 'Introduction à la Programmation Orientée Objet',
                'auteur' => 'Jean Dupont',
                'isbn' => '978-2-1234-5678-9',
                'quantite' => 15,
                'description' => 'Un guide complet pour comprendre les concepts de la programmation orientée objet avec des exemples pratiques en Java et C++.',
            ],
            [
                'titre' => 'Base de Données : Concepts et Applications',
                'auteur' => 'Marie Martin',
                'isbn' => '978-2-1234-5679-0',
                'quantite' => 12,
                'description' => 'Apprenez les fondamentaux des bases de données relationnelles, SQL, et la modélisation de données.',
            ],
            [
                'titre' => 'Algorithmes et Structures de Données',
                'auteur' => 'Pierre Durand',
                'isbn' => '978-2-1234-5680-1',
                'quantite' => 10,
                'description' => 'Une approche pratique des algorithmes classiques et des structures de données essentielles en informatique.',
            ],
            [
                'titre' => 'Intelligence Artificielle : Fondements',
                'auteur' => 'Sophie Bernard',
                'isbn' => '978-2-1234-5681-2',
                'quantite' => 8,
                'description' => 'Introduction aux concepts fondamentaux de l\'intelligence artificielle et du machine learning.',
            ],
            [
                'titre' => 'Sécurité Informatique et Cryptographie',
                'auteur' => 'Thomas Leroy',
                'isbn' => '978-2-1234-5682-3',
                'quantite' => 6,
                'description' => 'Guide complet sur les techniques de sécurisation des systèmes informatiques et les principes de cryptographie.',
            ],
        ];

        foreach ($livresSpecifiques as $livre) {
            Livre::create($livre);
        }
    }
}





