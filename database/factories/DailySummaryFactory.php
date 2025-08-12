<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailySummary>
 */
class DailySummaryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalTasks = fake()->numberBetween(0, 20);
        $completedTasks = fake()->numberBetween(0, $totalTasks);
        
        return [
            'user_id' => User::factory(),
            'date' => fake()->date(),
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'productivity_score' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
