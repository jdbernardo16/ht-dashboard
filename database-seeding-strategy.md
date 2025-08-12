# Database Seeding Strategy

## Overview

This document outlines the comprehensive database seeding strategy for the Hidden Treasures dashboard, including sample data for all modules and role-based user accounts.

## Seeding Structure

### 1. User Seeding Strategy

#### 1.1 Role-Based User Accounts

```php
// DatabaseSeeder.php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        SaleSeeder::class,
        ContentPostSeeder::class,
        ExpenseSeeder::class,
        GoalSeeder::class,
        TaskSeeder::class,
    ]);
}
```

#### 1.2 User Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        User::create([
            'full_name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@hiddentreasures.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'avatar_url' => 'https://ui-avatars.com/api/?name=Admin+User&background=3b82f6&color=fff',
        ]);

        // Create Manager User
        User::create([
            'full_name' => 'Manager User',
            'first_name' => 'Manager',
            'last_name' => 'User',
            'email' => 'manager@hiddentreasures.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'avatar_url' => 'https://ui-avatars.com/api/?name=Manager+User&background=10b981&color=fff',
        ]);

        // Create VA User
        User::create([
            'full_name' => 'VA User',
            'first_name' => 'VA',
            'last_name' => 'User',
            'email' => 'va@hiddentreasures.com',
            'password' => Hash::make('password'),
            'role' => 'va',
            'avatar_url' => 'https://ui-avatars.com/api/?name=VA+User&background=f59e0b&color=fff',
        ]);

        // Create Additional Users
        User::factory()->count(5)->create(['role' => 'manager']);
        User::factory()->count(10)->create(['role' => 'va']);
    }
}
```

### 2. Module-Specific Seeding

#### 2.1 Sales Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // Create sales for each user
        foreach ($users as $user) {
            // Create 20-50 sales per user
            $salesCount = rand(20, 50);

            for ($i = 0; $i < $salesCount; $i++) {
                Sale::create([
                    'user_id' => $user->id,
                    'type' => $this->getRandomSaleType(),
                    'amount' => $this->getRandomAmount(),
                    'date' => $this->getRandomDate(),
                    'description' => $this->getRandomDescription('sale'),
                ]);
            }
        }
    }

    private function getRandomSaleType(): string
    {
        $types = [
            'product',
            'service',
            'consultation',
            'course',
            'membership',
            'digital_product',
            'coaching',
            'affiliate'
        ];

        return $types[array_rand($types)];
    }

    private function getRandomAmount(): float
    {
        // Generate amounts between $10 and $5000
        return round(rand(1000, 500000) / 100, 2);
    }

    private function getRandomDate(): string
    {
        // Generate dates within the last 6 months
        return now()
            ->subDays(rand(1, 180))
            ->format('Y-m-d');
    }

    private function getRandomDescription(string $type): string
    {
        $descriptions = [
            'sale' => [
                'Product sale to new customer',
                'Service package sold',
                'Consultation session',
                'Online course purchase',
                'Monthly membership renewal',
                'Digital product download',
                'Coaching program enrollment',
                'Affiliate commission earned'
            ]
        ];

        return $descriptions[$type][array_rand($descriptions[$type])];
    }
}
```

#### 2.2 Content Post Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\ContentPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentPostSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $platforms = ['Instagram', 'Facebook', 'Twitter', 'LinkedIn', 'TikTok', 'YouTube'];

        foreach ($users as $user) {
            // Create 10-30 content posts per user
            $postsCount = rand(10, 30);

            for ($i = 0; $i < $postsCount; $i++) {
                $platform = $platforms[array_rand($platforms)];

                ContentPost::create([
                    'user_id' => $user->id,
                    'platform' => $platform,
                    'post_count' => rand(1, 10),
                    'date' => $this->getRandomDate(),
                    'engagement_metrics' => $this->generateEngagementMetrics($platform),
                ]);
            }
        }
    }

    private function getRandomDate(): string
    {
        return now()
            ->subDays(rand(1, 90))
            ->format('Y-m-d');
    }

    private function generateEngagementMetrics(string $platform): array
    {
        $baseMetrics = [
            'likes' => rand(10, 1000),
            'shares' => rand(1, 100),
            'comments' => rand(0, 50),
            'views' => rand(100, 10000),
        ];

        // Platform-specific adjustments
        switch ($platform) {
            case 'Instagram':
                $baseMetrics['saves'] = rand(5, 200);
                break;
            case 'Facebook':
                $baseMetrics['reactions'] = rand(5, 200);
                break;
            case 'Twitter':
                $baseMetrics['retweets'] = rand(1, 50);
                break;
            case 'LinkedIn':
                $baseMetrics['clicks'] = rand(10, 500);
                break;
            case 'TikTok':
                $baseMetrics['shares'] *= 3;
                break;
            case 'YouTube':
                $baseMetrics['subscribers'] = rand(1, 20);
                break;
        }

        return $baseMetrics;
    }
}
```

#### 2.3 Expense Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $categories = [
            'Marketing',
            'Software',
            'Hardware',
            'Office Supplies',
            'Travel',
            'Training',
            'Advertising',
            'Professional Services',
            'Utilities',
            'Insurance'
        ];

        foreach ($users as $user) {
            // Create 15-40 expenses per user
            $expensesCount = rand(15, 40);

            for ($i = 0; $i < $expensesCount; $i++) {
                Expense::create([
                    'user_id' => $user->id,
                    'category' => $categories[array_rand($categories)],
                    'amount' => $this->getRandomExpenseAmount(),
                    'date' => $this->getRandomDate(),
                    'description' => $this->getRandomExpenseDescription(),
                ]);
            }
        }
    }

    private function getRandomExpenseAmount(): float
    {
        // Generate amounts between $5 and $2000
        return round(rand(500, 200000) / 100, 2);
    }

    private function getRandomDate(): string
    {
        return now()
            ->subDays(rand(1, 180))
            ->format('Y-m-d');
    }

    private function getRandomExpenseDescription(): string
    {
        $descriptions = [
            'Monthly software subscription',
            'Office supplies purchase',
            'Marketing campaign costs',
            'Travel expenses for client meeting',
            'Professional development course',
            'Hardware upgrade',
            'Advertising spend',
            'Consultant fees',
            'Utility bills',
            'Insurance premium'
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
```

#### 2.4 Goal Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Seeder;

class GoalSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $currentYear = now()->year;
        $quarters = [1, 2, 3, 4];

        foreach ($users as $user) {
            // Create 2-5 goals per user per quarter
            foreach ($quarters as $quarter) {
                $goalsCount = rand(2, 5);

                for ($i = 0; $i < $goalsCount; $i++) {
                    $targetValue = $this->getRandomTargetValue();
                    $currentValue = rand(0, $targetValue);

                    Goal::create([
                        'user_id' => $user->id,
                        'title' => $this->getRandomGoalTitle(),
                        'description' => $this->getRandomGoalDescription(),
                        'target_value' => $targetValue,
                        'current_value' => $currentValue,
                        'quarter' => $quarter,
                        'year' => $currentYear,
                        'deadline' => $this->getQuarterEndDate($quarter, $currentYear),
                    ]);
                }
            }
        }
    }

    private function getRandomTargetValue(): float
    {
        // Generate target values between $1000 and $50000
        return round(rand(100000, 5000000) / 100, 2);
    }

    private function getRandomGoalTitle(): string
    {
        $titles = [
            'Increase monthly revenue',
            'Launch new product line',
            'Grow social media following',
            'Improve customer retention',
            'Reduce operational costs',
            'Expand market reach',
            'Launch marketing campaign',
            'Develop new partnerships',
            'Increase conversion rates',
            'Build email list'
        ];

        return $titles[array_rand($titles)];
    }

    private function getRandomGoalDescription(): string
    {
        return 'This is a strategic goal focused on business growth and development.';
    }

    private function getQuarterEndDate(int $quarter, int $year): string
    {
        $month = $quarter * 3;
        return date('Y-m-d', strtotime("last day of {$year}-{$month}"));
    }
}
```

#### 2.5 Task Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        foreach ($users as $user) {
            // Create 10-25 tasks per user
            $tasksCount = rand(10, 25);

            for ($i = 0; $i < $tasksCount; $i++) {
                $status = $statuses[array_rand($statuses)];
                $dueDate = $this->getRandomDueDate();

                Task::create([
                    'title' => $this->getRandomTaskTitle(),
                    'description' => $this->getRandomTaskDescription(),
                    'status' => $status,
                    'priority' => $priorities[array_rand($priorities)],
                    'assigned_to' => $user->id,
                    'due_date' => $dueDate,
                    'completed_at' => $status === 'completed' ? $this->getRandomCompletionDate($dueDate) : null,
                ]);
            }
        }
    }

    private function getRandomTaskTitle(): string
    {
        $titles = [
            'Review monthly sales report',
            'Update website content',
            'Prepare client presentation',
            'Send marketing emails',
            'Analyze competitor data',
            'Create social media posts',
            'Update expense tracking',
            'Review goal progress',
            'Schedule team meeting',
            'Prepare quarterly report',
            'Update CRM data',
            'Create marketing materials',
            'Review customer feedback',
            'Update pricing strategy',
            'Plan product launch'
        ];

        return $titles[array_rand($titles)];
    }

    private function getRandomTaskDescription(): string
    {
        return 'This task requires careful attention to detail and timely completion.';
    }

    private function getRandomDueDate(): string
    {
        // Due dates within next 30 days
        return now()
            ->addDays(rand(1, 30))
            ->format('Y-m-d');
    }

    private function getRandomCompletionDate(string $dueDate): string
    {
        // Completed within 7 days before or after due date
        $dueTimestamp = strtotime($dueDate);
        $offset = rand(-7, 7) * 86400; // 7 days in seconds

        return date('Y-m-d H:i:s', $dueTimestamp + $offset);
    }
}
```

### 3. Running the Seeders

#### 3.1 Development Environment

```bash
# Run all seeders
php artisan db:seed

# Run specific seeders
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=SaleSeeder
php artisan db:seed --class=ContentPostSeeder
php artisan db:seed --class=ExpenseSeeder
php artisan db:seed --class=GoalSeeder
php artisan db:seed --class=TaskSeeder

# Refresh and re-seed
php artisan migrate:fresh --seed
```

#### 3.2 Production Environment

```bash
# Create production seeder with minimal data
php artisan db:seed --class=ProductionSeeder
```

### 4. Testing Data

#### 4.1 Test User Credentials

| Role    | Email                       | Password |
| ------- | --------------------------- | -------- |
| Admin   | admin@hiddentreasures.com   | password |
| Manager | manager@hiddentreasures.com | password |
| VA      | va@hiddentreasures.com      | password |

#### 4.2 Sample Data Statistics

-   **Users**: 18 total (1 admin, 6 managers, 11 VAs)
-   **Sales**: ~1000 records (20-50 per user)
-   **Content Posts**: ~500 records (10-30 per user)
-   **Expenses**: ~750 records (15-40 per user)
-   **Goals**: ~200 records (8-20 per user)
-   **Tasks**: ~400 records (10-25 per user)

### 5. Custom Seeder for Testing

#### 5.1 Testing Seeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestingSeeder extends Seeder
{
    public function run(): void
    {
        // Create minimal data for testing
        $this->call([
            UserSeeder::class,
        ]);

        // Create specific test scenarios
        $this->createTestScenarios();
    }

    private function createTestScenarios(): void
    {
        // Create test data for specific scenarios
        // E.g., overdue tasks, completed goals, etc.
    }
}
```

### 6. Data Validation

#### 6.1 Validation Rules

-   All dates are within reasonable ranges
-   Amounts are positive numbers
-   Relationships are properly maintained
-   Data consistency across modules

#### 6.2 Data Integrity Checks

```php
// Run after seeding
php artisan tinker
>>> User::count()
>>> Sale::count()
>>> ContentPost::count()
>>> Expense::count()
>>> Goal::count()
>>> Task::count()
```

### 7. Seeding Best Practices

1. **Use factories for realistic data**
2. **Maintain referential integrity**
3. **Create meaningful test scenarios**
4. **Avoid hardcoded IDs**
5. **Use transactions for large datasets**
6. **Include timestamps in seeded data**
7. **Create both development and production seeders**
8. **Document all test credentials**
9. **Include data validation in seeders**
10. **Make seeders idempotent where possible**
