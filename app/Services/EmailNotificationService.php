<?php

namespace App\Services;

use App\Mail\TaskAssignmentMail;
use App\Mail\TaskCompletionMail;
use App\Mail\NewSaleMail;
use App\Mail\ExpenseApprovalMail;
use App\Mail\GoalProgressMail;
use App\Mail\ContentPublishedMail;
use App\Models\User;
use App\Models\Task;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\ContentPost;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class EmailNotificationService
{
    /**
     * Default email preferences for new users
     */
    private const DEFAULT_PREFERENCES = [
        'task_assignment' => true,
        'task_completion' => true,
        'new_sale' => true,
        'expense_approval' => true,
        'goal_progress' => true,
        'content_published' => true,
    ];

    /**
     * Send task assignment email notification
     */
    public function sendTaskAssignmentNotification(User $user, Task $task): bool
    {
        if (!$this->shouldSendEmail($user, 'task_assignment')) {
            return false;
        }

        try {
            Mail::to($user->email)
                ->queue(new TaskAssignmentMail($user, $task));
            
            Log::info('Task assignment email queued', [
                'user_id' => $user->id,
                'task_id' => $task->id,
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send task assignment email', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send task completion email notification
     */
    public function sendTaskCompletionNotification(User $user, Task $task): bool
    {
        if (!$this->shouldSendEmail($user, 'task_completion')) {
            return false;
        }

        try {
            Mail::to($user->email)
                ->queue(new TaskCompletionMail($user, $task));
            
            Log::info('Task completion email queued', [
                'user_id' => $user->id,
                'task_id' => $task->id,
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send task completion email', [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send new sale email notification
     */
    public function sendNewSaleNotification(User $user, Sale $sale): bool
    {
        if (!$this->shouldSendEmail($user, 'new_sale')) {
            return false;
        }

        try {
            Mail::to($user->email)
                ->queue(new NewSaleMail($user, $sale));
            
            Log::info('New sale email queued', [
                'user_id' => $user->id,
                'sale_id' => $sale->id,
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send new sale email', [
                'user_id' => $user->id,
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send expense approval email notification
     */
    public function sendExpenseApprovalNotification(User $user, Expense $expense): bool
    {
        if (!$this->shouldSendEmail($user, 'expense_approval')) {
            return false;
        }

        try {
            Mail::to($user->email)
                ->queue(new ExpenseApprovalMail($user, $expense));
            
            Log::info('Expense approval email queued', [
                'user_id' => $user->id,
                'expense_id' => $expense->id,
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send expense approval email', [
                'user_id' => $user->id,
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send goal progress email notification
     */
    public function sendGoalProgressNotification(User $user, Goal $goal, float $progressPercentage): bool
    {
        if (!$this->shouldSendEmail($user, 'goal_progress')) {
            return false;
        }

        try {
            Mail::to($user->email)
                ->queue(new GoalProgressMail($user, $goal, $progressPercentage));
            
            Log::info('Goal progress email queued', [
                'user_id' => $user->id,
                'goal_id' => $goal->id,
                'progress' => $progressPercentage,
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send goal progress email', [
                'user_id' => $user->id,
                'goal_id' => $goal->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send content published email notification
     */
    public function sendContentPublishedNotification(User $user, ContentPost $contentPost): bool
    {
        if (!$this->shouldSendEmail($user, 'content_published')) {
            return false;
        }

        try {
            Mail::to($user->email)
                ->queue(new ContentPublishedMail($user, $contentPost));
            
            Log::info('Content published email queued', [
                'user_id' => $user->id,
                'content_post_id' => $contentPost->id,
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send content published email', [
                'user_id' => $user->id,
                'content_post_id' => $contentPost->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Check if user should receive email notification
     */
    private function shouldSendEmail(User $user, string $notificationType): bool
    {
        // Check if email notifications are globally enabled for user
        if (!$user->email_notifications_enabled) {
            return false;
        }

        // Check if user has verified email (if email verification is enabled)
        if (config('auth.verification.enabled') && !$user->hasVerifiedEmail()) {
            return false;
        }

        // Check specific notification type preference
        $preferences = $user->email_preferences ?? self::DEFAULT_PREFERENCES;
        
        return ($preferences[$notificationType] ?? self::DEFAULT_PREFERENCES[$notificationType]) === true;
    }

    /**
     * Get default email preferences for new users
     */
    public static function getDefaultPreferences(): array
    {
        return self::DEFAULT_PREFERENCES;
    }

    /**
     * Update user email preferences
     */
    public function updateUserPreferences(User $user, array $preferences): bool
    {
        try {
            $user->email_preferences = array_merge(
                self::DEFAULT_PREFERENCES,
                $preferences
            );
            $user->save();
            
            Log::info('Email preferences updated', [
                'user_id' => $user->id,
                'preferences' => $preferences,
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to update email preferences', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Toggle email notifications for user
     */
    public function toggleEmailNotifications(User $user, bool $enabled): bool
    {
        try {
            $user->email_notifications_enabled = $enabled;
            $user->save();
            
            Log::info('Email notifications toggled', [
                'user_id' => $user->id,
                'enabled' => $enabled,
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to toggle email notifications', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
}