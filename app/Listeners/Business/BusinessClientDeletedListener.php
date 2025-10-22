<?php

namespace App\Listeners\Business;

use App\Events\Business\BusinessClientDeletedEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for BusinessClientDeletedEvent
 * 
 * This listener handles client deletion events by creating notifications,
 * logging business impact, and potentially triggering recovery processes.
 */
class BusinessClientDeletedListener extends AdministrativeAlertListener
{
    /**
     * Process the client deletion event
     *
     * @param BusinessClientDeletedEvent $event The client deletion event
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

        // Send email notifications for high severity
        if ($event->shouldSendEmail()) {
            $this->sendEmailNotifications($event, $recipients);
        }

        // Log the client deletion
        $this->logClientDeletion($event);

        // Check if recovery should be considered
        $this->checkClientRecovery($event);

        // Check if immediate review is needed
        $this->checkImmediateReview($event);

        // Create client tracking entry
        $this->createClientTrackingEntry($event);

        // Check for deletion patterns
        $this->checkDeletionPatterns($event);

        // Create client impact analysis
        $this->createClientImpactAnalysis($event);

        // Update business metrics
        $this->updateBusinessMetrics($event);
    }

    /**
     * Get the recipients for this business alert
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for client deletions
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For high-value clients or clients with active projects, also notify all managers
        if (in_array($event->getSeverity(), ['HIGH', 'CRITICAL']) || 
            $event->wasHighValueClient || 
            $event->activeProjectsCount > 0) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // Include the user who performed the deletion
        if ($event->deletedBy && !in_array($event->deletedBy, $recipients)) {
            $recipients[] = $event->deletedBy;
        }

        // If the deleter has a manager, include them
        if ($event->deletedBy && $event->deletedBy->manager_id && $event->deletedBy->manager_id !== $event->deletedBy->id) {
            $manager = User::find($event->deletedBy->manager_id);
            if ($manager && !in_array($manager, $recipients)) {
                $recipients[] = $manager;
            }
        }

        // For high-value clients, include sales team
        if ($event->wasHighValueClient) {
            $salesTeam = User::where('role', 'sales')->get()->toArray();
            $recipients = array_merge($recipients, $salesTeam);
        }

        // For clients with active projects, include project team
        if ($event->activeProjectsCount > 0) {
            $projectTeam = User::where('role', 'project_manager')->get()->toArray();
            $recipients = array_merge($recipients, $projectTeam);
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param BusinessClientDeletedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(BusinessClientDeletedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\BusinessClientDeletedNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param BusinessClientDeletedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(BusinessClientDeletedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\Business\BusinessClientDeletedMail($eventData, $recipient));
        }
    }

    /**
     * Log the client deletion with detailed information
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function logClientDeletion(BusinessClientDeletedEvent $event): void
    {
        Log::warning('Client deleted', [
            'event_type' => 'business_client_deleted',
            'deleted_client_id' => $event->deletedClient->id,
            'deleted_client_name' => $event->deletedClient->name,
            'deleted_client_email' => $event->deletedClient->email,
            'deleted_by_id' => $event->deletedBy?->id,
            'deleted_by_email' => $event->deletedBy?->email,
            'deletion_method' => $event->deletionMethod,
            'reason' => $event->reason,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'is_self_deletion' => $event->isSelfDeletion,
            'sales_count' => $event->salesCount,
            'total_sales_value' => $event->totalSalesValue,
            'active_projects_count' => $event->activeProjectsCount,
            'backup_created' => $event->backupCreated,
            'backup_path' => $event->backupPath,
            'was_scheduled' => $event->wasScheduled,
            'scheduled_date' => $event->scheduledDate?->format('Y-m-d H:i:s'),
            'was_high_value_client' => $event->wasHighValueClient,
            'lifetime_value' => $event->lifetimeValue,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if client recovery should be considered
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function checkClientRecovery(BusinessClientDeletedEvent $event): void
    {
        if ($event->shouldConsiderRecovery()) {
            try {
                // Create client recovery task
                $this->createClientRecoveryTask($event);
                
                Log::info('Client recovery task created', [
                    'client_id' => $event->deletedClient->id,
                    'client_name' => $event->deletedClient->name,
                    'was_high_value' => $event->wasHighValueClient,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create client recovery task', [
                    'client_id' => $event->deletedClient->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if immediate review is needed
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function checkImmediateReview(BusinessClientDeletedEvent $event): void
    {
        if ($event->requiresImmediateReview()) {
            try {
                // Create immediate review task
                $this->createImmediateReviewTask($event);
                
                Log::warning('Immediate client deletion review required', [
                    'client_id' => $event->deletedClient->id,
                    'deleted_by_id' => $event->deletedBy?->id,
                    'requires_review_reason' => $this->getReviewReason($event),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create immediate review task', [
                    'client_id' => $event->deletedClient->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create client tracking entry
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function createClientTrackingEntry(BusinessClientDeletedEvent $event): void
    {
        try {
            // Create client tracking record
            \App\Models\ClientTracking::create([
                'client_id' => $event->deletedClient->id,
                'client_name' => $event->deletedClient->name,
                'client_email' => $event->deletedClient->email,
                'deletion_method' => $event->deletionMethod,
                'deleted_by_user_id' => $event->deletedBy?->id,
                'deletion_reason' => $event->reason,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'is_self_deletion' => $event->isSelfDeletion,
                'sales_count' => $event->salesCount,
                'total_sales_value' => $event->totalSalesValue,
                'active_projects_count' => $event->activeProjectsCount,
                'backup_created' => $event->backupCreated,
                'backup_path' => $event->backupPath,
                'was_scheduled' => $event->wasScheduled,
                'scheduled_date' => $event->scheduledDate,
                'was_high_value_client' => $event->wasHighValueClient,
                'lifetime_value' => $event->lifetimeValue,
                'business_impact_score' => $this->calculateBusinessImpactScore($event),
                'deletion_indicators' => json_encode($this->getDeletionIndicators($event)),
                'metadata' => json_encode($event->context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create client tracking entry', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'client_id' => $event->deletedClient->id,
                    'deleted_by_id' => $event->deletedBy?->id,
                ],
            ]);
        }
    }

    /**
     * Check for deletion patterns
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function checkDeletionPatterns(BusinessClientDeletedEvent $event): void
    {
        if ($this->hasConcerningPatterns($event)) {
            try {
                // Create deletion pattern analysis
                $this->createDeletionPatternAnalysis($event);
                
                Log::warning('Concerning client deletion pattern detected', [
                    'client_id' => $event->deletedClient->id,
                    'deleted_by_id' => $event->deletedBy?->id,
                    'pattern_indicators' => $this->getPatternIndicators($event),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to analyze client deletion pattern', [
                    'client_id' => $event->deletedClient->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create client impact analysis
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function createClientImpactAnalysis(BusinessClientDeletedEvent $event): void
    {
        try {
            // Create impact analysis record
            \App\Models\ClientImpactAnalysis::create([
                'client_id' => $event->deletedClient->id,
                'client_name' => $event->deletedClient->name,
                'deleted_by_user_id' => $event->deletedBy?->id,
                'revenue_impact' => $this->assessRevenueImpact($event),
                'project_impact' => $this->assessProjectImpact($event),
                'relationship_impact' => $this->assessRelationshipImpact($event),
                'operational_impact' => $this->assessOperationalImpact($event),
                'total_sales_value' => $event->totalSalesValue,
                'active_projects_count' => $event->activeProjectsCount,
                'lifetime_value' => $event->lifetimeValue,
                'impact_score' => $this->calculateBusinessImpactScore($event),
                'recovery_recommendations' => json_encode($this->getRecoveryRecommendations($event)),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create client impact analysis', [
                'client_id' => $event->deletedClient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update business metrics
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function updateBusinessMetrics(BusinessClientDeletedEvent $event): void
    {
        try {
            // Update business metrics
            \App\Models\BusinessMetrics::updateOrCreate(
                [
                    'period' => now()->format('Y-m'),
                ],
                [
                    'total_clients' => \DB::raw('total_clients - 1'),
                    'deleted_clients' => \DB::raw('deleted_clients + 1'),
                    'high_value_clients_deleted' => \DB::raw($event->wasHighValueClient ? 'high_value_clients_deleted + 1' : 'high_value_clients_deleted'),
                    'total_revenue_lost' => \DB::raw("COALESCE(total_revenue_lost, 0) + {$event->totalSalesValue}"),
                    'lifetime_value_lost' => \DB::raw("COALESCE(lifetime_value_lost, 0) + " . ($event->lifetimeValue ?? 0)),
                    'active_projects_disrupted' => \DB::raw("COALESCE(active_projects_disrupted, 0) + {$event->activeProjectsCount}"),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to update business metrics', [
                'client_id' => $event->deletedClient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create client recovery task
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function createClientRecoveryTask(BusinessClientDeletedEvent $event): void
    {
        \App\Models\Task::create([
            'title' => $event->isSelfDeletion 
                ? "Client Recovery - {$event->deletedClient->name} (Self-Deletion)"
                : "Client Recovery - {$event->deletedClient->name}",
            'description' => "Attempt to recover deleted client {$event->deletedClient->name}. " . 
                ($event->wasHighValueClient ? "This was a high-value client with {$event->totalSalesValue} in sales." : "") .
                ($event->activeProjectsCount > 0 ? " Client had {$event->activeProjectsCount} active projects." : "") .
                ($event->backupCreated ? " Backup is available at {$event->backupPath}." : " No backup was created."),
            'assigned_to' => $this->getRecoveryAssignee($event),
            'created_by' => User::where('role', 'admin')->first()->id,
            'priority' => $event->wasHighValueClient ? 'high' : 'medium',
            'status' => 'pending',
            'due_date' => now()->addDays(3),
            'metadata' => json_encode([
                'recovery_details' => [
                    'client_id' => $event->deletedClient->id,
                    'client_name' => $event->deletedClient->name,
                    'backup_path' => $event->backupPath,
                    'deletion_method' => $event->deletionMethod,
                    'deleted_by' => $event->deletedBy?->id,
                    'is_self_deletion' => $event->isSelfDeletion,
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create immediate review task
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function createImmediateReviewTask(BusinessClientDeletedEvent $event): void
    {
        \App\Models\Task::create([
            'title' => "URGENT: Client Deletion Review - {$event->deletedClient->name}",
            'description' => "Immediate review required for deletion of high-value client {$event->deletedClient->name}. " .
                "Deletion performed by {$event->deletedBy?->email} without backup. " .
                "Client had {$event->activeProjectsCount} active projects and {$event->totalSalesValue} in sales.",
            'assigned_to' => $this->getReviewAssignee($event),
            'created_by' => User::where('role', 'admin')->first()->id,
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addHours(24),
            'metadata' => json_encode([
                'review_details' => [
                    'client_id' => $event->deletedClient->id,
                    'client_name' => $event->deletedClient->name,
                    'deleted_by' => $event->deletedBy?->id,
                    'deletion_reason' => $event->reason,
                    'backup_created' => $event->backupCreated,
                    'review_reason' => $this->getReviewReason($event),
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create deletion pattern analysis
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return void
     */
    protected function createDeletionPatternAnalysis(BusinessClientDeletedEvent $event): void
    {
        \App\Models\DeletionPatternAnalysis::create([
            'deleted_by_user_id' => $event->deletedBy?->id,
            'pattern_type' => 'client_deletion_pattern',
            'pattern_indicators' => json_encode($this->getPatternIndicators($event)),
            'triggering_client_id' => $event->deletedClient->id,
            'analysis_date' => now(),
            'risk_score' => $this->calculateDeletionRiskScore($event),
            'recommendations' => json_encode($this->getDeletionRecommendations($event)),
            'created_at' => now(),
        ]);
    }

    /**
     * Calculate business impact score
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return float The business impact score
     */
    protected function calculateBusinessImpactScore(BusinessClientDeletedEvent $event): float
    {
        $score = 0;
        
        // Sales value impact
        $score += min($event->totalSalesValue / 1000, 30); // Max 30 points
        
        // Lifetime value impact
        if ($event->lifetimeValue) {
            $score += min($event->lifetimeValue / 1000, 20); // Max 20 points
        }
        
        // High-value client bonus
        if ($event->wasHighValueClient) {
            $score += 20;
        }
        
        // Active projects impact
        $score += $event->activeProjectsCount * 5; // 5 points per active project
        
        // Backup status penalty
        if (!$event->backupCreated) {
            $score += 15;
        }
        
        // Deletion method penalty for permanent deletion
        if ($event->deletionMethod === 'permanent_delete') {
            $score += 10;
        }
        
        return min($score, 100); // Cap at 100
    }

    /**
     * Get deletion indicators
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return array List of deletion indicators
     */
    protected function getDeletionIndicators(BusinessClientDeletedEvent $event): array
    {
        $indicators = [];
        
        // Check for high-value client deletion
        if ($event->wasHighValueClient) {
            $indicators[] = 'high_value_client_deletion';
        }
        
        // Check for active projects
        if ($event->activeProjectsCount > 0) {
            $indicators[] = 'client_with_active_projects';
        }
        
        // Check for lack of backup
        if (!$event->backupCreated) {
            $indicators[] = 'deletion_without_backup';
        }
        
        // Check for permanent deletion
        if ($event->deletionMethod === 'permanent_delete') {
            $indicators[] = 'permanent_client_deletion';
        }
        
        // Check for self-deletion
        if ($event->isSelfDeletion) {
            $indicators[] = 'client_self_deletion';
        }
        
        // Check for deletion outside business hours
        $hour = now()->hour;
        if ($hour < 8 || $hour > 18) {
            $indicators[] = 'deletion_outside_business_hours';
        }
        
        return $indicators;
    }

    /**
     * Check for concerning patterns
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return bool True if concerning patterns are detected
     */
    protected function hasConcerningPatterns(BusinessClientDeletedEvent $event): bool
    {
        // Check for concerning indicators
        return $this->getPatternIndicators($event) !== [];
    }

    /**
     * Get pattern indicators
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return array List of pattern indicators
     */
    protected function getPatternIndicators(BusinessClientDeletedEvent $event): array
    {
        $indicators = [];
        
        // Check for multiple client deletions by same user
        $recentDeletions = \App\Models\ClientTracking::where('deleted_by_user_id', $event->deletedBy?->id)
            ->where('created_at', '>', now()->subDays(30))
            ->count();
            
        if ($recentDeletions >= 3) {
            $indicators[] = 'multiple_client_deletions';
        }
        
        // Check for high-value client deletions by non-admin
        if ($event->wasHighValueClient && $event->deletedBy && $event->deletedBy->role !== 'admin') {
            $indicators[] = 'high_value_deletion_by_non_admin';
        }
        
        // Check for deletions without proper reason
        if (!$event->reason || strlen($event->reason) < 10) {
            $indicators[] = 'deletion_without_proper_reason';
        }
        
        // Check for bulk deletions
        if ($event->deletionMethod === 'bulk_delete') {
            $indicators[] = 'bulk_client_deletion';
        }
        
        // Check for deletions of clients with active projects
        if ($event->activeProjectsCount > 0) {
            $indicators[] = 'deletion_with_active_projects';
        }
        
        return $indicators;
    }

    /**
     * Calculate deletion risk score
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return float The deletion risk score
     */
    protected function calculateDeletionRiskScore(BusinessClientDeletedEvent $event): float
    {
        $score = 0;
        
        // Base score from business impact
        $score += $this->calculateBusinessImpactScore($event) * 0.6;
        
        // Deletion method risk
        $score += match($event->deletionMethod) {
            'permanent_delete' => 20,
            'bulk_delete' => 15,
            'soft_delete' => 5,
            default => 0,
        };
        
        // User role risk
        if ($event->deletedBy) {
            $score += match($event->deletedBy->role) {
                'admin' => 0,
                'manager' => 5,
                'sales' => 10,
                default => 15,
            };
        }
        
        // Backup status risk
        if (!$event->backupCreated) {
            $score += 15;
        }
        
        return min($score, 100); // Cap at 100
    }

    /**
     * Assess revenue impact
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return string The revenue impact level
     */
    protected function assessRevenueImpact(BusinessClientDeletedEvent $event): string
    {
        if ($event->wasHighValueClient || $event->totalSalesValue > 50000) {
            return 'high';
        } elseif ($event->totalSalesValue > 10000 || $event->lifetimeValue > 25000) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess project impact
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return string The project impact level
     */
    protected function assessProjectImpact(BusinessClientDeletedEvent $event): string
    {
        if ($event->activeProjectsCount > 5) {
            return 'high';
        } elseif ($event->activeProjectsCount > 0) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess relationship impact
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return string The relationship impact level
     */
    protected function assessRelationshipImpact(BusinessClientDeletedEvent $event): string
    {
        if ($event->wasHighValueClient) {
            return 'high';
        } elseif ($event->salesCount > 10) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess operational impact
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return string The operational impact level
     */
    protected function assessOperationalImpact(BusinessClientDeletedEvent $event): string
    {
        if ($event->activeProjectsCount > 3 || $event->salesCount > 20) {
            return 'high';
        } elseif ($event->activeProjectsCount > 0 || $event->salesCount > 5) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recovery recommendations
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return array List of recovery recommendations
     */
    protected function getRecoveryRecommendations(BusinessClientDeletedEvent $event): array
    {
        $recommendations = [];
        
        if ($event->backupCreated) {
            $recommendations[] = 'Restore client from backup';
            $recommendations[] = 'Review restoration process with client';
        } else {
            $recommendations[] = 'Check for alternative data sources';
            $recommendations[] = 'Review backup policies to prevent future losses';
        }
        
        if ($event->wasHighValueClient) {
            $recommendations[] = 'Assign senior account manager for recovery';
            $recommendations[] = 'Offer incentive for client return';
        }
        
        if ($event->activeProjectsCount > 0) {
            $recommendations[] = 'Immediately reassign active projects';
            $recommendations[] = 'Notify all project stakeholders';
        }
        
        if ($event->isSelfDeletion) {
            $recommendations[] = 'Conduct exit interview';
            $recommendations[] = 'Analyze client satisfaction issues';
        }
        
        if (!$event->backupCreated) {
            $recommendations[] = 'Review data retention policies';
            $recommendations[] = 'Implement mandatory backup for client data';
        }
        
        return $recommendations;
    }

    /**
     * Get deletion recommendations
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return array List of deletion recommendations
     */
    protected function getDeletionRecommendations(BusinessClientDeletedEvent $event): array
    {
        $recommendations = [];
        
        if ($this->hasConcerningPatterns($event)) {
            $recommendations[] = 'Review user deletion permissions';
            $recommendations[] = 'Implement additional approval requirements';
            $recommendations[] = 'Monitor user deletion activity';
        }
        
        if ($event->deletedBy && $event->deletedBy->role !== 'admin' && $event->wasHighValueClient) {
            $recommendations[] = 'Restrict high-value client deletion to admins';
            $recommendations[] = 'Require managerial approval for client deletions';
        }
        
        if (!$event->backupCreated) {
            $recommendations[] = 'Enforce mandatory backup before client deletion';
            $recommendations[] = 'Review and improve backup procedures';
        }
        
        if ($event->deletionMethod === 'permanent_delete') {
            $recommendations[] = 'Restrict permanent deletions to system administrators';
            $recommendations[] = 'Implement soft delete as default method';
        }
        
        return $recommendations;
    }

    /**
     * Get review reason
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return string The review reason
     */
    protected function getReviewReason(BusinessClientDeletedEvent $event): string
    {
        if ($event->wasHighValueClient && !$event->backupCreated) {
            return 'High-value client deleted without backup';
        } elseif ($event->activeProjectsCount > 5) {
            return 'Client with multiple active projects deleted';
        } elseif ($event->totalSalesValue > 50000 && !$event->backupCreated) {
            return 'High-revenue client deleted without backup';
        }
        
        return 'Client deletion requires review';
    }

    /**
     * Get recovery assignee
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return int User ID of assignee
     */
    protected function getRecoveryAssignee(BusinessClientDeletedEvent $event): int
    {
        // For high-value clients, assign to admin
        if ($event->wasHighValueClient) {
            $admin = User::where('role', 'admin')->first();
            return $admin ? $admin->id : 1;
        }
        
        // For self-deletions, assign to customer service
        if ($event->isSelfDeletion) {
            $csUser = User::where('role', 'customer_service')->first();
            if ($csUser) {
                return $csUser->id;
            }
        }
        
        // Default to manager
        $manager = User::where('role', 'manager')->first();
        return $manager ? $manager->id : 1;
    }

    /**
     * Get review assignee
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return int User ID of assignee
     */
    protected function getReviewAssignee(BusinessClientDeletedEvent $event): int
    {
        // For urgent reviews, assign to admin
        $admin = User::where('role', 'admin')->first();
        return $admin ? $admin->id : 1;
    }

    /**
     * Prepare event data specific to client deletion events
     *
     * @param BusinessClientDeletedEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'deleted_client_id' => $event->deletedClient->id,
            'deleted_client_name' => $event->deletedClient->name,
            'deleted_client_email' => $event->deletedClient->email,
            'deleted_by_id' => $event->deletedBy?->id,
            'deleted_by_email' => $event->deletedBy?->email,
            'deletion_method' => $event->deletionMethod,
            'reason' => $event->reason,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'is_self_deletion' => $event->isSelfDeletion,
            'sales_count' => $event->salesCount,
            'total_sales_value' => $event->totalSalesValue,
            'active_projects_count' => $event->activeProjectsCount,
            'backup_created' => $event->backupCreated,
            'backup_path' => $event->backupPath,
            'was_scheduled' => $event->wasScheduled,
            'scheduled_date' => $event->scheduledDate?->format('Y-m-d H:i:s'),
            'was_high_value_client' => $event->wasHighValueClient,
            'lifetime_value' => $event->lifetimeValue,
            'requires_immediate_review' => $event->requiresImmediateReview(),
            'should_consider_recovery' => $event->shouldConsiderRecovery(),
        ]);
    }
}