<?php

namespace Database\Factories;

use App\Models\AbuseType;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportCase>
 */
class SupportCaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'is_anonymous' => false,
            'anonymous_nickname' => null,
            'abuse_type_id' => AbuseType::factory(),
            'subject' => fake()->sentence(5),
            'description' => fake()->paragraph(4),
            'preferred_contact_method' => fake()->randomElement(array_keys(SupportCase::contactMethods())),
            'contact_email' => null,
            'contact_phone' => null,
            'safe_contact_time' => fake()->optional()->randomElement(['Morning', 'Afternoon', 'Evening', 'Weekdays only']),
            'safe_contact_instruction' => fake()->optional()->sentence(),
            'urgency' => fake()->randomElement(SupportCase::urgencies()),
            'status' => SupportCase::STATUS_SUBMITTED,
            'assigned_officer_id' => null,
            'resolution_summary' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }

    public function highUrgency(): static
    {
        return $this->state(fn (array $attributes) => [
            'urgency' => SupportCase::URGENCY_HIGH,
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'urgency' => SupportCase::URGENCY_CRITICAL,
        ]);
    }

    public function assignedTo(User $officer): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_officer_id' => $officer->id,
            'status' => SupportCase::STATUS_ASSIGNED,
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_anonymous' => true,
            'anonymous_nickname' => 'Anonymous Demo',
            'contact_email' => 'anonymous@example.test',
        ]);
    }
}
