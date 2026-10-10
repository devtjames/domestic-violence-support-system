<?php

namespace Database\Factories;

use App\Models\SupportResource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportResource>
 */
class SupportResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_name' => fake()->company().' Support Service',
            'resource_type' => fake()->randomElement(SupportResource::resourceTypes()),
            'description' => fake()->paragraph(2),
            'phone' => fake()->optional()->numerify('555-01##'),
            'email' => fake()->optional()->safeEmail(),
            'website' => fake()->optional()->url(),
            'address' => fake()->optional()->streetAddress(),
            'location' => fake()->city(),
            'availability' => fake()->randomElement(['Weekdays', 'Appointment based', 'Community office hours']),
            'status' => SupportResource::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupportResource::STATUS_INACTIVE,
        ]);
    }
}
