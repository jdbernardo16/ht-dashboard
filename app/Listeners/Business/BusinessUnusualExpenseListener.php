<?php

namespace App\Listeners\Business;

use App\Events\Business\BusinessUnusualExpenseEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for BusinessUnusualExpenseEvent
 * 
 * This listener handles unusual expense events by creating notifications,
 * logging financial anomalies, and potentially triggering investigation processes.
 */
class BusinessUnusualExpenseListener extends AdministrativeAlertListener
{
    /**
     * Process the unusual expense event
     *
     * @param BusinessUnusualExpenseEvent $event The unusual expense event
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

        // Log the unusual expense
        $this->logUnusualExpense($event);

        // Check if expense should be blocked
        $this->checkExpenseBlocking($event);

        // Check if additional approval is needed
        $this->checkAdditionalApproval($event);

        // Create expense tracking entry
        $this->createExpenseTrackingEntry($event);

        // Check for expense patterns
        $this->checkExpensePatterns($event);

        // Create expense investigation tracking
        $this->createExpenseInvestigationTracking($event);

        // Update expense monitoring
        $this->updateExpenseMonitoring($event);
    }

    /**
     * Get the recipients for this business alert
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for unusual expenses
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For suspicious or high-risk expenses, also notify all managers
        if (in_array($event->getSeverity(), ['HIGH', 'CRITICAL']) || 
            $event->isSuspicious || 
            $event->requiresInvestigation) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // Include the user who submitted the expense
        if ($event->user && !in_array($event->user, $recipients)) {
            $recipients[] = $event->user;
        }

        // Include the approver if different from user
        if ($event->approver && $event->approver->id !== $event->user->id && 
            !in_array($event->approver, $recipients)) {
            $recipients[] = $event->approver;
        }

        // If the user has a manager, include them
        if ($event->user && $event->user->manager_id && $event->user->manager_id !== $event->user->id) {
            $manager = User::find($event->user->manager_id);
            if ($manager && !in_array($manager, $recipients)) {
                $recipients[] = $manager;
            }
        }

        // For suspicious expenses, include finance/audit team
        if ($event->isSuspicious) {
            $financeUsers = User::where('role', 'finance')->get()->toArray();
            $recipients = array_merge($recipients, $financeUsers);
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(BusinessUnusualExpenseEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\BusinessUnusualExpenseNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(BusinessUnusualExpenseEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\Business\BusinessUnusualExpenseMail($eventData, $recipient));
        }
    }

    /**
     * Log the unusual expense with detailed information
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function logUnusualExpense(BusinessUnusualExpenseEvent $event): void
    {
        Log::warning('Unusual expense detected', [
            'event_type' => 'business_unusual_expense',
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'expense_id' => $event->expense->id,
            'expense_amount' => $event->expenseAmount,
            'currency' => $event->currency,
            'expense_category' => $event->expenseCategory,
            'expected_amount' => $event->expectedAmount,
            'deviation_percentage' => $event->deviationPercentage,
            'reason' => $event->reason,
            'is_approved' => $event->isApproved,
            'approver_id' => $event->approver?->id,
            'approver_email' => $event->approver?->email,
            'is_recurring' => $event->isRecurring,
            'frequency' => $event->frequency,
            'is_suspicious' => $event->isSuspicious,
            'risk_level' => $event->riskLevel,
            'vendor' => $event->vendor,
            'requires_investigation' => $event->requiresInvestigation,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if expense should be blocked
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function checkExpenseBlocking(BusinessUnusualExpenseEvent $event): void
    {
        if ($event->shouldBlockExpense()) {
            try {
                // Block the expense
                $this->blockExpense($event);
                
                Log::info('Expense blocked due to suspicious activity', [
                    'expense_id' => $event->expense->id,
                    'user_id' => $event->user?->id,
                    'risk_level' => $event->riskLevel,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to block suspicious expense', [
                    'expense_id' => $event->expense->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if additional approval is needed
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function checkAdditionalApproval(BusinessUnusualExpenseEvent $event): void
    {
        if ($event->requiresAdditionalApproval()) {
            try {
                // Create additional approval request
                $this->createAdditionalApprovalRequest($event);
                
                Log::info('Additional approval request created', [
                    'expense_id' => $event->expense->id,
                    'user_id' => $event->user?->id,
                    'deviation_percentage' => $event->deviationPercentage,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create additional approval request', [
                    'expense_id' => $event->expense->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create expense tracking entry
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function createExpenseTrackingEntry(BusinessUnusualExpenseEvent $event): void
    {
        try {
            // Create expense tracking record
            \App\Models\ExpenseTracking::create([
                'expense_id' => $event->expense->id,
                'user_id' => $event->user?->id,
                'expense_amount' => $event->expenseAmount,
                'currency' => $event->currency,
                'expense_category' => $event->expenseCategory,
                'expected_amount' => $event->expectedAmount,
                'deviation_percentage' => $event->deviationPercentage,
                'is_approved' => $event->isApproved,
                'approver_id' => $event->approver?->id,
                'is_recurring' => $event->isRecurring,
                'frequency' => $event->frequency,
                'is_suspicious' => $event->isSuspicious,
                'risk_level' => $event->riskLevel,
                'vendor' => $event->vendor,
                'requires_investigation' => $event->requiresInvestigation,
                'risk_score' => $this->calculateRiskScore($event),
                'anomaly_indicators' => json_encode($this->getAnomalyIndicators($event)),
                'metadata' => json_encode($event->context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create expense tracking entry', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'expense_id' => $event->expense->id,
                    'user_id' => $event->user?->id,
                ],
            ]);
        }
    }

    /**
     * Check for expense patterns
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function checkExpensePatterns(BusinessUnusualExpenseEvent $event): void
    {
        if ($this->hasConcerningPatterns($event)) {
            try {
                // Create expense pattern analysis
                $this->createExpensePatternAnalysis($event);
                
                Log::warning('Concerning expense pattern detected', [
                    'expense_id' => $event->expense->id,
                    'user_id' => $event->user?->id,
                    'pattern_indicators' => $this->getPatternIndicators($event),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to analyze expense pattern', [
                    'expense_id' => $event->expense->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create expense investigation tracking
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function createExpenseInvestigationTracking(BusinessUnusualExpenseEvent $event): void
    {
        if ($event->requiresInvestigation || $event->isSuspicious) {
            try {
                // Create investigation record
                \App\Models\ExpenseInvestigation::create([
                    'expense_id' => $event->expense->id,
                    'user_id' => $event->user?->id,
                    'investigation_type' => $event->isSuspicious ? 'fraud_investigation' : 'anomaly_review',
                    'priority' => $event->riskLevel === 'critical' ? 'high' : 'medium',
                    'status' => 'pending',
                    'assigned_to' => $this->getInvestigationAssignee($event),
                    'reason' => $event->reason,
                    'risk_factors' => json_encode($this->getRiskFactors($event)),
                    'investigation_deadline' => now()->addDays($event->isSuspicious ? 3 : 7),
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create expense investigation tracking', [
                    'expense_id' => $event->expense->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Update expense monitoring
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function updateExpenseMonitoring(BusinessUnusualExpenseEvent $event): void
    {
        try {
            // Update user expense monitoring
            \App\Models\ExpenseMonitoring::updateOrCreate(
                [
                    'user_id' => $event->user?->id,
                    'expense_category' => $event->expenseCategory,
                    'period' => now()->format('Y-m'),
                ],
                [
                    'total_expenses' => \DB::raw("total_expenses + {$event->expenseAmount}"),
                    'unusual_count' => \DB::raw('unusual_count + 1'),
                    'high_risk_count' => \DB::raw($event->riskLevel === 'high' || $event->riskLevel === 'critical' ? 'high_risk_count + 1' : 'high_risk_count'),
                    'deviation_total' => \DB::raw("COALESCE(deviation_total, 0) + " . ($event->deviationPercentage ?? 0)),
                    'last_unusual_date' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to update expense monitoring', [
                'user_id' => $event->user?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Block the expense
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function blockExpense(BusinessUnusualExpenseEvent $event): void
    {
        // Update expense status to blocked
        $event->expense->update([
            'status' => 'blocked',
            'blocked_reason' => 'Suspicious activity detected - automatic block',
            'blocked_at' => now(),
        ]);

        // Create blocking notification
        \App\Models\ExpenseBlocking::create([
            'expense_id' => $event->expense->id,
            'user_id' => $event->user?->id,
            'blocking_reason' => 'Suspicious activity detected',
            'risk_level' => $event->riskLevel,
            'requires_manual_review' => true,
            'created_at' => now(),
        ]);
    }

    /**
     * Create additional approval request
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function createAdditionalApprovalRequest(BusinessUnusualExpenseEvent $event): void
    {
        \App\Models\ExpenseApprovalRequest::create([
            'expense_id' => $event->expense->id,
            'user_id' => $event->user?->id,
            'request_type' => 'additional_approval',
            'reason' => "Unusual expense deviation: {$event->deviationPercentage}%",
            'requested_amount' => $event->expenseAmount,
            'deviation_percentage' => $event->deviationPercentage,
            'risk_level' => $event->riskLevel,
            'assigned_to' => $this->getAdditionalApprover($event),
            'status' => 'pending',
            'deadline' => now()->addDays(3),
            'created_at' => now(),
        ]);
    }

    /**
     * Create expense pattern analysis
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return void
     */
    protected function createExpensePatternAnalysis(BusinessUnusualExpenseEvent $event): void
    {
        \App\Models\ExpensePatternAnalysis::create([
            'user_id' => $event->user?->id,
            'pattern_type' => 'unusual_expense_pattern',
            'pattern_indicators' => json_encode($this->getPatternIndicators($event)),
            'triggering_expense_id' => $event->expense->id,
            'analysis_date' => now(),
            'risk_score' => $this->calculateRiskScore($event),
            'recommendations' => json_encode($this->getPatternRecommendations($event)),
            'created_at' => now(),
        ]);
    }

    /**
     * Calculate risk score
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return float The risk score
     */
    protected function calculateRiskScore(BusinessUnusualExpenseEvent $event): float
    {
        $score = 0;
        
        // Base score from deviation percentage
        if ($event->deviationPercentage) {
            $score += min(abs($event->deviationPercentage) / 10, 30); // Max 30 points
        }
        
        // Suspicious activity bonus
        if ($event->isSuspicious) {
            $score += 40;
        }
        
        // Risk level bonus
        $score += match($event->riskLevel) {
            'critical' => 30,
            'high' => 20,
            'medium' => 10,
            default => 0,
        };
        
        // High amount bonus
        if ($event->expenseAmount >= 10000) {
            $score += 15;
        } elseif ($event->expenseAmount >= 5000) {
            $score += 10;
        }
        
        // Lack of approval penalty
        if (!$event->isApproved) {
            $score += 10;
        }
        
        return min($score, 100); // Cap at 100
    }

    /**
     * Get anomaly indicators
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return array List of anomaly indicators
     */
    protected function getAnomalyIndicators(BusinessUnusualExpenseEvent $event): array
    {
        $indicators = [];
        
        // Check for large deviation
        if ($event->deviationPercentage && abs($event->deviationPercentage) >= 100) {
            $indicators[] = 'large_deviation';
        }
        
        // Check for suspicious activity
        if ($event->isSuspicious) {
            $indicators[] = 'suspicious_activity';
        }
        
        // Check for high amount
        if ($event->expenseAmount >= 10000) {
            $indicators[] = 'high_amount';
        }
        
        // Check for unusual vendor
        if ($this->isUnusualVendor($event)) {
            $indicators[] = 'unusual_vendor';
        }
        
        // Check for unusual timing
        if ($this->isUnusualTiming($event)) {
            $indicators[] = 'unusual_timing';
        }
        
        return $indicators;
    }

    /**
     * Check for concerning patterns
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return bool True if concerning patterns are detected
     */
    protected function hasConcerningPatterns(BusinessUnusualExpenseEvent $event): bool
    {
        // Check for concerning indicators
        return $this->getPatternIndicators($event) !== [];
    }

    /**
     * Get pattern indicators
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return array List of pattern indicators
     */
    protected function getPatternIndicators(BusinessUnusualExpenseEvent $event): array
    {
        $indicators = [];
        
        // Check for repeated unusual expenses
        $recentUnusual = \App\Models\ExpenseTracking::where('user_id', $event->user?->id)
            ->where('requires_investigation', true)
            ->where('created_at', '>', now()->subDays(30))
            ->count();
            
        if ($recentUnusual >= 3) {
            $indicators[] = 'repeated_unusual_expenses';
        }
        
        // Check for category concentration
        $categoryTotal = \App\Models\ExpenseTracking::where('user_id', $event->user?->id)
            ->where('expense_category', $event->expenseCategory)
            ->where('created_at', '>', now()->subDays(90))
            ->sum('expense_amount');
            
        if ($categoryTotal > 20000) {
            $indicators[] = 'high_category_concentration';
        }
        
        // Check for weekend/holiday expenses
        if ($this->isWeekendOrHoliday($event)) {
            $indicators[] = 'weekend_holiday_expense';
        }
        
        // Check for rapid succession expenses
        $recentExpenses = \App\Models\ExpenseTracking::where('user_id', $event->user?->id)
            ->where('created_at', '>', now()->subHours(24))
            ->count();
            
        if ($recentExpenses >= 5) {
            $indicators[] = 'rapid_succession_expenses';
        }
        
        return $indicators;
    }

    /**
     * Get risk factors
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return array List of risk factors
     */
    protected function getRiskFactors(BusinessUnusualExpenseEvent $event): array
    {
        $factors = [];
        
        if ($event->isSuspicious) {
            $factors[] = 'suspicious_activity_detected';
        }
        
        if ($event->riskLevel === 'critical') {
            $factors[] = 'critical_risk_level';
        }
        
        if ($event->deviationPercentage && abs($event->deviationPercentage) >= 200) {
            $factors[] = 'extreme_deviation';
        }
        
        if (!$event->isApproved && $event->expenseAmount >= 5000) {
            $factors[] = 'unapproved_high_amount';
        }
        
        if ($this->isUnusualVendor($event)) {
            $factors[] = 'unusual_vendor_pattern';
        }
        
        return $factors;
    }

    /**
     * Get pattern recommendations
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return array List of recommendations
     */
    protected function getPatternRecommendations(BusinessUnusualExpenseEvent $event): array
    {
        $recommendations = [];
        
        if ($event->isSuspicious) {
            $recommendations[] = 'Immediate fraud investigation required';
            $recommendations[] = 'Consider temporary expense restrictions';
        }
        
        if ($event->deviationPercentage && abs($event->deviationPercentage) >= 100) {
            $recommendations[] = 'Review expense category budgets';
            $recommendations[] = 'Update expense expectations';
        }
        
        if (!$event->isApproved) {
            $recommendations[] = 'Strengthen approval workflows';
            $recommendations[] = 'Review user expense training';
        }
        
        if ($this->hasConcerningPatterns($event)) {
            $recommendations[] = 'Monitor user expense patterns closely';
            $recommendations[] = 'Consider expense policy review';
        }
        
        return $recommendations;
    }

    /**
     * Check if vendor is unusual
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return bool True if vendor is unusual
     */
    protected function isUnusualVendor(BusinessUnusualExpenseEvent $event): bool
    {
        if (!$event->vendor) {
            return false;
        }
        
        // Check if vendor has been used before by this user
        $previousUsage = \App\Models\ExpenseTracking::where('user_id', $event->user?->id)
            ->where('vendor', $event->vendor)
            ->count();
            
        return $previousUsage === 0;
    }

    /**
     * Check if timing is unusual
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return bool True if timing is unusual
     */
    protected function isUnusualTiming(BusinessUnusualExpenseEvent $event): bool
    {
        $hour = now()->hour;
        
        // Check for late night or early morning expenses
        return $hour < 6 || $hour > 22;
    }

    /**
     * Check if date is weekend or holiday
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return bool True if weekend or holiday
     */
    protected function isWeekendOrHoliday(BusinessUnusualExpenseEvent $event): bool
    {
        $dayOfWeek = now()->dayOfWeek;
        
        // Saturday (6) or Sunday (0)
        return $dayOfWeek === 0 || $dayOfWeek === 6;
    }

    /**
     * Get investigation assignee
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return int User ID of assignee
     */
    protected function getInvestigationAssignee(BusinessUnusualExpenseEvent $event): int
    {
        // For suspicious expenses, assign to finance team
        if ($event->isSuspicious) {
            $financeUser = User::where('role', 'finance')->first();
            return $financeUser ? $financeUser->id : 1;
        }
        
        // For other investigations, assign to manager
        if ($event->user && $event->user->manager_id) {
            return $event->user->manager_id;
        }
        
        // Default to admin
        $admin = User::where('role', 'admin')->first();
        return $admin ? $admin->id : 1;
    }

    /**
     * Get additional approver
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return int User ID of approver
     */
    protected function getAdditionalApprover(BusinessUnusualExpenseEvent $event): int
    {
        // For high amounts, assign to finance
        if ($event->expenseAmount >= 10000) {
            $financeUser = User::where('role', 'finance')->first();
            return $financeUser ? $financeUser->id : 1;
        }
        
        // For other cases, assign to manager or admin
        if ($event->user && $event->user->manager_id) {
            return $event->user->manager_id;
        }
        
        $admin = User::where('role', 'admin')->first();
        return $admin ? $admin->id : 1;
    }

    /**
     * Prepare event data specific to unusual expense events
     *
     * @param BusinessUnusualExpenseEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'expense_id' => $event->expense->id,
            'expense_amount' => $event->expenseAmount,
            'currency' => $event->currency,
            'expense_category' => $event->expenseCategory,
            'expected_amount' => $event->expectedAmount,
            'deviation_percentage' => $event->deviationPercentage,
            'reason' => $event->reason,
            'is_approved' => $event->isApproved,
            'approver_id' => $event->approver?->id,
            'approver_email' => $event->approver?->email,
            'is_recurring' => $event->isRecurring,
            'frequency' => $event->frequency,
            'is_suspicious' => $event->isSuspicious,
            'risk_level' => $event->riskLevel,
            'vendor' => $event->vendor,
            'requires_investigation' => $event->requiresInvestigation,
            'should_block_expense' => $event->shouldBlockExpense(),
            'requires_additional_approval' => $event->requiresAdditionalApproval(),
        ]);
    }
}