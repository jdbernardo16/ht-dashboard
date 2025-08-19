<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
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
            'category' => fake()->randomElement(['Labor', 'Software', 'Table', 'Advertising', 'Office Supplies', 'Travel', 'Utilities', 'Marketing']),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'expense_date' => fake()->date(),
            'description' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['pending', 'paid', 'cancelled']),
            'payment_method' => fake()->randomElement(['cash', 'card', 'online', 'bank_transfer']),
            'merchant' => fake()->optional()->company(),
            'receipt_number' => fake()->optional()->bothify('RCP-####-####'),
            'tax_amount' => fake()->optional()->randomFloat(2, 0, 500),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
