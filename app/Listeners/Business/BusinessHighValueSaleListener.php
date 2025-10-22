<?php

namespace App\Listeners\Business;

use App\Events\Business\BusinessHighValueSaleEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for BusinessHighValueSaleEvent
 * 
 * This listener handles high-value sale events by creating notifications,
 * logging business achievements, and potentially triggering celebration processes.
 */
class BusinessHighValueSaleListener extends AdministrativeAlertListener
{
    /**
     * Process the high-value sale event
     *
     * @param BusinessHighValueSaleEvent $event The high-value sale event
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

        // Send email notifications for high severity
        if ($event->shouldSendEmail()) {
            $this->sendEmailNotifications($event, $recipients);
        }

        // Log the business achievement
        $this->logBusinessAchievement($event);

        // Check if celebration should be triggered
        $this->checkCelebrationNeeded($event);

        // Check if bonus calculation should be triggered
        $this->checkBonusCalculation($event);

        // Create sales tracking entry
        $this->createSalesTrackingEntry($event);

        // Check for sales patterns
        $this->checkSalesPatterns($event);

        // Create sales milestone tracking
        $this->createSalesMilestoneTracking($event);

        // Update sales leaderboards
        $this->updateSalesLeaderboards($event);
    }

    /**
     * Get the recipients for this business alert
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for high-value sales
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For record high sales or very high value, also notify all managers
        if (in_array($event->getSeverity(), ['HIGH', 'CRITICAL']) || 
            $event->isRecordHigh || 
            $event->saleAmount >= 25000) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // Include the salesperson who made the sale
        if ($event->salesUser && !in_array($event->salesUser, $recipients)) {
            $recipients[] = $event->salesUser;
        }

        // If the salesperson has a manager, include them
        if ($event->salesUser && $event->salesUser->manager_id && $event->salesUser->manager_id !== $event->salesUser->id) {
            $manager = User::find($event->salesUser->manager_id);
            if ($manager && !in_array($manager, $recipients)) {
                $recipients[] = $manager;
            }
        }

        // For record high sales, include all sales team members for motivation
        if ($event->isRecordHigh) {
            $salesTeam = User::where('role', 'sales')->get()->toArray();
            $recipients = array_merge($recipients, $salesTeam);
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(BusinessHighValueSaleEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\BusinessHighValueSaleNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(BusinessHighValueSaleEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\Business\BusinessHighValueSaleMail($eventData, $recipient));
        }
    }

    /**
     * Log the business achievement with detailed information
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function logBusinessAchievement(BusinessHighValueSaleEvent $event): void
    {
        Log::info('High-value sale completed', [
            'event_type' => 'business_high_value_sale',
            'sales_user_id' => $event->salesUser?->id,
            'sales_user_email' => $event->salesUser?->email,
            'client_id' => $event->client->id,
            'client_name' => $event->client->name,
            'sale_id' => $event->sale->id,
            'sale_amount' => $event->saleAmount,
            'currency' => $event->currency,
            'profit_margin' => $event->profitMargin,
            'product_type' => $event->productType,
            'sale_category' => $event->saleCategory,
            'threshold_amount' => $event->thresholdAmount,
            'threshold_exceedance_percentage' => $event->thresholdExceedancePercentage,
            'is_record_high' => $event->isRecordHigh,
            'previous_record_high' => $event->previousRecordHigh,
            'is_unexpected' => $event->isUnexpected,
            'sales_channel' => $event->salesChannel,
            'closing_time_days' => $event->closingTimeDays,
            'requires_special_handling' => $event->requiresSpecialHandling,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if celebration should be triggered
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function checkCelebrationNeeded(BusinessHighValueSaleEvent $event): void
    {
        if ($event->shouldCelebrate()) {
            try {
                // Create celebration task
                $this->createCelebrationTask($event);
                
                Log::info('Sales celebration task created', [
                    'sale_amount' => $event->saleAmount,
                    'sales_user_id' => $event->salesUser?->id,
                    'is_record_high' => $event->isRecordHigh,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create sales celebration task', [
                    'sale_amount' => $event->saleAmount,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if bonus calculation should be triggered
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function checkBonusCalculation(BusinessHighValueSaleEvent $event): void
    {
        if ($event->shouldTriggerBonusCalculation()) {
            try {
                // Create bonus calculation task
                $this->createBonusCalculationTask($event);
                
                Log::info('Bonus calculation task created', [
                    'sale_amount' => $event->saleAmount,
                    'profit_margin' => $event->profitMargin,
                    'sales_user_id' => $event->salesUser?->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create bonus calculation task', [
                    'sale_amount' => $event->saleAmount,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create sales tracking entry
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function createSalesTrackingEntry(BusinessHighValueSaleEvent $event): void
    {
        try {
            // Create sales tracking record
            \App\Models\SalesTracking::create([
                'sales_user_id' => $event->salesUser?->id,
                'client_id' => $event->client->id,
                'sale_id' => $event->sale->id,
                'sale_amount' => $event->saleAmount,
                'currency' => $event->currency,
                'profit_margin' => $event->profitMargin,
                'product_type' => $event->productType,
                'sale_category' => $event->saleCategory,
                'sales_channel' => $event->salesChannel,
                'closing_time_days' => $event->closingTimeDays,
                'is_high_value' => true,
                'is_record_high' => $event->isRecordHigh,
                'is_unexpected' => $event->isUnexpected,
                'threshold_exceeded' => $event->thresholdAmount,
                'significance_level' => $this->calculateSignificanceLevel($event),
                'metadata' => json_encode($event->context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create sales tracking entry', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'sale_amount' => $event->saleAmount,
                    'sales_user_id' => $event->salesUser?->id,
                ],
            ]);
        }
    }

    /**
     * Check for sales patterns
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function checkSalesPatterns(BusinessHighValueSaleEvent $event): void
    {
        if ($this->hasSignificantPatterns($event)) {
            try {
                // Create sales pattern analysis
                $this->createSalesPatternAnalysis($event);
                
                Log::info('Significant sales pattern detected', [
                    'sales_user_id' => $event->salesUser?->id,
                    'sale_amount' => $event->saleAmount,
                    'pattern_indicators' => $this->getPatternIndicators($event),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to analyze sales pattern', [
                    'sales_user_id' => $event->salesUser?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create sales milestone tracking
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function createSalesMilestoneTracking(BusinessHighValueSaleEvent $event): void
    {
        try {
            // Check if this sale crosses any milestones
            $milestones = $this->checkSalesMilestones($event);
            
            foreach ($milestones as $milestone) {
                \App\Models\SalesMilestone::create([
                    'sales_user_id' => $event->salesUser?->id,
                    'milestone_type' => $milestone['type'],
                    'milestone_value' => $milestone['value'],
                    'achieved_at' => now(),
                    'triggering_sale_id' => $event->sale->id,
                    'description' => $milestone['description'],
                    'metadata' => json_encode([
                        'sale_amount' => $event->saleAmount,
                        'previous_best' => $milestone['previous_best'] ?? null,
                    ]),
                    'created_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create sales milestone tracking', [
                'sales_user_id' => $event->salesUser?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update sales leaderboards
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function updateSalesLeaderboards(BusinessHighValueSaleEvent $event): void
    {
        try {
            // Update various leaderboards
            $this->updateMonthlyLeaderboard($event);
            $this->updateQuarterlyLeaderboard($event);
            $this->updateYearlyLeaderboard($event);
            
            if ($event->isRecordHigh) {
                $this->updateAllTimeLeaderboard($event);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update sales leaderboards', [
                'sales_user_id' => $event->salesUser?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create celebration task
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function createCelebrationTask(BusinessHighValueSaleEvent $event): void
    {
        \App\Models\Task::create([
            'title' => $event->isRecordHigh 
                ? "RECORD HIGH SALE Celebration - {$event->currency}{$event->saleAmount}"
                : "High Value Sale Celebration - {$event->currency}{$event->saleAmount}",
            'description' => "Celebrate " . ($event->salesUser?->email) . "'s achievement of {$event->currency}{$event->saleAmount} sale with {$event->profitMargin}% profit margin. " . ($event->isRecordHigh ? 'This is a new record high!' : ''),
            'assigned_to' => $this->getCelebrationCoordinator($event),
            'created_by' => User::where('role', 'admin')->first()->id,
            'priority' => $event->isRecordHigh ? 'high' : 'medium',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'metadata' => json_encode([
                'celebration_details' => [
                    'sales_person' => $event->salesUser?->id,
                    'sale_amount' => $event->saleAmount,
                    'is_record_high' => $event->isRecordHigh,
                    'previous_record' => $event->previousRecordHigh,
                    'client_name' => $event->client->name,
                    'product_type' => $event->productType,
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create bonus calculation task
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function createBonusCalculationTask(BusinessHighValueSaleEvent $event): void
    {
        \App\Models\Task::create([
            'title' => "Bonus Calculation - {$event->currency}{$event->saleAmount} Sale",
            'description' => "Calculate sales bonus for {$event->salesUser?->email} for high-value sale of {$event->currency}{$event->saleAmount} with {$event->profitMargin}% profit margin.",
            'assigned_to' => $this->getBonusCoordinator($event),
            'created_by' => User::where('role', 'admin')->first()->id,
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addDays(3),
            'metadata' => json_encode([
                'bonus_calculation' => [
                    'sales_person' => $event->salesUser?->id,
                    'sale_amount' => $event->saleAmount,
                    'profit_margin' => $event->profitMargin,
                    'sale_id' => $event->sale->id,
                    'product_type' => $event->productType,
                ],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Create sales pattern analysis
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function createSalesPatternAnalysis(BusinessHighValueSaleEvent $event): void
    {
        \App\Models\SalesPatternAnalysis::create([
            'sales_user_id' => $event->salesUser?->id,
            'pattern_type' => 'high_value_success',
            'pattern_indicators' => json_encode($this->getPatternIndicators($event)),
            'triggering_sale_id' => $event->sale->id,
            'analysis_date' => now(),
            'significance_score' => $this->calculateSignificanceScore($event),
            'recommendations' => json_encode($this->getPatternRecommendations($event)),
            'created_at' => now(),
        ]);
    }

    /**
     * Calculate significance level
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return string The significance level
     */
    protected function calculateSignificanceLevel(BusinessHighValueSaleEvent $event): string
    {
        if ($event->isRecordHigh && $event->thresholdExceedancePercentage >= 200) {
            return 'exceptional';
        } elseif ($event->isRecordHigh || $event->saleAmount >= 50000) {
            return 'high';
        } elseif ($event->saleAmount >= 25000 || $event->thresholdExceedancePercentage >= 100) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Check for significant patterns
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return bool True if significant patterns are detected
     */
    protected function hasSignificantPatterns(BusinessHighValueSaleEvent $event): bool
    {
        // Check for significant indicators
        return $this->getPatternIndicators($event) !== [];
    }

    /**
     * Get pattern indicators
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return array List of pattern indicators
     */
    protected function getPatternIndicators(BusinessHighValueSaleEvent $event): array
    {
        $indicators = [];
        
        // Check for record high sales
        if ($event->isRecordHigh) {
            $indicators[] = 'record_high_achievement';
        }
        
        // Check for unexpected sales
        if ($event->isUnexpected) {
            $indicators[] = 'unexpected_success';
        }
        
        // Check for very high profit margins
        if ($event->profitMargin >= 50) {
            $indicators[] = 'exceptional_profit_margin';
        }
        
        // Check for quick closing times
        if ($event->closingTimeDays && $event->closingTimeDays <= 7) {
            $indicators[] = 'rapid_sales_cycle';
        }
        
        // Check for high threshold exceedance
        if ($event->thresholdExceedancePercentage >= 200) {
            $indicators[] = 'massive_threshold_exceedance';
        }
        
        // Check for multiple high-value sales from same salesperson
        $recentHighValueSales = \App\Models\SalesTracking::where('sales_user_id', $event->salesUser?->id)
            ->where('sale_amount', '>=', 10000)
            ->where('created_at', '>', now()->subDays(30))
            ->count();
            
        if ($recentHighValueSales >= 3) {
            $indicators[] = 'consistent_high_performance';
        }
        
        return $indicators;
    }

    /**
     * Check sales milestones
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return array List of achieved milestones
     */
    protected function checkSalesMilestones(BusinessHighValueSaleEvent $event): array
    {
        $milestones = [];
        
        // Check for personal best
        $personalBest = \App\Models\SalesTracking::where('sales_user_id', $event->salesUser?->id)
            ->max('sale_amount') ?? 0;
            
        if ($event->saleAmount > $personalBest) {
            $milestones[] = [
                'type' => 'personal_best',
                'value' => $event->saleAmount,
                'description' => "New personal best sale of {$event->currency}{$event->saleAmount}",
                'previous_best' => $personalBest,
            ];
        }
        
        // Check for monthly milestones
        $currentMonth = now()->format('Y-m');
        $monthlyTotal = \App\Models\SalesTracking::where('sales_user_id', $event->salesUser?->id)
            ->where('created_at', 'like', $currentMonth . '%')
            ->sum('sale_amount');
            
        $newMonthlyTotal = $monthlyTotal + $event->saleAmount;
        
        $milestoneAmounts = [10000, 25000, 50000, 100000, 250000, 500000];
        foreach ($milestoneAmounts as $amount) {
            if ($monthlyTotal < $amount && $newMonthlyTotal >= $amount) {
                $milestones[] = [
                    'type' => 'monthly_milestone',
                    'value' => $amount,
                    'description' => "Reached {$event->currency}{$amount} monthly sales milestone",
                ];
            }
        }
        
        return $milestones;
    }

    /**
     * Calculate significance score
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return float The significance score
     */
    protected function calculateSignificanceScore(BusinessHighValueSaleEvent $event): float
    {
        $score = 0;
        
        // Base score from sale amount
        $score += min($event->saleAmount / 1000, 50); // Max 50 points
        
        // Record high bonus
        if ($event->isRecordHigh) {
            $score += 20;
        }
        
        // Profit margin bonus
        $score += $event->profitMargin / 2; // Max 50 points for 100% margin
        
        // Unexpected success bonus
        if ($event->isUnexpected) {
            $score += 10;
        }
        
        // Quick closing bonus
        if ($event->closingTimeDays && $event->closingTimeDays <= 7) {
            $score += 10;
        }
        
        return min($score, 100); // Cap at 100
    }

    /**
     * Get pattern recommendations
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return array List of recommendations
     */
    protected function getPatternRecommendations(BusinessHighValueSaleEvent $event): array
    {
        $recommendations = [];
        
        if ($event->isRecordHigh) {
            $recommendations[] = 'Document success factors for training';
            $recommendations[] = 'Update sales targets based on new capabilities';
        }
        
        if ($event->isUnexpected) {
            $recommendations[] = 'Analyze market conditions for new opportunities';
            $recommendations[] = 'Explore similar unexpected sales channels';
        }
        
        if ($event->profitMargin >= 40) {
            $recommendations[] = 'Focus on similar high-margin products';
            $recommendations[] = 'Analyze pricing strategy for optimization';
        }
        
        if ($event->closingTimeDays && $event->closingTimeDays <= 7) {
            $recommendations[] = 'Streamline sales process for similar quick closes';
        }
        
        return $recommendations;
    }

    /**
     * Update monthly leaderboard
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function updateMonthlyLeaderboard(BusinessHighValueSaleEvent $event): void
    {
        $currentMonth = now()->format('Y-m');
        
        \App\Models\SalesLeaderboard::updateOrCreate(
            [
                'sales_user_id' => $event->salesUser?->id,
                'period_type' => 'monthly',
                'period' => $currentMonth,
            ],
            [
                'total_sales' => \DB::raw("total_sales + {$event->saleAmount}"),
                'high_value_count' => \DB::raw('high_value_count + 1'),
                'best_sale' => \DB::raw("GREATEST(COALESCE(best_sale, 0), {$event->saleAmount})"),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Update quarterly leaderboard
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function updateQuarterlyLeaderboard(BusinessHighValueSaleEvent $event): void
    {
        $currentQuarter = ceil(now()->month / 3);
        $currentYear = now()->year;
        $period = "{$currentYear}-Q{$currentQuarter}";
        
        \App\Models\SalesLeaderboard::updateOrCreate(
            [
                'sales_user_id' => $event->salesUser?->id,
                'period_type' => 'quarterly',
                'period' => $period,
            ],
            [
                'total_sales' => \DB::raw("total_sales + {$event->saleAmount}"),
                'high_value_count' => \DB::raw('high_value_count + 1'),
                'best_sale' => \DB::raw("GREATEST(COALESCE(best_sale, 0), {$event->saleAmount})"),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Update yearly leaderboard
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function updateYearlyLeaderboard(BusinessHighValueSaleEvent $event): void
    {
        $currentYear = now()->year;
        
        \App\Models\SalesLeaderboard::updateOrCreate(
            [
                'sales_user_id' => $event->salesUser?->id,
                'period_type' => 'yearly',
                'period' => $currentYear,
            ],
            [
                'total_sales' => \DB::raw("total_sales + {$event->saleAmount}"),
                'high_value_count' => \DB::raw('high_value_count + 1'),
                'best_sale' => \DB::raw("GREATEST(COALESCE(best_sale, 0), {$event->saleAmount})"),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Update all-time leaderboard
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return void
     */
    protected function updateAllTimeLeaderboard(BusinessHighValueSaleEvent $event): void
    {
        \App\Models\SalesLeaderboard::updateOrCreate(
            [
                'sales_user_id' => $event->salesUser?->id,
                'period_type' => 'all_time',
                'period' => 'all',
            ],
            [
                'total_sales' => \DB::raw("total_sales + {$event->saleAmount}"),
                'high_value_count' => \DB::raw('high_value_count + 1'),
                'best_sale' => $event->saleAmount, // This is the new record
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Get celebration coordinator
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return int User ID of coordinator
     */
    protected function getCelebrationCoordinator(BusinessHighValueSaleEvent $event): int
    {
        // Find a manager or admin to coordinate celebration
        $coordinator = User::whereIn('role', ['manager', 'admin'])->first();
        return $coordinator ? $coordinator->id : 1;
    }

    /**
     * Get bonus coordinator
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return int User ID of coordinator
     */
    protected function getBonusCoordinator(BusinessHighValueSaleEvent $event): int
    {
        // Find an admin or HR person to handle bonus calculation
        $coordinator = User::whereIn('role', ['admin', 'hr'])->first();
        return $coordinator ? $coordinator->id : 1;
    }

    /**
     * Prepare event data specific to high-value sale events
     *
     * @param BusinessHighValueSaleEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'sales_user_id' => $event->salesUser?->id,
            'sales_user_email' => $event->salesUser?->email,
            'client_id' => $event->client->id,
            'client_name' => $event->client->name,
            'sale_id' => $event->sale->id,
            'sale_amount' => $event->saleAmount,
            'currency' => $event->currency,
            'profit_margin' => $event->profitMargin,
            'product_type' => $event->productType,
            'sale_category' => $event->saleCategory,
            'threshold_amount' => $event->thresholdAmount,
            'threshold_exceedance_percentage' => $event->thresholdExceedancePercentage,
            'is_record_high' => $event->isRecordHigh,
            'previous_record_high' => $event->previousRecordHigh,
            'is_unexpected' => $event->isUnexpected,
            'sales_channel' => $event->salesChannel,
            'closing_time_days' => $event->closingTimeDays,
            'requires_special_handling' => $event->requiresSpecialHandling,
            'should_celebrate' => $event->shouldCelebrate(),
            'should_trigger_bonus_calculation' => $event->shouldTriggerBonusCalculation(),
        ]);
    }
}