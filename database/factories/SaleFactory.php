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
            'client_id' => User::where('role', 'client')->inRandomOrder()->first()->id ?? User::factory()->create(['role' => 'client'])->id,
            'type' => fake()->randomElement(['Cards', 'Listings', 'VA', 'Consigned']),
            'product_name' => fake()->words(3, true),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'sale_date' => fake()->date(),
            'description' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['pending', 'completed', 'cancelled']),
            'payment_method' => fake()->randomElement(['cash', 'card', 'online']),
        ];
    }
}
