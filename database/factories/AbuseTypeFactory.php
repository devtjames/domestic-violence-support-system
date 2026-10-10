<?php

namespace Database\Factories;

use App\Models\AbuseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbuseType>
 */
class AbuseTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Physical Abuse',
            'Emotional Abuse',
            'Psychological Abuse',
            'Sexual Abuse',
            'Financial Abuse',
            'Digital Abuse',
            'Verbal Abuse',
            'Coercive Control',
            'Child-related Abuse',
            'Other',
        ]).' '.fake()->unique()->numberBetween(100, 9999);

        return [
            'name' => $name,
            'slug' => (string) str($name)->slug(),
            'description' => fake()->sentence(),
            'status' => AbuseType::STATUS_ACTIVE,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AbuseType::STATUS_INACTIVE,
        ]);
    }
}
