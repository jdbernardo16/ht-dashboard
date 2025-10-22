<?php

namespace App\Listeners\UserAction;

use App\Events\UserAction\UserAccountDeletedEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for UserAccountDeletedEvent
 * 
 * This listener handles user account deletion events by creating notifications,
 * logging user actions, and potentially triggering follow-up actions.
 */
class UserAccountDeletedListener extends AdministrativeAlertListener
{
    /**
     * Process the user account deletion event
     *
     * @param UserAccountDeletedEvent $event The user account deletion event
     * @return void
     */
    protected function processEvent(\App\Events\AdministrativeAlertEvent $event): void
    {
        // Configure listener based on event severity
        $this->configureForEvent($event);

        // Get recipients for this alert
        $recipients = $this->getRecipients($event);

        // Create database notifications
        $this->createDatabaseNotifications($event, $recipients);

        // Send email notifications for critical/high severity
        if ($event->shouldSendEmail()) {
            $this->sendEmailNotifications($event, $recipients);
        }

        // Log the user action
        $this->logUserAction($event);

        // Check if data backup should be created
        $this->checkDataBackup($event);

        // Check if access revocation is needed
        $this->checkAccessRevocation($event);

        // Create user audit trail entry
        $this->createAuditTrailEntry($event);

        // Check for suspicious deletion patterns
        $this->checkSuspiciousDeletionPatterns($event);
    }

    /**
     * Get the recipients for this user action alert
     *
     * @param UserAccountDeletedEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for account deletions
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For high severity or admin account deletions, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || 
            $event->deletedUser->role === 'admin') {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // Include the user who performed the deletion (if different from recipients)
        if ($event->deletedBy && !in_array($event->deletedBy, $recipients)) {
            $recipients[] = $event->deletedBy;
        }

        // If the deleted user had a manager, include them
        if ($event->deletedUser->manager_id && $event->deletedUser->manager_id !== $event->deletedUser->id) {
            $manager = User::find($event->deletedUser->manager_id);
            if ($manager && !in_array($manager, $recipients)) {
                $recipients[] = $manager;
            }
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param UserAccountDeletedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(UserAccountDeletedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\UserAccountDeletedNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param UserAccountDeletedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(UserAccountDeletedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\UserAction\UserAccountDeletedMail($eventData, $recipient));
        }
    }

    /**
     * Log the user action with detailed information
     *
     * @param UserAccountDeletedEvent $event The event
     * @return void
     */
    protected function logUserAction(UserAccountDeletedEvent $event): void
    {
        Log::warning('User account deleted', [
            'event_type' => 'user_account_deleted',
            'deleted_user_id' => $event->deletedUser->id,
            'deleted_user_email' => $event->deletedUser->email,
            'deleted_user_role' => $event->deletedUser->role,
            'deleted_by_id' => $event->deletedBy?->id,
            'deleted_by_email' => $event->deletedBy?->email,
            'deletion_reason' => $event->deletionReason,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'was_forced' => $event->wasForced,
            'was_self_deletion' => $event->wasSelfDeletion(),
            'data_backed_up' => $event->dataBackedUp,
            'backup_path' => $event->backupPath,
            'had_active_tasks' => $event->hadActiveTasks,
            'had_assigned_clients' => $event->hadAssignedClients,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if data backup should be created
     *
     * @param UserAccountDeletedEvent $event The event
     * @return void
     */
    protected function checkDataBackup(UserAccountDeletedEvent $event): void
    {
        if (!$event->dataBackedUp && $this->shouldCreateBackup($event)) {
            try {
                // Create data backup
                $this->createDataBackup($event);
                
                Log::info('User data backup created', [
                    'deleted_user_id' => $event->deletedUser->id,
                    'backup_path' => $event->backupPath,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create user data backup', [
                    'deleted_user_id' => $event->deletedUser->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if access revocation is needed
     *
     * @param UserAccountDeletedEvent $event The event
     * @return void
     */
    protected function checkAccessRevocation(UserAccountDeletedEvent $event): void
    {
        if ($this->shouldRevokeAccess($event)) {
            try {
                // Revoke all access
                $this->revokeAllAccess($event);
                
                Log::info('User access revoked', [
                    'deleted_user_id' => $event->deletedUser->id,
                    'access_revoked_at' => now()->toISOString(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to revoke user access', [
                    'deleted_user_id' => $event->deletedUser->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create an audit trail entry for the user action
     *
     * @param UserAccountDeletedEvent $event The event
     * @return void
     */
    protected function createAuditTrailEntry(UserAccountDeletedEvent $event): void
    {
        try {
            // Create audit log entry
            \App\Models\UserAuditLog::create([
                'action' => 'account_deleted',
                'user_id' => $event->deletedUser->id,
                'performed_by' => $event->deletedBy?->id,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'reason' => $event->deletionReason,
                'was_forced' => $event->wasForced,
                'was_self_deletion' => $event->wasSelfDeletion(),
                'data_backed_up' => $event->dataBackedUp,
                'backup_path' => $event->backupPath,
                'metadata' => json_encode($event->context),
                'occurred_at' => $event->occurredAt,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit trail entry for user account deletion', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'deleted_user_id' => $event->deletedUser->id,
                    'deleted_by_id' => $event->deletedBy?->id,
                ],
            ]);
        }
    }

    /**
     * Check for suspicious deletion patterns
     *
     * @param UserAccountDeletedEvent $event The event
     * @return void
     */
    protected function checkSuspiciousDeletionPatterns(UserAccountDeletedEvent $event): void
    {
        if ($this->hasSuspiciousPatterns($event)) {
            try {
                // Create security alert
                $this->createSuspiciousDeletionAlert($event);
                
                Log::warning('Suspicious user account deletion pattern detected', [
                    'deleted_user_id' => $event->deletedUser->id,
                    'deleted_by_id' => $event->deletedBy?->id,
                    'ip_address' => $event->ipAddress,
                    'suspicious_indicators' => $this->getSuspiciousIndicators($event),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle suspicious deletion pattern', [
                    'deleted_user_id' => $event->deletedUser->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create data backup for the deleted user
     *
     * @param UserAccountDeletedEvent $event The event
     * @return void
     */
    protected function createDataBackup(UserAccountDeletedEvent $event): void
    {
        // Create backup directory
        $backupDir = storage_path("app/backups/users/{$event->deletedUser->id}");
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Create backup file with timestamp
        $backupFile = $backupDir . "/backup_" . now()->format('Y_m_d_H_i_s') . ".json";
        
        // Collect user data
        $userData = [
            'user' => $event->deletedUser->toArray(),
            'tasks' => $event->deletedUser->tasks()->get()->toArray(),
            'sales' => $event->deletedUser->sales()->get()->toArray(),
            'expenses' => $event->deletedUser->expenses()->get()->toArray(),
            'goals' => $event->deletedUser->goals()->get()->toArray(),
            'content_posts' => $event->deletedUser->contentPosts()->get()->toArray(),
            'notifications' => $event->deletedUser->notifications()->get()->toArray(),
            'backup_created_at' => now()->toISOString(),
        ];
        
        // Write backup to file
        file_put_contents($backupFile, json_encode($userData, JSON_PRETTY_PRINT));
        
        // Update event with backup path
        $event->backupPath = $backupFile;
        $event->dataBackedUp = true;
    }

    /**
     * Revoke all access for the deleted user
     *
     * @param UserAccountDeletedEvent $event The event
     * @return void
     */
    protected function revokeAllAccess(UserAccountDeletedEvent $event): void
    {
        // Revoke API tokens
        $event->deletedUser->tokens()->delete();
        
        // Revoke sessions
        $event->deletedUser->sessions()->delete();
        
        // Invalidate password reset tokens
        $event->deletedUser->update([
            'remember_token' => null,
            'password_reset_token' => null,
            'password_reset_expires' => null,
        ]);
        
        // Clear cache
        cache()->forget("user:{$event->deletedUser->id}");
        cache()->forget("user_permissions:{$event->deletedUser->id}");
    }

    /**
     * Create suspicious deletion alert
     *
     * @param UserAccountDeletedEvent $event The event
     * @return void
     */
    protected function createSuspiciousDeletionAlert(UserAccountDeletedEvent $event): void
    {
        \App\Models\SecurityAlert::create([
            'type' => 'suspicious_account_deletion',
            'severity' => 'HIGH',
            'user_id' => $event->deletedBy?->id,
            'target_user_id' => $event->deletedUser->id,
            'ip_address' => $event->ipAddress,
            'description' => $event->getDescription(),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Check if backup should be created
     *
     * @param UserAccountDeletedEvent $event The event
     * @return bool True if backup should be created
     */
    protected function shouldCreateBackup(UserAccountDeletedEvent $event): bool
    {
        // Create backup for admin users or users with significant data
        return $event->deletedUser->role === 'admin' ||
               $event->deletedUser->role === 'manager' ||
               $event->hadActiveTasks ||
               $event->hadAssignedClients ||
               $event->deletedUser->sales()->count() > 0;
    }

    /**
     * Check if access should be revoked
     *
     * @param UserAccountDeletedEvent $event The event
     * @return bool True if access should be revoked
     */
    protected function shouldRevokeAccess(UserAccountDeletedEvent $event): bool
    {
        // Always revoke access for deleted accounts
        return true;
    }

    /**
     * Check for suspicious patterns
     *
     * @param UserAccountDeletedEvent $event The event
     * @return bool True if suspicious patterns are detected
     */
    protected function hasSuspiciousPatterns(UserAccountDeletedEvent $event): bool
    {
        // Check for suspicious indicators
        return $this->getSuspiciousIndicators($event) !== [];
    }

    /**
     * Get suspicious indicators
     *
     * @param UserAccountDeletedEvent $event The event
     * @return array List of suspicious indicators
     */
    protected function getSuspiciousIndicators(UserAccountDeletedEvent $event): array
    {
        $indicators = [];
        
        // Check if admin account was deleted by non-admin
        if ($event->deletedUser->role === 'admin' && 
            $event->deletedBy && 
            $event->deletedBy->role !== 'admin') {
            $indicators[] = 'admin_deleted_by_non_admin';
        }
        
        // Check for self-deletion of admin account
        if ($event->wasSelfDeletion() && $event->deletedUser->role === 'admin') {
            $indicators[] = 'admin_self_deletion';
        }
        
        // Check for deletion without reason
        if (empty($event->deletionReason)) {
            $indicators[] = 'deletion_without_reason';
        }
        
        // Check for deletion of active user with recent activity
        if ($event->deletedUser->last_active_at && 
            $event->deletedUser->last_active_at->diffInDays(now()) < 7) {
            $indicators[] = 'recently_active_user_deleted';
        }
        
        // Check for multiple deletations from same IP
        $recentDeletions = \App\Models\UserAuditLog::where('action', 'account_deleted')
            ->where('ip_address', $event->ipAddress)
            ->where('occurred_at', '>', now()->subHours(24))
            ->count();
            
        if ($recentDeletions >= 3) {
            $indicators[] = 'multiple_deletions_same_ip';
        }
        
        return $indicators;
    }

    /**
     * Prepare event data specific to user account deletion events
     *
     * @param UserAccountDeletedEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'deleted_user_id' => $event->deletedUser->id,
            'deleted_user_email' => $event->deletedUser->email,
            'deleted_user_role' => $event->deletedUser->role,
            'deleted_by_id' => $event->deletedBy?->id,
            'deleted_by_email' => $event->deletedBy?->email,
            'deletion_reason' => $event->deletionReason,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'was_forced' => $event->wasForced,
            'was_self_deletion' => $event->wasSelfDeletion(),
            'data_backed_up' => $event->dataBackedUp,
            'backup_path' => $event->backupPath,
            'had_active_tasks' => $event->hadActiveTasks,
            'had_assigned_clients' => $event->hadAssignedClients,
            'task_count' => $event->deletedUser->tasks()->count(),
            'sales_count' => $event->deletedUser->sales()->count(),
            'client_count' => $event->deletedUser->clients()->count(),
            'risk_level' => $this->calculateRiskLevel($event),
            'recommended_actions' => $this->getRecommendedActions($event),
        ]);
    }

    /**
     * Calculate the risk level for the account deletion
     *
     * @param UserAccountDeletedEvent $event The event
     * @return string The risk level (low, medium, high, critical)
     */
    protected function calculateRiskLevel(UserAccountDeletedEvent $event): string
    {
        if ($event->deletedUser->role === 'admin' && $this->hasSuspiciousPatterns($event)) {
            return 'critical';
        } elseif ($event->deletedUser->role === 'admin') {
            return 'high';
        } elseif ($this->hasSuspiciousPatterns($event)) {
            return 'high';
        } elseif ($event->deletedUser->role === 'manager') {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the account deletion
     *
     * @param UserAccountDeletedEvent $event The event
     * @return array List of recommended actions
     */
    protected function getRecommendedActions(UserAccountDeletedEvent $event): array
    {
        $actions = [];

        if ($event->deletedUser->role === 'admin') {
            $actions[] = 'Review admin account deletion process';
            $actions[] = 'Verify authorization for admin deletion';
            $actions[] = 'Update security protocols';
            
            if ($this->hasSuspiciousPatterns($event)) {
                $actions[] = 'Investigate suspicious deletion patterns';
                $actions[] = 'Block suspicious IP address';
                $actions[] = 'Review access logs';
            }
        }

        if (!$event->dataBackedUp && $this->shouldCreateBackup($event)) {
            $actions[] = 'Create data backup for deleted user';
        }

        if ($event->hadActiveTasks) {
            $actions[] = 'Reassign active tasks to other users';
            $actions[] = 'Notify task stakeholders of reassignment';
        }

        if ($event->hadAssignedClients) {
            $actions[] = 'Reassign clients to other team members';
            $actions[] = 'Notify clients of account manager change';
        }

        if ($event->wasSelfDeletion() && $event->deletedUser->role === 'admin') {
            $actions[] = 'Verify admin self-deletion legitimacy';
            $actions[] = 'Review approval process for admin deletion';
        }

        if ($this->hasSuspiciousPatterns($event)) {
            $actions[] = 'Investigate suspicious deletion indicators';
            $actions[] = 'Monitor for related suspicious activities';
            $actions[] = 'Consider temporary IP blocking';
        }

        if (empty($event->deletionReason)) {
            $actions[] = 'Follow up on deletion reason';
            $actions[] = 'Update deletion policy to require reason';
        }

        return $actions;
    }
}