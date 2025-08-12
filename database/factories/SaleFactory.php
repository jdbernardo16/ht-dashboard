<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
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
            'type' => fake()->randomElement(['Cards', 'Listings', 'VA', 'Consigned']),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'date' => fake()->date(),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
