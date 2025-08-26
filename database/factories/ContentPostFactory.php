<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContentPost>
 */
class ContentPostFactory extends Factory
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
            'client_id' => User::factory()->create(['role' => 'client'])->id,
            'title' => fake()->sentence(),
            'platform' => [fake()->randomElement(['website', 'facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube', 'pinterest', 'email', 'other'])],
            'content_type' => fake()->randomElement(['post', 'story', 'reel', 'video', 'image', 'carousel', 'live', 'article']),
            'description' => fake()->paragraph(),
            'post_count' => fake()->numberBetween(1, 10),
            'scheduled_date' => fake()->date(),
            'status' => fake()->randomElement(['draft', 'scheduled', 'published', 'archived']),
            'content_category' => fake()->word(),
            'tags' => [fake()->word(), fake()->word()],
            'notes' => fake()->paragraph(),
            'engagement_metrics' => [
                'likes' => fake()->numberBetween(0, 1000),
                'shares' => fake()->numberBetween(0, 500),
                'comments' => fake()->numberBetween(0, 200),
                'views' => fake()->numberBetween(100, 10000),
            ],
        ];
    }
}
