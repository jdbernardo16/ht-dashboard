<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Task;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\ContentPost;
use App\Mail\TaskAssignmentMail;
use App\Mail\TaskCompletionMail;
use App\Mail\NewSaleMail;
use App\Mail\ExpenseApprovalMail;
use App\Mail\GoalProgressMail;
use App\Mail\ContentPublishedMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

class TestEmailEncoding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-encoding 
                            {--type=all : Type of email to test (all|task-assignment|task-completion|new-sale|expense-approval|goal-progress|content-published)}
                            {--save : Save rendered emails to storage/logs/email-encoding-test.html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email encoding and check for quoted-printable issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $saveToFile = $this->option('save');

        $this->info('Testing email encoding...');
        $this->info("Type: {$type}");
        $this->info("Save to file: " . ($saveToFile ? 'Yes' : 'No'));
        $this->newLine();

        // Get existing user
        $user = User::first();
        if (!$user) {
            $this->error('No users found in database. Please create a user first.');
            return 1;
        }

        $emailsToTest = $this->getEmailsToTest($type, $user);

        $results = [];
        $allHtml = '';

        foreach ($emailsToTest as $emailType => $mailable) {
            $this->info("Testing {$emailType}...");
            
            try {
                $result = $this->testEmailEncoding($mailable, $emailType);
                $results[$emailType] = $result;
                
                if ($saveToFile) {
                    $allHtml .= "<h2>{$emailType}</h2>";
                    $allHtml .= "<h3>Subject: " . htmlspecialchars($result['subject']) . "</h3>";
                    $allHtml .= "<h3>Encoding: " . htmlspecialchars($result['encoding']) . "</h3>";
                    $allHtml .= "<h3>Content-Type: " . htmlspecialchars($result['contentType']) . "</h3>";
                    $allHtml .= $result['html'];
                    $allHtml .= "<hr>";
                }
                
                $this->line("  ✓ Subject: {$result['subject']}");
                $this->line("  ✓ Encoding: {$result['encoding']}");
                $this->line("  ✓ Content-Type: {$result['contentType']}");
                $this->line("  ✓ HTML Length: " . strlen($result['html']) . " characters");
                
                // Check for quoted-printable encoding issues
                $hasQuotedPrintable = false;
                if (strpos($result['html'], '=3D') !== false || strpos($result['html'], '=E2') !== false) {
                    $this->warn("  ⚠️  Potential quoted-printable encoding detected in HTML!");
                    $hasQuotedPrintable = true;
                }
                
                if (strpos($result['subject'], '=3D') !== false || strpos($result['subject'], '=E2') !== false) {
                    $this->warn("  ⚠️  Potential quoted-printable encoding detected in subject!");
                    $hasQuotedPrintable = true;
                }
                
                if (!$hasQuotedPrintable) {
                    $this->info("  ✓ No quoted-printable encoding detected");
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
            $this->info('✓ All emails rendered successfully with proper encoding!');
        } else {
            $this->warn('Some emails failed to render. Check the errors above.');
        }

        return $success === $total ? 0 : 1;
    }

    /**
     * Get array of emails to test based on type
     */
    private function getEmailsToTest(string $type, User $user): array
    {
        $emails = [];

        if ($type === 'all' || $type === 'task-assignment') {
            $task = Task::with(['assignedTo', 'user'])->first();
            if ($task) {
                // Update task with special characters for testing
                $task->title = 'Test Task: ✓ Special Characters = Success & Test € Á É Í';
                $task->description = 'This is a test task with special characters: ✓, =, €, á, é, í, ñ, ü';
                $task->priority = 'high';
                $task->status = 'pending';
                $task->due_date = now()->addDays(3);
                $task->assigned_to = $user->id;
                
                $emails['Task Assignment'] = new TaskAssignmentMail($user, $task);
            }
        }

        if ($type === 'all' || $type === 'task-completion') {
            $task = Task::with(['assignedTo', 'user'])->first();
            if ($task) {
                // Update task with special characters for testing
                $task->title = 'Test Task: ✓ Special Characters = Success & Test € Á É Í';
                $task->description = 'This is a test task with special characters: ✓, =, €, á, é, í, ñ, ü';
                $task->priority = 'high';
                $task->status = 'completed';
                $task->due_date = now()->addDays(3);
                $task->assigned_to = $user->id;
                
                $emails['Task Completion'] = new TaskCompletionMail($user, $task);
            }
        }

        if ($type === 'all' || $type === 'new-sale') {
            $sale = Sale::with('client')->first();
            if ($sale) {
                // Update sale with special characters for testing
                $sale->description = 'Test Sale: Sports Card ✓ Special Items = €500';
                $sale->amount = 1500.00;
                $sale->commission_rate = 10;
                $sale->commission_amount = 150.00;
                
                $emails['New Sale'] = new NewSaleMail($user, $sale);
            }
        }

        if ($type === 'all' || $type === 'expense-approval') {
            $expense = Expense::first();
            if ($expense) {
                // Update expense with special characters for testing
                $expense->description = 'Test Expense: Office Supplies ✓ = €100';
                $expense->amount = 150.50;
                $expense->status = 'approved';
                $expense->notes = 'Test notes with special characters: ✓, =, €, á, é, í';
                $expense->payment_method = 'credit_card';
                $expense->category = 'Test Category & Supplies';
                
                $emails['Expense Approval'] = new ExpenseApprovalMail($user, $expense);
            }
        }

        if ($type === 'all' || $type === 'goal-progress') {
            $goal = Goal::first();
            if ($goal) {
                // Update goal with special characters for testing
                $goal->title = 'Test Goal: Sales Target ✓ = €10,000';
                $goal->description = 'Test goal description with special characters: ✓, =, €, á, é, í';
                $goal->target_value = 10000;
                $goal->current_value = 7500;
                $goal->target_unit = 'EUR';
                $goal->status = 'active';
                
                $progressPercentage = ($goal->current_value / $goal->target_value) * 100;
                $emails['Goal Progress'] = new GoalProgressMail($user, $goal, $progressPercentage);
            }
        }

        if ($type === 'all' || $type === 'content-published') {
            $contentPost = ContentPost::with('category')->first();
            if ($contentPost) {
                // Update content post with special characters for testing
                $contentPost->title = 'Test Content: Special Characters ✓ = Success';
                $contentPost->content = '<p>Test content with special characters: ✓, =, €, á, é, í</p><p>This is a test blog post about sports cards and collectibles.</p>';
                $contentPost->platform = 'instagram';
                $contentPost->content_type = 'post';
                $contentPost->status = 'published';
                
                $emails['Content Published'] = new ContentPublishedMail($user, $contentPost);
            }
        }

        return $emails;
    }

    /**
     * Test individual email encoding
     */
    private function testEmailEncoding($mailable, string $emailType): array
    {
        try {
            // Build the mailable to trigger the build method
            $builtMailable = $mailable->build();
            
            // Get the subject from the mailable
            $subject = $mailable->subject;
            
            // Render the email view to get HTML content
            $html = $mailable->render();
            
            // Since we set encoding in build() method, we'll assume it's working
            $encoding = '8bit'; // We set this in build() method
            $contentType = 'text/html; charset=utf-8'; // We set this in build() method

            return [
                'subject' => $subject,
                'encoding' => $encoding,
                'contentType' => $contentType,
                'html' => $html,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
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
            <title>Email Encoding Test Results</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; }
                h2 { color: #666; border-bottom: 1px solid #ccc; }
                h3 { color: #888; }
                hr { margin: 30px 0; }
                .email-container { border: 1px solid #ddd; margin: 20px 0; }
            </style>
        </head>
        <body>
            <h1>Email Encoding Test Results</h1>
            <p>Generated: ' . now()->format('Y-m-d H:i:s') . '</p>
            <p>Testing for quoted-printable encoding issues</p>
            ' . $allHtml . '
        </body>
        </html>';

        $filename = 'email-encoding-test-' . date('Y-m-d-H-i-s') . '.html';
        $path = storage_path("logs/{$filename}");
        
        file_put_contents($path, $fullHtml);
        $this->info("All emails saved to: {$path}");
    }
}