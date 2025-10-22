<?php

namespace App\Events\UserAction;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a user goal fails or is not achieved
 * 
 * This event is fired when users fail to achieve their set goals,
 * helping to track performance issues and identify areas needing support.
 */
class UserGoalFailedEvent extends AdministrativeAlertEvent
{
    /**
     * The user who failed to achieve the goal
     */
    public User $user;

    /**
     * The goal that was not achieved
     */
    public object $goal;

    /**
     * The target value that was supposed to be achieved
     */
    public float|int $targetValue;

    /**
     * The actual value achieved
     */
    public float|int $actualValue;

    /**
     * The percentage of the goal that was achieved
     */
    public float $achievementPercentage;

    /**
     * The goal deadline/end date
     */
    public \DateTime $goalDeadline;

    /**
     * The number of days the goal was overdue (if applicable)
     */
    public int|null $daysOverdue;

    /**
     * The type of goal (sales, tasks, content, etc.)
     */
    public string $goalType;

    /**
     * The period of the goal (weekly, monthly, quarterly)
     */
    public string $goalPeriod;

    /**
     * Whether this is a critical goal
     */
    public bool $isCriticalGoal;

    /**
     * Whether this is a repeated failure
     */
    public bool $isRepeatedFailure;

    /**
     * The number of consecutive failures for this goal type
     */
    public int $consecutiveFailures;

    /**
     * The factors that contributed to the failure
     */
    public array $contributingFactors;

    /**
     * Whether intervention was attempted
     */
    public bool $interventionAttempted;

    /**
     * Whether the user was notified about the failure
     */
    public bool $userNotified;

    /**
     * Create a new event instance
     *
     * @param User $user The user who failed the goal
     * @param object $goal The goal that was not achieved
     * @param float|int $targetValue The target value
     * @param float|int $actualValue The actual value achieved
     * @param float $achievementPercentage The achievement percentage
     * @param \DateTime $goalDeadline The goal deadline
     * @param string $goalType The goal type
     * @param string $goalPeriod The goal period
     * @param bool $isCriticalGoal Whether this is critical
     * @param bool $isRepeatedFailure Whether this is repeated
     * @param int $consecutiveFailures Number of consecutive failures
     * @param array $contributingFactors Contributing factors
     * @param bool $interventionAttempted Whether intervention was attempted
     * @param bool $userNotified Whether user was notified
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        User $user,
        object $goal,
        float|int $targetValue,
        float|int $actualValue,
        float $achievementPercentage,
        \DateTime $goalDeadline,
        string $goalType,
        string $goalPeriod,
        bool $isCriticalGoal = false,
        bool $isRepeatedFailure = false,
        int $consecutiveFailures = 1,
        array $contributingFactors = [],
        bool $interventionAttempted = false,
        bool $userNotified = false,
        User|null $initiatedBy = null
    ) {
        $this->user = $user;
        $this->goal = $goal;
        $this->targetValue = $targetValue;
        $this->actualValue = $actualValue;
        $this->achievementPercentage = $achievementPercentage;
        $this->goalDeadline = $goalDeadline;
        $this->goalType = $goalType;
        $this->goalPeriod = $goalPeriod;
        $this->isCriticalGoal = $isCriticalGoal;
        $this->isRepeatedFailure = $isRepeatedFailure;
        $this->consecutiveFailures = $consecutiveFailures;
        $this->contributingFactors = $contributingFactors;
        $this->interventionAttempted = $interventionAttempted;
        $this->userNotified = $userNotified;

        // Calculate days overdue
        $now = new \DateTime();
        $this->daysOverdue = $now > $goalDeadline ? $now->diff($goalDeadline)->days : null;

        $context = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'goal_id' => $goal->id,
            'target_value' => $targetValue,
            'actual_value' => $actualValue,
            'achievement_percentage' => $achievementPercentage,
            'goal_deadline' => $goalDeadline->format('Y-m-d H:i:s'),
            'days_overdue' => $this->daysOverdue,
            'goal_type' => $goalType,
            'goal_period' => $goalPeriod,
            'is_critical_goal' => $isCriticalGoal,
            'is_repeated_failure' => $isRepeatedFailure,
            'consecutive_failures' => $consecutiveFailures,
            'contributing_factors' => $contributingFactors,
            'intervention_attempted' => $interventionAttempted,
            'user_notified' => $userNotified,
        ];

        parent::__construct($context, $initiatedBy);
    }

    /**
     * Get the category of this event
     *
     * @return string The event category
     */
    public function getCategory(): string
    {
        return 'UserAction';
    }

    /**
     * Get the severity level of this event
     *
     * @return string The severity level
     */
    public function getSeverity(): string
    {
        // Critical for critical goals with repeated failures
        if ($this->isCriticalGoal && $this->consecutiveFailures >= 3) {
            return self::SEVERITY_HIGH;
        } elseif ($this->isCriticalGoal || $this->consecutiveFailures >= 3) {
            return self::SEVERITY_MEDIUM;
        } elseif ($this->achievementPercentage < 25) {
            return self::SEVERITY_MEDIUM;
        }
        
        return self::SEVERITY_LOW;
    }

    /**
     * Get the title of this event
     *
     * @return string The event title
     */
    public function getTitle(): string
    {
        if ($this->isCriticalGoal && $this->isRepeatedFailure) {
            return "Critical Goal Repeatedly Failed";
        } elseif ($this->isCriticalGoal) {
            return "Critical Goal Failed";
        } elseif ($this->isRepeatedFailure) {
            return "Goal Repeatedly Failed";
        }
        
        return "Goal Failed";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "User '{$this->user->email}' (ID: {$this->user->id}) failed to achieve their ";
        $description .= "{$this->goalPeriod} {$this->goalType} goal. ";
        
        $description .= "Target: {$this->targetValue}, Achieved: {$this->actualValue} ";
        $description .= "({$this->achievementPercentage}% completion). ";
        
        if ($this->isCriticalGoal) {
            $description .= "This was a critical goal. ";
        }
        
        if ($this->isRepeatedFailure) {
            $description .= "This is the {$this->consecutiveFailures}th consecutive failure. ";
        }
        
        if ($this->daysOverdue) {
            $description .= "Goal was {$this->daysOverdue} days overdue. ";
        }
        
        if (!empty($this->contributingFactors)) {
            $description .= "Contributing factors: " . implode(', ', $this->contributingFactors) . ". ";
        }
        
        if ($this->interventionAttempted) {
            $description .= "Intervention was attempted. ";
        }
        
        if (!$this->userNotified) {
            $description .= "User has not been notified yet. ";
        }
        
        return $description;
    }

    /**
     * Get the URL for taking action on this event
     *
     * @return string|null The action URL
     */
    public function getActionUrl(): string|null
    {
        return route('admin.users.show', $this->user->id) . '#goals';
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for critical goals
        if ($this->isCriticalGoal || $this->consecutiveFailures >= 3) {
            return 'user-action-high-alerts';
        }
        
        return 'user-action-alerts';
    }

    /**
     * Get the email subject for notifications
     *
     * @return string The email subject
     */
    public function getEmailSubject(): string
    {
        if ($this->isCriticalGoal && $this->isRepeatedFailure) {
            return "[HIGH] Critical Goal Repeatedly Failed - {$this->user->email}";
        } elseif ($this->isCriticalGoal) {
            return "[MEDIUM] Critical Goal Failed - {$this->user->email}";
        } elseif ($this->isRepeatedFailure) {
            return "[MEDIUM] Goal Repeatedly Failed ({$this->consecutiveFailures} times)";
        }
        
        return "[USER ACTION] Goal Failed - {$this->goalType}";
    }

    /**
     * Check if this failure requires immediate intervention
     *
     * @return bool True if immediate intervention is required
     */
    public function requiresImmediateIntervention(): bool
    {
        return ($this->isCriticalGoal && $this->consecutiveFailures >= 3) ||
               ($this->achievementPercentage < 10 && $this->isCriticalGoal);
    }

    /**
     * Check if performance review should be triggered
     *
     * @return bool True if performance review is needed
     */
    public function shouldTriggerPerformanceReview(): bool
    {
        return $this->consecutiveFailures >= 2 || 
               ($this->isCriticalGoal && $this->achievementPercentage < 50);
    }

    /**
     * Get additional metadata for logging and analytics
     *
     * @return array Additional metadata
     */
    public function getMetadata(): array
    {
        return [
            'urgency_level' => $this->calculateUrgencyLevel(),
            'requires_intervention' => $this->requiresImmediateIntervention(),
            'recommended_actions' => $this->getRecommendedActions(),
            'performance_assessment' => $this->assessPerformance(),
            'failure_category' => $this->categorizeFailure(),
            'support_options' => $this->getSupportOptions(),
        ];
    }

    /**
     * Calculate the urgency level based on various factors
     *
     * @return string The urgency level (low, medium, high, critical)
     */
    private function calculateUrgencyLevel(): string
    {
        if ($this->isCriticalGoal && $this->consecutiveFailures >= 3) {
            return 'high';
        } elseif ($this->isCriticalGoal || $this->consecutiveFailures >= 3) {
            return 'medium';
        } elseif ($this->achievementPercentage < 25) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the goal failure
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->isCriticalGoal && $this->consecutiveFailures >= 3) {
            $actions[] = 'Schedule urgent performance review';
            $actions[] = 'Consider temporary role reassignment';
            $actions[] = 'Provide additional training/support';
        }
        
        if ($this->consecutiveFailures >= 2) {
            $actions[] = 'Schedule performance review';
            $actions[] = 'Analyze performance trends';
        }
        
        if ($this->achievementPercentage < 25) {
            $actions[] = 'Review goal setting process';
            $actions[] = 'Assess if goals are realistic';
            $actions[] = 'Provide immediate coaching';
        }
        
        if (!$this->interventionAttempted) {
            $actions[] = 'Initiate manager intervention';
            $actions[] = 'Schedule one-on-one meeting';
        }
        
        if (!$this->userNotified) {
            $actions[] = 'Notify user of failure';
            $actions[] = 'Provide constructive feedback';
        }
        
        if ($this->shouldTriggerPerformanceReview()) {
            $actions[] = 'Document performance issues';
            $actions[] = 'Create performance improvement plan';
        }
        
        return $actions;
    }

    /**
     * Assess the performance impact
     *
     * @return array Performance assessment details
     */
    private function assessPerformance(): array
    {
        return [
            'performance_level' => $this->calculatePerformanceLevel(),
            'trend_direction' => $this->isRepeatedFailure ? 'declining' : 'stable',
            'impact_on_team' => $this->assessTeamImpact(),
            'business_impact' => $this->assessBusinessImpact(),
        ];
    }

    /**
     * Calculate the performance level
     *
     * @return string The performance level
     */
    private function calculatePerformanceLevel(): string
    {
        if ($this->achievementPercentage < 25) {
            return 'poor';
        } elseif ($this->achievementPercentage < 50) {
            return 'below_expectations';
        } elseif ($this->achievementPercentage < 75) {
            return 'meets_minimum';
        } elseif ($this->achievementPercentage < 100) {
            return 'good';
        }
        
        return 'excellent';
    }

    /**
     * Assess the team impact
     *
     * @return string The team impact level
     */
    private function assessTeamImpact(): string
    {
        if ($this->isCriticalGoal && $this->consecutiveFailures >= 2) {
            return 'high';
        } elseif ($this->isCriticalGoal) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the business impact
     *
     * @return string The business impact level
     */
    private function assessBusinessImpact(): string
    {
        if ($this->isCriticalGoal && $this->achievementPercentage < 25) {
            return 'high';
        } elseif ($this->isCriticalGoal) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Categorize the failure type
     *
     * @return string The failure category
     */
    private function categorizeFailure(): string
    {
        if ($this->isCriticalGoal && $this->isRepeatedFailure) {
            return 'critical_repeated_failure';
        } elseif ($this->isCriticalGoal) {
            return 'critical_goal_failure';
        } elseif ($this->isRepeatedFailure) {
            return 'repeated_failure';
        } elseif ($this->achievementPercentage < 25) {
            return 'significant_failure';
        }
        
        return 'minor_failure';
    }

    /**
     * Get support options based on the failure
     *
     * @return array Available support options
     */
    private function getSupportOptions(): array
    {
        $options = [
            'manager_coaching',
            'peer_mentoring',
            'additional_training',
        ];
        
        if ($this->achievementPercentage < 50) {
            $options[] = 'goal_adjustment';
            $options[] = 'resource_allocation';
        }
        
        if ($this->isCriticalGoal) {
            $options[] = 'intensive_support';
            $options[] = 'performance_improvement_plan';
        }
        
        if ($this->consecutiveFailures >= 2) {
            $options[] = 'skills_assessment';
            $options[] = 'role_reconsideration';
        }
        
        return $options;
    }
}