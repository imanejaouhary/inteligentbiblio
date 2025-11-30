<?php

namespace Database\Factories;

use App\Models\Cours;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cours>
 */
class CoursFactory extends Factory
{
    protected $model = Cours::class;

    public function definition(): array
    {
        return [
            'titre' => 'Cours ' . fake()->sentence(2),
            'description' => fake()->paragraph(),
            'prof_id' => User::factory()->prof(),
            'fichier_path' => 'cours/' . fake()->uuid() . '.pdf',
            'taille' => fake()->numberBetween(10_000, 3_000_000),
            'extension' => 'pdf',
        ];
    }
}







