<?php

namespace App\Listeners\UserAction;

use App\Events\UserAction\UserGoalFailedEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for UserGoalFailedEvent
 * 
 * This listener handles goal failure events by creating notifications,
 * logging performance issues, and potentially triggering intervention processes.
 */
class UserGoalFailedListener extends AdministrativeAlertListener
{
    /**
     * Process the goal failure event
     *
     * @param UserGoalFailedEvent $event The goal failure event
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

        // Log the goal failure
        $this->logGoalFailure($event);

        // Check if intervention is needed
        $this->checkInterventionNeeded($event);

        // Check if performance review should be triggered
        $this->checkPerformanceReview($event);

        // Create performance tracking entry
        $this->createPerformanceTrackingEntry($event);

        // Check for performance patterns
        $this->checkPerformancePatterns($event);

        // Create goal adjustment recommendations
        $this->createGoalAdjustmentRecommendations($event);

        // Notify user if not already notified
        $this->notifyUserIfNeeded($event);
    }

    /**
     * Get the recipients for this user action alert
     *
     * @param UserGoalFailedEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify managers for goal failures
        $managers = User::where('role', 'manager')->get()->toArray();
        $recipients = array_merge($recipients, $managers);

        // For critical goals or repeated failures, also notify admins
        if (in_array($event->getSeverity(), ['HIGH', 'CRITICAL']) || 
            $event->consecutiveFailures >= 3 || 
            $event->isCriticalGoal) {
            $admins = User::where('role', 'admin')->get()->toArray();
            $recipients = array_merge($recipients, $admins);
        }

        // Include the user who failed the goal (if different from recipients)
        if ($event->user && !in_array($event->user, $recipients)) {
            $recipients[] = $event->user;
        }

        // If the user has a manager, include them specifically
        if ($event->user && $event->user->manager_id && $event->user->manager_id !== $event->user->id) {
            $manager = User::find($event->user->manager_id);
            if ($manager && !in_array($manager, $recipients)) {
                $recipients[] = $manager;
            }
        }

        // Include HR for critical performance issues
        if ($event->consecutiveFailures >= 3 && $event->isCriticalGoal) {
            $hrUsers = User::where('role', 'hr')->get()->toArray();
            $recipients = array_merge($recipients, $hrUsers);
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param UserGoalFailedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(UserGoalFailedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\UserGoalFailedNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param UserGoalFailedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(UserGoalFailedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\UserAction\UserGoalFailedMail($eventData, $recipient));
        }
    }

    /**
     * Log the goal failure with detailed information
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function logGoalFailure(UserGoalFailedEvent $event): void
    {
        Log::warning('User goal failed', [
            'event_type' => 'user_goal_failed',
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'goal_id' => $event->goal->id,
            'goal_type' => $event->goalType,
            'goal_period' => $event->goalPeriod,
            'target_value' => $event->targetValue,
            'actual_value' => $event->actualValue,
            'achievement_percentage' => $event->achievementPercentage,
            'goal_deadline' => $event->goalDeadline->format('Y-m-d H:i:s'),
            'days_overdue' => $event->daysOverdue,
            'is_critical_goal' => $event->isCriticalGoal,
            'is_repeated_failure' => $event->isRepeatedFailure,
            'consecutive_failures' => $event->consecutiveFailures,
            'contributing_factors' => $event->contributingFactors,
            'intervention_attempted' => $event->interventionAttempted,
            'user_notified' => $event->userNotified,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if intervention is needed
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function checkInterventionNeeded(UserGoalFailedEvent $event): void
    {
        if ($event->requiresImmediateIntervention()) {
            try {
                // Create intervention task
                $this->createInterventionTask($event);
                
                Log::info('Goal failure intervention task created', [
                    'goal_type' => $event->goalType,
                    'user_id' => $event->user?->id,
                    'consecutive_failures' => $event->consecutiveFailures,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create goal failure intervention task', [
                    'goal_type' => $event->goalType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if performance review should be triggered
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function checkPerformanceReview(UserGoalFailedEvent $event): void
    {
        if ($event->shouldTriggerPerformanceReview()) {
            try {
                // Create performance review
                $this->createPerformanceReview($event);
                
                Log::info('Performance review triggered by goal failure', [
                    'goal_type' => $event->goalType,
                    'user_id' => $event->user?->id,
                    'achievement_percentage' => $event->achievementPercentage,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create performance review', [
                    'goal_type' => $event->goalType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create performance tracking entry
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function createPerformanceTrackingEntry(UserGoalFailedEvent $event): void
    {
        try {
            // Create performance tracking record
            \App\Models\PerformanceTracking::create([
                'user_id' => $event->user?->id,
                'goal_id' => $event->goal->id,
                'goal_type' => $event->goalType,
                'goal_period' => $event->goalPeriod,
                'target_value' => $event->targetValue,
                'actual_value' => $event->actualValue,
                'achievement_percentage' => $event->achievementPercentage,
                'goal_deadline' => $event->goalDeadline,
                'days_overdue' => $event->daysOverdue,
                'is_critical_goal' => $event->isCriticalGoal,
                'consecutive_failures' => $event->consecutiveFailures,
                'contributing_factors' => json_encode($event->contributingFactors),
                'intervention_attempted' => $event->interventionAttempted,
                'performance_level' => $this->calculatePerformanceLevel($event),
                'trend_direction' => $event->isRepeatedFailure ? 'declining' : 'stable',
                'metadata' => json_encode($event->context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create performance tracking entry', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'goal_type' => $event->goalType,
                    'user_id' => $event->user?->id,
                ],
            ]);
        }
    }

    /**
     * Check for performance patterns
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function checkPerformancePatterns(UserGoalFailedEvent $event): void
    {
        if ($this->hasConcerningPatterns($event)) {
            try {
                // Create performance pattern alert
                $this->createPerformancePatternAlert($event);
                
                Log::warning('Concerning performance pattern detected', [
                    'goal_type' => $event->goalType,
                    'user_id' => $event->user?->id,
                    'pattern_indicators' => $this->getPatternIndicators($event),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle performance pattern', [
                    'goal_type' => $event->goalType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create goal adjustment recommendations
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function createGoalAdjustmentRecommendations(UserGoalFailedEvent $event): void
    {
        try {
            // Create goal adjustment recommendations
            \App\Models\GoalAdjustmentRecommendation::create([
                'user_id' => $event->user?->id,
                'goal_id' => $event->goal->id,
                'goal_type' => $event->goalType,
                'current_target' => $event->targetValue,
                'recommended_target' => $this->calculateRecommendedTarget($event),
                'adjustment_reason' => $this->getAdjustmentReason($event),
                'achievement_percentage' => $event->achievementPercentage,
                'contributing_factors' => json_encode($event->contributingFactors),
                'priority' => $this->getAdjustmentPriority($event),
                'status' => 'pending_review',
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create goal adjustment recommendations', [
                'goal_type' => $event->goalType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify user if not already notified
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function notifyUserIfNeeded(UserGoalFailedEvent $event): void
    {
        if (!$event->userNotified && $event->user) {
            try {
                // Send user notification
                $event->user->notify(
                    new \App\Notifications\GoalFailureUserNotification($event)
                );
                
                Log::info('User notified of goal failure', [
                    'goal_type' => $event->goalType,
                    'user_id' => $event->user->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to notify user of goal failure', [
                    'goal_type' => $event->goalType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create intervention task
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function createInterventionTask(UserGoalFailedEvent $event): void
    {
        \App\Models\Task::create([
            'title' => "Performance Intervention: {$event->goalType} Goal Failure",
            'description' => "Immediate intervention required for {$event->user?->email} due to critical goal failure. Target: {$event->targetValue}, Achieved: {$event->actualValue} ({$event->achievementPercentage}%). Consecutive failures: {$event->consecutiveFailures}.",
            'assigned_to' => $this->getInterventionAssignee($event),
            'created_by' => User::where('role', 'manager')->first()->id,
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addHours(48),
            'metadata' => json_encode([
                'goal_failure' => [
                    'type' => $event->goalType,
                    'user_id' => $event->user?->id,
                    'target' => $event->targetValue,
                    'actual' => $event->actualValue,
                    'consecutive_failures' => $event->consecutiveFailures,
                    'is_critical' => $event->isCriticalGoal,
                    'occurred_at' => $event->occurredAt->toISOString(),
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create performance review
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function createPerformanceReview(UserGoalFailedEvent $event): void
    {
        \App\Models\PerformanceReview::create([
            'user_id' => $event->user?->id,
            'review_type' => 'goal_failure_review',
            'trigger_reason' => "{$event->goalType} goal failure - {$event->achievementPercentage}% achievement",
            'reviewer_id' => $this->getPerformanceReviewer($event),
            'priority' => $event->consecutiveFailures >= 3 ? 'high' : 'medium',
            'status' => 'scheduled',
            'scheduled_date' => now()->addDays(7),
            'metadata' => json_encode([
                'goal_failure_details' => [
                    'goal_type' => $event->goalType,
                    'target' => $event->targetValue,
                    'actual' => $event->actualValue,
                    'achievement_percentage' => $event->achievementPercentage,
                    'consecutive_failures' => $event->consecutiveFailures,
                    'contributing_factors' => $event->contributingFactors,
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create performance pattern alert
     *
     * @param UserGoalFailedEvent $event The event
     * @return void
     */
    protected function createPerformancePatternAlert(UserGoalFailedEvent $event): void
    {
        \App\Models\PerformanceAlert::create([
            'type' => 'performance_pattern_decline',
            'severity' => $event->consecutiveFailures >= 3 ? 'HIGH' : 'MEDIUM',
            'user_id' => $event->user?->id,
            'description' => "Concerning performance pattern detected for {$event->goalType} goals. {$event->consecutiveFailures} consecutive failures with {$event->achievementPercentage}% average achievement.",
            'status' => 'active',
            'pattern_indicators' => json_encode($this->getPatternIndicators($event)),
            'created_at' => now(),
        ]);
    }

    /**
     * Calculate performance level
     *
     * @param UserGoalFailedEvent $event The event
     * @return string The performance level
     */
    protected function calculatePerformanceLevel(UserGoalFailedEvent $event): string
    {
        if ($event->achievementPercentage < 25) {
            return 'poor';
        } elseif ($event->achievementPercentage < 50) {
            return 'below_expectations';
        } elseif ($event->achievementPercentage < 75) {
            return 'meets_minimum';
        } elseif ($event->achievementPercentage < 100) {
            return 'good';
        }
        
        return 'excellent';
    }

    /**
     * Check for concerning patterns
     *
     * @param UserGoalFailedEvent $event The event
     * @return bool True if concerning patterns are detected
     */
    protected function hasConcerningPatterns(UserGoalFailedEvent $event): bool
    {
        // Check for concerning indicators
        return $this->getPatternIndicators($event) !== [];
    }

    /**
     * Get pattern indicators
     *
     * @param UserGoalFailedEvent $event The event
     * @return array List of pattern indicators
     */
    protected function getPatternIndicators(UserGoalFailedEvent $event): array
    {
        $indicators = [];
        
        // Check for consecutive failures
        if ($event->consecutiveFailures >= 3) {
            $indicators[] = 'multiple_consecutive_failures';
        }
        
        // Check for very low achievement
        if ($event->achievementPercentage < 25) {
            $indicators[] = 'very_low_achievement';
        }
        
        // Check for critical goal failures
        if ($event->isCriticalGoal && $event->achievementPercentage < 50) {
            $indicators[] = 'critical_goal_underperformance';
        }
        
        // Check for overdue goals
        if ($event->daysOverdue && $event->daysOverdue > 30) {
            $indicators[] = 'significantly_overdue';
        }
        
        // Check for multiple goal type failures
        $recentFailures = \App\Models\PerformanceTracking::where('user_id', $event->user?->id)
            ->where('achievement_percentage', '<', 75)
            ->where('created_at', '>', now()->subDays(90))
            ->distinct('goal_type')
            ->count();
            
        if ($recentFailures >= 3) {
            $indicators[] = 'multiple_goal_type_failures';
        }
        
        return $indicators;
    }

    /**
     * Calculate recommended target
     *
     * @param UserGoalFailedEvent $event The event
     * @return float The recommended target
     */
    protected function calculateRecommendedTarget(UserGoalFailedEvent $event): float
    {
        // Calculate a more realistic target based on current performance
        if ($event->achievementPercentage < 25) {
            return $event->targetValue * 0.6; // Reduce by 40%
        } elseif ($event->achievementPercentage < 50) {
            return $event->targetValue * 0.8; // Reduce by 20%
        } elseif ($event->achievementPercentage < 75) {
            return $event->targetValue * 0.9; // Reduce by 10%
        }
        
        return $event->targetValue * 0.95; // Slight reduction
    }

    /**
     * Get adjustment reason
     *
     * @param UserGoalFailedEvent $event The event
     * @return string The adjustment reason
     */
    protected function getAdjustmentReason(UserGoalFailedEvent $event): string
    {
        if ($event->achievementPercentage < 25) {
            return 'Significant underperformance requires target adjustment';
        } elseif ($event->consecutiveFailures >= 3) {
            return 'Repeated failures indicate unrealistic target';
        } elseif ($event->isCriticalGoal && $event->achievementPercentage < 50) {
            return 'Critical goal consistently not achievable';
        }
        
        return 'Performance below target requires adjustment';
    }

    /**
     * Get adjustment priority
     *
     * @param UserGoalFailedEvent $event The event
     * @return string The adjustment priority
     */
    protected function getAdjustmentPriority(UserGoalFailedEvent $event): string
    {
        if ($event->isCriticalGoal && $event->consecutiveFailures >= 3) {
            return 'high';
        } elseif ($event->achievementPercentage < 25) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get intervention assignee
     *
     * @param UserGoalFailedEvent $event The event
     * @return int User ID of assignee
     */
    protected function getInterventionAssignee(UserGoalFailedEvent $event): int
    {
        // Assign to user's manager if available
        if ($event->user && $event->user->manager_id) {
            return $event->user->manager_id;
        }
        
        // Find a manager to assign the task to
        $manager = User::where('role', 'manager')->first();
        return $manager ? $manager->id : 1;
    }

    /**
     * Get performance reviewer
     *
     * @param UserGoalFailedEvent $event The event
     * @return int User ID of reviewer
     */
    protected function getPerformanceReviewer(UserGoalFailedEvent $event): int
    {
        // Assign to user's manager if available
        if ($event->user && $event->user->manager_id) {
            return $event->user->manager_id;
        }
        
        // Find a manager to assign the review to
        $manager = User::where('role', 'manager')->first();
        return $manager ? $manager->id : 1;
    }

    /**
     * Prepare event data specific to goal failure events
     *
     * @param UserGoalFailedEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'goal_id' => $event->goal->id,
            'goal_type' => $event->goalType,
            'goal_period' => $event->goalPeriod,
            'target_value' => $event->targetValue,
            'actual_value' => $event->actualValue,
            'achievement_percentage' => $event->achievementPercentage,
            'goal_deadline' => $event->goalDeadline->format('Y-m-d H:i:s'),
            'days_overdue' => $event->daysOverdue,
            'is_critical_goal' => $event->isCriticalGoal,
            'is_repeated_failure' => $event->isRepeatedFailure,
            'consecutive_failures' => $event->consecutiveFailures,
            'contributing_factors' => $event->contributingFactors,
            'intervention_attempted' => $event->interventionAttempted,
            'user_notified' => $event->userNotified,
            'requires_immediate_intervention' => $event->requiresImmediateIntervention(),
            'should_trigger_performance_review' => $event->shouldTriggerPerformanceReview(),
        ]);
    }
}