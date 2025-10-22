<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Category;
use App\Models\ContentPost;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\Notification;
use App\Models\Sale;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class TestObservers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:observers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test model observers by creating sample data and checking notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Testing Model Observers ===');
        $this->line('');

        try {
            // Clear any existing notifications for clean testing
            Notification::query()->delete();
            $this->info('✓ Cleared existing notifications');

            // Get test users
            $admin = User::where('role', 'admin')->first();
            $manager = User::where('role', 'manager')->first();
            $va = User::where('role', 'va')->first();

            if (!$admin || !$manager || !$va) {
                $this->error('Error: Required test users not found. Please ensure you have admin, manager, and va users.');
                return 1;
            }

            $this->info('Found test users:');
            $this->line("- Admin: {$admin->name} (ID: {$admin->id})");
            $this->line("- Manager: {$manager->name} (ID: {$manager->id})");
            $this->line("- VA: {$va->name} (ID: {$va->id})");
            $this->line('');

            // Test 1: Task Observer
            $this->info('=== Testing Task Observer ===');

            // Create a task assigned to VA
            $task = Task::create([
                'title' => 'Test Task for Observer',
                'description' => 'This is a test task to verify observer functionality',
                'status' => 'pending',
                'priority' => 'high',
                'user_id' => $manager->id,
                'assigned_to' => $va->id,
                'due_date' => now()->addDays(3),
            ]);

            $this->info('✓ Created high-priority task assigned to VA');

            // Update task to completed
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
                'actual_hours' => 2.5,
            ]);

            $this->info('✓ Updated task to completed status');

            // Test 2: Sale Observer
            $this->line('');
            $this->info('=== Testing Sale Observer ===');

            // Get or create a test client
            $client = Client::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'first_name' => 'Test',
                    'last_name' => 'Client',
                    'phone' => '123-456-7890',
                    'company' => 'Test Company'
                ]
            );

            // Create a regular sale
            $sale = Sale::create([
                'user_id' => $va->id,
                'client_id' => $client->id,
                'type' => 'Cards',
                'product_name' => 'Test Sports Card',
                'amount' => 250.00,
                'sale_date' => now(),
                'description' => 'Test sale for observer',
            ]);

            $this->info('✓ Created regular sale');

            // Create a high-value sale
            $highValueSale = Sale::create([
                'user_id' => $va->id,
                'client_id' => $client->id,
                'type' => 'Cards',
                'product_name' => 'Test Vintage Card',
                'amount' => 1500.00,
                'sale_date' => now(),
                'description' => 'High-value test sale for observer',
            ]);

            $this->info('✓ Created high-value sale');

            // Test 3: Expense Observer
            $this->line('');
            $this->info('=== Testing Expense Observer ===');

            // Create a regular expense
            $expense = Expense::create([
                'user_id' => $va->id,
                'title' => 'Office Supplies',
                'category' => 'Office Supplies',
                'amount' => 45.50,
                'expense_date' => now(),
                'description' => 'Test expense for observer',
                'status' => 'pending',
                'payment_method' => 'card',
            ]);

            $this->info('✓ Created regular expense');

            // Create a high-value expense
            $highValueExpense = Expense::create([
                'user_id' => $manager->id,
                'title' => 'Software Purchase',
                'category' => 'Software',
                'amount' => 750.00,
                'expense_date' => now(),
                'description' => 'High-value expense for observer',
                'status' => 'pending',
                'payment_method' => 'bank_transfer',
            ]);

            $this->info('✓ Created high-value expense');

            // Approve the expense
            $expense->update(['status' => 'paid']);
            $this->info('✓ Approved expense');

            // Test 4: Goal Observer
            $this->line('');
            $this->info('=== Testing Goal Observer ===');

            // Create a goal
            $goal = Goal::create([
                'user_id' => $va->id,
                'title' => 'Test Sales Goal',
                'description' => 'Test goal for observer',
                'target_value' => 1000.00,
                'current_value' => 0,
                'type' => 'sales',
                'priority' => 'high',
                'status' => 'not_started',
                'quarter' => 1,
                'year' => date('Y'),
                'deadline' => now()->addMonths(3),
            ]);

            $this->info('✓ Created goal');

            // Update goal progress to trigger milestone
            $goal->update(['current_value' => 300.00]); // 30% - should trigger 25% milestone
            $this->info('✓ Updated goal to 30% (should trigger 25% milestone)');

            $goal->update(['current_value' => 600.00]); // 60% - should trigger 50% milestone
            $this->info('✓ Updated goal to 60% (should trigger 50% milestone)');

            $goal->update(['current_value' => 1000.00, 'status' => 'completed']); // 100% - should trigger completion
            $this->info('✓ Updated goal to 100% completed');

            // Test 5: ContentPost Observer
            $this->line('');
            $this->info('=== Testing ContentPost Observer ===');

            // Get or create a test category
            $category = Category::firstOrCreate(
                ['name' => 'Test Category for Observers'],
                [
                    'type' => 'content',
                    'slug' => 'test-category-observers'
                ]
            );

            // Create a content post
            $contentPost = ContentPost::create([
                'user_id' => $va->id,
                'client_id' => $client->id,
                'category_id' => $category->id,
                'platform' => ['instagram', 'facebook'],
                'content_type' => 'image',
                'title' => 'Test Content for Observer',
                'description' => 'Test content post to verify observer functionality',
                'status' => 'draft',
                'scheduled_date' => now()->addDay(),
            ]);

            $this->info('✓ Created content post');

            // Schedule the content
            $contentPost->update(['status' => 'scheduled']);
            $this->info('✓ Scheduled content post');

            // Publish the content
            $contentPost->update([
                'status' => 'published',
                'published_date' => now(),
                'content_url' => 'https://instagram.com/p/test123',
            ]);
            $this->info('✓ Published content post');

            // Add high engagement metrics
            $contentPost->update([
                'engagement_metrics' => [
                    'views' => 500,
                    'likes' => 75,
                    'comments' => 25,
                    'shares' => 15,
                ]
            ]);
            $this->info('✓ Added high engagement metrics');

            // Check notifications created
            $this->line('');
            $this->info('=== Checking Notifications Created ===');

            $notifications = Notification::all();

            $this->info("Total notifications created: {$notifications->count()}");
            $this->line('');

            // Group notifications by type
            $notificationsByType = $notifications->groupBy('type');

            foreach ($notificationsByType as $type => $typeNotifications) {
                $this->info("{$type}: {$typeNotifications->count()} notifications");
                foreach ($typeNotifications as $notification) {
                    $this->line("  - {$notification->title} (User ID: {$notification->user_id})");
                }
                $this->line('');
            }

            // Test specific user notifications
            $this->info('=== Notifications by User ===');

            $users = [$admin, $manager, $va];
            foreach ($users as $user) {
                $userNotifications = $notifications->where('user_id', $user->id);
                $this->info("{$user->name} ({$user->role}): {$userNotifications->count()} notifications");
                foreach ($userNotifications as $notification) {
                    $this->line("  - {$notification->title}: {$notification->message}");
                }
                $this->line('');
            }

            $this->info('=== Observer Test Complete ===');
            $this->info('All observer tests completed successfully!');
            $this->info('Check the notifications above to verify proper observer functionality.');

            return 0;

        } catch (\Exception $e) {
            $this->error("Error during testing: " . $e->getMessage());
            $this->error("Stack trace:\n" . $e->getTraceAsString());
            return 1;
        }
    }
}