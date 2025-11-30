<?php

namespace Database\Factories;

use App\Models\Livre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Livre>
 */
class LivreFactory extends Factory
{
    protected $model = Livre::class;

    public function definition(): array
    {
        return [
            'titre' => fake()->sentence(3),
            'auteur' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'quantite' => fake()->numberBetween(1, 10),
            'description' => fake()->paragraph(),
            'image_path' => null,
        ];
    }
}







