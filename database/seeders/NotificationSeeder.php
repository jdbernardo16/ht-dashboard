<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users to assign notifications to
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Define notification types
        $notificationTypes = [
            'system_alert',
            'task_update',
            'expense_approved',
            'sales_update',
            'goal_reminder',
            'content_published',
            'client_update',
            'deadline_reminder'
        ];

        // Create sample notifications for each user
        foreach ($users as $user) {
            // Create 5-10 notifications per user
            $notificationCount = rand(5, 10);
            
            for ($i = 0; $i < $notificationCount; $i++) {
                $type = Arr::random($notificationTypes);
                $isRead = rand(0, 1) === 1;
                
                $notification = [
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $this->getNotificationTitle($type),
                    'message' => $this->getNotificationMessage($type, $user->first_name),
                    'data' => $this->getNotificationData($type),
                    'read_at' => $isRead ? now()->subMinutes(rand(5, 1440)) : null,
                    'created_at' => now()->subMinutes(rand(1, 4320)),
                    'updated_at' => now(),
                ];

                Notification::create($notification);
            }
        }
    }

    /**
     * Get notification title based on type
     */
    private function getNotificationTitle(string $type): string
    {
        $titles = [
            'system_alert' => 'System Alert',
            'task_update' => 'Task Update',
            'expense_approved' => 'Expense Approved',
            'sales_update' => 'Sales Update',
            'goal_reminder' => 'Goal Reminder',
            'content_published' => 'Content Published',
            'client_update' => 'Client Update',
            'deadline_reminder' => 'Deadline Reminder'
        ];

        return $titles[$type] ?? 'Notification';
    }

    /**
     * Get notification message based on type
     */
    private function getNotificationMessage(string $type, string $userName): string
    {
        $messages = [
            'system_alert' => "Hi {$userName}, there's a system update scheduled for tonight at 11 PM EST.",
            'task_update' => "Hi {$userName}, your task status has been updated. Please check your dashboard.",
            'expense_approved' => "Hi {$userName}, your expense submission has been approved and processed.",
            'sales_update' => "Hi {$userName}, you have a new sales record. Check your sales dashboard for details.",
            'goal_reminder' => "Hi {$userName}, don't forget about your quarterly goals. You're doing great!",
            'content_published' => "Hi {$userName}, your content has been successfully published and is live now.",
            'client_update' => "Hi {$userName}, there's an update on one of your clients. Please review the details.",
            'deadline_reminder' => "Hi {$userName}, you have a task deadline approaching in the next 24 hours."
        ];

        return $messages[$type] ?? "Hi {$userName}, you have a new notification.";
    }

    /**
     * Get notification data based on type
     */
    private function getNotificationData(string $type): ?array
    {
        $data = [
            'system_alert' => [
                'scheduled_time' => '23:00 EST',
                'duration' => '2 hours',
                'affected_services' => ['dashboard', 'api']
            ],
            'task_update' => [
                'task_id' => rand(1, 20),
                'old_status' => 'pending',
                'new_status' => 'in_progress'
            ],
            'expense_approved' => [
                'expense_id' => rand(1, 15),
                'amount' => '$' . rand(50, 500) . '.' . rand(0, 99),
                'category' => Arr::random(['Software', 'Office Supplies', 'Marketing'])
            ],
            'sales_update' => [
                'sale_id' => rand(1, 25),
                'amount' => '$' . rand(100, 2000) . '.' . rand(0, 99),
                'client_name' => Arr::random(['John Doe', 'Jane Smith', 'Michael Johnson'])
            ],
            'goal_reminder' => [
                'goal_id' => rand(1, 10),
                'progress' => rand(60, 95) . '%',
                'days_remaining' => rand(5, 30)
            ],
            'content_published' => [
                'content_id' => rand(1, 20),
                'platform' => Arr::random(['Facebook', 'Instagram', 'Twitter']),
                'post_count' => rand(1, 5)
            ],
            'client_update' => [
                'client_id' => rand(1, 10),
                'update_type' => Arr::random(['new_order', 'contact_info', 'status_change'])
            ],
            'deadline_reminder' => [
                'task_id' => rand(1, 20),
                'task_title' => 'Complete ' . Arr::random(['report', 'analysis', 'project']),
                'hours_remaining' => rand(2, 24)
            ]
        ];

        return $data[$type] ?? null;
    }
}