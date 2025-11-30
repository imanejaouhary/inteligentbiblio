<?php

namespace Database\Seeders;

use App\Models\Cours;
use App\Models\CoursFiliere;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoursSeeder extends Seeder
{
    public function run(): void
    {
        $profs = User::where('role', 'prof')->get();

        if ($profs->isEmpty()) {
            return;
        }

        $profs->each(function (User $prof): void {
            $coursList = Cours::factory()->count(3)->create([
                'prof_id' => $prof->id,
            ]);

            foreach ($coursList as $cours) {
                CoursFiliere::create([
                    'cours_id' => $cours->id,
                    'filiere' => fake()->randomElement(['IL', 'ADIA']),
                ]);
            }
        });
    }
}







