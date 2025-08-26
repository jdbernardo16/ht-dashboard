<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Send a system alert notification.
     */
    public static function sendSystemAlert(User $user, string $message, array $data = []): Notification
    {
        return self::createNotification($user, 'system_alert', 'System Alert', $message, $data);
    }

    /**
     * Send a task update notification.
     */
    public static function sendTaskUpdate(User $user, string $taskTitle, string $action, array $data = []): Notification
    {
        $message = "Task '{$taskTitle}' has been {$action}";
        return self::createNotification($user, 'task_update', 'Task Update', $message, $data);
    }

    /**
     * Send an expense approval notification.
     */
    public static function sendExpenseApproval(User $user, float $amount, string $category, array $data = []): Notification
    {
        $message = "Your expense of {$amount} for {$category} has been approved";
        return self::createNotification($user, 'expense_approved', 'Expense Approved', $message, $data);
    }

    /**
     * Send a goal achievement notification.
     */
    public static function sendGoalAchievement(User $user, string $goalTitle, array $data = []): Notification
    {
        $message = "Congratulations! You've achieved your goal: {$goalTitle}";
        return self::createNotification($user, 'goal_achieved', 'Goal Achieved', $message, $data);
    }

    /**
     * Send a sale completion notification.
     */
    public static function sendSaleCompletion(User $user, float $amount, string $clientName, array $data = []): Notification
    {
        $message = "Sale completed for {$clientName} with amount {$amount}";
        return self::createNotification($user, 'sale_completed', 'Sale Completed', $message, $data);
    }

    /**
     * Send a content publication notification.
     */
    public static function sendContentPublication(User $user, string $contentTitle, string $platform, array $data = []): Notification
    {
        $message = "Your content '{$contentTitle}' has been published on {$platform}";
        return self::createNotification($user, 'content_published', 'Content Published', $message, $data);
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
    protected static function createNotification(User $user, string $type, string $title, string $message, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
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
}