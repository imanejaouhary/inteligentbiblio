<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['etudiant', 'prof', 'bibliothecaire']),
            'filiere' => fn (array $attributes) => $attributes['role'] === 'etudiant'
                ? fake()->randomElement(['IL', 'ADIA'])
                : null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'filiere' => null,
        ]);
    }

    public function bibliothecaire(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'bibliothecaire',
            'filiere' => null,
        ]);
    }

    public function prof(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'prof',
            'filiere' => null,
        ]);
    }

    public function etudiant(string $filiere = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'etudiant',
            'filiere' => $filiere ?? fake()->randomElement(['IL', 'ADIA']),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
