<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use App\Models\ClientMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ClientMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all clients and users
        $clients = Client::all();
        $users = User::all();
        
        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Please run ClientSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Sample client-related files
        $sampleFiles = [
            [
                'file_name' => 'client_contract.pdf',
                'file_path' => 'clients/contracts/client_contract.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 262144,
                'original_name' => 'consignment_agreement_2024.pdf',
                'description' => 'Signed consignment agreement'
            ],
            [
                'file_name' => 'client_photo.jpg',
                'file_path' => 'clients/photos/client_photo.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 153600,
                'original_name' => 'client_headshot.jpg',
                'description' => 'Client profile photo'
            ],
            [
                'file_name' => 'collection_inventory.xlsx',
                'file_path' => 'clients/inventories/collection_inventory.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => 327680,
                'original_name' => 'sports_card_collection_list.xlsx',
                'description' => 'Detailed inventory of client\'s sports card collection'
            ],
            [
                'file_name' => 'identification_doc.png',
                'file_path' => 'clients/documents/identification_doc.png',
                'mime_type' => 'image/png',
                'file_size' => 204800,
                'original_name' => 'driver_license.png',
                'description' => 'Client identification document'
            ],
            [
                'file_name' => 'payment_receipt.pdf',
                'file_path' => 'clients/payments/payment_receipt.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 131072,
                'original_name' => 'payment_confirmation.pdf',
                'description' => 'Payment receipt for consignment fees'
            ],
            [
                'file_name' => 'collection_photos.zip',
                'file_path' => 'clients/photos/collection_photos.zip',
                'mime_type' => 'application/zip',
                'file_size' => 2097152,
                'original_name' => 'high_value_cards_photos.zip',
                'description' => 'High-resolution photos of high-value cards'
            ],
            [
                'file_name' => 'appraisal_report.pdf',
                'file_path' => 'clients/appraisals/appraisal_report.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 409600,
                'original_name' => 'professional_appraisal.pdf',
                'description' => 'Professional appraisal report for collection'
            ],
            [
                'file_name' => 'insurance_policy.pdf',
                'file_path' => 'clients/insurance/insurance_policy.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 294912,
                'original_name' => 'collection_insurance.pdf',
                'description' => 'Insurance policy documentation'
            ]
        ];

        // Create media for about 80% of clients
        $clientsWithMedia = $clients->random((int)($clients->count() * 0.8));

        foreach ($clientsWithMedia as $client) {
            // Each client can have 1-5 media files
            $mediaCount = rand(1, 5);
            
            for ($i = 0; $i < $mediaCount; $i++) {
                $fileData = Arr::random($sampleFiles);
                $user = $users->random();
                
                $media = [
                    'client_id' => $client->id,
                    'user_id' => $user->id,
                    'file_name' => $fileData['file_name'],
                    'file_path' => $fileData['file_path'],
                    'mime_type' => $fileData['mime_type'],
                    'file_size' => $fileData['file_size'],
                    'original_name' => $fileData['original_name'],
                    'description' => $fileData['description'],
                    'order' => $i + 1,
                    'is_primary' => $i === 0, // First file is primary
                    'created_at' => now()->subMinutes(rand(1, 4320)),
                    'updated_at' => now(),
                ];

                ClientMedia::create($media);
            }
        }
    }
}