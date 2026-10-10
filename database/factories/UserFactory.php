<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'role' => User::ROLE_USER,
            'account_status' => User::STATUS_ACTIVE,
            'remember_token' => Str::random(10),
        ];
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

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function supportOfficer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_SUPPORT_OFFICER,
            'phone' => fake()->optional()->numerify('555-01##'),
            'staff_id' => 'SO-'.fake()->unique()->numerify('####'),
            'specialization' => fake()->randomElement(User::supportOfficerSpecializations()),
            'date_joined' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_status' => User::STATUS_DISABLED,
        ]);
    }
}
