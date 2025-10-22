<?php

namespace App\Listeners\Business;

use App\Events\Business\BusinessPaymentStatusChangedEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for BusinessPaymentStatusChangedEvent
 * 
 * This listener handles payment status change events by creating notifications,
 * logging payment updates, and potentially triggering collection or recovery processes.
 */
class BusinessPaymentStatusChangedListener extends AdministrativeAlertListener
{
    /**
     * Process the payment status change event
     *
     * @param BusinessPaymentStatusChangedEvent $event The payment status change event
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

        // Log the payment status change
        $this->logPaymentStatusChange($event);

        // Check if collection efforts should be triggered
        $this->checkCollectionEfforts($event);

        // Check if immediate attention is needed
        $this->checkImmediateAttention($event);

        // Create payment tracking entry
        $this->createPaymentTrackingEntry($event);

        // Check for payment patterns
        $this->checkPaymentPatterns($event);

        // Create payment status history
        $this->createPaymentStatusHistory($event);

        // Update client payment monitoring
        $this->updateClientPaymentMonitoring($event);
    }

    /**
     * Get the recipients for this business alert
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for payment status changes
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For payment failures or high-value payment issues, also notify all managers
        if (in_array($event->getSeverity(), ['HIGH', 'CRITICAL']) || 
            $event->isPaymentFailure || 
            ($event->isHighValue && $event->isPaymentDelay)) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // Include the user who triggered the status change
        if ($event->user && !in_array($event->user, $recipients)) {
            $recipients[] = $event->user;
        }

        // For payment failures, include finance team
        if ($event->isPaymentFailure) {
            $financeUsers = User::where('role', 'finance')->get()->toArray();
            $recipients = array_merge($recipients, $financeUsers);
        }

        // For collection-related status changes, include collections team
        if ($event->shouldTriggerCollection()) {
            $collectionsUsers = User::where('role', 'collections')->get()->toArray();
            $recipients = array_merge($recipients, $collectionsUsers);
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(BusinessPaymentStatusChangedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\BusinessPaymentStatusChangedNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(BusinessPaymentStatusChangedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\Business\BusinessPaymentStatusChangedMail($eventData, $recipient));
        }
    }

    /**
     * Log the payment status change with detailed information
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function logPaymentStatusChange(BusinessPaymentStatusChangedEvent $event): void
    {
        Log::info('Payment status changed', [
            'event_type' => 'business_payment_status_changed',
            'payment_id' => $event->payment->id,
            'client_id' => $event->client->id,
            'client_name' => $event->client->name,
            'previous_status' => $event->previousStatus,
            'new_status' => $event->newStatus,
            'payment_amount' => $event->paymentAmount,
            'currency' => $event->currency,
            'due_date' => $event->dueDate?->format('Y-m-d H:i:s'),
            'days_overdue' => $event->daysOverdue,
            'is_payment_failure' => $event->isPaymentFailure,
            'is_payment_delay' => $event->isPaymentDelay,
            'is_payment_recovery' => $event->isPaymentRecovery,
            'change_reason' => $event->changeReason,
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'payment_method' => $event->paymentMethod,
            'requires_follow_up' => $event->requiresFollowUp,
            'previous_status_changes' => $event->previousStatusChanges,
            'is_high_value' => $event->isHighValue,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if collection efforts should be triggered
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function checkCollectionEfforts(BusinessPaymentStatusChangedEvent $event): void
    {
        if ($event->shouldTriggerCollection()) {
            try {
                // Create collection task
                $this->createCollectionTask($event);
                
                Log::info('Collection efforts triggered', [
                    'payment_id' => $event->payment->id,
                    'client_id' => $event->client->id,
                    'days_overdue' => $event->daysOverdue,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to trigger collection efforts', [
                    'payment_id' => $event->payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if immediate attention is needed
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function checkImmediateAttention(BusinessPaymentStatusChangedEvent $event): void
    {
        if ($event->requiresImmediateAttention()) {
            try {
                // Create urgent attention task
                $this->createUrgentAttentionTask($event);
                
                Log::warning('Urgent payment attention required', [
                    'payment_id' => $event->payment->id,
                    'client_id' => $event->client->id,
                    'severity' => $event->getSeverity(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create urgent attention task', [
                    'payment_id' => $event->payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create payment tracking entry
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function createPaymentTrackingEntry(BusinessPaymentStatusChangedEvent $event): void
    {
        try {
            // Create payment tracking record
            \App\Models\PaymentTracking::create([
                'payment_id' => $event->payment->id,
                'client_id' => $event->client->id,
                'previous_status' => $event->previousStatus,
                'new_status' => $event->newStatus,
                'payment_amount' => $event->paymentAmount,
                'currency' => $event->currency,
                'due_date' => $event->dueDate,
                'days_overdue' => $event->daysOverdue,
                'is_payment_failure' => $event->isPaymentFailure,
                'is_payment_delay' => $event->isPaymentDelay,
                'is_payment_recovery' => $event->isPaymentRecovery,
                'change_reason' => $event->changeReason,
                'changed_by_user_id' => $event->user?->id,
                'payment_method' => $event->paymentMethod,
                'requires_follow_up' => $event->requiresFollowUp,
                'previous_status_changes' => $event->previousStatusChanges,
                'is_high_value' => $event->isHighValue,
                'urgency_level' => $this->calculateUrgencyLevel($event),
                'risk_indicators' => json_encode($this->getRiskIndicators($event)),
                'metadata' => json_encode($event->context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create payment tracking entry', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'payment_id' => $event->payment->id,
                    'client_id' => $event->client->id,
                ],
            ]);
        }
    }

    /**
     * Check for payment patterns
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function checkPaymentPatterns(BusinessPaymentStatusChangedEvent $event): void
    {
        if ($this->hasConcerningPatterns($event)) {
            try {
                // Create payment pattern analysis
                $this->createPaymentPatternAnalysis($event);
                
                Log::warning('Concerning payment pattern detected', [
                    'payment_id' => $event->payment->id,
                    'client_id' => $event->client->id,
                    'pattern_indicators' => $this->getPatternIndicators($event),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to analyze payment pattern', [
                    'payment_id' => $event->payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create payment status history
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function createPaymentStatusHistory(BusinessPaymentStatusChangedEvent $event): void
    {
        try {
            // Create status history record
            \App\Models\PaymentStatusHistory::create([
                'payment_id' => $event->payment->id,
                'client_id' => $event->client->id,
                'previous_status' => $event->previousStatus,
                'new_status' => $event->newStatus,
                'change_reason' => $event->changeReason,
                'changed_by_user_id' => $event->user?->id,
                'change_timestamp' => $event->occurredAt,
                'days_overdue_at_change' => $event->daysOverdue,
                'is_automated_change' => is_null($event->user),
                'metadata' => json_encode([
                    'payment_amount' => $event->paymentAmount,
                    'payment_method' => $event->paymentMethod,
                    'previous_changes_count' => $event->previousStatusChanges,
                ]),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create payment status history', [
                'payment_id' => $event->payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update client payment monitoring
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function updateClientPaymentMonitoring(BusinessPaymentStatusChangedEvent $event): void
    {
        try {
            // Update client payment monitoring
            \App\Models\ClientPaymentMonitoring::updateOrCreate(
                [
                    'client_id' => $event->client->id,
                    'period' => now()->format('Y-m'),
                ],
                [
                    'total_payments' => \DB::raw('total_payments + 1'),
                    'failed_payments' => \DB::raw($event->isPaymentFailure ? 'failed_payments + 1' : 'failed_payments'),
                    'delayed_payments' => \DB::raw($event->isPaymentDelay ? 'delayed_payments + 1' : 'delayed_payments'),
                    'recovered_payments' => \DB::raw($event->isPaymentRecovery ? 'recovered_payments + 1' : 'recovered_payments'),
                    'high_value_payments' => \DB::raw($event->isHighValue ? 'high_value_payments + 1' : 'high_value_payments'),
                    'total_amount_overdue' => \DB::raw($event->isPaymentDelay ? "COALESCE(total_amount_overdue, 0) + {$event->paymentAmount}" : 'total_amount_overdue'),
                    'last_status_change' => now(),
                    'risk_score' => $this->calculateClientRiskScore($event),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to update client payment monitoring', [
                'client_id' => $event->client->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create collection task
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function createCollectionTask(BusinessPaymentStatusChangedEvent $event): void
    {
        \App\Models\Task::create([
            'title' => "Collection Required - {$event->client->name} ({$event->currency}{$event->paymentAmount})",
            'description' => "Payment collection required for client {$event->client->name}. Payment amount: {$event->currency}{$event->paymentAmount}, Status: {$event->newStatus}, Days overdue: {$event->daysOverdue}.",
            'assigned_to' => $this->getCollectionsAssignee($event),
            'created_by' => $event->user?->id ?? User::where('role', 'admin')->first()->id,
            'priority' => $event->daysOverdue >= 60 ? 'high' : 'medium',
            'status' => 'pending',
            'due_date' => now()->addDays($event->daysOverdue >= 60 ? 3 : 7),
            'metadata' => json_encode([
                'collection_details' => [
                    'payment_id' => $event->payment->id,
                    'client_id' => $event->client->id,
                    'amount' => $event->paymentAmount,
                    'days_overdue' => $event->daysOverdue,
                    'payment_method' => $event->paymentMethod,
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create urgent attention task
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function createUrgentAttentionTask(BusinessPaymentStatusChangedEvent $event): void
    {
        \App\Models\Task::create([
            'title' => "URGENT: Payment Issue - {$event->client->name}",
            'description' => "Urgent attention required for payment issue with client {$event->client->name}. Payment amount: {$event->currency}{$event->paymentAmount}, Status changed to: {$event->newStatus}.",
            'assigned_to' => $this->getUrgentAttentionAssignee($event),
            'created_by' => $event->user?->id ?? User::where('role', 'admin')->first()->id,
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addHours(24),
            'metadata' => json_encode([
                'urgent_details' => [
                    'payment_id' => $event->payment->id,
                    'client_id' => $event->client->id,
                    'amount' => $event->paymentAmount,
                    'severity' => $event->getSeverity(),
                    'previous_status' => $event->previousStatus,
                    'new_status' => $event->newStatus,
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create payment pattern analysis
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return void
     */
    protected function createPaymentPatternAnalysis(BusinessPaymentStatusChangedEvent $event): void
    {
        \App\Models\PaymentPatternAnalysis::create([
            'client_id' => $event->client->id,
            'pattern_type' => 'payment_status_change_pattern',
            'pattern_indicators' => json_encode($this->getPatternIndicators($event)),
            'triggering_payment_id' => $event->payment->id,
            'analysis_date' => now(),
            'risk_score' => $this->calculatePaymentRiskScore($event),
            'recommendations' => json_encode($this->getPatternRecommendations($event)),
            'created_at' => now(),
        ]);
    }

    /**
     * Calculate urgency level
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return string The urgency level
     */
    protected function calculateUrgencyLevel(BusinessPaymentStatusChangedEvent $event): string
    {
        if ($event->isPaymentFailure && $event->isHighValue) {
            return 'critical';
        } elseif ($event->isPaymentFailure || ($event->isPaymentDelay && $event->daysOverdue >= 30)) {
            return 'high';
        } elseif ($event->isPaymentDelay || $event->requiresFollowUp) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get risk indicators
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return array List of risk indicators
     */
    protected function getRiskIndicators(BusinessPaymentStatusChangedEvent $event): array
    {
        $indicators = [];
        
        // Check for payment failure
        if ($event->isPaymentFailure) {
            $indicators[] = 'payment_failure';
        }
        
        // Check for significant delay
        if ($event->daysOverdue && $event->daysOverdue >= 30) {
            $indicators[] = 'significant_delay';
        }
        
        // Check for high value
        if ($event->isHighValue) {
            $indicators[] = 'high_value_payment';
        }
        
        // Check for multiple status changes
        if ($event->previousStatusChanges >= 3) {
            $indicators[] = 'multiple_status_changes';
        }
        
        // Check for recovery after failure
        if ($event->isPaymentRecovery) {
            $indicators[] = 'payment_recovery';
        }
        
        return $indicators;
    }

    /**
     * Check for concerning patterns
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return bool True if concerning patterns are detected
     */
    protected function hasConcerningPatterns(BusinessPaymentStatusChangedEvent $event): bool
    {
        // Check for concerning indicators
        return $this->getPatternIndicators($event) !== [];
    }

    /**
     * Get pattern indicators
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return array List of pattern indicators
     */
    protected function getPatternIndicators(BusinessPaymentStatusChangedEvent $event): array
    {
        $indicators = [];
        
        // Check for repeated payment failures
        $recentFailures = \App\Models\PaymentTracking::where('client_id', $event->client->id)
            ->where('is_payment_failure', true)
            ->where('created_at', '>', now()->subDays(90))
            ->count();
            
        if ($recentFailures >= 3) {
            $indicators[] = 'repeated_payment_failures';
        }
        
        // Check for frequent status changes
        if ($event->previousStatusChanges >= 5) {
            $indicators[] = 'frequent_status_changes';
        }
        
        // Check for chronic delays
        $chronicDelays = \App\Models\PaymentTracking::where('client_id', $event->client->id)
            ->where('days_overdue', '>=', 15)
            ->where('created_at', '>', now()->subDays(180))
            ->count();
            
        if ($chronicDelays >= 5) {
            $indicators[] = 'chronic_payment_delays';
        }
        
        // Check for high-value payment issues
        if ($event->isHighValue && $event->isPaymentFailure) {
            $indicators[] = 'high_value_payment_failure';
        }
        
        return $indicators;
    }

    /**
     * Calculate payment risk score
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return float The risk score
     */
    protected function calculatePaymentRiskScore(BusinessPaymentStatusChangedEvent $event): float
    {
        $score = 0;
        
        // Base score from payment failure
        if ($event->isPaymentFailure) {
            $score += 40;
        }
        
        // Delay score
        if ($event->daysOverdue) {
            $score += min($event->daysOverdue / 7, 30); // Max 30 points
        }
        
        // High-value penalty
        if ($event->isHighValue) {
            $score += 20;
        }
        
        // Multiple changes penalty
        $score += min($event->previousStatusChanges * 5, 20); // Max 20 points
        
        // Recovery bonus
        if ($event->isPaymentRecovery) {
            $score -= 10;
        }
        
        return max(0, min($score, 100)); // Cap between 0 and 100
    }

    /**
     * Calculate client risk score
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return float The client risk score
     */
    protected function calculateClientRiskScore(BusinessPaymentStatusChangedEvent $event): float
    {
        // Get current client monitoring data
        $monitoring = \App\Models\ClientPaymentMonitoring::where('client_id', $event->client->id)
            ->where('period', now()->format('Y-m'))
            ->first();
            
        if (!$monitoring) {
            return $this->calculatePaymentRiskScore($event);
        }
        
        $score = 0;
        
        // Failure rate
        if ($monitoring->total_payments > 0) {
            $failureRate = ($monitoring->failed_payments / $monitoring->total_payments) * 100;
            $score += min($failureRate, 40);
        }
        
        // Delay rate
        if ($monitoring->total_payments > 0) {
            $delayRate = ($monitoring->delayed_payments / $monitoring->total_payments) * 100;
            $score += min($delayRate, 30);
        }
        
        // High-value issues
        if ($monitoring->high_value_payments > 0) {
            $score += ($monitoring->failed_payments / $monitoring->high_value_payments) * 20;
        }
        
        // Recovery rate (reduces score)
        if ($monitoring->failed_payments > 0) {
            $recoveryRate = ($monitoring->recovered_payments / $monitoring->failed_payments) * 100;
            $score -= min($recoveryRate, 20);
        }
        
        return max(0, min($score, 100));
    }

    /**
     * Get pattern recommendations
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return array List of recommendations
     */
    protected function getPatternRecommendations(BusinessPaymentStatusChangedEvent $event): array
    {
        $recommendations = [];
        
        if ($event->isPaymentFailure) {
            $recommendations[] = 'Contact client immediately';
            $recommendations[] = 'Review payment method validity';
            $recommendations[] = 'Consider alternative payment arrangements';
        }
        
        if ($event->daysOverdue && $event->daysOverdue >= 30) {
            $recommendations[] = 'Initiate collection procedures';
            $recommendations[] = 'Review client credit terms';
        }
        
        if ($event->previousStatusChanges >= 3) {
            $recommendations[] = 'Investigate payment processing issues';
            $recommendations[] = 'Review payment system integration';
        }
        
        if ($event->isPaymentRecovery) {
            $recommendations[] = 'Document recovery process';
            $recommendations[] = 'Analyze recovery factors for future prevention';
        }
        
        if ($event->isHighValue && $event->isPaymentFailure) {
            $recommendations[] = 'Escalate to senior management';
            $recommendations[] = 'Consider legal options';
        }
        
        return $recommendations;
    }

    /**
     * Get collections assignee
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return int User ID of assignee
     */
    protected function getCollectionsAssignee(BusinessPaymentStatusChangedEvent $event): int
    {
        // Assign to collections team if available
        $collectionsUser = User::where('role', 'collections')->first();
        if ($collectionsUser) {
            return $collectionsUser->id;
        }
        
        // Otherwise assign to finance team
        $financeUser = User::where('role', 'finance')->first();
        return $financeUser ? $financeUser->id : 1;
    }

    /**
     * Get urgent attention assignee
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return int User ID of assignee
     */
    protected function getUrgentAttentionAssignee(BusinessPaymentStatusChangedEvent $event): int
    {
        // For urgent issues, assign to admin
        $admin = User::where('role', 'admin')->first();
        return $admin ? $admin->id : 1;
    }

    /**
     * Prepare event data specific to payment status change events
     *
     * @param BusinessPaymentStatusChangedEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'payment_id' => $event->payment->id,
            'client_id' => $event->client->id,
            'client_name' => $event->client->name,
            'previous_status' => $event->previousStatus,
            'new_status' => $event->newStatus,
            'payment_amount' => $event->paymentAmount,
            'currency' => $event->currency,
            'due_date' => $event->dueDate?->format('Y-m-d H:i:s'),
            'days_overdue' => $event->daysOverdue,
            'is_payment_failure' => $event->isPaymentFailure,
            'is_payment_delay' => $event->isPaymentDelay,
            'is_payment_recovery' => $event->isPaymentRecovery,
            'change_reason' => $event->changeReason,
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'payment_method' => $event->paymentMethod,
            'requires_follow_up' => $event->requiresFollowUp,
            'previous_status_changes' => $event->previousStatusChanges,
            'is_high_value' => $event->isHighValue,
            'requires_immediate_attention' => $event->requiresImmediateAttention(),
            'should_trigger_collection' => $event->shouldTriggerCollection(),
        ]);
    }
}