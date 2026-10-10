<?php

namespace Database\Factories;

use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseActivity>
 */
class CaseActivityFactory extends Factory
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
            'user_id' => User::factory(),
            'action_type' => fake()->randomElement([
                CaseActivity::ACTION_CASE_SUBMITTED,
                CaseActivity::ACTION_STATUS_CHANGED,
                CaseActivity::ACTION_URGENCY_CHANGED,
                CaseActivity::ACTION_CASE_ASSIGNED,
            ]),
            'description' => fake()->sentence(),
            'previous_value' => null,
            'new_value' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
