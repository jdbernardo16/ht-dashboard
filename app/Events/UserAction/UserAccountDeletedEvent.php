<?php

namespace App\Events\UserAction;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a user account is deleted
 * 
 * This event is fired when a user account is permanently deleted,
 * helping to track account removals and potential data loss concerns.
 */
class UserAccountDeletedEvent extends AdministrativeAlertEvent
{
    /**
     * The user that was deleted
     */
    public User $deletedUser;

    /**
     * The user who performed the deletion
     */
    public User|null $deletedBy;

    /**
     * The reason for account deletion
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
     * Whether this was a self-deletion
     */
    public bool $isSelfDeletion;

    /**
     * Whether the deleted user was an admin
     */
    public bool $wasAdmin;

    /**
     * The number of tasks/sales/expenses associated with the deleted user
     */
    public array $associatedData;

    /**
     * Whether data backup was created before deletion
     */
    public bool $backupCreated;

    /**
     * The backup file path (if backup was created)
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
     * Create a new event instance
     *
     * @param User $deletedUser The user that was deleted
     * @param string $deletionMethod The deletion method
     * @param string $ipAddress The source IP address
     * @param string $userAgent The browser user agent
     * @param User|null $deletedBy The user who performed the deletion
     * @param string|null $reason The reason for deletion
     * @param array $associatedData Associated data counts
     * @param bool $backupCreated Whether backup was created
     * @param string|null $backupPath The backup file path
     * @param bool $wasScheduled Whether this was scheduled
     * @param \DateTime|null $scheduledDate The scheduled date
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        User $deletedUser,
        string $deletionMethod,
        string $ipAddress,
        string $userAgent,
        User|null $deletedBy = null,
        string|null $reason = null,
        array $associatedData = [],
        bool $backupCreated = false,
        string|null $backupPath = null,
        bool $wasScheduled = false,
        \DateTime|null $scheduledDate = null,
        User|null $initiatedBy = null
    ) {
        $this->deletedUser = $deletedUser;
        $this->deletionMethod = $deletionMethod;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->deletedBy = $deletedBy;
        $this->reason = $reason;
        $this->associatedData = $associatedData;
        $this->backupCreated = $backupCreated;
        $this->backupPath = $backupPath;
        $this->wasScheduled = $wasScheduled;
        $this->scheduledDate = $scheduledDate;
        $this->isSelfDeletion = $deletedBy?->id === $deletedUser->id;
        $this->wasAdmin = $deletedUser->is_admin ?? false;

        $context = [
            'deleted_user_id' => $deletedUser->id,
            'deleted_user_email' => $deletedUser->email,
            'deleted_user_name' => $deletedUser->name,
            'deleted_by_id' => $deletedBy?->id,
            'deleted_by_email' => $deletedBy?->email,
            'deletion_method' => $deletionMethod,
            'reason' => $reason,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'is_self_deletion' => $this->isSelfDeletion,
            'was_admin' => $this->wasAdmin,
            'associated_data' => $associatedData,
            'backup_created' => $backupCreated,
            'backup_path' => $backupPath,
            'was_scheduled' => $wasScheduled,
            'scheduled_date' => $scheduledDate?->format('Y-m-d H:i:s'),
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
        // Critical for admin account deletion or bulk operations
        if ($this->wasAdmin || $this->deletionMethod === 'bulk_delete') {
            return self::SEVERITY_HIGH;
        } elseif ($this->hasSignificantData() || !$this->backupCreated) {
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
        if ($this->wasAdmin) {
            return "Admin Account Deleted";
        } elseif ($this->isSelfDeletion) {
            return "User Self-Deleted Account";
        } elseif ($this->deletionMethod === 'bulk_delete') {
            return "User Account Bulk Deleted";
        }
        
        return "User Account Deleted";
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
        
        $description = "{$deleter} deleted user account '{$this->deletedUser->email}' (ID: {$this->deletedUser->id}). ";
        
        if ($this->wasAdmin) {
            $description .= "This was an admin account. ";
        }
        
        if ($this->isSelfDeletion) {
            $description .= "This was a self-deletion. ";
        }
        
        $description .= "Deletion method: {$this->deletionMethod}. ";
        
        if ($this->reason) {
            $description .= "Reason: {$this->reason}. ";
        }
        
        if (!empty($this->associatedData)) {
            $dataSummary = [];
            foreach ($this->associatedData as $type => $count) {
                if ($count > 0) {
                    $dataSummary[] = "{$count} {$type}";
                }
            }
            if (!empty($dataSummary)) {
                $description .= "Associated data: " . implode(', ', $dataSummary) . ". ";
            }
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
        
        return route('admin.users.index');
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for admin deletions
        if ($this->wasAdmin || $this->deletionMethod === 'bulk_delete') {
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
        if ($this->wasAdmin) {
            return "[HIGH] Admin Account Deleted - {$this->deletedUser->email}";
        } elseif ($this->isSelfDeletion) {
            return "[MEDIUM] User Self-Deleted Account - {$this->deletedUser->email}";
        } elseif ($this->deletionMethod === 'bulk_delete') {
            return "[HIGH] User Account Bulk Deleted";
        }
        
        return "[USER ACTION] User Account Deleted - {$this->deletedUser->email}";
    }

    /**
     * Check if the deleted user had significant data
     *
     * @return bool True if user had significant data
     */
    private function hasSignificantData(): bool
    {
        $totalData = array_sum($this->associatedData);
        return $totalData > 10; // Consider more than 10 records as significant
    }

    /**
     * Determine if data recovery should be considered
     *
     * @return bool True if data recovery should be considered
     */
    public function shouldConsiderDataRecovery(): bool
    {
        return $this->hasSignificantData() && !$this->backupCreated;
    }

    /**
     * Determine if this deletion requires audit review
     *
     * @return bool True if audit review is required
     */
    public function requiresAuditReview(): bool
    {
        return $this->wasAdmin || 
               $this->deletionMethod === 'bulk_delete' ||
               !$this->backupCreated;
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
            'requires_review' => $this->requiresAuditReview(),
            'recommended_actions' => $this->getRecommendedActions(),
            'data_impact_assessment' => $this->assessDataImpact(),
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
        if ($this->wasAdmin) {
            return 'high';
        } elseif ($this->deletionMethod === 'bulk_delete') {
            return 'high';
        } elseif ($this->hasSignificantData() && !$this->backupCreated) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the deletion
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->wasAdmin) {
            $actions[] = 'Review admin access logs';
            $actions[] = 'Verify admin privileges reassignment';
            $actions[] = 'Update security documentation';
        }
        
        if ($this->deletionMethod === 'bulk_delete') {
            $actions[] = 'Review bulk deletion logs';
            $actions[] = 'Verify all affected accounts';
            $actions[] = 'Check for unintended deletions';
        }
        
        if (!$this->backupCreated && $this->hasSignificantData()) {
            $actions[] = 'Consider data recovery options';
            $actions[] = 'Review backup policies';
        }
        
        if ($this->isSelfDeletion) {
            $actions[] = 'Review user feedback';
            $actions[] = 'Analyze self-deletion patterns';
        }
        
        if ($this->requiresAuditReview()) {
            $actions[] = 'Schedule audit review';
            $actions[] = 'Document deletion justification';
        }
        
        if (!empty($this->associatedData)) {
            $actions[] = 'Review data ownership transfer';
            $actions[] = 'Update associated records';
        }
        
        return $actions;
    }

    /**
     * Assess the impact of this deletion
     *
     * @return array Impact assessment details
     */
    private function assessDataImpact(): array
    {
        return [
            'data_volume' => $this->calculateDataVolume(),
            'business_impact' => $this->assessBusinessImpact(),
            'security_impact' => $this->assessSecurityImpact(),
            'operational_impact' => $this->assessOperationalImpact(),
        ];
    }

    /**
     * Calculate the data volume impact
     *
     * @return string The data volume level
     */
    private function calculateDataVolume(): string
    {
        $totalData = array_sum($this->associatedData);
        
        if ($totalData > 100) {
            return 'high';
        } elseif ($totalData > 10) {
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
        if ($this->wasAdmin) {
            return 'high';
        } elseif ($this->hasSignificantData()) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the security impact
     *
     * @return string The security impact level
     */
    private function assessSecurityImpact(): string
    {
        if ($this->wasAdmin) {
            return 'high';
        } elseif ($this->deletionMethod === 'bulk_delete') {
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
        if ($this->hasSignificantData()) {
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
        if ($this->wasAdmin) {
            return 'admin_deletion';
        } elseif ($this->isSelfDeletion) {
            return 'self_deletion';
        } elseif ($this->deletionMethod === 'bulk_delete') {
            return 'bulk_deletion';
        } elseif ($this->wasScheduled) {
            return 'scheduled_deletion';
        }
        
        return 'standard_deletion';
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
        
        if ($this->hasSignificantData()) {
            $options[] = 'contact_data_recovery_service';
        }
        
        return $options;
    }
}