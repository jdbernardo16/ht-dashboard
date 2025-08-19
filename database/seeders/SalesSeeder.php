<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('role', ['admin', 'manager', 'va'])->get();
        $clients = User::where('role', 'client')->get();

        $productServices = [
            'Digital Marketing Package',
            'Website Development',
            'Social Media Management',
            'SEO Optimization',
            'Content Creation',
            'Email Marketing Campaign',
            'Brand Strategy Consultation',
            'E-commerce Setup',
            'Lead Generation Service',
            'Business Coaching',
            'Graphic Design Package',
            'Video Production',
            'PPC Campaign Management',
            'Sales Funnel Creation',
            'Online Course Development',
        ];

        $saleTypes = ['Cards', 'Listings', 'VA', 'Consigned'];
        $paymentMethods = ['cash', 'card', 'online'];
        $statuses = ['pending', 'completed', 'cancelled'];

        // Create sales for the last 6 months
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();

        foreach ($users as $user) {
            // Create 15-25 sales per user
            $salesCount = rand(15, 25);

            for ($i = 0; $i < $salesCount; $i++) {
                $saleDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );

                $amount = rand(500, 15000) + (rand(0, 99) / 100);
                $status = $statuses[array_rand($statuses)];

                Sale::create([
                    'user_id' => $user->id,
                    'client_id' => $clients->random()->id,
                    'type' => $saleTypes[array_rand($saleTypes)],
                    'product_name' => $productServices[array_rand($productServices)],
                    'amount' => $amount,
                    'sale_date' => $saleDate,
                    'description' => $this->generateSaleDescription(),
                    'status' => $status,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);
            }
        }

        // Create some high-value sales
        $highValueSales = [
            [
                'user_id' => $users->where('role', 'manager')->first()->id,
                'client_id' => $clients->first()->id,
                'type' => 'Listings',
                'product_name' => 'Complete Business Transformation Package',
                'amount' => 25000.00,
                'sale_date' => Carbon::now()->subDays(10),
                'description' => 'Comprehensive 6-month business transformation including strategy, marketing, and operations optimization',
                'status' => 'completed',
                'payment_method' => 'online',
            ],
            [
                'user_id' => $users->where('role', 'admin')->first()->id,
                'client_id' => $clients->last()->id,
                'type' => 'Cards',
                'product_name' => 'Enterprise Digital Marketing Suite',
                'amount' => 15000.00,
                'sale_date' => Carbon::now()->subDays(5),
                'description' => 'Full-scale digital marketing implementation for enterprise client',
                'status' => 'completed',
                'payment_method' => 'online',
            ],
        ];

        foreach ($highValueSales as $sale) {
            Sale::create($sale);
        }
    }

    /**
     * Generate realistic sale descriptions
     */
    private function generateSaleDescription(): string
    {
        $descriptions = [
            'Monthly retainer for ongoing services',
            'One-time project completion payment',
            'Initial consultation and strategy session',
            'Quarterly service package renewal',
            'Custom project based on client requirements',
            'Standard service package with custom modifications',
            'Rush delivery service with expedited timeline',
            'Premium service tier with additional features',
            'Basic service package for new client onboarding',
            'Advanced service package with extended support',
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
