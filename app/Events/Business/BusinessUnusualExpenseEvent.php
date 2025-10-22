<?php

namespace App\Events\Business;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when an unusual expense is detected
 * 
 * This event is fired when expenses exceed expected patterns or thresholds,
 * helping to track potential financial issues or irregularities.
 */
class BusinessUnusualExpenseEvent extends AdministrativeAlertEvent
{
    /**
     * The user who submitted the expense
     */
    public User $user;

    /**
     * The expense record
     */
    public object $expense;

    /**
     * The expense amount
     */
    public float $expenseAmount;

    /**
     * The expense category
     */
    public string $expenseCategory;

    /**
     * The currency used for the expense
     */
    public string $currency;

    /**
     * The expected/typical amount for this category
     */
    public float|null $expectedAmount;

    /**
     * The percentage deviation from expected
     */
    public float|null $deviationPercentage;

    /**
     * The reason for the unusual expense
     */
    public string|null $reason;

    /**
     * Whether this expense was approved
     */
    public bool $isApproved;

    /**
     * The approver (if approved)
     */
    public User|null $approver;

    /**
     * Whether this expense is recurring
     */
    public bool $isRecurring;

    /**
     * The expense frequency (if recurring)
     */
    public string|null $frequency;

    /**
     * Whether this appears to be fraudulent
     */
    public bool $isSuspicious;

    /**
     * The risk level associated with this expense
     */
    public string $riskLevel;

    /**
     * The vendor or payee
     */
    public string|null $vendor;

    /**
     * Whether this expense requires investigation
     */
    public bool $requiresInvestigation;

    /**
     * Create a new event instance
     *
     * @param User $user The user who submitted the expense
     * @param object $expense The expense record
     * @param float $expenseAmount The expense amount
     * @param string $expenseCategory The expense category
     * @param string $currency The currency
     * @param float|null $expectedAmount The expected amount
     * @param string|null $reason The reason for the expense
     * @param bool $isApproved Whether this is approved
     * @param User|null $approver The approver
     * @param bool $isRecurring Whether this is recurring
     * @param string|null $frequency The frequency
     * @param bool $isSuspicious Whether this appears suspicious
     * @param string $riskLevel The risk level
     * @param string|null $vendor The vendor
     * @param bool $requiresInvestigation Whether investigation is needed
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        User $user,
        object $expense,
        float $expenseAmount,
        string $expenseCategory,
        string $currency,
        float|null $expectedAmount = null,
        string|null $reason = null,
        bool $isApproved = false,
        User|null $approver = null,
        bool $isRecurring = false,
        string|null $frequency = null,
        bool $isSuspicious = false,
        string $riskLevel = 'medium',
        string|null $vendor = null,
        bool $requiresInvestigation = false,
        User|null $initiatedBy = null
    ) {
        $this->user = $user;
        $this->expense = $expense;
        $this->expenseAmount = $expenseAmount;
        $this->expenseCategory = $expenseCategory;
        $this->currency = $currency;
        $this->expectedAmount = $expectedAmount;
        $this->reason = $reason;
        $this->isApproved = $isApproved;
        $this->approver = $approver;
        $this->isRecurring = $isRecurring;
        $this->frequency = $frequency;
        $this->isSuspicious = $isSuspicious;
        $this->riskLevel = $riskLevel;
        $this->vendor = $vendor;
        $this->requiresInvestigation = $requiresInvestigation;

        $this->deviationPercentage = $this->calculateDeviationPercentage();

        $context = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'expense_id' => $expense->id,
            'expense_amount' => $expenseAmount,
            'expense_category' => $expenseCategory,
            'currency' => $currency,
            'expected_amount' => $expectedAmount,
            'deviation_percentage' => $this->deviationPercentage,
            'reason' => $reason,
            'is_approved' => $isApproved,
            'approver_id' => $approver?->id,
            'approver_email' => $approver?->email,
            'is_recurring' => $isRecurring,
            'frequency' => $frequency,
            'is_suspicious' => $isSuspicious,
            'risk_level' => $riskLevel,
            'vendor' => $vendor,
            'requires_investigation' => $requiresInvestigation,
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
        // Critical for suspicious or very high deviation expenses
        if ($this->isSuspicious || $this->riskLevel === 'critical') {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->requiresInvestigation || $this->riskLevel === 'high') {
            return self::SEVERITY_HIGH;
        } elseif ($this->isLargeDeviation() || !$this->isApproved) {
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
        if ($this->isSuspicious) {
            return "Suspicious Expense Detected";
        } elseif ($this->requiresInvestigation) {
            return "Unusual Expense Requiring Investigation";
        } elseif ($this->isLargeDeviation()) {
            return "High Deviation Expense";
        }
        
        return "Unusual Expense Detected";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "Unusual expense detected from user '{$this->user->email}' (ID: {$this->user->id}). ";
        
        $description .= "Amount: {$this->currency}{$this->expenseAmount} in category '{$this->expenseCategory}'. ";
        
        if ($this->expectedAmount) {
            $description .= "Expected amount: {$this->currency}{$this->expectedAmount} ";
            $description .= "(deviation: {$this->deviationPercentage}%). ";
        }
        
        if ($this->isSuspicious) {
            $description .= "This expense appears suspicious and may require immediate attention. ";
        }
        
        if ($this->requiresInvestigation) {
            $description .= "This expense requires investigation. ";
        }
        
        if ($this->vendor) {
            $description .= "Vendor: {$this->vendor}. ";
        }
        
        if ($this->reason) {
            $description .= "Reason: {$this->reason}. ";
        }
        
        if ($this->isRecurring) {
            $description .= "This is a recurring expense (frequency: {$this->frequency}). ";
        }
        
        if ($this->isApproved) {
            $approverName = $this->approver ? $this->approver->email : 'Unknown';
            $description .= "Expense was approved by {$approverName}. ";
        } else {
            $description .= "Expense is not yet approved. ";
        }
        
        $description .= "Risk level: {$this->riskLevel}.";
        
        return $description;
    }

    /**
     * Get the URL for taking action on this event
     *
     * @return string|null The action URL
     */
    public function getActionUrl(): string|null
    {
        return route('admin.expenses.show', $this->expense->id);
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for suspicious expenses
        if ($this->isSuspicious || $this->riskLevel === 'critical') {
            return 'business-critical-alerts';
        } elseif ($this->requiresInvestigation) {
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
        if ($this->isSuspicious) {
            return "[CRITICAL] Suspicious Expense Detected - {$this->currency}{$this->expenseAmount}";
        } elseif ($this->requiresInvestigation) {
            return "[HIGH] Unusual Expense Requiring Investigation";
        } elseif ($this->isLargeDeviation()) {
            return "[MEDIUM] High Deviation Expense ({$this->deviationPercentage}% deviation)";
        }
        
        return "[BUSINESS] Unusual Expense - {$this->expenseCategory}";
    }

    /**
     * Calculate the deviation percentage from expected amount
     *
     * @return float|null The deviation percentage
     */
    private function calculateDeviationPercentage(): float|null
    {
        if (!$this->expectedAmount || $this->expectedAmount == 0) {
            return null;
        }

        return (($this->expenseAmount - $this->expectedAmount) / $this->expectedAmount) * 100;
    }

    /**
     * Check if this is a large deviation
     *
     * @return bool True if this is a large deviation
     */
    private function isLargeDeviation(): bool
    {
        return $this->deviationPercentage && 
               ($this->deviationPercentage >= 100 || $this->deviationPercentage <= -50);
    }

    /**
     * Check if this expense should be blocked
     *
     * @return bool True if expense should be blocked
     */
    public function shouldBlockExpense(): bool
    {
        return $this->isSuspicious || 
               $this->riskLevel === 'critical' ||
               ($this->deviationPercentage && $this->deviationPercentage >= 500);
    }

    /**
     * Check if this expense requires additional approval
     *
     * @return bool True if additional approval is required
     */
    public function requiresAdditionalApproval(): bool
    {
        return $this->requiresInvestigation || 
               $this->riskLevel === 'high' ||
               ($this->deviationPercentage && $this->deviationPercentage >= 200);
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
            'requires_blocking' => $this->shouldBlockExpense(),
            'recommended_actions' => $this->getRecommendedActions(),
            'risk_assessment' => $this->assessRisk(),
            'expense_category' => $this->categorizeExpense(),
            'investigation_options' => $this->getInvestigationOptions(),
        ];
    }

    /**
     * Calculate the urgency level based on various factors
     *
     * @return string The urgency level (low, medium, high, critical)
     */
    private function calculateUrgencyLevel(): string
    {
        if ($this->isSuspicious || $this->riskLevel === 'critical') {
            return 'critical';
        } elseif ($this->requiresInvestigation || $this->riskLevel === 'high') {
            return 'high';
        } elseif ($this->isLargeDeviation() || !$this->isApproved) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the expense
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->isSuspicious) {
            $actions[] = 'Immediately block expense';
            $actions[] = 'Contact user for clarification';
            $actions[] = 'Review user expense history';
            $actions[] = 'Consider fraud investigation';
        }
        
        if ($this->requiresInvestigation) {
            $actions[] = 'Initiate expense investigation';
            $actions[] = 'Request supporting documentation';
            $actions[] = 'Verify vendor legitimacy';
        }
        
        if ($this->shouldBlockExpense()) {
            $actions[] = 'Block expense processing';
            $actions[] = 'Notify management';
        }
        
        if ($this->requiresAdditionalApproval()) {
            $actions[] = 'Request senior management approval';
            $actions[] = 'Document justification';
        }
        
        if (!$this->isApproved && !$this->isSuspicious) {
            $actions[] = 'Review expense approval workflow';
            $actions[] = 'Process standard approval';
        }
        
        if ($this->isRecurring && $this->isLargeDeviation()) {
            $actions[] = 'Review recurring expense schedule';
            $actions[] = 'Update expense expectations';
        }
        
        if ($this->deviationPercentage && $this->deviationPercentage >= 200) {
            $actions[] = 'Analyze expense patterns';
            $actions[] = 'Update budget forecasts';
        }
        
        return $actions;
    }

    /**
     * Assess the risk associated with this expense
     *
     * @return array Risk assessment details
     */
    private function assessRisk(): array
    {
        return [
            'financial_risk' => $this->assessFinancialRisk(),
            'compliance_risk' => $this->assessComplianceRisk(),
            'fraud_risk' => $this->assessFraudRisk(),
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
        if ($this->expenseAmount >= 10000 || ($this->deviationPercentage && $this->deviationPercentage >= 500)) {
            return 'high';
        } elseif ($this->expenseAmount >= 5000 || ($this->deviationPercentage && $this->deviationPercentage >= 200)) {
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
        if ($this->isSuspicious || !$this->isApproved) {
            return 'high';
        } elseif ($this->requiresInvestigation) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the fraud risk
     *
     * @return string The fraud risk level
     */
    private function assessFraudRisk(): string
    {
        if ($this->isSuspicious) {
            return 'critical';
        } elseif ($this->riskLevel === 'high' || $this->requiresInvestigation) {
            return 'high';
        } elseif ($this->isLargeDeviation()) {
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
        if ($this->isRecurring && $this->isLargeDeviation()) {
            return 'high';
        } elseif ($this->requiresInvestigation) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Categorize the expense type
     *
     * @return string The expense category
     */
    private function categorizeExpense(): string
    {
        if ($this->isSuspicious) {
            return 'suspicious_expense';
        } elseif ($this->requiresInvestigation) {
            return 'investigation_required';
        } elseif ($this->isLargeDeviation()) {
            return 'high_deviation';
        } elseif ($this->isRecurring) {
            return 'recurring_unusual';
        }
        
        return 'unusual_expense';
    }

    /**
     * Get investigation options based on the expense
     *
     * @return array Available investigation options
     */
    private function getInvestigationOptions(): array
    {
        $options = [
            'review_documentation',
            'verify_vendor',
            'check_user_history',
        ];
        
        if ($this->isSuspicious) {
            $options[] = 'fraud_investigation';
            $options[] = 'security_review';
        }
        
        if ($this->expenseAmount >= 5000) {
            $options[] = 'management_review';
            $options[] = 'audit_trail_analysis';
        }
        
        if ($this->isRecurring) {
            $options[] = 'pattern_analysis';
            $options[] = 'historical_comparison';
        }
        
        if ($this->vendor) {
            $options[] = 'vendor_verification';
            $options[] = 'reference_check';
        }
        
        return $options;
    }
}