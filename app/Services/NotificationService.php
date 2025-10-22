<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Task;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\ContentPost;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Email notification service instance
     */
    private static $emailService;
    /**
     * Send a system alert notification.
     */
    public static function sendSystemAlert(User $user, string $message, array $data = []): Notification
    {
        $notification = self::createNotification($user, 'system_alert', 'System Alert', $message, $data);
        
        // System alerts typically don't have email notifications
        return $notification;
    }

    /**
     * Send a task update notification.
     */
    public static function sendTaskUpdate(User $user, string $taskTitle, string $action, array $data = []): Notification
    {
        $message = "Task '{$taskTitle}' has been {$action}";
        $notification = self::createNotification($user, 'task_update', 'Task Update', $message, $data);
        
        // Send email notification if enabled
        if (isset($data['task']) && $data['task'] instanceof Task) {
            self::getEmailService()->sendTaskAssignmentNotification($user, $data['task']);
        }
        
        return $notification;
    }

    /**
     * Send an expense approval notification.
     */
    public static function sendExpenseApproval(User $user, float $amount, string $category, array $data = []): Notification
    {
        $message = "Your expense of {$amount} for {$category} has been approved";
        $notification = self::createNotification($user, 'expense_approved', 'Expense Approved', $message, $data);
        
        // Send email notification if enabled
        if (isset($data['expense']) && $data['expense'] instanceof Expense) {
            self::getEmailService()->sendExpenseApprovalNotification($user, $data['expense']);
        }
        
        return $notification;
    }

    /**
     * Send a goal achievement notification.
     */
    public static function sendGoalAchievement(User $user, string $goalTitle, array $data = []): Notification
    {
        $message = "Congratulations! You've achieved your goal: {$goalTitle}";
        $notification = self::createNotification($user, 'goal_achieved', 'Goal Achieved', $message, $data);
        
        // Send email notification if enabled
        if (isset($data['goal']) && $data['goal'] instanceof Goal) {
            self::getEmailService()->sendGoalProgressNotification($user, $data['goal'], 100);
        }
        
        return $notification;
    }

    /**
     * Send a sale completion notification.
     */
    public static function sendSaleCompletion(User $user, float $amount, string $clientName, array $data = []): Notification
    {
        $message = "Sale completed for {$clientName} with amount {$amount}";
        $notification = self::createNotification($user, 'sale_completed', 'Sale Completed', $message, $data);
        
        // Send email notification if enabled
        if (isset($data['sale']) && $data['sale'] instanceof Sale) {
            self::getEmailService()->sendNewSaleNotification($user, $data['sale']);
        }
        
        return $notification;
    }

    /**
     * Send a content publication notification.
     */
    public static function sendContentPublication(User $user, string $contentTitle, string $platform, array $data = []): Notification
    {
        $message = "Your content '{$contentTitle}' has been published on {$platform}";
        $notification = self::createNotification($user, 'content_published', 'Content Published', $message, $data);
        
        // Send email notification if enabled
        if (isset($data['content_post']) && $data['content_post'] instanceof ContentPost) {
            self::getEmailService()->sendContentPublishedNotification($user, $data['content_post']);
        }
        
        return $notification;
    }

    /**
     * Send a daily summary notification.
     */
    public static function sendDailySummary(User $user, array $summaryData): Notification
    {
        $completedTasks = $summaryData['completed_tasks'] ?? 0;
        $totalSales = $summaryData['total_sales'] ?? 0;
        
        $message = "Daily summary: {$completedTasks} tasks completed, {$totalSales} in sales";
        return self::createNotification($user, 'daily_summary', 'Daily Summary', $message, $summaryData);
    }

    /**
     * Send a reminder notification.
     */
    public static function sendReminder(User $user, string $subject, string $message, array $data = []): Notification
    {
        return self::createNotification($user, 'reminder', "Reminder: {$subject}", $message, $data);
    }

    /**
     * Create a notification with the given parameters.
     */
    public static function createNotification(User $user, string $type, string $title, string $message, array $data = []): Notification
    {
        Log::info('Creating notification', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data_keys' => array_keys($data),
            'timestamp' => now()->toISOString(),
            'broadcast_connection' => config('broadcasting.default'),
            'memory_usage' => memory_get_usage(true),
        ]);
        
        $startTime = microtime(true);
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        Log::info('Notification created successfully', [
            'notification_id' => $notification->id,
            'type' => $type,
            'user_id' => $user->id,
            'execution_time_ms' => round($executionTime, 2),
            'created_at' => $notification->created_at->toISOString(),
            'will_broadcast' => true,
            'broadcast_channel' => 'private-notifications.' . $user->id,
        ]);
        
        // Log additional debugging information for specific notification types
        if (in_array($type, ['task_assigned', 'task_completed', 'expense_created', 'goal_created', 'sale_completed'])) {
            Log::info('Critical notification created', [
                'notification_id' => $notification->id,
                'type' => $type,
                'user_id' => $user->id,
                'requires_immediate_attention' => true,
                'data' => $data,
            ]);
        }
        
        return $notification;
    }

    /**
     * Send notification to multiple users.
     */
    public static function broadcastToUsers(array $users, string $type, string $title, string $message, array $data = []): array
    {
        $notifications = [];
        
        foreach ($users as $user) {
            $notifications[] = self::createNotification($user, $type, $title, $message, $data);
        }
        
        return $notifications;
    }

    /**
     * Send notification to users with specific role.
     */
    public static function broadcastToRole(string $role, string $type, string $title, string $message, array $data = []): array
    {
        $users = User::where('role', $role)->get();
        return self::broadcastToUsers($users->all(), $type, $title, $message, $data);
    }

    /**
     * Send notification to all users.
     */
    public static function broadcastToAll(string $type, string $title, string $message, array $data = []): array
    {
        $users = User::all();
        return self::broadcastToUsers($users->all(), $type, $title, $message, $data);
    }

    /**
     * Send an expense rejection notification.
     */
    public static function sendExpenseRejection(User $user, float $amount, string $category, array $data = []): Notification
    {
        $message = "Your expense of {$amount} for {$category} has been rejected";
        $notification = self::createNotification($user, 'expense_rejected', 'Expense Rejected', $message, $data);
        
        // Send email notification if enabled
        if (isset($data['expense']) && $data['expense'] instanceof Expense) {
            self::getEmailService()->sendExpenseApprovalNotification($user, $data['expense']);
        }
        
        return $notification;
    }

    /**
     * Send a goal milestone notification.
     */
    public static function sendGoalMilestone(User $user, string $goalTitle, int $milestone, array $data = []): Notification
    {
        $message = "You've reached {$milestone}% of your goal: {$goalTitle}";
        $notification = self::createNotification($user, 'goal_milestone', 'Goal Milestone Reached', $message, $data);
        
        // Send email notification if enabled
        if (isset($data['goal']) && $data['goal'] instanceof Goal) {
            self::getEmailService()->sendGoalProgressNotification($user, $data['goal'], $milestone);
        }
        
        return $notification;
    }

    /**
     * Send a content update notification.
     */
    public static function sendContentUpdate(User $user, string $contentTitle, string $action, array $data = []): Notification
    {
        $message = "Content '{$contentTitle}' has been {$action}";
        return self::createNotification($user, 'content_update', 'Content Update', $message, $data);
    }

    /**
     * Send a high value sale notification.
     */
    public static function sendHighValueSale(User $user, float $amount, string $clientName, array $data = []): Notification
    {
        $message = "High-value sale of {$amount} recorded for {$clientName}";
        return self::createNotification($user, 'high_value_sale', 'High Value Sale', $message, $data);
    }

    /**
     * Send a high value expense notification.
     */
    public static function sendHighValueExpense(User $user, float $amount, string $category, array $data = []): Notification
    {
        $message = "High-value expense of {$amount} for {$category} requires attention";
        return self::createNotification($user, 'high_value_expense', 'High Value Expense', $message, $data);
    }

    /**
     * Send a content high engagement notification.
     */
    public static function sendContentHighEngagement(User $user, string $contentTitle, int $engagementCount, array $data = []): Notification
    {
        $message = "Your content '{$contentTitle}' has {$engagementCount} engagements!";
        return self::createNotification($user, 'content_high_engagement', 'Content High Engagement', $message, $data);
    }

    /**
     * Send a task priority update notification.
     */
    public static function sendTaskPriorityUpdate(User $user, string $taskTitle, string $priority, array $data = []): Notification
    {
        $message = "Task '{$taskTitle}' priority updated to {$priority}";
        return self::createNotification($user, 'task_priority_updated', 'Task Priority Updated', $message, $data);
    }

    /**
     * Send a goal deadline reminder notification.
     */
    public static function sendGoalDeadlineReminder(User $user, string $goalTitle, int $daysLeft, array $data = []): Notification
    {
        $statusText = $daysLeft < 0 ? 'overdue' : 'due soon';
        $message = "Goal '{$goalTitle}' is {$statusText} ({$daysLeft} days)";
        return self::createNotification($user, 'goal_deadline_reminder', 'Goal Deadline Reminder', $message, $data);
    }

    /**
     * Send task assignment notification
     */
    public static function sendTaskAssignment(User $user, Task $task): Notification
    {
        $message = "You have been assigned a new task: {$task->title}";
        $notification = self::createNotification($user, 'task_assigned', 'Task Assigned', $message, ['task_id' => $task->id]);
        
        // Send email notification if enabled
        self::getEmailService()->sendTaskAssignmentNotification($user, $task);
        
        return $notification;
    }

    /**
     * Send task completion notification
     */
    public static function sendTaskCompletion(User $user, Task $task): Notification
    {
        $message = "Task '{$task->title}' has been completed";
        $notification = self::createNotification($user, 'task_completed', 'Task Completed', $message, ['task_id' => $task->id]);
        
        // Send email notification if enabled
        self::getEmailService()->sendTaskCompletionNotification($user, $task);
        
        return $notification;
    }

    /**
     * Get email notification service instance
     */
    private static function getEmailService(): EmailNotificationService
    {
        if (self::$emailService === null) {
            self::$emailService = new EmailNotificationService();
        }
        
        return self::$emailService;
    }

    /**
     * Set email notification service instance (for testing)
     */
    public static function setEmailService(EmailNotificationService $service): void
    {
        self::$emailService = $service;
    }
}