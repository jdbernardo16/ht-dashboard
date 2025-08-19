<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin users
        $adminUsers = [
            [
                'full_name' => 'Sarah Johnson',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@hiddentreasures.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'avatar_url' => 'https://ui-avatars.com/api/?name=SJ&background=3b82f6&color=fff',
            ],
            [
                'full_name' => 'Michael Chen',
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'email' => 'michael.chen@hiddentreasures.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'avatar_url' => 'https://ui-avatars.com/api/?name=MC&background=3b82f6&color=fff',
            ],
        ];

        // Create Manager users
        $managerUsers = [
            [
                'full_name' => 'Emily Rodriguez',
                'first_name' => 'Emily',
                'last_name' => 'Rodriguez',
                'email' => 'emily.rodriguez@hiddentreasures.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'avatar_url' => 'https://ui-avatars.com/api/?name=ER&background=10b981&color=fff',
            ],
            [
                'full_name' => 'David Kim',
                'first_name' => 'David',
                'last_name' => 'Kim',
                'email' => 'david.kim@hiddentreasures.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'avatar_url' => 'https://ui-avatars.com/api/?name=DK&background=10b981&color=fff',
            ],
        ];

        // Create Virtual Assistant users
        $vaUsers = [
            [
                'full_name' => 'Jessica Williams',
                'first_name' => 'Jessica',
                'last_name' => 'Williams',
                'email' => 'jessica.williams@hiddentreasures.com',
                'password' => Hash::make('password'),
                'role' => 'va',
                'avatar_url' => 'https://ui-avatars.com/api/?name=JW&background=f59e0b&color=fff',
            ],
            [
                'full_name' => 'Alex Thompson',
                'first_name' => 'Alex',
                'last_name' => 'Thompson',
                'email' => 'alex.thompson@hiddentreasures.com',
                'password' => Hash::make('password'),
                'role' => 'va',
                'avatar_url' => 'https://ui-avatars.com/api/?name=AT&background=f59e0b&color=fff',
            ],
        ];

        // Create Client users (for sales relationships)
        $clientUsers = [
            [
                'full_name' => 'Robert Anderson',
                'first_name' => 'Robert',
                'last_name' => 'Anderson',
                'email' => 'robert.anderson@client.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'avatar_url' => 'https://ui-avatars.com/api/?name=RA&background=8b5cf6&color=fff',
            ],
            [
                'full_name' => 'Lisa Martinez',
                'first_name' => 'Lisa',
                'last_name' => 'Martinez',
                'email' => 'lisa.martinez@client.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'avatar_url' => 'https://ui-avatars.com/api/?name=LM&background=8b5cf6&color=fff',
            ],
            [
                'full_name' => 'James Wilson',
                'first_name' => 'James',
                'last_name' => 'Wilson',
                'email' => 'james.wilson@client.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'avatar_url' => 'https://ui-avatars.com/api/?name=JW&background=8b5cf6&color=fff',
            ],
            [
                'full_name' => 'Maria Garcia',
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'email' => 'maria.garcia@client.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'avatar_url' => 'https://ui-avatars.com/api/?name=MG&background=8b5cf6&color=fff',
            ],
            [
                'full_name' => 'Christopher Lee',
                'first_name' => 'Christopher',
                'last_name' => 'Lee',
                'email' => 'christopher.lee@client.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'avatar_url' => 'https://ui-avatars.com/api/?name=CL&background=8b5cf6&color=fff',
            ],
        ];

        // Create all users
        foreach (array_merge($adminUsers, $managerUsers, $vaUsers, $clientUsers) as $userData) {
            User::create($userData);
        }

        // Create additional test users for each role
        User::create([
            'full_name' => 'Test Admin',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@hiddentreasures.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'avatar_url' => 'https://ui-avatars.com/api/?name=TA&background=ef4444&color=fff',
        ]);

        User::create([
            'full_name' => 'Test Manager',
            'first_name' => 'Test',
            'last_name' => 'Manager',
            'email' => 'manager@hiddentreasures.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'avatar_url' => 'https://ui-avatars.com/api/?name=TM&background=06b6d4&color=fff',
        ]);

        User::create([
            'full_name' => 'Test VA',
            'first_name' => 'Test',
            'last_name' => 'VA',
            'email' => 'va@hiddentreasures.com',
            'password' => Hash::make('password'),
            'role' => 'va',
            'avatar_url' => 'https://ui-avatars.com/api/?name=TV&background=84cc16&color=fff',
        ]);
    }
}
