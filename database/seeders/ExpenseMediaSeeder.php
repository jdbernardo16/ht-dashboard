<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use App\Models\ExpenseMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ExpenseMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all expenses and users
        $expenses = Expense::all();
        $users = User::all();
        
        if ($expenses->isEmpty()) {
            $this->command->warn('No expenses found. Please run ExpenseSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Sample expense-related files
        $sampleFiles = [
            [
                'file_name' => 'receipt_scanned.jpg',
                'file_path' => 'expenses/receipts/receipt_scanned.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 245760,
                'original_name' => 'office_supplies_receipt.jpg',
                'description' => 'Scanned receipt for office supplies'
            ],
            [
                'file_name' => 'invoice.pdf',
                'file_path' => 'expenses/invoices/invoice.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 196608,
                'original_name' => 'software_license_invoice.pdf',
                'description' => 'Invoice for software license renewal'
            ],
            [
                'file_name' => 'credit_card_statement.pdf',
                'file_path' => 'expenses/statements/credit_card_statement.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 327680,
                'original_name' => 'business_credit_card_october.pdf',
                'description' => 'Business credit card statement'
            ],
            [
                'file_name' => 'utility_bill.pdf',
                'file_path' => 'expenses/bills/utility_bill.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 163840,
                'original_name' => 'electricity_bill.pdf',
                'description' => 'Monthly electricity bill'
            ],
            [
                'file_name' => 'travel_expense_report.xlsx',
                'file_path' => 'expenses/reports/travel_expense_report.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => 262144,
                'original_name' => 'business_trip_expenses.xlsx',
                'description' => 'Travel expense report for business trip'
            ],
            [
                'file_name' => 'advertising_invoice.pdf',
                'file_path' => 'expenses/invoices/advertising_invoice.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 212992,
                'original_name' => 'social_media_ads_invoice.pdf',
                'description' => 'Invoice for social media advertising'
            ],
            [
                'file_name' => 'equipment_receipt.jpg',
                'file_path' => 'expenses/receipts/equipment_receipt.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 184320,
                'original_name' => 'scanner_purchase_receipt.jpg',
                'description' => 'Receipt for new scanner equipment'
            ],
            [
                'file_name' => 'shipping_label.pdf',
                'file_path' => 'expenses/shipping/shipping_label.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 98304,
                'original_name' => 'fedex_shipping_label.pdf',
                'description' => 'Shipping label for outgoing packages'
            ],
            [
                'file_name' => 'tax_document.pdf',
                'file_path' => 'expenses/tax/tax_document.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 393216,
                'original_name' => 'tax_form_1099.pdf',
                'description' => 'Tax documentation for contractor payments'
            ],
            [
                'file_name' => 'bank_statement.pdf',
                'file_path' => 'expenses/statements/bank_statement.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 458752,
                'original_name' => 'business_account_statement.pdf',
                'description' => 'Business bank account statement'
            ]
        ];

        // Create media for about 70% of expenses
        $expensesWithMedia = $expenses->random((int)($expenses->count() * 0.7));

        foreach ($expensesWithMedia as $expense) {
            // Each expense can have 1-3 media files
            $mediaCount = rand(1, 3);
            
            for ($i = 0; $i < $mediaCount; $i++) {
                $fileData = Arr::random($sampleFiles);
                $user = $users->random();
                
                $media = [
                    'expense_id' => $expense->id,
                    'user_id' => $user->id,
                    'file_name' => $fileData['file_name'],
                    'file_path' => $fileData['file_path'],
                    'mime_type' => $fileData['mime_type'],
                    'file_size' => $fileData['file_size'],
                    'original_name' => $fileData['original_name'],
                    'description' => $fileData['description'],
                    'order' => $i + 1,
                    'is_primary' => $i === 0, // First file is primary
                    'created_at' => now()->subMinutes(rand(1, 2880)),
                    'updated_at' => now(),
                ];

                ExpenseMedia::create($media);
            }
        }
    }
}