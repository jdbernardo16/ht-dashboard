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
            'platform' => fake()->randomElement(['Facebook', 'TikTok', 'YouTube']),
            'post_count' => fake()->numberBetween(1, 10),
            'date' => fake()->date(),
            'engagement_metrics' => [
                'likes' => fake()->numberBetween(0, 1000),
                'shares' => fake()->numberBetween(0, 500),
                'comments' => fake()->numberBetween(0, 200),
                'views' => fake()->numberBetween(100, 10000),
            ],
        ];
    }
}
