<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample clients
        $clients = [
            [
                'full_name' => 'John Doe',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'role' => 'client',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
            [
                'full_name' => 'Jane Smith',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'role' => 'client',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
            [
                'full_name' => 'Michael Johnson',
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@example.com',
                'role' => 'client',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
            [
                'full_name' => 'Sarah Williams',
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'sarah.williams@example.com',
                'role' => 'client',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
            [
                'full_name' => 'David Brown',
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@example.com',
                'role' => 'client',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($clients as $client) {
            User::create($client);
        }
    }
}
