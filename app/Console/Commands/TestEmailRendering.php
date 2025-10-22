<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Task;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Goal;
use App\Models\ContentPost;
use App\Mail\TaskAssignmentMail;
use App\Mail\TaskCompletionMail;
use App\Mail\NewSaleMail;
use App\Mail\ExpenseApprovalMail;
use App\Mail\GoalProgressMail;
use App\Mail\ContentPublishedMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

class TestEmailRendering extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-rendering 
                            {--email= : Email address to send test emails to}
                            {--type=all : Type of email to test (all|task-assignment|task-completion|new-sale|expense-approval|goal-progress|content-published)}
                            {--queue : Queue emails instead of sending synchronously}
                            {--save : Save rendered emails to storage/logs/email-test.html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email rendering and check for quoted-printable encoding issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email') ?? 'test@example.com';
        $type = $this->option('type');
        $useQueue = $this->option('queue');
        $saveToFile = $this->option('save');

        $this->info('Testing email rendering...');
        $this->info("Email: {$email}");
        $this->info("Type: {$type}");
        $this->info("Queue: " . ($useQueue ? 'Yes' : 'No'));
        $this->info("Save to file: " . ($saveToFile ? 'Yes' : 'No'));
        $this->newLine();

        // Create test data
        $testData = $this->createTestData();

        $emailsToTest = $this->getEmailsToTest($type, $testData, $email);

        $results = [];
        $allHtml = '';

        foreach ($emailsToTest as $emailType => $mailable) {
            $this->info("Testing {$emailType}...");
            
            try {
                $result = $this->testEmailRendering($mailable, $emailType, $useQueue, $saveToFile);
                $results[$emailType] = $result;
                
                if ($saveToFile) {
                    $allHtml .= "<h2>{$emailType}</h2>";
                    $allHtml .= $result['html'];
                    $allHtml .= "<hr>";
                }
                
                $this->line("  ✓ Subject: {$result['subject']}");
                $this->line("  ✓ Encoding: {$result['encoding']}");
                $this->line("  ✓ Content-Type: {$result['contentType']}");
                $this->line("  ✓ HTML Length: " . strlen($result['html']) . " characters");
                
                // Check for quoted-printable encoding issues
                if (strpos($result['html'], '=3D') !== false || strpos($result['html'], '=E2') !== false) {
                    $this->warn("  ⚠️  Potential quoted-printable encoding detected in HTML!");
                }
                
                if (strpos($result['subject'], '=3D') !== false || strpos($result['subject'], '=E2') !== false) {
                    $this->warn("  ⚠️  Potential quoted-printable encoding detected in subject!");
                }
                
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                $results[$emailType] = ['error' => $e->getMessage()];
            }
            
            $this->newLine();
        }

        // Save all emails to one file if requested
        if ($saveToFile && !empty($allHtml)) {
            $this->saveAllEmailsToFile($allHtml);
        }

        // Summary
        $this->info('Test Summary:');
        $total = count($emailsToTest);
        $success = count(array_filter($results, fn($r) => !isset($r['error'])));
        
        $this->line("Total emails tested: {$total}");
        $this->line("Successful: {$success}");
        $this->line("Failed: " . ($total - $success));

        if ($success === $total) {
            $this->info('✓ All emails rendered successfully!');
        } else {
            $this->warn('Some emails failed to render. Check the errors above.');
        }

        return $success === $total ? 0 : 1;
    }

    /**
     * Create test data for email templates
     */
    private function createTestData(): array
    {
        // Create or get test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'full_name' => 'Test User',
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_notifications_enabled' => true,
            ]
        );

        // Create test task
        $task = Task::firstOrCreate(
            ['id' => 999],
            [
                'user_id' => $user->id,
                'title' => 'Test Task: ✓ Special Characters = Success & Test € Á É Í',
                'description' => 'This is a test task with special characters: ✓, =, €, á, é, í, ñ, ü',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(3),
                'assigned_to' => $user->id,
                'assigned_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create test client with unique email
        $client = Client::firstOrCreate(
            ['email' => 'test-client-' . time() . '@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'Client',
                'name' => 'Test Client & Company',
                'phone' => '+1-555-0123',
                'address' => '123 Test St, Test City, TC 12345',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create test sale
        $sale = Sale::firstOrCreate(
            ['id' => 999],
            [
                'user_id' => $user->id,
                'client_id' => $client->id,
                'type' => 'direct',
                'product_name' => 'Test Sports Card Collection',
                'amount' => 1500.00,
                'description' => 'Test Sale: Sports Card ✓ Special Items = €500',
                'sale_date' => now(),
                'status' => 'completed',
                'commission_rate' => 10,
                'commission_amount' => 150.00,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create test category
        $category = Category::firstOrCreate(
            ['id' => 999],
            [
                'name' => 'Test Category & Supplies',
                'type' => 'expense',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create test expense
        $expense = Expense::firstOrCreate(
            ['id' => 999],
            [
                'user_id' => $user->id,
                'category_id' => $category->id,
                'description' => 'Test Expense: Office Supplies ✓ = €100',
                'amount' => 150.50,
                'expense_date' => now(),
                'status' => 'approved',
                'notes' => 'Test notes with special characters: ✓, =, €, á, é, í',
                'payment_method' => 'credit_card',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create test goal
        $goal = Goal::firstOrCreate(
            ['id' => 999],
            [
                'user_id' => $user->id,
                'title' => 'Test Goal: Sales Target ✓ = €10,000',
                'description' => 'Test goal description with special characters: ✓, =, €, á, é, í',
                'target_value' => 10000,
                'current_value' => 7500,
                'target_unit' => 'EUR',
                'start_date' => now()->subMonths(1),
                'end_date' => now()->addMonths(2),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create test content post
        $contentPost = ContentPost::firstOrCreate(
            ['id' => 999],
            [
                'title' => 'Test Content: Special Characters ✓ = Success',
                'content' => '<p>Test content with special characters: ✓, =, €, á, é, í</p><p>This is a test blog post about sports cards and collectibles.</p>',
                'platform' => 'instagram',
                'content_type' => 'post',
                'status' => 'published',
                'scheduled_date' => now(),
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return compact('user', 'task', 'client', 'sale', 'category', 'expense', 'goal', 'contentPost');
    }

    /**
     * Get array of emails to test based on type
     */
    private function getEmailsToTest(string $type, array $testData, string $email): array
    {
        $emails = [];

        if ($type === 'all' || $type === 'task-assignment') {
            $emails['Task Assignment'] = new TaskAssignmentMail($testData['user'], $testData['task']);
        }

        if ($type === 'all' || $type === 'task-completion') {
            $testData['task']->status = 'completed';
            $emails['Task Completion'] = new TaskCompletionMail($testData['user'], $testData['task']);
        }

        if ($type === 'all' || $type === 'new-sale') {
            $emails['New Sale'] = new NewSaleMail($testData['user'], $testData['sale']);
        }

        if ($type === 'all' || $type === 'expense-approval') {
            $emails['Expense Approval'] = new ExpenseApprovalMail($testData['user'], $testData['expense']);
        }

        if ($type === 'all' || $type === 'goal-progress') {
            $progressPercentage = ($testData['goal']->current_value / $testData['goal']->target_value) * 100;
            $emails['Goal Progress'] = new GoalProgressMail($testData['user'], $testData['goal'], $progressPercentage);
        }

        if ($type === 'all' || $type === 'content-published') {
            $emails['Content Published'] = new ContentPublishedMail($testData['user'], $testData['contentPost']);
        }

        return $emails;
    }

    /**
     * Test individual email rendering
     */
    private function testEmailRendering($mailable, string $emailType, bool $useQueue, bool $saveToFile): array
    {
        // Build the mailable to get the Symfony message
        $builtMailable = $mailable->build();
        $symfonyMessage = $builtMailable->getSymfonyMessage();
        
        // Get headers
        $encoding = $symfonyMessage->getHeaders()->get('Content-Transfer-Encoding')?->getBodyAsString() ?? 'not-set';
        $contentType = $symfonyMessage->getHeaders()->get('Content-Type')?->getBodyAsString() ?? 'not-set';
        
        // Get subject
        $subject = $symfonyMessage->getSubject();
        
        // Get HTML content
        $html = $symfonyMessage->getHtmlBody();
        
        // Send email if not just saving to file
        if (!$saveToFile) {
            if ($useQueue) {
                Mail::to('test@example.com')->queue($mailable);
            } else {
                Mail::to('test@example.com')->send($mailable);
            }
        }

        return [
            'subject' => $subject,
            'encoding' => $encoding,
            'contentType' => $contentType,
            'html' => $html,
        ];
    }

    /**
     * Save all emails to a single HTML file
     */
    private function saveAllEmailsToFile(string $allHtml): void
    {
        $fullHtml = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Email Rendering Test Results</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; }
                h2 { color: #666; border-bottom: 1px solid #ccc; }
                hr { margin: 30px 0; }
                .email-container { border: 1px solid #ddd; margin: 20px 0; }
            </style>
        </head>
        <body>
            <h1>Email Rendering Test Results</h1>
            <p>Generated: ' . now()->format('Y-m-d H:i:s') . '</p>
            ' . $allHtml . '
        </body>
        </html>';

        $filename = 'email-test-' . date('Y-m-d-H-i-s') . '.html';
        $path = storage_path("logs/{$filename}");
        
        file_put_contents($path, $fullHtml);
        $this->info("All emails saved to: {$path}");
    }
}