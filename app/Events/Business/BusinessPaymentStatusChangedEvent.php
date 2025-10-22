<?php

namespace App\Events\Business;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a payment status changes significantly
 * 
 * This event is fired when payment statuses change in ways that require attention,
 * helping to track payment issues, delays, or important status updates.
 */
class BusinessPaymentStatusChangedEvent extends AdministrativeAlertEvent
{
    /**
     * The user who triggered the status change
     */
    public User|null $user;

    /**
     * The payment record
     */
    public object $payment;

    /**
     * The client associated with the payment
     */
    public object $client;

    /**
     * The previous payment status
     */
    public string $previousStatus;

    /**
     * The new payment status
     */
    public string $newStatus;

    /**
     * The payment amount
     */
    public float $paymentAmount;

    /**
     * The currency used for the payment
     */
    public string $currency;

    /**
     * The due date of the payment
     */
    public \DateTime|null $dueDate;

    /**
     * The number of days overdue (if applicable)
     */
    public int|null $daysOverdue;

    /**
     * Whether this is a payment failure
     */
    public bool $isPaymentFailure;

    /**
     * Whether this is a payment delay
     */
    public bool $isPaymentDelay;

    /**
     * Whether this is a payment recovery
     */
    public bool $isPaymentRecovery;

    /**
     * The reason for the status change
     */
    public string|null $changeReason;

    /**
     * The payment method used
     */
    public string|null $paymentMethod;

    /**
     * Whether this payment requires follow-up
     */
    public bool $requiresFollowUp;

    /**
     * The number of previous status changes
     */
    public int $previousStatusChanges;

    /**
     * Whether this is a high-value payment
     */
    public bool $isHighValue;

    /**
     * Create a new event instance
     *
     * @param object $payment The payment record
     * @param object $client The client
     * @param string $previousStatus The previous status
     * @param string $newStatus The new status
     * @param float $paymentAmount The payment amount
     * @param string $currency The currency
     * @param \DateTime|null $dueDate The due date
     * @param string|null $changeReason The reason for change
     * @param User|null $user The user who triggered the change
     * @param string|null $paymentMethod The payment method
     * @param bool $requiresFollowUp Whether follow-up is needed
     * @param int $previousStatusChanges Number of previous changes
     * @param bool $isHighValue Whether this is high value
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        object $payment,
        object $client,
        string $previousStatus,
        string $newStatus,
        float $paymentAmount,
        string $currency,
        \DateTime|null $dueDate = null,
        string|null $changeReason = null,
        User|null $user = null,
        string|null $paymentMethod = null,
        bool $requiresFollowUp = false,
        int $previousStatusChanges = 0,
        bool $isHighValue = false,
        User|null $initiatedBy = null
    ) {
        $this->payment = $payment;
        $this->client = $client;
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
        $this->paymentAmount = $paymentAmount;
        $this->currency = $currency;
        $this->dueDate = $dueDate;
        $this->changeReason = $changeReason;
        $this->user = $user;
        $this->paymentMethod = $paymentMethod;
        $this->requiresFollowUp = $requiresFollowUp;
        $this->previousStatusChanges = $previousStatusChanges;
        $this->isHighValue = $isHighValue;

        // Calculate derived properties
        $this->isPaymentFailure = $this->determineIfPaymentFailure();
        $this->isPaymentDelay = $this->determineIfPaymentDelay();
        $this->isPaymentRecovery = $this->determineIfPaymentRecovery();
        $this->daysOverdue = $this->calculateDaysOverdue();

        $context = [
            'payment_id' => $payment->id,
            'client_id' => $client->id,
            'client_name' => $client->name,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'payment_amount' => $paymentAmount,
            'currency' => $currency,
            'due_date' => $dueDate?->format('Y-m-d H:i:s'),
            'days_overdue' => $this->daysOverdue,
            'is_payment_failure' => $this->isPaymentFailure,
            'is_payment_delay' => $this->isPaymentDelay,
            'is_payment_recovery' => $this->isPaymentRecovery,
            'change_reason' => $changeReason,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'payment_method' => $paymentMethod,
            'requires_follow_up' => $requiresFollowUp,
            'previous_status_changes' => $previousStatusChanges,
            'is_high_value' => $isHighValue,
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
        // Critical for payment failures or high-value payment issues
        if ($this->isPaymentFailure && $this->isHighValue) {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->isPaymentFailure || ($this->isPaymentDelay && $this->daysOverdue >= 30)) {
            return self::SEVERITY_HIGH;
        } elseif ($this->isPaymentDelay || $this->requiresFollowUp) {
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
        if ($this->isPaymentFailure) {
            return "Payment Failure Detected";
        } elseif ($this->isPaymentRecovery) {
            return "Payment Recovery - Status Changed";
        } elseif ($this->isPaymentDelay) {
            return "Payment Delay - Status Changed";
        }
        
        return "Payment Status Changed";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "Payment status changed from '{$this->previousStatus}' to '{$this->newStatus}' ";
        $description .= "for client '{$this->client->name}'. ";
        
        $description .= "Payment amount: {$this->currency}{$this->paymentAmount}. ";
        
        if ($this->user) {
            $description .= "Status change initiated by '{$this->user->email}'. ";
        }
        
        if ($this->isPaymentFailure) {
            $description .= "This represents a payment failure. ";
        } elseif ($this->isPaymentDelay) {
            $description .= "This represents a payment delay. ";
        } elseif ($this->isPaymentRecovery) {
            $description .= "This represents a payment recovery. ";
        }
        
        if ($this->daysOverdue) {
            $description .= "Payment is {$this->daysOverdue} days overdue. ";
        }
        
        if ($this->dueDate) {
            $description .= "Due date was {$this->dueDate->format('Y-m-d')}. ";
        }
        
        if ($this->changeReason) {
            $description .= "Reason: {$this->changeReason}. ";
        }
        
        if ($this->paymentMethod) {
            $description .= "Payment method: {$this->paymentMethod}. ";
        }
        
        if ($this->previousStatusChanges > 0) {
            $description .= "This is the " . ($this->previousStatusChanges + 1) . "th status change for this payment. ";
        }
        
        if ($this->isHighValue) {
            $description .= "This is a high-value payment. ";
        }
        
        if ($this->requiresFollowUp) {
            $description .= "This payment requires follow-up action. ";
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
        return route('admin.payments.show', $this->payment->id);
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for payment failures
        if ($this->isPaymentFailure || ($this->isPaymentDelay && $this->daysOverdue >= 30)) {
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
        if ($this->isPaymentFailure) {
            return "[HIGH] Payment Failure - {$this->client->name} ({$this->currency}{$this->paymentAmount})";
        } elseif ($this->isPaymentRecovery) {
            return "[MEDIUM] Payment Recovery - {$this->client->name}";
        } elseif ($this->isPaymentDelay) {
            return "[MEDIUM] Payment Delay - {$this->client->name} ({$this->daysOverdue} days overdue)";
        }
        
        return "[BUSINESS] Payment Status Changed - {$this->client->name}";
    }

    /**
     * Determine if this is a payment failure
     *
     * @return bool True if this is a payment failure
     */
    private function determineIfPaymentFailure(): bool
    {
        $failureStatuses = ['failed', 'declined', 'rejected', 'chargeback', 'disputed'];
        return in_array($this->newStatus, $failureStatuses);
    }

    /**
     * Determine if this is a payment delay
     *
     * @return bool True if this is a payment delay
     */
    private function determineIfPaymentDelay(): bool
    {
        $delayStatuses = ['overdue', 'late', 'pending', 'delayed'];
        return in_array($this->newStatus, $delayStatuses) && $this->daysOverdue > 0;
    }

    /**
     * Determine if this is a payment recovery
     *
     * @return bool True if this is a payment recovery
     */
    private function determineIfPaymentRecovery(): bool
    {
        $recoveryStatuses = ['paid', 'completed', 'settled'];
        $previousProblemStatuses = ['overdue', 'late', 'failed', 'declined'];
        
        return in_array($this->newStatus, $recoveryStatuses) && 
               in_array($this->previousStatus, $previousProblemStatuses);
    }

    /**
     * Calculate days overdue
     *
     * @return int|null The number of days overdue
     */
    private function calculateDaysOverdue(): int|null
    {
        if (!$this->dueDate || in_array($this->newStatus, ['paid', 'completed', 'settled'])) {
            return null;
        }

        $now = new \DateTime();
        return $now > $this->dueDate ? $now->diff($this->dueDate)->days : 0;
    }

    /**
     * Check if this payment requires immediate attention
     *
     * @return bool True if immediate attention is required
     */
    public function requiresImmediateAttention(): bool
    {
        return ($this->isPaymentFailure && $this->isHighValue) ||
               ($this->isPaymentDelay && $this->daysOverdue >= 60) ||
               ($this->previousStatusChanges >= 5);
    }

    /**
     * Check if this payment should trigger collection efforts
     *
     * @return bool True if collection efforts should be triggered
     */
    public function shouldTriggerCollection(): bool
    {
        return $this->isPaymentDelay && 
               ($this->daysOverdue >= 30 || $this->isHighValue);
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
            'requires_immediate_attention' => $this->requiresImmediateAttention(),
            'recommended_actions' => $this->getRecommendedActions(),
            'payment_assessment' => $this->assessPaymentRisk(),
            'status_category' => $this->categorizeStatusChange(),
            'resolution_options' => $this->getResolutionOptions(),
        ];
    }

    /**
     * Calculate the urgency level based on various factors
     *
     * @return string The urgency level (low, medium, high, critical)
     */
    private function calculateUrgencyLevel(): string
    {
        if ($this->isPaymentFailure && $this->isHighValue) {
            return 'critical';
        } elseif ($this->isPaymentFailure || ($this->isPaymentDelay && $this->daysOverdue >= 30)) {
            return 'high';
        } elseif ($this->isPaymentDelay || $this->requiresFollowUp) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the status change
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->isPaymentFailure) {
            $actions[] = 'Contact client immediately';
            $actions[] = 'Investigate payment failure reason';
            $actions[] = 'Offer alternative payment methods';
            
            if ($this->isHighValue) {
                $actions[] = 'Escalate to management';
                $actions[] = 'Consider legal options';
            }
        }
        
        if ($this->isPaymentDelay) {
            $actions[] = 'Send payment reminder';
            $actions[] = 'Contact client for payment status';
            
            if ($this->daysOverdue >= 30) {
                $actions[] = 'Initiate collection process';
            }
        }
        
        if ($this->isPaymentRecovery) {
            $actions[] = 'Confirm payment receipt';
            $actions[] = 'Update client account';
            $actions[] = 'Send payment confirmation';
        }
        
        if ($this->shouldTriggerCollection()) {
            $actions[] = 'Initiate collection efforts';
            $actions[] = 'Review payment terms';
        }
        
        if ($this->requiresFollowUp) {
            $actions[] = 'Schedule follow-up action';
            $actions[] = 'Set reminder for next action';
        }
        
        if ($this->previousStatusChanges >= 3) {
            $actions[] = 'Review payment processing workflow';
            $actions[] = 'Analyze status change patterns';
        }
        
        return $actions;
    }

    /**
     * Assess the payment risk
     *
     * @return array Payment risk assessment details
     */
    private function assessPaymentRisk(): array
    {
        return [
            'financial_risk' => $this->assessFinancialRisk(),
            'client_risk' => $this->assessClientRisk(),
            'collection_risk' => $this->assessCollectionRisk(),
            'operational_risk' => $this->assessOperationalRisk(),
        ];
    }

    /**
     * Assess the financial risk
     *
     * @return string The financial risk level
     */
    private function assessFinancialRisk(): string
    {
        if ($this->isPaymentFailure && $this->isHighValue) {
            return 'critical';
        } elseif ($this->isPaymentFailure || ($this->isPaymentDelay && $this->daysOverdue >= 60)) {
            return 'high';
        } elseif ($this->isPaymentDelay) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the client risk
     *
     * @return string The client risk level
     */
    private function assessClientRisk(): string
    {
        if ($this->previousStatusChanges >= 5 || ($this->isPaymentDelay && $this->daysOverdue >= 90)) {
            return 'high';
        } elseif ($this->previousStatusChanges >= 3 || ($this->isPaymentDelay && $this->daysOverdue >= 30)) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the collection risk
     *
     * @return string The collection risk level
     */
    private function assessCollectionRisk(): string
    {
        if ($this->isPaymentFailure || ($this->isPaymentDelay && $this->daysOverdue >= 60)) {
            return 'high';
        } elseif ($this->isPaymentDelay && $this->daysOverdue >= 30) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the operational risk
     *
     * @return string The operational risk level
     */
    private function assessOperationalRisk(): string
    {
        if ($this->previousStatusChanges >= 5) {
            return 'high';
        } elseif ($this->previousStatusChanges >= 3) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Categorize the status change type
     *
     * @return string The status change category
     */
    private function categorizeStatusChange(): string
    {
        if ($this->isPaymentFailure) {
            return 'payment_failure';
        } elseif ($this->isPaymentRecovery) {
            return 'payment_recovery';
        } elseif ($this->isPaymentDelay) {
            return 'payment_delay';
        } elseif ($this->previousStatusChanges >= 3) {
            return 'frequent_changes';
        }
        
        return 'standard_change';
    }

    /**
     * Get resolution options based on the status change
     *
     * @return array Available resolution options
     */
    private function getResolutionOptions(): array
    {
        $options = [
            'contact_client',
            'update_payment_method',
        ];
        
        if ($this->isPaymentFailure) {
            $options[] = 'retry_payment';
            $options[] = 'offer_payment_plan';
        }
        
        if ($this->isPaymentDelay) {
            $options[] = 'send_reminder';
            $options[] = 'negotiate_terms';
        }
        
        if ($this->daysOverdue >= 30) {
            $options[] = 'escalate_to_collections';
            $options[] = 'legal_options';
        }
        
        if ($this->isHighValue) {
            $options[] = 'management_review';
            $options[] = 'special_handling';
        }
        
        return $options;
    }
}