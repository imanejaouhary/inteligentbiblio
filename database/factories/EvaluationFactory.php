<?php

namespace Database\Factories;

use App\Models\Evaluation;
use App\Models\Livre;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evaluation>
 */
class EvaluationFactory extends Factory
{
    protected $model = Evaluation::class;

    public function definition(): array
    {
        return [
            'livre_id' => Livre::factory(),
            'user_id' => User::factory()->etudiant(),
            'note' => fake()->numberBetween(1, 5),
            'commentaire' => fake()->optional()->sentence(),
        ];
    }
}







