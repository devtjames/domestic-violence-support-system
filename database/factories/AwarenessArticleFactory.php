<?php

namespace Database\Factories;

use App\Models\AwarenessArticle;
use App\Models\AwarenessCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AwarenessArticle>
 */
class AwarenessArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'awareness_category_id' => AwarenessCategory::factory(),
            'author_id' => User::factory()->admin(),
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title),
            'featured_image' => null,
            'short_description' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'status' => AwarenessArticle::STATUS_DRAFT,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AwarenessArticle::STATUS_PUBLISHED,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AwarenessArticle::STATUS_ARCHIVED,
            'published_at' => null,
        ]);
    }
}
