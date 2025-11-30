<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use App\Models\Livre;
use App\Models\User;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    public function run(): void
    {
        $etudiants = User::where('role', 'etudiant')->get();
        $livres = Livre::all();

        if ($etudiants->isEmpty() || $livres->isEmpty()) {
            return;
        }

        foreach ($livres as $livre) {
            $reviewers = $etudiants->random(min(5, $etudiants->count()));

            foreach ($reviewers as $user) {
                Evaluation::firstOrCreate(
                    ['livre_id' => $livre->id, 'user_id' => $user->id],
                    [
                        'note' => fake()->numberBetween(3, 5),
                        'commentaire' => fake()->optional()->sentence(),
                    ]
                );
            }
        }
    }
}







