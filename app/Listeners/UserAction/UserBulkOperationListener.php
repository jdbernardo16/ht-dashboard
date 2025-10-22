<?php

namespace App\Listeners\UserAction;

use App\Events\UserAction\UserBulkOperationEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for UserBulkOperationEvent
 * 
 * This listener handles bulk operation events by creating notifications,
 * logging user actions, and potentially triggering follow-up actions.
 */
class UserBulkOperationListener extends AdministrativeAlertListener
{
    /**
     * Process the bulk operation event
     *
     * @param UserBulkOperationEvent $event The bulk operation event
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

        // Check if operation should be reviewed
        $this->checkOperationReview($event);

        // Check if rollback is needed
        $this->checkRollbackNeeded($event);

        // Create user audit trail entry
        $this->createAuditTrailEntry($event);

        // Check for suspicious operation patterns
        $this->checkSuspiciousOperationPatterns($event);
    }

    /**
     * Get the recipients for this user action alert
     *
     * @param UserBulkOperationEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for bulk operations
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For high severity or large operations, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || 
            $event->itemCount >= 100) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // Include the user who performed the operation (if different from recipients)
        if ($event->performedBy && !in_array($event->performedBy, $recipients)) {
            $recipients[] = $event->performedBy;
        }

        // If the user has a manager, include them
        if ($event->performedBy && $event->performedBy->manager_id && $event->performedBy->manager_id !== $event->performedBy->id) {
            $manager = User::find($event->performedBy->manager_id);
            if ($manager && !in_array($manager, $recipients)) {
                $recipients[] = $manager;
            }
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param UserBulkOperationEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(UserBulkOperationEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\UserBulkOperationNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param UserBulkOperationEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(UserBulkOperationEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\UserAction\UserBulkOperationMail($eventData, $recipient));
        }
    }

    /**
     * Log the user action with detailed information
     *
     * @param UserBulkOperationEvent $event The event
     * @return void
     */
    protected function logUserAction(UserBulkOperationEvent $event): void
    {
        Log::warning('Bulk operation performed', [
            'event_type' => 'user_bulk_operation',
            'performed_by_id' => $event->performedBy?->id,
            'performed_by_email' => $event->performedBy?->email,
            'operation_type' => $event->operationType,
            'target_resource' => $event->targetResource,
            'item_count' => $event->itemCount,
            'affected_items' => $event->affectedItems,
            'success_count' => $event->successCount,
            'failure_count' => $event->failureCount,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'was_scheduled' => $event->wasScheduled,
            'had_approval' => $event->hadApproval,
            'approver_id' => $event->approver?->id,
            'approver_email' => $event->approver?->email,
            'reason' => $event->reason,
            'estimated_impact' => $event->estimatedImpact,
            'duration' => $event->duration,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if operation should be reviewed
     *
     * @param UserBulkOperationEvent $event The event
     * @return void
     */
    protected function checkOperationReview(UserBulkOperationEvent $event): void
    {
        if ($this->shouldRequireReview($event)) {
            try {
                // Create operation review
                $this->createOperationReview($event);
                
                Log::info('Bulk operation review created', [
                    'operation_type' => $event->operationType,
                    'performed_by_id' => $event->performedBy?->id,
                    'item_count' => $event->itemCount,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create bulk operation review', [
                    'operation_type' => $event->operationType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if rollback is needed
     *
     * @param UserBulkOperationEvent $event The event
     * @return void
     */
    protected function checkRollbackNeeded(UserBulkOperationEvent $event): void
    {
        if ($this->shouldRollback($event)) {
            try {
                // Create rollback task
                $this->createRollbackTask($event);
                
                Log::warning('Bulk operation rollback task created', [
                    'operation_type' => $event->operationType,
                    'performed_by_id' => $event->performedBy?->id,
                    'failure_count' => $event->failureCount,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create bulk operation rollback task', [
                    'operation_type' => $event->operationType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create an audit trail entry for the user action
     *
     * @param UserBulkOperationEvent $event The event
     * @return void
     */
    protected function createAuditTrailEntry(UserBulkOperationEvent $event): void
    {
        try {
            // Create audit log entry
            \App\Models\UserAuditLog::create([
                'action' => 'bulk_operation',
                'user_id' => $event->performedBy?->id,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'operation_type' => $event->operationType,
                'target_resource' => $event->targetResource,
                'item_count' => $event->itemCount,
                'affected_items' => json_encode($event->affectedItems),
                'success_count' => $event->successCount,
                'failure_count' => $event->failureCount,
                'was_scheduled' => $event->wasScheduled,
                'had_approval' => $event->hadApproval,
                'approver_id' => $event->approver?->id,
                'reason' => $event->reason,
                'estimated_impact' => $event->estimatedImpact,
                'duration' => $event->duration,
                'metadata' => json_encode($event->context),
                'occurred_at' => $event->occurredAt,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit trail entry for bulk operation', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'operation_type' => $event->operationType,
                    'performed_by_id' => $event->performedBy?->id,
                ],
            ]);
        }
    }

    /**
     * Check for suspicious operation patterns
     *
     * @param UserBulkOperationEvent $event The event
     * @return void
     */
    protected function checkSuspiciousOperationPatterns(UserBulkOperationEvent $event): void
    {
        if ($this->hasSuspiciousPatterns($event)) {
            try {
                // Create security alert
                $this->createSuspiciousOperationAlert($event);
                
                Log::warning('Suspicious bulk operation pattern detected', [
                    'operation_type' => $event->operationType,
                    'performed_by_id' => $event->performedBy?->id,
                    'ip_address' => $event->ipAddress,
                    'suspicious_indicators' => $this->getSuspiciousIndicators($event),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle suspicious bulk operation pattern', [
                    'operation_type' => $event->operationType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create operation review
     *
     * @param UserBulkOperationEvent $event The event
     * @return void
     */
    protected function createOperationReview(UserBulkOperationEvent $event): void
    {
        \App\Models\BulkOperationReview::create([
            'operation_type' => $event->operationType,
            'target_resource' => $event->targetResource,
            'performed_by_id' => $event->performedBy?->id,
            'item_count' => $event->itemCount,
            'success_count' => $event->successCount,
            'failure_count' => $event->failureCount,
            'reason' => $event->reason,
            'had_approval' => $event->hadApproval,
            'approver_id' => $event->approver?->id,
            'status' => 'pending_review',
            'review_priority' => $this->getReviewPriority($event),
            'created_at' => now(),
        ]);
    }

    /**
     * Create rollback task
     *
     * @param UserBulkOperationEvent $event The event
     * @return void
     */
    protected function createRollbackTask(UserBulkOperationEvent $event): void
    {
        \App\Models\Task::create([
            'title' => "Rollback: {$event->operationType} on {$event->targetResource}",
            'description' => "Rollback bulk operation that affected {$event->itemCount} items with {$event->failureCount} failures.",
            'assigned_to' => $this->getRollbackAssignee($event),
            'created_by' => User::where('role', 'admin')->first()->id,
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addHours(24),
            'metadata' => json_encode([
                'original_operation' => [
                    'type' => $event->operationType,
                    'resource' => $event->targetResource,
                    'items' => $event->affectedItems,
                    'performed_by' => $event->performedBy?->id,
                    'occurred_at' => $event->occurredAt->toISOString(),
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create suspicious operation alert
     *
     * @param UserBulkOperationEvent $event The event
     * @return void
     */
    protected function createSuspiciousOperationAlert(UserBulkOperationEvent $event): void
    {
        \App\Models\SecurityAlert::create([
            'type' => 'suspicious_bulk_operation',
            'severity' => 'HIGH',
            'user_id' => $event->performedBy?->id,
            'ip_address' => $event->ipAddress,
            'description' => $event->getDescription(),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Check if review should be required
     *
     * @param UserBulkOperationEvent $event The event
     * @return bool True if review should be required
     */
    protected function shouldRequireReview(UserBulkOperationEvent $event): bool
    {
        // Require review for large operations or sensitive operations
        return $event->itemCount >= 50 ||
               in_array($event->operationType, ['delete', 'update', 'export']) ||
               $event->estimatedImpact === 'high' ||
               !$event->hadApproval;
    }

    /**
     * Check if rollback should be performed
     *
     * @param UserBulkOperationEvent $event The event
     * @return bool True if rollback should be performed
     */
    protected function shouldRollback(UserBulkOperationEvent $event): bool
    {
        // Rollback if failure rate is high or operation is critical
        $failureRate = $event->itemCount > 0 ? ($event->failureCount / $event->itemCount) * 100 : 0;
        
        return $failureRate > 25 || // More than 25% failures
               in_array($event->operationType, ['delete']) && $event->failureCount > 0; // Any failures for delete operations
    }

    /**
     * Check for suspicious patterns
     *
     * @param UserBulkOperationEvent $event The event
     * @return bool True if suspicious patterns are detected
     */
    protected function hasSuspiciousPatterns(UserBulkOperationEvent $event): bool
    {
        // Check for suspicious indicators
        return $this->getSuspiciousIndicators($event) !== [];
    }

    /**
     * Get suspicious indicators
     *
     * @param UserBulkOperationEvent $event The event
     * @return array List of suspicious indicators
     */
    protected function getSuspiciousIndicators(UserBulkOperationEvent $event): array
    {
        $indicators = [];
        
        // Check for bulk operations without approval
        if (!$event->hadApproval && $event->itemCount >= 10) {
            $indicators[] = 'bulk_operation_without_approval';
        }
        
        // Check for bulk delete operations by non-admin
        if ($event->operationType === 'delete' && 
            $event->performedBy && 
            $event->performedBy->role !== 'admin') {
            $indicators[] = 'bulk_delete_by_non_admin';
        }
        
        // Check for bulk operations outside business hours
        $hour = now()->hour;
        if ($hour < 8 || $hour > 18) {
            $indicators[] = 'operation_outside_business_hours';
        }
        
        // Check for unusually large operations
        if ($event->itemCount > 1000) {
            $indicators[] = 'unusually_large_operation';
        }
        
        // Check for operations on sensitive resources
        if (in_array($event->targetResource, ['users', 'sales', 'clients'])) {
            $indicators[] = 'operation_on_sensitive_resource';
        }
        
        // Check for multiple bulk operations from same user
        $recentOperations = \App\Models\UserAuditLog::where('action', 'bulk_operation')
            ->where('user_id', $event->performedBy?->id)
            ->where('occurred_at', '>', now()->subHours(24))
            ->count();
            
        if ($recentOperations >= 5) {
            $indicators[] = 'multiple_bulk_operations_same_user';
        }
        
        return $indicators;
    }

    /**
     * Get review priority based on the operation
     *
     * @param UserBulkOperationEvent $event The event
     * @return string The review priority (low, medium, high, critical)
     */
    protected function getReviewPriority(UserBulkOperationEvent $event): string
    {
        if ($event->estimatedImpact === 'high' || $event->failureCount > 0) {
            return 'high';
        } elseif ($event->itemCount >= 100 || !$event->hadApproval) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get assignee for rollback task
     *
     * @param UserBulkOperationEvent $event The event
     * @return int User ID of assignee
     */
    protected function getRollbackAssignee(UserBulkOperationEvent $event): int
    {
        // Assign to original performer if they're an admin, otherwise assign to an admin
        if ($event->performedBy && $event->performedBy->role === 'admin') {
            return $event->performedBy->id;
        }
        
        // Find an admin to assign the task to
        $admin = User::where('role', 'admin')->first();
        return $admin ? $admin->id : 1;
    }

    /**
     * Prepare event data specific to bulk operation events
     *
     * @param UserBulkOperationEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'performed_by_id' => $event->performedBy?->id,
            'performed_by_email' => $event->performedBy?->email,
            'operation_type' => $event->operationType,
            'target_resource' => $event->targetResource,
            'item_count' => $event->itemCount,
            'affected_items' => $event->affectedItems,
            'success_count' => $event->successCount,
            'failure_count' => $event->failureCount,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'was_scheduled' => $event->wasScheduled,
            'had_approval' => $event->hadApproval,
            'approver_id' => $event->approver?->id,
            'approver_email' => $event->approver?->email,
            'reason' => $event->reason,
            'estimated_impact' => $event->estimatedImpact,
            'duration' => $event->duration,
            'failure_rate' => $this->calculateFailureRate($event),
            'risk_level' => $this->calculateRiskLevel($event),
            'requires_review' => $this->shouldRequireReview($event),
            'requires_rollback' => $this->shouldRollback($event),
            'recommended_actions' => $this->getRecommendedActions($event),
        ]);
    }

    /**
     * Calculate failure rate percentage
     *
     * @param UserBulkOperationEvent $event The event
     * @return float Failure rate percentage
     */
    protected function calculateFailureRate(UserBulkOperationEvent $event): float
    {
        if ($event->itemCount === 0) {
            return 0;
        }
        
        return round(($event->failureCount / $event->itemCount) * 100, 2);
    }

    /**
     * Calculate the risk level for the bulk operation
     *
     * @param UserBulkOperationEvent $event The event
     * @return string The risk level (low, medium, high, critical)
     */
    protected function calculateRiskLevel(UserBulkOperationEvent $event): string
    {
        if ($this->hasSuspiciousPatterns($event) || 
            ($event->operationType === 'delete' && $event->itemCount >= 100)) {
            return 'critical';
        } elseif ($event->estimatedImpact === 'high' || 
                 !$event->hadApproval || 
                 $event->failureCount > 0) {
            return 'high';
        } elseif ($event->itemCount >= 50 || 
                 in_array($event->operationType, ['delete', 'update'])) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the bulk operation
     *
     * @param UserBulkOperationEvent $event The event
     * @return array List of recommended actions
     */
    protected function getRecommendedActions(UserBulkOperationEvent $event): array
    {
        $actions = [];

        if ($this->hasSuspiciousPatterns($event)) {
            $actions[] = 'Investigate suspicious operation patterns';
            $actions[] = 'Review user permissions';
            $actions[] = 'Monitor user activity';
        }

        if (!$event->hadApproval && $event->itemCount >= 10) {
            $actions[] = 'Review approval process for bulk operations';
            $actions[] = 'Update bulk operation policies';
        }

        if ($event->operationType === 'delete' && $event->performedBy && $event->performedBy->role !== 'admin') {
            $actions[] = 'Review delete permissions';
            $actions[] = 'Consider restricting delete operations to admins';
        }

        if ($this->shouldRollback($event)) {
            $actions[] = 'Execute rollback plan';
            $actions[] = 'Notify affected users';
            $actions[] = 'Document rollback process';
        }

        if ($this->shouldRequireReview($event)) {
            $actions[] = 'Conduct operation review';
            $actions[] = 'Verify operation results';
            $actions[] = 'Document operation outcomes';
        }

        if ($event->failureCount > 0) {
            $actions[] = 'Investigate operation failures';
            $actions[] = 'Address failure root causes';
            $actions[] = 'Improve error handling';
        }

        if ($event->estimatedImpact === 'high') {
            $actions[] = 'Assess operation impact';
            $actions[] = 'Monitor system performance';
            $actions[] = 'Communicate with stakeholders';
        }

        if ($event->duration > 300) { // More than 5 minutes
            $actions[] = 'Optimize operation performance';
            $actions[] = 'Consider operation timeouts';
        }

        return $actions;
    }
}