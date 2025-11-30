<?php

namespace Database\Factories;

use App\Models\Reclamation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reclamation>
 */
class ReclamationFactory extends Factory
{
    protected $model = Reclamation::class;

    public function definition(): array
    {
        return [
            'etudiant_id' => User::factory()->etudiant(),
            'sujet' => 'Réclamation ' . fake()->sentence(3),
            'message' => fake()->paragraph(),
            'statut' => Reclamation::STATUT_EN_ATTENTE,
        ];
    }
}







