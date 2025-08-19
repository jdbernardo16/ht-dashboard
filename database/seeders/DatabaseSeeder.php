<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed all the necessary data
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SalesSeeder::class,
            ExpenseSeeder::class,
            TaskSeeder::class,
            ContentPostSeeder::class,
            GoalSeeder::class,
        ]);
    }
}
