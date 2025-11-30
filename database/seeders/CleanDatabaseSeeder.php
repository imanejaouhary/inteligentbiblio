<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Cours;
use App\Models\CoursFiliere;
use App\Models\Emprunt;
use App\Models\Evaluation;
use App\Models\Livre;
use App\Models\Reclamation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanDatabaseSeeder extends Seeder
{
    /**
     * Nettoie la base de données en supprimant toutes les données de test
     * Garde uniquement les comptes admin et bibliothécaire essentiels
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Supprimer toutes les données liées
        Evaluation::truncate();
        Reclamation::truncate();
        Emprunt::truncate();
        CoursFiliere::truncate();
        Cours::truncate();
        Livre::truncate();
        AuditLog::truncate();

        // Supprimer tous les utilisateurs sauf admin et bibliothécaire
        User::whereNotIn('email', ['admin@ecole.test', 'biblio@ecole.test'])
            ->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Base de données nettoyée. Seuls les comptes admin et bibliothécaire ont été conservés.');
    }
}

