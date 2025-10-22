<?php

namespace App\Events\UserAction;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a user performs mass content deletion
 * 
 * This event is fired when users delete large amounts of content,
 * helping to track potential data loss or malicious activity.
 */
class UserMassContentDeletionEvent extends AdministrativeAlertEvent
{
    /**
     * The user who performed the mass deletion
     */
    public User $user;

    /**
     * The type of content that was deleted
     */
    public string $contentType;

    /**
     * The number of content items deleted
     */
    public int $deletedCount;

    /**
     * The IDs of the deleted content items
     */
    public array $deletedItemIds;

    /**
     * The date range of deleted content
     */
    public array $dateRange;

    /**
     * The filters/criteria used for deletion
     */
    public array $deletionCriteria;

    /**
     * The IP address from which the deletion was performed
     */
    public string $ipAddress;

    /**
     * The user agent string from the request
     */
    public string $userAgent;

    /**
     * Whether this deletion was scheduled
     */
    public bool $wasScheduled;

    /**
     * Whether backup was created before deletion
     */
    public bool $backupCreated;

    /**
     * The backup file path (if created)
     */
    public string|null $backupPath;

    /**
     * Whether approval was obtained for this deletion
     */
    public bool $hasApproval;

    /**
     * The approver (if approval was obtained)
     */
    public User|null $approver;

    /**
     * The reason provided for the mass deletion
     */
    public string|null $reason;

    /**
     * Whether the deleted content was published
     */
    public bool $wasPublishedContent;

    /**
     * The estimated data size deleted (in MB)
     */
    public float|null $estimatedDataSize;

    /**
     * Create a new event instance
     *
     * @param User $user The user who performed the deletion
     * @param string $contentType The type of content deleted
     * @param int $deletedCount Number of items deleted
     * @param array $deletedItemIds The IDs of deleted items
     * @param string $ipAddress The source IP address
     * @param string $userAgent The browser user agent
     * @param array $dateRange The date range of deleted content
     * @param array $deletionCriteria The deletion criteria
     * @param bool $wasScheduled Whether this was scheduled
     * @param bool $backupCreated Whether backup was created
     * @param string|null $backupPath The backup file path
     * @param bool $hasApproval Whether approval was obtained
     * @param User|null $approver The approver
     * @param string|null $reason The reason for deletion
     * @param bool $wasPublishedContent Whether content was published
     * @param float|null $estimatedDataSize Estimated data size in MB
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        User $user,
        string $contentType,
        int $deletedCount,
        array $deletedItemIds,
        string $ipAddress,
        string $userAgent,
        array $dateRange = [],
        array $deletionCriteria = [],
        bool $wasScheduled = false,
        bool $backupCreated = false,
        string|null $backupPath = null,
        bool $hasApproval = false,
        User|null $approver = null,
        string|null $reason = null,
        bool $wasPublishedContent = false,
        float|null $estimatedDataSize = null,
        User|null $initiatedBy = null
    ) {
        $this->user = $user;
        $this->contentType = $contentType;
        $this->deletedCount = $deletedCount;
        $this->deletedItemIds = $deletedItemIds;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->dateRange = $dateRange;
        $this->deletionCriteria = $deletionCriteria;
        $this->wasScheduled = $wasScheduled;
        $this->backupCreated = $backupCreated;
        $this->backupPath = $backupPath;
        $this->hasApproval = $hasApproval;
        $this->approver = $approver;
        $this->reason = $reason;
        $this->wasPublishedContent = $wasPublishedContent;
        $this->estimatedDataSize = $estimatedDataSize;

        $context = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'content_type' => $contentType,
            'deleted_count' => $deletedCount,
            'deleted_item_ids' => $deletedItemIds,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'date_range' => $dateRange,
            'deletion_criteria' => $deletionCriteria,
            'was_scheduled' => $wasScheduled,
            'backup_created' => $backupCreated,
            'backup_path' => $backupPath,
            'has_approval' => $hasApproval,
            'approver_id' => $approver?->id,
            'approver_email' => $approver?->email,
            'reason' => $reason,
            'was_published_content' => $wasPublishedContent,
            'estimated_data_size' => $estimatedDataSize,
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
        // Critical for large scale published content deletion without approval
        if ($this->wasPublishedContent && $this->deletedCount >= 100 && !$this->hasApproval) {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->deletedCount >= 1000 || ($this->wasPublishedContent && !$this->hasApproval)) {
            return self::SEVERITY_HIGH;
        } elseif ($this->deletedCount >= 100 || ($this->wasPublishedContent && !$this->backupCreated)) {
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
        if ($this->wasPublishedContent && !$this->hasApproval) {
            return "Unauthorized Mass Published Content Deletion";
        } elseif ($this->deletedCount >= 1000) {
            return "Large Scale Content Deletion";
        } elseif ($this->wasPublishedContent) {
            return "Mass Published Content Deletion";
        }
        
        return "Mass Content Deletion";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "User '{$this->user->email}' (ID: {$this->user->id}) performed mass deletion of ";
        $description .= "{$this->deletedCount} {$this->contentType} items. ";
        
        if ($this->wasPublishedContent) {
            $description .= "The deleted content was published. ";
        }
        
        if (!$this->hasApproval && $this->wasPublishedContent) {
            $description .= "WARNING: This deletion of published content was performed without proper approval. ";
        } elseif ($this->hasApproval) {
            $approverName = $this->approver ? $this->approver->email : 'Unknown';
            $description .= "Deletion was approved by {$approverName}. ";
        }
        
        if ($this->backupCreated) {
            $description .= "Data backup was created before deletion. ";
        } else {
            $description .= "No data backup was created. ";
        }
        
        if ($this->reason) {
            $description .= "Reason: {$this->reason}. ";
        }
        
        if (!empty($this->dateRange)) {
            $description .= "Date range: {$this->dateRange['start']} to {$this->dateRange['end']}. ";
        }
        
        if ($this->estimatedDataSize) {
            $description .= "Estimated data size deleted: {$this->estimatedDataSize}MB. ";
        }
        
        if (!empty($this->deletionCriteria)) {
            $description .= "Deletion criteria: " . json_encode($this->deletionCriteria) . ". ";
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
        return route('admin.users.show', $this->user->id) . '#content';
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for published content or large deletions
        if ($this->wasPublishedContent || $this->deletedCount >= 1000) {
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
        if ($this->wasPublishedContent && !$this->hasApproval) {
            return "[CRITICAL] Unauthorized Mass Published Content Deletion";
        } elseif ($this->deletedCount >= 1000) {
            return "[HIGH] Large Scale Content Deletion ({$this->deletedCount} items)";
        } elseif ($this->wasPublishedContent) {
            return "[HIGH] Mass Published Content Deletion";
        }
        
        return "[USER ACTION] Mass Content Deletion - {$this->contentType}";
    }

    /**
     * Check if this deletion requires immediate review
     *
     * @return bool True if immediate review is required
     */
    public function requiresImmediateReview(): bool
    {
        return ($this->wasPublishedContent && !$this->hasApproval) ||
               $this->deletedCount >= 1000 ||
               ($this->wasPublishedContent && !$this->backupCreated);
    }

    /**
     * Check if content recovery should be attempted
     *
     * @return bool True if recovery should be attempted
     */
    public function shouldAttemptRecovery(): bool
    {
        return $this->wasPublishedContent && !$this->hasApproval;
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
            'impact_assessment' => $this->assessImpact(),
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
        if ($this->wasPublishedContent && !$this->hasApproval && $this->deletedCount >= 100) {
            return 'critical';
        } elseif ($this->deletedCount >= 1000 || ($this->wasPublishedContent && !$this->hasApproval)) {
            return 'high';
        } elseif ($this->deletedCount >= 100 || ($this->wasPublishedContent && !$this->backupCreated)) {
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
        
        if ($this->wasPublishedContent && !$this->hasApproval) {
            $actions[] = 'Immediately review deletion';
            $actions[] = 'Consider content restoration';
            $actions[] = 'Contact user for explanation';
            $actions[] = 'Review content publication policies';
        }
        
        if ($this->deletedCount >= 1000) {
            $actions[] = 'Assess system impact';
            $actions[] = 'Monitor storage changes';
        }
        
        if (!$this->backupCreated && $this->wasPublishedContent) {
            $actions[] = 'Check for backup alternatives';
            $actions[] = 'Review backup policies';
        }
        
        if ($this->requiresImmediateReview()) {
            $actions[] = 'Escalate to content management team';
            $actions[] = 'Document incident';
        }
        
        if (!$this->hasApproval && ($this->wasPublishedContent || $this->deletedCount >= 100)) {
            $actions[] = 'Review approval workflows';
            $actions[] = 'Update user permissions';
        }
        
        if ($this->shouldAttemptRecovery()) {
            $actions[] = 'Attempt content recovery from backup';
            $actions[] = 'Check for content caches';
        }
        
        return $actions;
    }

    /**
     * Assess the impact of this deletion
     *
     * @return array Impact assessment details
     */
    private function assessImpact(): array
    {
        return [
            'content_impact' => $this->assessContentImpact(),
            'seo_impact' => $this->assessSeoImpact(),
            'user_impact' => $this->assessUserImpact(),
            'business_impact' => $this->assessBusinessImpact(),
        ];
    }

    /**
     * Assess the content impact
     *
     * @return string The content impact level
     */
    private function assessContentImpact(): string
    {
        if ($this->wasPublishedContent && $this->deletedCount >= 100) {
            return 'critical';
        } elseif ($this->deletedCount >= 1000) {
            return 'high';
        } elseif ($this->wasPublishedContent) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the SEO impact
     *
     * @return string The SEO impact level
     */
    private function assessSeoImpact(): string
    {
        if ($this->wasPublishedContent && $this->deletedCount >= 50) {
            return 'high';
        } elseif ($this->wasPublishedContent) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the user impact
     *
     * @return string The user impact level
     */
    private function assessUserImpact(): string
    {
        if ($this->wasPublishedContent && $this->deletedCount >= 100) {
            return 'high';
        } elseif ($this->deletedCount >= 500) {
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
        if ($this->wasPublishedContent && !$this->hasApproval) {
            return 'high';
        } elseif ($this->deletedCount >= 1000) {
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
        if ($this->wasPublishedContent && !$this->hasApproval) {
            return 'unauthorized_published_deletion';
        } elseif ($this->wasPublishedContent) {
            return 'published_content_deletion';
        } elseif ($this->deletedCount >= 1000) {
            return 'large_scale_deletion';
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
            $options[] = 'selective_content_recovery';
        } else {
            $options[] = 'check_database_logs';
            $options[] = 'review_content_caches';
        }
        
        if ($this->wasPublishedContent) {
            $options[] = 'check_wayback_machine';
            $options[] = 'review_cdn_caches';
        }
        
        if ($this->deletedCount >= 100) {
            $options[] = 'contact_content_recovery_service';
        }
        
        return $options;
    }
}