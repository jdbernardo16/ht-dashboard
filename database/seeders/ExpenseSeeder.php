<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('role', ['admin', 'manager', 'va'])->get();

        $expenseCategories = [
            'Labor',
            'Software',
            'Table',
            'Advertising',
            'Office Supplies',
            'Travel',
            'Utilities',
            'Marketing',
            'Inventory',
        ];

        $paymentMethods = ['cash', 'card', 'online', 'bank_transfer'];
        $statuses = ['pending', 'paid', 'cancelled'];

        // Create expenses for the last 3 months
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        foreach ($users as $user) {
            // Create 20-30 expenses per user
            $expensesCount = rand(20, 30);

            for ($i = 0; $i < $expensesCount; $i++) {
                $expenseDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );

                $amount = rand(50, 2000) + (rand(0, 99) / 100);
                $category = $expenseCategories[array_rand($expenseCategories)];
                $status = $statuses[array_rand($statuses)];

                Expense::create([
                    'user_id' => $user->id,
                    'title' => $this->generateExpenseTitle($category),
                    'category' => $category,
                    'amount' => $amount,
                    'description' => $this->generateExpenseDescription($category),
                    'expense_date' => $expenseDate,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'receipt_number' => null,
                    'status' => $status,
                    'notes' => $this->generateExpenseNotes($category),
                    'tax_amount' => null,
                    'merchant' => $this->generateVendorName($category),
                    'created_at' => $expenseDate,
                    'updated_at' => $expenseDate,
                ]);
            }
        }

        // Create some recurring monthly expenses
        $recurringExpenses = [
            [
                'user_id' => $users->first()->id,
                'title' => 'Adobe Creative Cloud Subscription',
                'category' => 'Software',
                'amount' => 299.00,
                'description' => 'Adobe Creative Cloud subscription',
                'expense_date' => Carbon::now()->subDays(5),
                'payment_method' => 'card',
                'status' => 'paid',
                'notes' => 'Monthly subscription for design tools',
                'tax_amount' => null,
                'merchant' => 'Adobe Inc.',
            ],
            [
                'user_id' => $users->first()->id,
                'title' => 'Internet Service',
                'category' => 'Utilities',
                'amount' => 150.00,
                'description' => 'High-speed internet service',
                'expense_date' => Carbon::now()->subDays(3),
                'payment_method' => 'bank_transfer',
                'status' => 'paid',
                'notes' => 'Monthly internet service for office',
                'tax_amount' => null,
                'merchant' => 'Comcast Business',
            ],
            [
                'user_id' => $users->last()->id,
                'title' => 'Legal Retainer Fee',
                'category' => 'Labor',
                'amount' => 500.00,
                'description' => 'Monthly legal retainer',
                'expense_date' => Carbon::now()->subDays(7),
                'payment_method' => 'bank_transfer',
                'status' => 'paid',
                'notes' => 'Legal retainer for contract support',
                'tax_amount' => null,
                'merchant' => 'Smith & Associates Law Firm',
            ],
        ];

        foreach ($recurringExpenses as $expense) {
            Expense::create($expense);
        }
    }

    /**
     * Generate realistic expense descriptions
     */
    private function generateExpenseDescription(string $category): string
    {
        $descriptions = [
            'Labor' => [
                'Contractor payment for services',
                'Hourly contractor payment',
                'Freelance developer invoice',
                'Legal retainer fee',
                'Consulting services',
            ],
            'Software' => [
                'Monthly subscription for project management tool',
                'Annual license for design software',
                'CRM system monthly fee',
                'Analytics tool subscription',
                'Video editing software purchase',
            ],
            'Table' => [
                'Office table purchase',
                'Conference room table purchase',
                'Replacement table for workstations',
                'Ergonomic standing desk purchase',
                'Office furniture expense',
            ],
            'Advertising' => [
                'Facebook ad campaign for new product launch',
                'Google Ads budget for Q3 promotion',
                'Instagram influencer collaboration',
                'LinkedIn sponsored content',
                'Outdoor advertising campaign',
            ],
            'Office Supplies' => [
                'Printer ink and paper restock',
                'Office furniture for new team member',
                'Stationery and organizational supplies',
                'Whiteboard and presentation materials',
                'Coffee and refreshments for team',
            ],
            'Travel' => [
                'Flight to client meeting in New York',
                'Hotel accommodation for business trip',
                'Uber rides to client presentations',
                'Gas and parking for local client visits',
                'Conference registration and travel',
            ],
            'Utilities' => [
                'High-speed internet service',
                'Electricity bill for office',
                'Water service charge',
                'Office phone and internet line',
                'Cloud hosting and infrastructure fees',
            ],
            'Marketing' => [
                'Brand strategy consultation',
                'Email marketing campaign setup',
                'Content creation for marketing',
                'Lead generation service',
                'Marketing collateral design',
            ],
            'Inventory' => [
                'Office supplies restock',
                'Product inventory purchase',
                'Stock replenishment order',
                'Merchandise inventory acquisition',
                'Supply chain restocking',
            ],
        ];

        // Return a category-specific description if available
        if (isset($descriptions[$category]) && is_array($descriptions[$category]) && count($descriptions[$category]) > 0) {
            $list = $descriptions[$category];
            return $list[array_rand($list)];
        }

        // Fallback: pick any description from the pool, or return a generic default
        $all = [];
        foreach ($descriptions as $arr) {
            if (is_array($arr)) {
                $all = array_merge($all, $arr);
            }
        }

        return count($all) ? $all[array_rand($all)] : 'General business expense';
    }

    /**
     * Generate expense titles
     */
    private function generateExpenseTitle(string $category): string
    {
        $titles = [
            'Labor' => [
                'Contractor Payment',
                'Freelancer Invoice',
                'Consulting Services',
                'Legal Retainer',
                'Hourly Services'
            ],
            'Software' => [
                'Software Subscription',
                'Annual License',
                'Tool Subscription',
                'App Purchase',
                'Platform Fee'
            ],
            'Table' => [
                'Office Furniture',
                'Conference Table',
                'Workstation Desk',
                'Standing Desk',
                'Meeting Table'
            ],
            'Advertising' => [
                'Ad Campaign',
                'Marketing Promotion',
                'Social Media Ads',
                'Online Advertising',
                'Brand Campaign'
            ],
            'Office Supplies' => [
                'Office Supplies',
                'Stationery Purchase',
                'Printer Supplies',
                'Office Materials',
                'Work Supplies'
            ],
            'Travel' => [
                'Business Travel',
                'Client Meeting Trip',
                'Conference Travel',
                'Work Trip',
                'Business Journey'
            ],
            'Utilities' => [
                'Utility Bill',
                'Internet Service',
                'Electricity Payment',
                'Phone Service',
                'Cloud Hosting'
            ],
            'Marketing' => [
                'Marketing Campaign',
                'Brand Strategy',
                'Lead Generation',
                'Content Creation',
                'Email Marketing'
            ],
            'Inventory' => [
                'Inventory Restock',
                'Supply Purchase',
                'Stock Replenishment',
                'Materials Order',
                'Product Inventory'
            ],
        ];

        // Return a category-specific title if available
        if (isset($titles[$category]) && is_array($titles[$category]) && count($titles[$category]) > 0) {
            $list = $titles[$category];
            return $list[array_rand($list)];
        }

        // Fallback: pick any title from the pool, or return a generic default
        $all = [];
        foreach ($titles as $arr) {
            if (is_array($arr)) {
                $all = array_merge($all, $arr);
            }
        }

        return count($all) ? $all[array_rand($all)] : 'Business Expense';
    }

    /**
     * Generate expense notes
     */
    private function generateExpenseNotes(string $category): string
    {
        $notes = [
            'Labor' => 'Contractor invoice processed',
            'Software' => 'Essential tool for daily operations',
            'Table' => 'Office furniture purchase recorded',
            'Advertising' => 'Approved by marketing manager for campaign',
            'Office Supplies' => 'Standard monthly office supply restock',
            'Travel' => 'Business travel approved by supervisor',
            'Utilities' => 'Monthly utility bill for office',
            'Marketing' => 'Marketing activity approved by manager',
            'Inventory' => 'Inventory restock for business operations',
        ];

        return $notes[$category] ?? 'Standard business expense';
    }

    /**
     * Generate vendor names
     */
    private function generateVendorName(string $category): string
    {
        $vendors = [
            'Labor' => ['Upwork', 'Local Contractor', 'Freelancer Inc.', 'Staffing Agency', 'Contractor Co.'],
            'Software' => ['Adobe', 'Microsoft', 'Slack', 'Zoom', 'Dropbox'],
            'Table' => ['IKEA', 'Staples', 'Office Depot', 'Amazon Business', 'Herman Miller'],
            'Advertising' => ['Facebook Ads', 'Google Ads', 'LinkedIn', 'Instagram', 'Mailchimp'],
            'Office Supplies' => ['Staples', 'Office Depot', 'Amazon Business', 'Costco', 'Target'],
            'Travel' => ['Delta Airlines', 'Marriott', 'Uber', 'Enterprise', 'Southwest'],
            'Utilities' => ['Comcast Business', 'Local Power Co.', 'Verizon', 'AT&T', 'Cloud Provider'],
            'Marketing' => ['Local Marketing Agency', 'Growth Agency', 'Marketing Consultants', 'Agency Co.', 'Freelance Marketer'],
            'Inventory' => ['Office Supply Co.', 'Wholesale Distributor', 'Supply Chain Partner', 'Inventory Vendor', 'Business Supplies Inc.'],
        ];

        // Return a category-specific vendor if available
        if (isset($vendors[$category]) && is_array($vendors[$category]) && count($vendors[$category]) > 0) {
            $list = $vendors[$category];
            return $list[array_rand($list)];
        }

        // Fallback: pick any vendor from the pool, or return a generic default
        $all = [];
        foreach ($vendors as $v) {
            if (is_array($v)) {
                $all = array_merge($all, $v);
            }
        }

        return count($all) ? $all[array_rand($all)] : 'Business Vendor';
    }
}
