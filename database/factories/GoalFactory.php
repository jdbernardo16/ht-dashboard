<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Goal>
 */
class GoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $targetValue = fake()->randomFloat(2, 1000, 50000);
        $currentValue = fake()->randomFloat(2, 0, $targetValue);
        
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'target_value' => $targetValue,
            'current_value' => $currentValue,
            'quarter' => fake()->randomElement(['Q1', 'Q2', 'Q3', 'Q4']),
            'year' => fake()->year(),
            'deadline' => fake()->dateTimeBetween('now', '+90 days'),
        ];
    }
}
