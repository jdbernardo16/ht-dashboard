<?php

namespace App\Events\Business;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a client is deleted
 * 
 * This event is fired when clients are deleted from the system,
 * helping to track potential business impact and data loss concerns.
 */
class BusinessClientDeletedEvent extends AdministrativeAlertEvent
{
    /**
     * The client that was deleted
     */
    public object $deletedClient;

    /**
     * The user who performed the deletion
     */
    public User|null $deletedBy;

    /**
     * The reason for client deletion
     */
    public string|null $reason;

    /**
     * The method of deletion (soft_delete, permanent_delete, bulk_delete)
     */
    public string $deletionMethod;

    /**
     * The IP address from which the deletion was performed
     */
    public string $ipAddress;

    /**
     * The user agent string from the request
     */
    public string $userAgent;

    /**
     * Whether this was a self-deletion (client requested)
     */
    public bool $isSelfDeletion;

    /**
     * The number of sales associated with the deleted client
     */
    public int $salesCount;

    /**
     * The total value of sales associated with the deleted client
     */
    public float $totalSalesValue;

    /**
     * The number of active projects/tasks associated with the client
     */
    public int $activeProjectsCount;

    /**
     * Whether backup was created before deletion
     */
    public bool $backupCreated;

    /**
     * The backup file path (if created)
     */
    public string|null $backupPath;

    /**
     * Whether this deletion was scheduled
     */
    public bool $wasScheduled;

    /**
     * The scheduled deletion date (if applicable)
     */
    public \DateTime|null $scheduledDate;

    /**
     * Whether this client was a high-value client
     */
    public bool $wasHighValueClient;

    /**
     * The client's lifetime value
     */
    public float|null $lifetimeValue;

    /**
     * Create a new event instance
     *
     * @param object $deletedClient The client that was deleted
     * @param string $deletionMethod The deletion method
     * @param string $ipAddress The source IP address
     * @param string $userAgent The browser user agent
     * @param User|null $deletedBy The user who performed the deletion
     * @param string|null $reason The reason for deletion
     * @param int $salesCount Number of associated sales
     * @param float $totalSalesValue Total sales value
     * @param int $activeProjectsCount Number of active projects
     * @param bool $backupCreated Whether backup was created
     * @param string|null $backupPath The backup file path
     * @param bool $wasScheduled Whether this was scheduled
     * @param \DateTime|null $scheduledDate The scheduled date
     * @param bool $wasHighValueClient Whether this was a high-value client
     * @param float|null $lifetimeValue The client's lifetime value
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        object $deletedClient,
        string $deletionMethod,
        string $ipAddress,
        string $userAgent,
        User|null $deletedBy = null,
        string|null $reason = null,
        int $salesCount = 0,
        float $totalSalesValue = 0,
        int $activeProjectsCount = 0,
        bool $backupCreated = false,
        string|null $backupPath = null,
        bool $wasScheduled = false,
        \DateTime|null $scheduledDate = null,
        bool $wasHighValueClient = false,
        float|null $lifetimeValue = null,
        User|null $initiatedBy = null
    ) {
        $this->deletedClient = $deletedClient;
        $this->deletionMethod = $deletionMethod;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->deletedBy = $deletedBy;
        $this->reason = $reason;
        $this->salesCount = $salesCount;
        $this->totalSalesValue = $totalSalesValue;
        $this->activeProjectsCount = $activeProjectsCount;
        $this->backupCreated = $backupCreated;
        $this->backupPath = $backupPath;
        $this->wasScheduled = $wasScheduled;
        $this->scheduledDate = $scheduledDate;
        $this->wasHighValueClient = $wasHighValueClient;
        $this->lifetimeValue = $lifetimeValue;
        $this->isSelfDeletion = $this->determineIfSelfDeletion();

        $context = [
            'deleted_client_id' => $deletedClient->id,
            'deleted_client_name' => $deletedClient->name,
            'deleted_client_email' => $deletedClient->email,
            'deleted_by_id' => $deletedBy?->id,
            'deleted_by_email' => $deletedBy?->email,
            'deletion_method' => $deletionMethod,
            'reason' => $reason,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'is_self_deletion' => $this->isSelfDeletion,
            'sales_count' => $salesCount,
            'total_sales_value' => $totalSalesValue,
            'active_projects_count' => $activeProjectsCount,
            'backup_created' => $backupCreated,
            'backup_path' => $backupPath,
            'was_scheduled' => $wasScheduled,
            'scheduled_date' => $scheduledDate?->format('Y-m-d H:i:s'),
            'was_high_value_client' => $wasHighValueClient,
            'lifetime_value' => $lifetimeValue,
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
        return 'Business';
    }

    /**
     * Get the severity level of this event
     *
     * @return string The severity level
     */
    public function getSeverity(): string
    {
        // Critical for high-value client deletion or clients with active projects
        if ($this->wasHighValueClient || $this->activeProjectsCount > 0) {
            return self::SEVERITY_HIGH;
        } elseif ($this->salesCount > 10 || $this->totalSalesValue > 10000) {
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
        if ($this->wasHighValueClient) {
            return "High-Value Client Deleted";
        } elseif ($this->activeProjectsCount > 0) {
            return "Client with Active Projects Deleted";
        } elseif ($this->isSelfDeletion) {
            return "Client Self-Deleted Account";
        }
        
        return "Client Deleted";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $deleter = $this->deletedBy 
            ? "User '{$this->deletedBy->email}' (ID: {$this->deletedBy->id})"
            : "System";
        
        $description = "{$deleter} deleted client '{$this->deletedClient->name}' ";
        $description .= "(Email: {$this->deletedClient->email}). ";
        
        if ($this->wasHighValueClient) {
            $description .= "This was a high-value client. ";
        }
        
        if ($this->isSelfDeletion) {
            $description .= "This was a self-deletion requested by the client. ";
        }
        
        $description .= "Deletion method: {$this->deletionMethod}. ";
        
        if ($this->reason) {
            $description .= "Reason: {$this->reason}. ";
        }
        
        if ($this->salesCount > 0) {
            $description .= "Associated sales: {$this->salesCount} (Total value: \${$this->totalSalesValue}). ";
        }
        
        if ($this->activeProjectsCount > 0) {
            $description .= "Active projects: {$this->activeProjectsCount}. ";
        }
        
        if ($this->lifetimeValue) {
            $description .= "Client lifetime value: \${$this->lifetimeValue}. ";
        }
        
        if ($this->backupCreated) {
            $description .= "Data backup was created. ";
        } else {
            $description .= "No data backup was created. ";
        }
        
        if ($this->wasScheduled) {
            $description .= "This was a scheduled deletion. ";
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
        if ($this->deletedBy) {
            return route('admin.users.show', $this->deletedBy->id);
        }
        
        return route('admin.clients.index');
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for high-value clients
        if ($this->wasHighValueClient || $this->activeProjectsCount > 0) {
            return 'business-high-alerts';
        }
        
        return 'business-alerts';
    }

    /**
     * Get the email subject for notifications
     *
     * @return string The email subject
     */
    public function getEmailSubject(): string
    {
        if ($this->wasHighValueClient) {
            return "[HIGH] High-Value Client Deleted - {$this->deletedClient->name}";
        } elseif ($this->activeProjectsCount > 0) {
            return "[HIGH] Client with Active Projects Deleted";
        } elseif ($this->isSelfDeletion) {
            return "[MEDIUM] Client Self-Deleted Account - {$this->deletedClient->name}";
        }
        
        return "[BUSINESS] Client Deleted - {$this->deletedClient->name}";
    }

    /**
     * Determine if this was a self-deletion
     *
     * @return bool True if this was a self-deletion
     */
    private function determineIfSelfDeletion(): bool
    {
        // This would typically be determined by checking if the deletion reason
        // contains client-requested keywords or if there's a specific flag
        return str_contains(strtolower($this->reason ?? ''), 'client requested') ||
               str_contains(strtolower($this->reason ?? ''), 'self deletion');
    }

    /**
     * Check if this deletion requires immediate review
     *
     * @return bool True if immediate review is required
     */
    public function requiresImmediateReview(): bool
    {
        return ($this->wasHighValueClient && !$this->backupCreated) ||
               $this->activeProjectsCount > 5 ||
               ($this->totalSalesValue > 50000 && !$this->backupCreated);
    }

    /**
     * Check if client recovery should be considered
     *
     * @return bool True if recovery should be considered
     */
    public function shouldConsiderRecovery(): bool
    {
        return $this->wasHighValueClient || 
               $this->activeProjectsCount > 0 ||
               $this->totalSalesValue > 25000;
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
            'business_impact' => $this->assessBusinessImpact(),
            'deletion_category' => $this->categorizeDeletion(),
            'recovery_options' => $this->getRecoveryOptions(),
        ];
    }

    /**
     * Calculate the urgency level based on various factors
     *
     * @return string The urgency level (low, medium, high, critical)
     */
    private function calculateUrgencyLevel(): string
    {
        if ($this->wasHighValueClient && $this->activeProjectsCount > 0) {
            return 'high';
        } elseif ($this->wasHighValueClient || $this->activeProjectsCount > 0) {
            return 'medium';
        } elseif ($this->salesCount > 10 || $this->totalSalesValue > 10000) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the client deletion
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->wasHighValueClient) {
            $actions[] = 'Review client relationship management';
            $actions[] = 'Analyze reasons for high-value client loss';
            $actions[] = 'Consider outreach for recovery';
        }
        
        if ($this->activeProjectsCount > 0) {
            $actions[] = 'Reassign active projects';
            $actions[] = 'Notify project teams';
            $actions[] = 'Review project deadlines';
        }
        
        if ($this->salesCount > 5) {
            $actions[] = 'Review sales pipeline impact';
            $actions[] = 'Update sales forecasts';
        }
        
        if (!$this->backupCreated && ($this->wasHighValueClient || $this->salesCount > 0)) {
            $actions[] = 'Check for data recovery options';
            $actions[] = 'Review backup policies';
        }
        
        if ($this->requiresImmediateReview()) {
            $actions[] = 'Schedule immediate management review';
            $actions[] = 'Document business impact';
        }
        
        if ($this->isSelfDeletion) {
            $actions[] = 'Conduct exit interview';
            $actions[] = 'Analyze client satisfaction issues';
        }
        
        if ($this->totalSalesValue > 25000) {
            $actions[] = 'Review revenue projections';
            $actions[] = 'Assess financial impact';
        }
        
        return $actions;
    }

    /**
     * Assess the business impact of this deletion
     *
     * @return array Business impact assessment details
     */
    private function assessBusinessImpact(): array
    {
        return [
            'revenue_impact' => $this->assessRevenueImpact(),
            'project_impact' => $this->assessProjectImpact(),
            'relationship_impact' => $this->assessRelationshipImpact(),
            'operational_impact' => $this->assessOperationalImpact(),
        ];
    }

    /**
     * Assess the revenue impact
     *
     * @return string The revenue impact level
     */
    private function assessRevenueImpact(): string
    {
        if ($this->wasHighValueClient || $this->totalSalesValue > 50000) {
            return 'high';
        } elseif ($this->totalSalesValue > 10000 || $this->lifetimeValue > 25000) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the project impact
     *
     * @return string The project impact level
     */
    private function assessProjectImpact(): string
    {
        if ($this->activeProjectsCount > 5) {
            return 'high';
        } elseif ($this->activeProjectsCount > 0) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the relationship impact
     *
     * @return string The relationship impact level
     */
    private function assessRelationshipImpact(): string
    {
        if ($this->wasHighValueClient) {
            return 'high';
        } elseif ($this->salesCount > 10) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the operational impact
     *
     * @return string The operational impact level
     */
    private function assessOperationalImpact(): string
    {
        if ($this->activeProjectsCount > 3) {
            return 'high';
        } elseif ($this->activeProjectsCount > 0 || $this->salesCount > 5) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Categorize the deletion type
     *
     * @return string The deletion category
     */
    private function categorizeDeletion(): string
    {
        if ($this->wasHighValueClient) {
            return 'high_value_client_deletion';
        } elseif ($this->isSelfDeletion) {
            return 'client_self_deletion';
        } elseif ($this->activeProjectsCount > 0) {
            return 'active_client_deletion';
        } elseif ($this->wasScheduled) {
            return 'scheduled_deletion';
        }
        
        return 'standard_client_deletion';
    }

    /**
     * Get recovery options based on the deletion
     *
     * @return array Available recovery options
     */
    private function getRecoveryOptions(): array
    {
        $options = [];
        
        if ($this->backupCreated) {
            $options[] = 'restore_from_backup';
            $options[] = 'partial_data_recovery';
        } else {
            $options[] = 'check_database_logs';
            $options[] = 'review_audit_trail';
        }
        
        if ($this->deletionMethod === 'soft_delete') {
            $options[] = 'undo_soft_delete';
        }
        
        if ($this->wasHighValueClient) {
            $options[] = 'contact_client_directly';
            $options[] = 'offer_recovery_incentives';
        }
        
        if ($this->activeProjectsCount > 0) {
            $options[] = 'preserve_project_data';
            $options[] = 'minimize_disruption';
        }
        
        return $options;
    }
}