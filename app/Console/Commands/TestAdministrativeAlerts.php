<?php

namespace App\Console\Commands;

use App\Events\Security\SecurityFailedLoginEvent;
use App\Events\Security\SecurityAccessViolationEvent;
use App\Events\Security\SecurityAdminAccountModifiedEvent;
use App\Events\Security\SecuritySuspiciousSessionEvent;
use App\Events\System\SystemDatabaseFailureEvent;
use App\Events\System\SystemFileUploadFailureEvent;
use App\Events\System\SystemQueueFailureEvent;
use App\Events\System\SystemPerformanceIssueEvent;
use App\Events\UserAction\UserAccountDeletedEvent;
use App\Events\UserAction\UserBulkOperationEvent;
use App\Events\UserAction\UserMassContentDeletionEvent;
use App\Events\UserAction\UserGoalFailedEvent;
use App\Events\Business\BusinessHighValueSaleEvent;
use App\Events\Business\BusinessUnusualExpenseEvent;
use App\Events\Business\BusinessPaymentStatusChangedEvent;
use App\Events\Business\BusinessClientDeletedEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Command to test all administrative alert events
 * 
 * This command allows you to trigger each type of administrative alert
 * to verify that the listeners and notifications are working correctly.
 */
class TestAdministrativeAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-administrative-alerts {--type=all : Type of alert to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all administrative alert events';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        
        $this->info('Testing Administrative Alert System...');
        $this->line('=' . str_repeat('=', 50));
        
        // Get a test user
        $user = User::where('role', 'admin')->first();
        if (!$user) {
            $this->error('No admin user found. Please create an admin user first.');
            return Command::FAILURE;
        }
        
        $this->line("Using admin user: {$user->name} ({$user->email})");
        $this->line('');
        
        if ($type === 'all') {
            $this->testAllAlerts($user);
        } else {
            $this->testSpecificAlert($type, $user);
        }
        
        $this->line('');
        $this->info('Administrative alert testing completed!');
        $this->info('Check your logs and notifications to verify the alerts were triggered.');
        
        return Command::SUCCESS;
    }
    
    /**
     * Test all types of administrative alerts
     */
    private function testAllAlerts(User $user): void
    {
        $this->info('Testing ALL administrative alert types...');
        $this->line('');
        
        // Security Events
        $this->testSecurityEvents($user);
        
        // System Events
        $this->testSystemEvents($user);
        
        // User Action Events
        $this->testUserActionEvents($user);
        
        // Business Events
        $this->testBusinessEvents($user);
    }
    
    /**
     * Test security-related events
     */
    private function testSecurityEvents(User $user): void
    {
        $this->info('Testing Security Events:');
        $this->line('------------------------');
        
        // Failed Login Event
        $this->line('1. Testing Failed Login Event...');
        Event::dispatch(new SecurityFailedLoginEvent(
            'test@example.com',
            '192.168.1.100',
            'Test Browser',
            3,
            false,
            ['city' => 'Test City', 'country' => 'Test Country'],
            $user
        ));
        $this->line('   ✓ Failed Login Event triggered');
        
        // Access Violation Event
        $this->line('2. Testing Access Violation Event...');
        Event::dispatch(new SecurityAccessViolationEvent(
            'admin.dashboard',
            'GET',
            'User attempted to access admin dashboard without privileges',
            ['user_id' => $user->id],
            $user
        ));
        $this->line('   ✓ Access Violation Event triggered');
        
        // Admin Account Modified Event
        $this->line('3. Testing Admin Account Modified Event...');
        Event::dispatch(new SecurityAdminAccountModifiedEvent(
            $user,
            ['role' => ['old' => 'admin', 'new' => 'super_admin']],
            $user
        ));
        $this->line('   ✓ Admin Account Modified Event triggered');
        
        // Suspicious Session Event
        $this->line('4. Testing Suspicious Session Event...');
        Event::dispatch(new SecuritySuspiciousSessionEvent(
            'Multiple failed login attempts from different IPs',
            ['ip_addresses' => ['192.168.1.100', '192.168.1.101']],
            $user
        ));
        $this->line('   ✓ Suspicious Session Event triggered');
        
        $this->line('');
    }
    
    /**
     * Test system-related events
     */
    private function testSystemEvents(User $user): void
    {
        $this->info('Testing System Events:');
        $this->line('-----------------------');
        
        // Database Failure Event
        $this->line('1. Testing Database Failure Event...');
        Event::dispatch(new SystemDatabaseFailureEvent(
            'SELECT * FROM users WHERE id = ?',
            'Connection refused',
            ['bindings' => [1]],
            $user
        ));
        $this->line('   ✓ Database Failure Event triggered');
        
        // File Upload Failure Event
        $this->line('2. Testing File Upload Failure Event...');
        Event::dispatch(new SystemFileUploadFailureEvent(
            'test-image.jpg',
            'File size exceeds maximum allowed size',
            ['file_size' => 10485760, 'max_size' => 5242880],
            $user
        ));
        $this->line('   ✓ File Upload Failure Event triggered');
        
        // Queue Failure Event
        $this->line('3. Testing Queue Failure Event...');
        Event::dispatch(new SystemQueueFailureEvent(
            'App\Jobs\SendEmailNotification',
            'Maximum execution time exceeded',
            ['job_id' => 'test-job-123'],
            $user
        ));
        $this->line('   ✓ Queue Failure Event triggered');
        
        // Performance Issue Event
        $this->line('4. Testing Performance Issue Event...');
        Event::dispatch(new SystemPerformanceIssueEvent(
            'response_time',
            15000,
            '5000ms',
            ['route' => 'admin.dashboard', 'method' => 'GET'],
            $user
        ));
        $this->line('   ✓ Performance Issue Event triggered');
        
        $this->line('');
    }
    
    /**
     * Test user action-related events
     */
    private function testUserActionEvents(User $user): void
    {
        $this->info('Testing User Action Events:');
        $this->line('---------------------------');
        
        // User Account Deleted Event
        $this->line('1. Testing User Account Deleted Event...');
        Event::dispatch(new UserAccountDeletedEvent(
            $user,
            'Test deletion',
            $user
        ));
        $this->line('   ✓ User Account Deleted Event triggered');
        
        // User Bulk Operation Event
        $this->line('2. Testing User Bulk Operation Event...');
        Event::dispatch(new UserBulkOperationEvent(
            'clients',
            10,
            'delete',
            ['filter' => 'inactive'],
            $user
        ));
        $this->line('   ✓ User Bulk Operation Event triggered');
        
        // User Mass Content Deletion Event
        $this->line('3. Testing User Mass Content Deletion Event...');
        Event::dispatch(new UserMassContentDeletionEvent(
            25,
            ['content_type' => 'posts', 'reason' => 'cleanup'],
            $user
        ));
        $this->line('   ✓ User Mass Content Deletion Event triggered');
        
        // User Goal Failed Event
        $this->line('4. Testing User Goal Failed Event...');
        $goal = (object) [
            'id' => 1,
            'title' => 'Test Goal',
            'user' => $user,
        ];
        Event::dispatch(new UserGoalFailedEvent(
            $goal,
            'Deadline passed without completion',
            ['progress' => 75],
            $user
        ));
        $this->line('   ✓ User Goal Failed Event triggered');
        
        $this->line('');
    }
    
    /**
     * Test business-related events
     */
    private function testBusinessEvents(User $user): void
    {
        $this->info('Testing Business Events:');
        $this->line('------------------------');
        
        // High Value Sale Event
        $this->line('1. Testing High Value Sale Event...');
        $sale = (object) [
            'id' => 1,
            'amount' => 1500,
            'user' => $user,
            'client' => (object) ['name' => 'Test Client'],
        ];
        Event::dispatch(new BusinessHighValueSaleEvent(
            $sale,
            1000,
            ['sale_type' => 'online'],
            $user
        ));
        $this->line('   ✓ High Value Sale Event triggered');
        
        // Unusual Expense Event
        $this->line('2. Testing Unusual Expense Event...');
        $expense = (object) [
            'id' => 1,
            'amount' => 750,
            'category' => 'entertainment',
            'user' => $user,
        ];
        Event::dispatch(new BusinessUnusualExpenseEvent(
            $expense,
            'High-value expense in unusual category',
            ['expense_date' => now()->format('Y-m-d')],
            $user
        ));
        $this->line('   ✓ Unusual Expense Event triggered');
        
        // Payment Status Changed Event
        $this->line('3. Testing Payment Status Changed Event...');
        $payment = (object) [
            'id' => 1,
            'amount' => 500,
            'status' => 'paid',
        ];
        Event::dispatch(new BusinessPaymentStatusChangedEvent(
            $payment,
            'pending',
            'paid',
            ['payment_date' => now()->format('Y-m-d')],
            $user
        ));
        $this->line('   ✓ Payment Status Changed Event triggered');
        
        // Client Deleted Event
        $this->line('4. Testing Client Deleted Event...');
        $client = (object) [
            'id' => 1,
            'name' => 'Test Client',
            'email' => 'client@example.com',
        ];
        Event::dispatch(new BusinessClientDeletedEvent(
            $client,
            'Test deletion',
            ['sales_count' => 5],
            $user
        ));
        $this->line('   ✓ Client Deleted Event triggered');
        
        $this->line('');
    }
    
    /**
     * Test a specific type of alert
     */
    private function testSpecificAlert(string $type, User $user): void
    {
        $this->info("Testing specific alert type: {$type}");
        $this->line('-----------------------------------------');
        
        switch ($type) {
            case 'security':
                $this->testSecurityEvents($user);
                break;
            case 'system':
                $this->testSystemEvents($user);
                break;
            case 'user-action':
                $this->testUserActionEvents($user);
                break;
            case 'business':
                $this->testBusinessEvents($user);
                break;
            default:
                $this->error("Unknown alert type: {$type}");
                $this->line('Available types: security, system, user-action, business, all');
                break;
        }
    }
}