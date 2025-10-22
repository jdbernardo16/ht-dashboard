<?php

namespace App\Events\UserAction;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a user performs a bulk operation
 * 
 * This event is fired when users perform bulk operations like mass updates,
 * deletions, or exports, helping to track potentially risky activities.
 */
class UserBulkOperationEvent extends AdministrativeAlertEvent
{
    /**
     * The user who performed the bulk operation
     */
    public User $user;

    /**
     * The type of bulk operation performed
     */
    public string $operationType;

    /**
     * The resource type the operation was performed on
     */
    public string $resourceType;

    /**
     * The number of items affected by the operation
     */
    public int $itemCount;

    /**
     * The specific items that were affected (IDs)
     */
    public array $affectedItems;

    /**
     * The operation parameters/filters used
     */
    public array $operationParameters;

    /**
     * The IP address from which the operation was performed
     */
    public string $ipAddress;

    /**
     * The user agent string from the request
     */
    public string $userAgent;

    /**
     * Whether this operation was scheduled
     */
    public bool $wasScheduled;

    /**
     * The estimated duration of the operation in seconds
     */
    public int|null $estimatedDuration;

    /**
     * Whether this operation is potentially destructive
     */
    public bool $isDestructive;

    /**
     * Whether this operation affects sensitive data
     */
    public bool $affectsSensitiveData;

    /**
     * Whether approval was obtained for this operation
     */
    public bool $hasApproval;

    /**
     * The approver (if approval was obtained)
     */
    public User|null $approver;

    /**
     * The reason provided for the bulk operation
     */
    public string|null $reason;

    /**
     * Create a new event instance
     *
     * @param User $user The user who performed the operation
     * @param string $operationType The type of operation
     * @param string $resourceType The resource type
     * @param int $itemCount Number of items affected
     * @param array $affectedItems The affected item IDs
     * @param string $ipAddress The source IP address
     * @param string $userAgent The browser user agent
     * @param array $operationParameters Operation parameters
     * @param bool $wasScheduled Whether this was scheduled
     * @param int|null $estimatedDuration Estimated duration in seconds
     * @param bool $isDestructive Whether this is destructive
     * @param bool $affectsSensitiveData Whether this affects sensitive data
     * @param bool $hasApproval Whether approval was obtained
     * @param User|null $approver The approver
     * @param string|null $reason The reason for the operation
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        User $user,
        string $operationType,
        string $resourceType,
        int $itemCount,
        array $affectedItems,
        string $ipAddress,
        string $userAgent,
        array $operationParameters = [],
        bool $wasScheduled = false,
        int|null $estimatedDuration = null,
        bool $isDestructive = false,
        bool $affectsSensitiveData = false,
        bool $hasApproval = false,
        User|null $approver = null,
        string|null $reason = null,
        User|null $initiatedBy = null
    ) {
        $this->user = $user;
        $this->operationType = $operationType;
        $this->resourceType = $resourceType;
        $this->itemCount = $itemCount;
        $this->affectedItems = $affectedItems;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->operationParameters = $operationParameters;
        $this->wasScheduled = $wasScheduled;
        $this->estimatedDuration = $estimatedDuration;
        $this->isDestructive = $isDestructive;
        $this->affectsSensitiveData = $affectsSensitiveData;
        $this->hasApproval = $hasApproval;
        $this->approver = $approver;
        $this->reason = $reason;

        $context = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'operation_type' => $operationType,
            'resource_type' => $resourceType,
            'item_count' => $itemCount,
            'affected_items' => $affectedItems,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'operation_parameters' => $operationParameters,
            'was_scheduled' => $wasScheduled,
            'estimated_duration' => $estimatedDuration,
            'is_destructive' => $isDestructive,
            'affects_sensitive_data' => $affectsSensitiveData,
            'has_approval' => $hasApproval,
            'approver_id' => $approver?->id,
            'approver_email' => $approver?->email,
            'reason' => $reason,
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
        // Critical for large destructive operations without approval
        if ($this->isDestructive && $this->itemCount >= 1000 && !$this->hasApproval) {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->isDestructive && !$this->hasApproval) {
            return self::SEVERITY_HIGH;
        } elseif ($this->affectsSensitiveData || $this->itemCount >= 1000) {
            return self::SEVERITY_MEDIUM;
        } elseif ($this->itemCount >= 100) {
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
        if ($this->isDestructive && !$this->hasApproval) {
            return "Unauthorized Destructive Bulk Operation";
        } elseif ($this->isDestructive) {
            return "Destructive Bulk Operation Performed";
        } elseif ($this->affectsSensitiveData) {
            return "Bulk Operation on Sensitive Data";
        } elseif ($this->itemCount >= 1000) {
            return "Large Scale Bulk Operation";
        }
        
        return "Bulk Operation Performed";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "User '{$this->user->email}' (ID: {$this->user->id}) performed a bulk ";
        $description .= "{$this->operationType} operation on {$this->itemCount} {$this->resourceType} items. ";
        
        if ($this->isDestructive) {
            $description .= "This was a destructive operation. ";
        }
        
        if ($this->affectsSensitiveData) {
            $description .= "This operation affects sensitive data. ";
        }
        
        if (!$this->hasApproval && $this->isDestructive) {
            $description .= "WARNING: This destructive operation was performed without proper approval. ";
        } elseif ($this->hasApproval) {
            $approverName = $this->approver ? $this->approver->email : 'Unknown';
            $description .= "Operation was approved by {$approverName}. ";
        }
        
        if ($this->reason) {
            $description .= "Reason: {$this->reason}. ";
        }
        
        if ($this->estimatedDuration) {
            $description .= "Estimated duration: {$this->estimatedDuration} seconds. ";
        }
        
        if ($this->wasScheduled) {
            $description .= "This was a scheduled operation. ";
        }
        
        if (!empty($this->operationParameters)) {
            $description .= "Operation parameters: " . json_encode($this->operationParameters) . ". ";
        }
        
        $description .= "Source: {$this->ipAddress}";
        
        return $description;
    }

    /**
     * Get the URL for taking action on this event
     *
     * @return string|null The action URL
     */
    public function getActionUrl(): string|null
    {
        return route('admin.users.show', $this->user->id) . '#activity';
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for destructive operations
        if ($this->isDestructive || $this->itemCount >= 1000) {
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
        if ($this->isDestructive && !$this->hasApproval) {
            return "[CRITICAL] Unauthorized Destructive Bulk Operation";
        } elseif ($this->isDestructive) {
            return "[HIGH] Destructive Bulk Operation - {$this->operationType}";
        } elseif ($this->affectsSensitiveData) {
            return "[HIGH] Bulk Operation on Sensitive Data";
        } elseif ($this->itemCount >= 1000) {
            return "[MEDIUM] Large Scale Bulk Operation ({$this->itemCount} items)";
        }
        
        return "[USER ACTION] Bulk Operation - {$this->operationType}";
    }

    /**
     * Check if this operation requires immediate review
     *
     * @return bool True if immediate review is required
     */
    public function requiresImmediateReview(): bool
    {
        return ($this->isDestructive && !$this->hasApproval) ||
               ($this->affectsSensitiveData && !$this->hasApproval) ||
               $this->itemCount >= 5000;
    }

    /**
     * Check if this operation should be rolled back
     *
     * @return bool True if rollback should be considered
     */
    public function shouldConsiderRollback(): bool
    {
        return $this->isDestructive && !$this->hasApproval;
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
            'requires_immediate_review' => $this->requiresImmediateReview(),
            'recommended_actions' => $this->getRecommendedActions(),
            'risk_assessment' => $this->assessRisk(),
            'operation_category' => $this->categorizeOperation(),
            'compliance_flags' => $this->getComplianceFlags(),
        ];
    }

    /**
     * Calculate the urgency level based on various factors
     *
     * @return string The urgency level (low, medium, high, critical)
     */
    private function calculateUrgencyLevel(): string
    {
        if ($this->isDestructive && !$this->hasApproval && $this->itemCount >= 1000) {
            return 'critical';
        } elseif ($this->isDestructive && !$this->hasApproval) {
            return 'high';
        } elseif ($this->affectsSensitiveData || $this->itemCount >= 1000) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the operation
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->isDestructive && !$this->hasApproval) {
            $actions[] = 'Immediately review operation';
            $actions[] = 'Consider rollback';
            $actions[] = 'Suspend user privileges';
            $actions[] = 'Contact user for explanation';
        }
        
        if ($this->affectsSensitiveData && !$this->hasApproval) {
            $actions[] = 'Review data access permissions';
            $actions[] = 'Audit data access logs';
            $actions[] = 'Verify data protection compliance';
        }
        
        if ($this->itemCount >= 1000) {
            $actions[] = 'Monitor system performance';
            $actions[] = 'Review operation impact';
        }
        
        if ($this->requiresImmediateReview()) {
            $actions[] = 'Escalate to security team';
            $actions[] = 'Document incident';
        }
        
        if (!$this->hasApproval && ($this->isDestructive || $this->affectsSensitiveData)) {
            $actions[] = 'Review approval workflows';
            $actions[] = 'Update user permissions';
        }
        
        if ($this->wasScheduled) {
            $actions[] = 'Review scheduling policies';
        }
        
        return $actions;
    }

    /**
     * Assess the risk level of this operation
     *
     * @return array Risk assessment details
     */
    private function assessRisk(): array
    {
        return [
            'data_risk' => $this->assessDataRisk(),
            'system_risk' => $this->assessSystemRisk(),
            'compliance_risk' => $this->assessComplianceRisk(),
            'business_risk' => $this->assessBusinessRisk(),
        ];
    }

    /**
     * Assess the data risk
     *
     * @return string The data risk level
     */
    private function assessDataRisk(): string
    {
        if ($this->isDestructive && $this->affectsSensitiveData) {
            return 'critical';
        } elseif ($this->affectsSensitiveData) {
            return 'high';
        } elseif ($this->isDestructive) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the system risk
     *
     * @return string The system risk level
     */
    private function assessSystemRisk(): string
    {
        if ($this->itemCount >= 5000) {
            return 'high';
        } elseif ($this->itemCount >= 1000) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the compliance risk
     *
     * @return string The compliance risk level
     */
    private function assessComplianceRisk(): string
    {
        if ($this->affectsSensitiveData && !$this->hasApproval) {
            return 'high';
        } elseif ($this->affectsSensitiveData || !$this->hasApproval) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the business risk
     *
     * @return string The business risk level
     */
    private function assessBusinessRisk(): string
    {
        if ($this->isDestructive && $this->itemCount >= 1000) {
            return 'high';
        } elseif ($this->isDestructive) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Categorize the operation type
     *
     * @return string The operation category
     */
    private function categorizeOperation(): string
    {
        if ($this->isDestructive) {
            return 'destructive_operation';
        } elseif ($this->affectsSensitiveData) {
            return 'sensitive_data_operation';
        } elseif ($this->itemCount >= 1000) {
            return 'large_scale_operation';
        } elseif ($this->wasScheduled) {
            return 'scheduled_operation';
        }
        
        return 'standard_operation';
    }

    /**
     * Get compliance flags for this operation
     *
     * @return array Compliance flags
     */
    private function getComplianceFlags(): array
    {
        $flags = [];
        
        if ($this->isDestructive && !$this->hasApproval) {
            $flags[] = 'unauthorized_destructive_action';
        }
        
        if ($this->affectsSensitiveData && !$this->hasApproval) {
            $flags[] = 'unauthorized_sensitive_data_access';
        }
        
        if ($this->itemCount >= 1000 && !$this->hasApproval) {
            $flags[] = 'large_scale_unapproved_operation';
        }
        
        if ($this->wasScheduled && !$this->hasApproval) {
            $flags[] = 'unapproved_scheduled_operation';
        }
        
        return $flags;
    }
}