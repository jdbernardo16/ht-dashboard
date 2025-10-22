<?php

namespace App\Listeners\UserAction;

use App\Events\UserAction\UserMassContentDeletionEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for UserMassContentDeletionEvent
 * 
 * This listener handles mass content deletion events by creating notifications,
 * logging user actions, and potentially triggering content recovery processes.
 */
class UserMassContentDeletionListener extends AdministrativeAlertListener
{
    /**
     * Process the mass content deletion event
     *
     * @param UserMassContentDeletionEvent $event The mass content deletion event
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

        // Check if content recovery should be attempted
        $this->checkContentRecovery($event);

        // Check if review is needed
        $this->checkReviewNeeded($event);

        // Create user audit trail entry
        $this->createAuditTrailEntry($event);

        // Check for suspicious deletion patterns
        $this->checkSuspiciousDeletionPatterns($event);

        // Create content backup verification
        $this->verifyContentBackup($event);
    }

    /**
     * Get the recipients for this user action alert
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for mass content deletion
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For published content or large deletions, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || 
            $event->deletedCount >= 100 || 
            $event->wasPublishedContent) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // Include the user who performed the deletion
        if ($event->user && !in_array($event->user, $recipients)) {
            $recipients[] = $event->user;
        }

        // Include the approver if different from user
        if ($event->approver && $event->approver->id !== $event->user->id && 
            !in_array($event->approver, $recipients)) {
            $recipients[] = $event->approver;
        }

        // If the user has a manager, include them
        if ($event->user && $event->user->manager_id && $event->user->manager_id !== $event->user->id) {
            $manager = User::find($event->user->manager_id);
            if ($manager && !in_array($manager, $recipients)) {
                $recipients[] = $manager;
            }
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param UserMassContentDeletionEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(UserMassContentDeletionEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\UserMassContentDeletionNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param UserMassContentDeletionEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(UserMassContentDeletionEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\UserAction\UserMassContentDeletionMail($eventData, $recipient));
        }
    }

    /**
     * Log the user action with detailed information
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function logUserAction(UserMassContentDeletionEvent $event): void
    {
        Log::warning('Mass content deletion performed', [
            'event_type' => 'user_mass_content_deletion',
            'performed_by_id' => $event->user?->id,
            'performed_by_email' => $event->user?->email,
            'content_type' => $event->contentType,
            'deleted_count' => $event->deletedCount,
            'deleted_item_ids' => $event->deletedItemIds,
            'date_range' => $event->dateRange,
            'deletion_criteria' => $event->deletionCriteria,
            'was_published_content' => $event->wasPublishedContent,
            'was_scheduled' => $event->wasScheduled,
            'backup_created' => $event->backupCreated,
            'backup_path' => $event->backupPath,
            'has_approval' => $event->hasApproval,
            'approver_id' => $event->approver?->id,
            'approver_email' => $event->approver?->email,
            'reason' => $event->reason,
            'estimated_data_size' => $event->estimatedDataSize,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if content recovery should be attempted
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function checkContentRecovery(UserMassContentDeletionEvent $event): void
    {
        if ($event->shouldAttemptRecovery()) {
            try {
                // Create content recovery task
                $this->createContentRecoveryTask($event);
                
                Log::info('Content recovery task created', [
                    'content_type' => $event->contentType,
                    'deleted_by_id' => $event->user?->id,
                    'deleted_count' => $event->deletedCount,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create content recovery task', [
                    'content_type' => $event->contentType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if review is needed
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function checkReviewNeeded(UserMassContentDeletionEvent $event): void
    {
        if ($this->shouldRequireReview($event)) {
            try {
                // Create content deletion review
                $this->createContentDeletionReview($event);
                
                Log::info('Content deletion review created', [
                    'content_type' => $event->contentType,
                    'performed_by_id' => $event->user?->id,
                    'deleted_count' => $event->deletedCount,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create content deletion review', [
                    'content_type' => $event->contentType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create an audit trail entry for the user action
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function createAuditTrailEntry(UserMassContentDeletionEvent $event): void
    {
        try {
            // Create audit log entry
            \App\Models\UserAuditLog::create([
                'action' => 'mass_content_deletion',
                'user_id' => $event->user?->id,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'content_type' => $event->contentType,
                'deleted_count' => $event->deletedCount,
                'deleted_item_ids' => json_encode($event->deletedItemIds),
                'date_range' => json_encode($event->dateRange),
                'deletion_criteria' => json_encode($event->deletionCriteria),
                'was_published_content' => $event->wasPublishedContent,
                'was_scheduled' => $event->wasScheduled,
                'backup_created' => $event->backupCreated,
                'backup_path' => $event->backupPath,
                'has_approval' => $event->hasApproval,
                'approver_id' => $event->approver?->id,
                'reason' => $event->reason,
                'estimated_data_size' => $event->estimatedDataSize,
                'metadata' => json_encode($event->context),
                'occurred_at' => $event->occurredAt,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit trail entry for mass content deletion', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'content_type' => $event->contentType,
                    'performed_by_id' => $event->user?->id,
                ],
            ]);
        }
    }

    /**
     * Check for suspicious deletion patterns
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function checkSuspiciousDeletionPatterns(UserMassContentDeletionEvent $event): void
    {
        if ($this->hasSuspiciousPatterns($event)) {
            try {
                // Create security alert
                $this->createSuspiciousDeletionAlert($event);
                
                Log::warning('Suspicious mass content deletion pattern detected', [
                    'content_type' => $event->contentType,
                    'performed_by_id' => $event->user?->id,
                    'ip_address' => $event->ipAddress,
                    'suspicious_indicators' => $this->getSuspiciousIndicators($event),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle suspicious mass content deletion pattern', [
                    'content_type' => $event->contentType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Verify content backup
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function verifyContentBackup(UserMassContentDeletionEvent $event): void
    {
        if ($event->backupCreated && $event->backupPath) {
            try {
                // Verify backup file exists and is accessible
                $backupVerified = $this->verifyBackupFile($event->backupPath);
                
                if (!$backupVerified) {
                    Log::error('Content backup verification failed', [
                        'backup_path' => $event->backupPath,
                        'content_type' => $event->contentType,
                        'performed_by_id' => $event->user?->id,
                    ]);
                    
                    // Create backup failure alert
                    $this->createBackupFailureAlert($event);
                }
            } catch (\Exception $e) {
                Log::error('Failed to verify content backup', [
                    'backup_path' => $event->backupPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create content recovery task
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function createContentRecoveryTask(UserMassContentDeletionEvent $event): void
    {
        \App\Models\Task::create([
            'title' => "Content Recovery: {$event->contentType} Deletion",
            'description' => "Recover {$event->deletedCount} deleted {$event->contentType} items. Deletion performed by {$event->user?->email} without proper approval.",
            'assigned_to' => $this->getRecoveryAssignee($event),
            'created_by' => User::where('role', 'admin')->first()->id,
            'priority' => $event->wasPublishedContent ? 'high' : 'medium',
            'status' => 'pending',
            'due_date' => now()->addHours(24),
            'metadata' => json_encode([
                'original_deletion' => [
                    'type' => $event->contentType,
                    'items' => $event->deletedItemIds,
                    'performed_by' => $event->user?->id,
                    'backup_path' => $event->backupPath,
                    'occurred_at' => $event->occurredAt->toISOString(),
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create content deletion review
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function createContentDeletionReview(UserMassContentDeletionEvent $event): void
    {
        \App\Models\ContentDeletionReview::create([
            'content_type' => $event->contentType,
            'performed_by_id' => $event->user?->id,
            'deleted_count' => $event->deletedCount,
            'was_published_content' => $event->wasPublishedContent,
            'had_approval' => $event->hasApproval,
            'approver_id' => $event->approver?->id,
            'backup_created' => $event->backupCreated,
            'backup_path' => $event->backupPath,
            'reason' => $event->reason,
            'status' => 'pending_review',
            'review_priority' => $this->getReviewPriority($event),
            'created_at' => now(),
        ]);
    }

    /**
     * Create suspicious deletion alert
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function createSuspiciousDeletionAlert(UserMassContentDeletionEvent $event): void
    {
        \App\Models\SecurityAlert::create([
            'type' => 'suspicious_content_deletion',
            'severity' => 'HIGH',
            'user_id' => $event->user?->id,
            'ip_address' => $event->ipAddress,
            'description' => $event->getDescription(),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Create backup failure alert
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return void
     */
    protected function createBackupFailureAlert(UserMassContentDeletionEvent $event): void
    {
        \App\Models\SystemAlert::create([
            'type' => 'backup_verification_failed',
            'severity' => 'MEDIUM',
            'description' => "Backup verification failed for {$event->contentType} deletion. Backup path: {$event->backupPath}",
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Verify backup file exists and is accessible
     *
     * @param string $backupPath The backup file path
     * @return bool True if backup is verified
     */
    protected function verifyBackupFile(string $backupPath): bool
    {
        try {
            $storage = \Storage::disk('local');
            return $storage->exists($backupPath) && $storage->size($backupPath) > 0;
        } catch (\Exception $e) {
            Log::error('Backup file verification error', [
                'backup_path' => $backupPath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if review should be required
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return bool True if review should be required
     */
    protected function shouldRequireReview(UserMassContentDeletionEvent $event): bool
    {
        // Require review for published content, large deletions, or deletions without approval
        return $event->wasPublishedContent ||
               $event->deletedCount >= 50 ||
               !$event->hasApproval ||
               !$event->backupCreated;
    }

    /**
     * Check for suspicious patterns
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return bool True if suspicious patterns are detected
     */
    protected function hasSuspiciousPatterns(UserMassContentDeletionEvent $event): bool
    {
        // Check for suspicious indicators
        return $this->getSuspiciousIndicators($event) !== [];
    }

    /**
     * Get suspicious indicators
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return array List of suspicious indicators
     */
    protected function getSuspiciousIndicators(UserMassContentDeletionEvent $event): array
    {
        $indicators = [];
        
        // Check for published content deletion without approval
        if ($event->wasPublishedContent && !$event->hasApproval) {
            $indicators[] = 'published_content_deletion_without_approval';
        }
        
        // Check for large content deletion by non-admin
        if ($event->deletedCount >= 100 && 
            $event->user && 
            $event->user->role !== 'admin') {
            $indicators[] = 'large_content_deletion_by_non_admin';
        }
        
        // Check for content deletion outside business hours
        $hour = now()->hour;
        if ($hour < 8 || $hour > 18) {
            $indicators[] = 'deletion_outside_business_hours';
        }
        
        // Check for unusually large content deletion
        if ($event->deletedCount > 1000) {
            $indicators[] = 'unusually_large_content_deletion';
        }
        
        // Check for deletion without backup
        if ($event->wasPublishedContent && !$event->backupCreated) {
            $indicators[] = 'published_content_deletion_without_backup';
        }
        
        // Check for multiple content deletions from same user
        $recentDeletions = \App\Models\UserAuditLog::where('action', 'mass_content_deletion')
            ->where('user_id', $event->user?->id)
            ->where('occurred_at', '>', now()->subHours(24))
            ->count();
            
        if ($recentDeletions >= 3) {
            $indicators[] = 'multiple_content_deletions_same_user';
        }
        
        return $indicators;
    }

    /**
     * Get review priority based on the deletion
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return string The review priority (low, medium, high, critical)
     */
    protected function getReviewPriority(UserMassContentDeletionEvent $event): string
    {
        if ($event->wasPublishedContent && !$event->hasApproval) {
            return 'critical';
        } elseif ($event->deletedCount >= 1000 || !$event->backupCreated) {
            return 'high';
        } elseif ($event->deletedCount >= 100 || !$event->hasApproval) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get assignee for recovery task
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return int User ID of assignee
     */
    protected function getRecoveryAssignee(UserMassContentDeletionEvent $event): int
    {
        // Assign to original performer if they're an admin, otherwise assign to an admin
        if ($event->user && $event->user->role === 'admin') {
            return $event->user->id;
        }
        
        // Find an admin to assign the task to
        $admin = User::where('role', 'admin')->first();
        return $admin ? $admin->id : 1;
    }

    /**
     * Prepare event data specific to mass content deletion events
     *
     * @param UserMassContentDeletionEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'performed_by_id' => $event->user?->id,
            'performed_by_email' => $event->user?->email,
            'content_type' => $event->contentType,
            'deleted_count' => $event->deletedCount,
            'deleted_item_ids' => $event->deletedItemIds,
            'date_range' => $event->dateRange,
            'deletion_criteria' => $event->deletionCriteria,
            'was_published_content' => $event->wasPublishedContent,
            'was_scheduled' => $event->wasScheduled,
            'backup_created' => $event->backupCreated,
            'backup_path' => $event->backupPath,
            'has_approval' => $event->hasApproval,
            'approver_id' => $event->approver?->id,
            'approver_email' => $event->approver?->email,
            'reason' => $event->reason,
            'estimated_data_size' => $event->estimatedDataSize,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'requires_immediate_review' => $event->requiresImmediateReview(),
            'should_attempt_recovery' => $event->shouldAttemptRecovery(),
        ]);
    }
}