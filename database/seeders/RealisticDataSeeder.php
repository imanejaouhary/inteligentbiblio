<?php

namespace Database\Seeders;

use App\Models\Cours;
use App\Models\CoursFiliere;
use App\Models\Emprunt;
use App\Models\Evaluation;
use App\Models\Livre;
use App\Models\Reclamation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\CarbonImmutable;

class RealisticDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Création de données réalistes...');
        
        // Nettoyer d'abord les anciennes données de test
        $this->cleanOldData();

        // 1. Utilisateurs
        $this->createUsers();
        
        // 2. Livres
        $this->createLivres();
        
        // 3. Professeurs et Cours
        $this->createCours();
        
        // 4. Emprunts avec QR codes
        $this->createEmprunts();
        
        // 5. Évaluations
        $this->createEvaluations();
        
        // 6. Réclamations
        $this->createReclamations();

        $this->command->info('Données réalistes créées avec succès !');
    }

    private function cleanOldData(): void
    {
        $this->command->info('Nettoyage des anciennes données...');
        
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Supprimer toutes les données liées
        \App\Models\Evaluation::truncate();
        \App\Models\Reclamation::truncate();
        \App\Models\Emprunt::truncate();
        \App\Models\CoursFiliere::truncate();
        \App\Models\Cours::truncate();
        \App\Models\Livre::truncate();
        \App\Models\AuditLog::truncate();
        
        // Supprimer tous les utilisateurs sauf ceux qu'on va créer
        \App\Models\User::whereNotIn('email', [
            'admin@universite.ma',
            'biblio@universite.ma',
            'y.idrissi@universite.ma',
            'a.alami@universite.ma',
            'm.benali@universite.ma',
        ])->delete();
        
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function createUsers(): void
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@universite.ma'],
            [
                'name' => 'Ahmed Alami',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
                'filiere' => null,
            ]
        );

        // Bibliothécaire
        User::firstOrCreate(
            ['email' => 'biblio@universite.ma'],
            [
                'name' => 'Fatima Benali',
                'password' => Hash::make('biblio1234'),
                'role' => 'bibliothecaire',
                'filiere' => null,
            ]
        );

        // Professeurs
        $profs = [
            [
                'name' => 'Professeur Youssef Idrissi',
                'email' => 'y.idrissi@universite.ma',
                'password' => Hash::make('prof1234'),
            ],
            [
                'name' => 'Professeur Aicha Alami',
                'email' => 'a.alami@universite.ma',
                'password' => Hash::make('prof1234'),
            ],
            [
                'name' => 'Professeur Mohamed Benali',
                'email' => 'm.benali@universite.ma',
                'password' => Hash::make('prof1234'),
            ],
        ];

        foreach ($profs as $prof) {
            User::firstOrCreate(
                ['email' => $prof['email']],
                array_merge($prof, ['role' => 'prof', 'filiere' => null])
            );
        }

        // Étudiants IL
        $etudiantsIL = [
            ['name' => 'Ahmed Benali', 'email' => 'ahmed.benali@universite.ma'],
            ['name' => 'Fatima Alami', 'email' => 'fatima.alami@universite.ma'],
            ['name' => 'Youssef Idrissi', 'email' => 'youssef.idrissi@universite.ma'],
            ['name' => 'Aicha Bennani', 'email' => 'aicha.bennani@universite.ma'],
            ['name' => 'Mohamed Amrani', 'email' => 'mohamed.amrani@universite.ma'],
            ['name' => 'Sanae El Fassi', 'email' => 'sanae.elfassi@universite.ma'],
            ['name' => 'Omar Alaoui', 'email' => 'omar.alaoui@universite.ma'],
            ['name' => 'Laila Berrada', 'email' => 'laila.berrada@universite.ma'],
            ['name' => 'Karim Tazi', 'email' => 'karim.tazi@universite.ma'],
            ['name' => 'Nadia Chraibi', 'email' => 'nadia.chraibi@universite.ma'],
        ];

        foreach ($etudiantsIL as $etudiant) {
            User::firstOrCreate(
                ['email' => $etudiant['email']],
                [
                    'name' => $etudiant['name'],
                    'password' => Hash::make('etudiant1234'),
                    'role' => 'etudiant',
                    'filiere' => 'IL',
                ]
            );
        }

        // Étudiants ADIA
        $etudiantsADIA = [
            ['name' => 'Hassan Bensaid', 'email' => 'hassan.bensaid@universite.ma'],
            ['name' => 'Imane El Ouazzani', 'email' => 'imane.elouazzani@universite.ma'],
            ['name' => 'Mehdi Alaoui', 'email' => 'mehdi.alaoui@universite.ma'],
            ['name' => 'Sara Bennani', 'email' => 'sara.bennani@universite.ma'],
            ['name' => 'Amine Tazi', 'email' => 'amine.tazi@universite.ma'],
            ['name' => 'Nour El Fassi', 'email' => 'nour.elfassi@universite.ma'],
            ['name' => 'Rachid Berrada', 'email' => 'rachid.berrada@universite.ma'],
            ['name' => 'Salma Chraibi', 'email' => 'salma.chraibi@universite.ma'],
            ['name' => 'Yassine Amrani', 'email' => 'yassine.amrani@universite.ma'],
            ['name' => 'Zineb Alami', 'email' => 'zineb.alami@universite.ma'],
        ];

        foreach ($etudiantsADIA as $etudiant) {
            User::firstOrCreate(
                ['email' => $etudiant['email']],
                [
                    'name' => $etudiant['name'],
                    'password' => Hash::make('etudiant1234'),
                    'role' => 'etudiant',
                    'filiere' => 'ADIA',
                ]
            );
        }
    }

    private function createLivres(): void
    {
        $livres = [
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
                'description' => 'Apprenez les fondamentaux des bases de données relationnelles, SQL, et la modélisation de données avec des exercices pratiques.',
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
            [
                'titre' => 'Développement Web Moderne',
                'auteur' => 'Sarah Johnson',
                'isbn' => '978-2-1234-5683-4',
                'quantite' => 14,
                'description' => 'Apprenez les technologies web modernes : HTML5, CSS3, JavaScript, React, Node.js.',
            ],
            [
                'titre' => 'Réseaux et Télécommunications',
                'auteur' => 'Marc Dubois',
                'isbn' => '978-2-1234-5684-5',
                'quantite' => 9,
                'description' => 'Comprendre les réseaux informatiques, les protocoles TCP/IP, et les technologies de télécommunication.',
            ],
            [
                'titre' => 'Génie Logiciel et Méthodes Agiles',
                'auteur' => 'Laura Martinez',
                'isbn' => '978-2-1234-5685-6',
                'quantite' => 11,
                'description' => 'Les meilleures pratiques du génie logiciel et les méthodes agiles de développement.',
            ],
            [
                'titre' => 'Systèmes d\'Exploitation',
                'auteur' => 'David Wilson',
                'isbn' => '978-2-1234-5686-7',
                'quantite' => 7,
                'description' => 'Fonctionnement des systèmes d\'exploitation : processus, mémoire, fichiers, sécurité.',
            ],
            [
                'titre' => 'Architecture des Ordinateurs',
                'auteur' => 'Emma Brown',
                'isbn' => '978-2-1234-5687-8',
                'quantite' => 5,
                'description' => 'Comprendre l\'architecture matérielle des ordinateurs et les composants électroniques.',
            ],
        ];

        foreach ($livres as $livre) {
            Livre::firstOrCreate(
                ['isbn' => $livre['isbn']],
                array_merge($livre, [
                    'disponible_numerique' => false,
                    'fichier_path' => null,
                    'format' => null,
                    'taille_fichier' => null,
                ])
            );
        }
    }

    private function createCours(): void
    {
        $profs = User::where('role', 'prof')->get();
        
        if ($profs->isEmpty()) {
            return;
        }

        $coursData = [
            [
                'titre' => 'Programmation Java Avancée',
                'description' => 'Cours approfondi sur la programmation orientée objet en Java',
                'filiere' => 'IL',
                'prof_index' => 0,
            ],
            [
                'titre' => 'Base de Données MySQL',
                'description' => 'Apprentissage de MySQL et des requêtes SQL complexes',
                'filiere' => 'IL',
                'prof_index' => 0,
            ],
            [
                'titre' => 'Développement Web avec React',
                'description' => 'Création d\'applications web modernes avec React',
                'filiere' => 'ADIA',
                'prof_index' => 1,
            ],
            [
                'titre' => 'API REST et Laravel',
                'description' => 'Développement d\'APIs REST avec le framework Laravel',
                'filiere' => 'ADIA',
                'prof_index' => 1,
            ],
            [
                'titre' => 'Sécurité des Applications Web',
                'description' => 'Protection des applications web contre les vulnérabilités',
                'filiere' => 'IL',
                'prof_index' => 2,
            ],
            [
                'titre' => 'Cloud Computing et DevOps',
                'description' => 'Introduction au cloud computing et aux pratiques DevOps',
                'filiere' => 'ADIA',
                'prof_index' => 2,
            ],
        ];

        foreach ($coursData as $coursInfo) {
            $prof = $profs[$coursInfo['prof_index'] % $profs->count()];
            
            $cours = Cours::create([
                'titre' => $coursInfo['titre'],
                'description' => $coursInfo['description'],
                'prof_id' => $prof->id,
                'fichier_path' => 'cours/simule_' . str_replace(' ', '_', strtolower($coursInfo['titre'])) . '.pdf',
                'taille' => 1024000, // 1MB simulé
                'extension' => 'pdf',
            ]);

            CoursFiliere::create([
                'cours_id' => $cours->id,
                'filiere' => $coursInfo['filiere'],
            ]);
        }
    }

    private function createEmprunts(): void
    {
        $etudiants = User::where('role', 'etudiant')->get();
        $livres = Livre::all();

        if ($etudiants->isEmpty() || $livres->isEmpty()) {
            return;
        }

        // Créer quelques emprunts réalistes
        $empruntsData = [
            // Emprunts en cours
            ['etudiant_email' => 'ahmed.benali@universite.ma', 'livre_isbn' => '978-2-1234-5678-9', 'jours_passe' => 5],
            ['etudiant_email' => 'fatima.alami@universite.ma', 'livre_isbn' => '978-2-1234-5679-0', 'jours_passe' => 3],
            ['etudiant_email' => 'youssef.idrissi@universite.ma', 'livre_isbn' => '978-2-1234-5680-1', 'jours_passe' => 10],
            ['etudiant_email' => 'hassan.bensaid@universite.ma', 'livre_isbn' => '978-2-1234-5683-4', 'jours_passe' => 2],
            ['etudiant_email' => 'aicha.bennani@universite.ma', 'livre_isbn' => '978-2-1234-5681-2', 'jours_passe' => 7],
            
            // Emprunts retournés
            ['etudiant_email' => 'omar.alaoui@universite.ma', 'livre_isbn' => '978-2-1234-5682-3', 'jours_passe' => 20, 'retourne' => true],
            ['etudiant_email' => 'imane.elouazzani@universite.ma', 'livre_isbn' => '978-2-1234-5684-5', 'jours_passe' => 25, 'retourne' => true],
        ];

        foreach ($empruntsData as $data) {
            $etudiant = User::where('email', $data['etudiant_email'])->first();
            $livre = Livre::where('isbn', $data['livre_isbn'])->first();

            if (!$etudiant || !$livre || $livre->quantite <= 0) {
                continue;
            }

            $dateEmprunt = CarbonImmutable::today()->subDays($data['jours_passe']);
            $dateRetourPrevue = $dateEmprunt->addDays(14);
            
            $statut = isset($data['retourne']) && $data['retourne'] 
                ? Emprunt::STATUT_RETOURNE 
                : Emprunt::STATUT_EN_COURS;

            $dateRetourEffective = null;
            if ($statut === Emprunt::STATUT_RETOURNE) {
                $dateRetourEffective = $dateRetourPrevue->addDays(2);
            }

            $token = \Illuminate\Support\Str::random(32);

            $emprunt = Emprunt::create([
                'etudiant_id' => $etudiant->id,
                'livre_id' => $livre->id,
                'date_emprunt' => $dateEmprunt,
                'date_retour_prevue' => $dateRetourPrevue,
                'date_retour_effective' => $dateRetourEffective,
                'statut' => $statut,
                'reservation_token' => hash('sha256', $token),
            ]);

            // Générer le QR code pour les emprunts en cours
            if ($statut === Emprunt::STATUT_EN_COURS) {
                $this->genererQrCode($emprunt, $token);
                $livre->decrement('quantite');
            } else {
                // Pour les retours, incrémenter la quantité
                $livre->increment('quantite');
            }
        }
    }

    private function genererQrCode(Emprunt $emprunt, string $token): void
    {
        try {
            $emprunt->load(['etudiant', 'livre']);

            $qrData = [
                'type' => 'reservation',
                'emprunt_id' => $emprunt->id,
                'token' => $token,
                'etudiant_id' => $emprunt->etudiant_id,
                'etudiant_nom' => $emprunt->etudiant->name,
                'livre_id' => $emprunt->livre_id,
                'livre_titre' => $emprunt->livre->titre,
                'livre_isbn' => $emprunt->livre->isbn,
                'date_emprunt' => $emprunt->date_emprunt->toDateString(),
                'date_retour_prevue' => $emprunt->date_retour_prevue->toDateString(),
                'timestamp' => now()->toIso8601String(),
            ];

            $qrUrl = \App\Services\QrCodeService::generateQrCodeUrl($qrData);
            $qrPath = "qr_codes/emprunt_{$emprunt->id}.png";
            $saved = \App\Services\QrCodeService::downloadAndSaveQrCode($qrUrl, $qrPath);

            $emprunt->update([
                'qr_code_path' => $saved ? $qrPath : null,
                'qr_generated_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur génération QR code: " . $e->getMessage());
        }
    }

    private function createEvaluations(): void
    {
        $etudiants = User::where('role', 'etudiant')->take(5)->get();
        $livres = Livre::take(3)->get();

        foreach ($livres as $livre) {
            foreach ($etudiants as $etudiant) {
                Evaluation::firstOrCreate(
                    ['livre_id' => $livre->id, 'user_id' => $etudiant->id],
                    [
                        'note' => fake()->numberBetween(4, 5),
                        'commentaire' => fake()->optional()->sentence(),
                    ]
                );
            }
        }
    }

    private function createReclamations(): void
    {
        $etudiants = User::where('role', 'etudiant')->take(3)->get();

        $sujets = [
            'Livre manquant à la bibliothèque',
            'Demande de prolongation d\'emprunt',
            'Question sur les horaires d\'ouverture',
        ];

        foreach ($etudiants as $index => $etudiant) {
            Reclamation::create([
                'etudiant_id' => $etudiant->id,
                'sujet' => $sujets[$index % count($sujets)],
                'message' => 'Bonjour, j\'aimerais obtenir des informations concernant ma demande.',
                'statut' => $index === 0 ? Reclamation::STATUT_EN_ATTENTE : Reclamation::STATUT_EN_COURS,
            ]);
        }
    }
}

