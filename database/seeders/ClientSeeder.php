<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

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
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1-555-0101',
                'address' => '123 Main St, Anytown, USA',
                'company' => 'Doe Enterprises',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1-555-0102',
                'address' => '456 Oak Ave, Somewhere, USA',
                'company' => 'Smith & Co',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@example.com',
                'phone' => '+1-555-0103',
                'address' => '789 Pine Rd, Nowhere, USA',
                'company' => 'Johnson Industries',
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'sarah.williams@example.com',
                'phone' => '+1-555-0104',
                'address' => '321 Elm St, Anycity, USA',
                'company' => 'Williams Group',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@example.com',
                'phone' => '+1-555-0105',
                'address' => '654 Maple Dr, Somecity, USA',
                'company' => 'Brown Corporation',
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
