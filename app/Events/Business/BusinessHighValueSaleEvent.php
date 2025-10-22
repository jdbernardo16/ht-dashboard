<?php

namespace App\Events\Business;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a high-value sale is completed
 * 
 * This event is fired when sales exceed certain value thresholds,
 * helping to track significant business transactions and milestones.
 */
class BusinessHighValueSaleEvent extends AdministrativeAlertEvent
{
    /**
     * The user who completed the sale
     */
    public User $salesUser;

    /**
     * The client who made the purchase
     */
    public object $client;

    /**
     * The sale record
     */
    public object $sale;

    /**
     * The sale amount
     */
    public float $saleAmount;

    /**
     * The profit margin for the sale
     */
    public float $profitMargin;

    /**
     * The currency used for the sale
     */
    public string $currency;

    /**
     * The product or service sold
     */
    public string $productType;

    /**
     * The sale category
     */
    public string $saleCategory;

    /**
     * Whether this is a record high sale
     */
    public bool $isRecordHigh;

    /**
     * The previous record high amount (if applicable)
     */
    public float|null $previousRecordHigh;

    /**
     * The threshold that was exceeded
     */
    public float $thresholdAmount;

    /**
     * The percentage by which the threshold was exceeded
     */
    public float $thresholdExceedancePercentage;

    /**
     * Whether this sale was unexpected
     */
    public bool $isUnexpected;

    /**
     * The sales channel used
     */
    public string $salesChannel;

    /**
     * The time taken to close this sale
     */
    public int|null $closingTimeDays;

    /**
     * Whether this sale requires special handling
     */
    public bool $requiresSpecialHandling;

    /**
     * Create a new event instance
     *
     * @param User $salesUser The user who completed the sale
     * @param object $client The client who made the purchase
     * @param object $sale The sale record
     * @param float $saleAmount The sale amount
     * @param float $profitMargin The profit margin
     * @param string $currency The currency
     * @param string $productType The product type
     * @param string $saleCategory The sale category
     * @param float $thresholdAmount The threshold exceeded
     * @param bool $isRecordHigh Whether this is a record high
     * @param float|null $previousRecordHigh The previous record
     * @param bool $isUnexpected Whether this was unexpected
     * @param string $salesChannel The sales channel
     * @param int|null $closingTimeDays Time to close in days
     * @param bool $requiresSpecialHandling Whether special handling is needed
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        User $salesUser,
        object $client,
        object $sale,
        float $saleAmount,
        float $profitMargin,
        string $currency,
        string $productType,
        string $saleCategory,
        float $thresholdAmount,
        bool $isRecordHigh = false,
        float|null $previousRecordHigh = null,
        bool $isUnexpected = false,
        string $salesChannel = 'direct',
        int|null $closingTimeDays = null,
        bool $requiresSpecialHandling = false,
        User|null $initiatedBy = null
    ) {
        $this->salesUser = $salesUser;
        $this->client = $client;
        $this->sale = $sale;
        $this->saleAmount = $saleAmount;
        $this->profitMargin = $profitMargin;
        $this->currency = $currency;
        $this->productType = $productType;
        $this->saleCategory = $saleCategory;
        $this->thresholdAmount = $thresholdAmount;
        $this->isRecordHigh = $isRecordHigh;
        $this->previousRecordHigh = $previousRecordHigh;
        $this->isUnexpected = $isUnexpected;
        $this->salesChannel = $salesChannel;
        $this->closingTimeDays = $closingTimeDays;
        $this->requiresSpecialHandling = $requiresSpecialHandling;

        $this->thresholdExceedancePercentage = $this->calculateThresholdExceedance();

        $context = [
            'sales_user_id' => $salesUser->id,
            'sales_user_email' => $salesUser->email,
            'client_id' => $client->id,
            'client_name' => $client->name,
            'sale_id' => $sale->id,
            'sale_amount' => $saleAmount,
            'profit_margin' => $profitMargin,
            'currency' => $currency,
            'product_type' => $productType,
            'sale_category' => $saleCategory,
            'threshold_amount' => $thresholdAmount,
            'threshold_exceedance_percentage' => $this->thresholdExceedancePercentage,
            'is_record_high' => $isRecordHigh,
            'previous_record_high' => $previousRecordHigh,
            'is_unexpected' => $isUnexpected,
            'sales_channel' => $salesChannel,
            'closing_time_days' => $closingTimeDays,
            'requires_special_handling' => $requiresSpecialHandling,
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
        // High for record high sales or very high value sales
        if ($this->isRecordHigh || $this->saleAmount >= 50000) {
            return self::SEVERITY_HIGH;
        } elseif ($this->saleAmount >= 10000 || $this->isUnexpected) {
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
        if ($this->isRecordHigh) {
            return "Record High Sale Achieved";
        } elseif ($this->isUnexpected) {
            return "Unexpected High Value Sale";
        } elseif ($this->saleAmount >= 50000) {
            return "Very High Value Sale Completed";
        }
        
        return "High Value Sale Completed";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "High value sale completed by '{$this->salesUser->email}' (ID: {$this->salesUser->id}). ";
        
        $description .= "Sale amount: {$this->currency}{$this->saleAmount} to client '{$this->client->name}'. ";
        
        $description .= "Product: {$this->productType}, Category: {$this->saleCategory}. ";
        
        $description .= "Profit margin: {$this->profitMargin}%. ";
        
        if ($this->isRecordHigh) {
            $description .= "This is a new record high sale! ";
            if ($this->previousRecordHigh) {
                $description .= "Previous record: {$this->currency}{$this->previousRecordHigh}. ";
            }
        }
        
        if ($this->isUnexpected) {
            $description .= "This sale was unexpected/surprise. ";
        }
        
        $description .= "Exceeded threshold of {$this->currency}{$this->thresholdAmount} by {$this->thresholdExceedancePercentage}%. ";
        
        if ($this->salesChannel) {
            $description .= "Sales channel: {$this->salesChannel}. ";
        }
        
        if ($this->closingTimeDays) {
            $description .= "Closing time: {$this->closingTimeDays} days. ";
        }
        
        if ($this->requiresSpecialHandling) {
            $description .= "This sale requires special handling. ";
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
        return route('admin.sales.show', $this->sale->id);
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for record high sales
        if ($this->isRecordHigh || $this->saleAmount >= 50000) {
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
        if ($this->isRecordHigh) {
            return "[HIGH] RECORD HIGH SALE - {$this->currency}{$this->saleAmount}";
        } elseif ($this->isUnexpected) {
            return "[MEDIUM] Unexpected High Value Sale - {$this->currency}{$this->saleAmount}";
        } elseif ($this->saleAmount >= 50000) {
            return "[HIGH] Very High Value Sale - {$this->currency}{$this->saleAmount}";
        }
        
        return "[BUSINESS] High Value Sale - {$this->currency}{$this->saleAmount}";
    }

    /**
     * Calculate the threshold exceedance percentage
     *
     * @return float The percentage exceedance
     */
    private function calculateThresholdExceedance(): float
    {
        if ($this->thresholdAmount == 0) {
            return 0;
        }

        return (($this->saleAmount - $this->thresholdAmount) / $this->thresholdAmount) * 100;
    }

    /**
     * Check if this sale requires celebration
     *
     * @return bool True if celebration is warranted
     */
    public function shouldCelebrate(): bool
    {
        return $this->isRecordHigh || 
               $this->saleAmount >= 25000 ||
               $this->thresholdExceedancePercentage >= 100;
    }

    /**
     * Check if this sale should trigger bonus calculation
     *
     * @return bool True if bonus calculation should be triggered
     */
    public function shouldTriggerBonusCalculation(): bool
    {
        return $this->saleAmount >= 10000 || $this->profitMargin >= 30;
    }

    /**
     * Get additional metadata for logging and analytics
     *
     * @return array Additional metadata
     */
    public function getMetadata(): array
    {
        return [
            'significance_level' => $this->calculateSignificanceLevel(),
            'requires_celebration' => $this->shouldCelebrate(),
            'recommended_actions' => $this->getRecommendedActions(),
            'business_impact' => $this->assessBusinessImpact(),
            'sales_category' => $this->categorizeSale(),
            'incentive_options' => $this->getIncentiveOptions(),
        ];
    }

    /**
     * Calculate the significance level based on various factors
     *
     * @return string The significance level (low, medium, high, exceptional)
     */
    private function calculateSignificanceLevel(): string
    {
        if ($this->isRecordHigh && $this->thresholdExceedancePercentage >= 200) {
            return 'exceptional';
        } elseif ($this->isRecordHigh || $this->saleAmount >= 50000) {
            return 'high';
        } elseif ($this->saleAmount >= 10000 || $this->thresholdExceedancePercentage >= 100) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the sale
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->isRecordHigh) {
            $actions[] = 'Announce record achievement';
            $actions[] = 'Update sales targets';
            $actions[] = 'Document success factors';
        }
        
        if ($this->shouldCelebrate()) {
            $actions[] = 'Plan team celebration';
            $actions[] = 'Recognize salesperson achievement';
        }
        
        if ($this->shouldTriggerBonusCalculation()) {
            $actions[] = 'Calculate sales bonus';
            $actions[] = 'Review commission structure';
        }
        
        if ($this->isUnexpected) {
            $actions[] = 'Analyze unexpected success factors';
            $actions[] = 'Review market conditions';
        }
        
        if ($this->requiresSpecialHandling) {
            $actions[] = 'Coordinate special delivery';
            $actions[] = 'Assign dedicated support';
        }
        
        if ($this->profitMargin >= 40) {
            $actions[] = 'Analyze high margin factors';
            $actions[] = 'Consider similar opportunities';
        }
        
        return $actions;
    }

    /**
     * Assess the business impact of this sale
     *
     * @return array Business impact assessment details
     */
    private function assessBusinessImpact(): array
    {
        return [
            'revenue_impact' => $this->assessRevenueImpact(),
            'profit_impact' => $this->assessProfitImpact(),
            'market_impact' => $this->assessMarketImpact(),
            'team_morale_impact' => $this->assessTeamMoraleImpact(),
        ];
    }

    /**
     * Assess the revenue impact
     *
     * @return string The revenue impact level
     */
    private function assessRevenueImpact(): string
    {
        if ($this->saleAmount >= 50000) {
            return 'exceptional';
        } elseif ($this->saleAmount >= 20000) {
            return 'high';
        } elseif ($this->saleAmount >= 10000) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the profit impact
     *
     * @return string The profit impact level
     */
    private function assessProfitImpact(): string
    {
        if ($this->profitMargin >= 40) {
            return 'exceptional';
        } elseif ($this->profitMargin >= 30) {
            return 'high';
        } elseif ($this->profitMargin >= 20) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the market impact
     *
     * @return string The market impact level
     */
    private function assessMarketImpact(): string
    {
        if ($this->isRecordHigh || $this->isUnexpected) {
            return 'high';
        } elseif ($this->saleAmount >= 25000) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Assess the team morale impact
     *
     * @return string The team morale impact level
     */
    private function assessTeamMoraleImpact(): string
    {
        if ($this->isRecordHigh || $this->shouldCelebrate()) {
            return 'high';
        } elseif ($this->saleAmount >= 15000) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Categorize the sale type
     *
     * @return string The sale category
     */
    private function categorizeSale(): string
    {
        if ($this->isRecordHigh) {
            return 'record_sale';
        } elseif ($this->isUnexpected) {
            return 'unexpected_sale';
        } elseif ($this->saleAmount >= 50000) {
            return 'enterprise_sale';
        } elseif ($this->saleAmount >= 10000) {
            return 'high_value_sale';
        }
        
        return 'standard_sale';
    }

    /**
     * Get incentive options based on the sale
     *
     * @return array Available incentive options
     */
    private function getIncentiveOptions(): array
    {
        $options = [
            'sales_commission',
            'team_bonus',
        ];
        
        if ($this->isRecordHigh) {
            $options[] = 'record_bonus';
            $options[] = 'recognition_award';
        }
        
        if ($this->profitMargin >= 30) {
            $options[] = 'profit_sharing';
        }
        
        if ($this->shouldCelebrate()) {
            $options[] = 'team_celebration';
            $options[] = 'public_recognition';
        }
        
        if ($this->saleAmount >= 25000) {
            $options[] = 'performance_bonus';
            $options[] = 'career_advancement';
        }
        
        return $options;
    }
}