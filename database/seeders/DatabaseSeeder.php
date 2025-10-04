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
        // Seed all the necessary data in correct order to respect foreign key constraints
        $this->call([
            UserSeeder::class,        // Users must exist first (referenced by many tables)
            ClientSeeder::class,      // Clients must exist before sales (sales.client_id)
            CategorySeeder::class,    // Categories must exist before content posts
            GoalSeeder::class,        // Goals must exist before tasks (tasks.related_goal_id)
            SalesSeeder::class,       // Sales depend on users and clients
            ExpenseSeeder::class,     // Expenses depend on users
            TaskSeeder::class,        // Tasks depend on users and goals
            ContentPostSeeder::class, // Content posts depend on users and categories
            
            // New seeders for additional tables
            DailySummarySeeder::class,    // Daily summaries depend on users
            NotificationSeeder::class,    // Notifications depend on users
            
            // Media seeders (depend on their parent tables)
            TaskMediaSeeder::class,       // Task media depends on tasks and users
            ContentPostMediaSeeder::class, // Content post media depends on content posts and users
            ClientMediaSeeder::class,     // Client media depends on clients and users
            ExpenseMediaSeeder::class,    // Expense media depends on expenses and users
        ]);
    }
}
