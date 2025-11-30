<?php

namespace Database\Seeders;

use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\CarbonImmutable;

class EmpruntSeeder extends Seeder
{
    public function run(): void
    {
        $etudiants = User::where('role', 'etudiant')->get();
        $livres = Livre::all();

        if ($etudiants->isEmpty() || $livres->isEmpty()) {
            return;
        }

        foreach ($etudiants as $etudiant) {
            // Chaque étudiant a entre 1 et 4 emprunts
            $nombreEmprunts = fake()->numberBetween(1, 4);
            $selection = $livres->random(min($nombreEmprunts, $livres->count()));

            foreach ($selection as $livre) {
                // Dates variées sur les 60 derniers jours
                $joursPasse = fake()->numberBetween(0, 60);
                $dateEmprunt = CarbonImmutable::today()->subDays($joursPasse);
                $dateRetourPrevue = $dateEmprunt->addDays(14);

                // Déterminer le statut selon la date
                $statut = $this->determinerStatut($dateEmprunt, $dateRetourPrevue);
                
                // Date de retour effective si le livre est retourné
                $dateRetourEffective = null;
                if (in_array($statut, [Emprunt::STATUT_RETOURNE, Emprunt::STATUT_EN_ATTENTE_RETOUR])) {
                    $dateRetourEffective = $dateRetourPrevue->addDays(fake()->numberBetween(0, 5));
                }

                // Vérifier que le livre a encore de la quantité disponible
                if ($livre->quantite > 0) {
                    Emprunt::create([
                        'etudiant_id' => $etudiant->id,
                        'livre_id' => $livre->id,
                        'date_emprunt' => $dateEmprunt,
                        'date_retour_prevue' => $dateRetourPrevue,
                        'date_retour_effective' => $dateRetourEffective,
                        'statut' => $statut,
                    ]);

                    // Décrémenter la quantité seulement si l'emprunt est actif
                    if (in_array($statut, [Emprunt::STATUT_EN_COURS, Emprunt::STATUT_RETARD, Emprunt::STATUT_EN_ATTENTE_RETOUR])) {
                        $livre->decrement('quantite');
                    }
                }
            }
        }
    }

    private function determinerStatut(CarbonImmutable $dateEmprunt, CarbonImmutable $dateRetourPrevue): string
    {
        $aujourdhui = CarbonImmutable::today();
        $joursDepuisEmprunt = $aujourdhui->diffInDays($dateEmprunt);
        $joursDepuisRetourPrevu = $aujourdhui->diffInDays($dateRetourPrevue);

        // 30% de chance d'être retourné
        if (fake()->boolean(30)) {
            return Emprunt::STATUT_RETOURNE;
        }

        // 10% de chance d'être en attente de retour
        if (fake()->boolean(10)) {
            return Emprunt::STATUT_EN_ATTENTE_RETOUR;
        }

        // Si la date de retour est dépassée, c'est en retard
        if ($dateRetourPrevue->isPast() && $joursDepuisRetourPrevu > 0) {
            // 50% de chance d'être en retard, sinon retourné
            return fake()->boolean(50) ? Emprunt::STATUT_RETARD : Emprunt::STATUT_RETOURNE;
        }

        // Sinon, en cours
        return Emprunt::STATUT_EN_COURS;
    }
}







