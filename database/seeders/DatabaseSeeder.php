<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        User::factory()->create([
            'full_name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@hiddentreasures.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create default manager user
        User::factory()->create([
            'full_name' => 'Manager User',
            'first_name' => 'Manager',
            'last_name' => 'User',
            'email' => 'manager@hiddentreasures.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        // Create default VA user
        User::factory()->create([
            'full_name' => 'VA User',
            'first_name' => 'VA',
            'last_name' => 'User',
            'email' => 'va@hiddentreasures.com',
            'password' => bcrypt('password'),
            'role' => 'va',
        ]);

        // Create additional test users
        User::factory(5)->create();

        // Create sample clients for sales functionality
        $this->call([
            ClientSeeder::class,
        ]);
    }
}
