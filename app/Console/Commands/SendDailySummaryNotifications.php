<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDailySummaryNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily summary notifications to all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending daily summary notifications...');

        // Get summary data for today
        $today = now()->format('Y-m-d');
        
        $summaryData = [
            'completed_tasks' => DB::table('tasks')
                ->whereDate('updated_at', $today)
                ->where('status', 'completed')
                ->count(),
            'total_sales' => DB::table('sales')
                ->whereDate('sale_date', $today)
                ->where('status', 'completed')
                ->sum('amount'),
            'new_expenses' => DB::table('expenses')
                ->whereDate('expense_date', $today)
                ->count(),
            'published_content' => DB::table('content_posts')
                ->whereDate('published_date', $today)
                ->where('status', 'published')
                ->count(),
            'date' => $today,
        ];

        // Send notifications to all users
        $users = User::all();
        $sentCount = 0;

        foreach ($users as $user) {
            try {
                NotificationService::sendDailySummary($user, $summaryData);
                $sentCount++;
            } catch (\Exception $e) {
                $this->error("Failed to send notification to user {$user->id}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$sentCount} daily summary notifications.");
        
        return Command::SUCCESS;
    }
}