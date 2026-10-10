<?php

namespace Database\Factories;

use App\Models\CaseNote;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseNote>
 */
class CaseNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'support_case_id' => SupportCase::factory(),
            'user_id' => User::factory()->supportOfficer(),
            'note' => fake()->paragraph(),
        ];
    }
}
