<?php

namespace Database\Factories;

use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\CarbonImmutable;

/**
 * @extends Factory<Emprunt>
 */
class EmpruntFactory extends Factory
{
    protected $model = Emprunt::class;

    public function definition(): array
    {
        $dateEmprunt = CarbonImmutable::today()->subDays(fake()->numberBetween(0, 10));
        $dateRetourPrevue = $dateEmprunt->addDays(14);

        return [
            'etudiant_id' => User::factory()->etudiant(),
            'livre_id' => Livre::factory(),
            'date_emprunt' => $dateEmprunt,
            'date_retour_prevue' => $dateRetourPrevue,
            'date_retour_effective' => null,
            'statut' => Emprunt::STATUT_EN_COURS,
        ];
    }
}







