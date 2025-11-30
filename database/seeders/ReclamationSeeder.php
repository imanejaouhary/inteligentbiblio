<?php

namespace Database\Seeders;

use App\Models\Reclamation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReclamationSeeder extends Seeder
{
    public function run(): void
    {
        $etudiants = User::where('role', 'etudiant')->get();

        if ($etudiants->isEmpty()) {
            return;
        }

        // 60% des étudiants ont une réclamation
        $etudiantsAvecReclamation = $etudiants->random((int) ($etudiants->count() * 0.6));

        foreach ($etudiantsAvecReclamation as $etudiant) {
            // Déterminer le statut de manière variée
            $statut = fake()->randomElement([
                Reclamation::STATUT_EN_ATTENTE,
                Reclamation::STATUT_EN_ATTENTE,
                Reclamation::STATUT_EN_COURS,
                Reclamation::STATUT_RESOLU,
            ]);

            Reclamation::create([
                'etudiant_id' => $etudiant->id,
                'sujet' => $this->genererSujet(),
                'message' => $this->genererMessage(),
                'statut' => $statut,
            ]);
        }
    }

    private function genererSujet(): string
    {
        $sujets = [
            'Livre manquant à la bibliothèque',
            'Problème avec la réservation',
            'Livre endommagé reçu',
            'Retard dans le traitement de ma demande',
            'Question sur les horaires d\'ouverture',
            'Demande de prolongation d\'emprunt',
            'Livre non disponible alors qu\'indiqué disponible',
            'Problème avec le système de réservation',
            'Demande d\'information sur un livre',
            'Réclamation sur les frais de retard',
        ];

        return fake()->randomElement($sujets);
    }

    private function genererMessage(): string
    {
        $messages = [
            'Bonjour, je souhaiterais signaler un problème concernant mon emprunt.',
            'J\'ai remarqué que le livre que j\'ai réservé n\'est pas disponible comme indiqué sur le site.',
            'Le livre que j\'ai reçu est dans un état déplorable, plusieurs pages sont manquantes.',
            'Je n\'arrive pas à accéder à mon compte pour voir mes emprunts en cours.',
            'Pouvez-vous m\'aider à résoudre ce problème rapidement ?',
            'J\'aimerais savoir si je peux prolonger la durée de mon emprunt.',
            'Le système indique que j\'ai un retard mais j\'ai bien retourné le livre.',
        ];

        return fake()->randomElement($messages) . ' ' . fake()->optional()->sentence();
    }
}







