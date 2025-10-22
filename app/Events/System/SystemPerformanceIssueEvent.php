<?php

namespace App\Events\System;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a performance issue is detected
 * 
 * This event is fired when system performance metrics exceed thresholds,
 * helping to identify potential performance bottlenecks or system overload.
 */
class SystemPerformanceIssueEvent extends AdministrativeAlertEvent
{
    /**
     * The type of performance issue detected
     */
    public string $issueType;

    /**
     * The metric that triggered the alert
     */
    public string $metric;

    /**
     * The current value of the metric
     */
    public float|int $currentValue;

    /**
     * The threshold value that was exceeded
     */
    public float|int $thresholdValue;

    /**
     * The duration of the performance issue in seconds
     */
    public int $duration;

    /**
     * The server or component affected
     */
    public string $component;

    /**
     * The process or application causing the issue (if known)
     */
    public string|null $process;

    /**
     * The memory usage at the time of the issue (in MB)
     */
    public float|null $memoryUsage;

    /**
     * The CPU usage at the time of the issue (percentage)
     */
    public float|null $cpuUsage;

    /**
     * The disk usage at the time of the issue (percentage)
     */
    public float|null $diskUsage;

    /**
     * The number of active connections/requests
     */
    public int|null $activeConnections;

    /**
     * Whether this is a critical performance degradation
     */
    public bool $isCriticalDegradation;

    /**
     * Whether this is affecting user experience
     */
    public bool $isAffectingUsers;

    /**
     * Whether automatic mitigation was attempted
     */
    public bool $mitigationAttempted;

    /**
     * Whether the mitigation was successful
     */
    public bool $mitigationSuccessful;

    /**
     * Create a new event instance
     *
     * @param string $issueType The type of performance issue
     * @param string $metric The metric that triggered the alert
     * @param float|int $currentValue The current metric value
     * @param float|int $thresholdValue The threshold that was exceeded
     * @param int $duration Duration of the issue in seconds
     * @param string $component The affected component
     * @param string|null $process The process causing the issue
     * @param float|null $memoryUsage Memory usage in MB
     * @param float|null $cpuUsage CPU usage percentage
     * @param float|null $diskUsage Disk usage percentage
     * @param int|null $activeConnections Number of active connections
     * @param bool $isCriticalDegradation Whether this is critical degradation
     * @param bool $isAffectingUsers Whether users are affected
     * @param bool $mitigationAttempted Whether mitigation was attempted
     * @param bool $mitigationSuccessful Whether mitigation was successful
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        string $issueType,
        string $metric,
        float|int $currentValue,
        float|int $thresholdValue,
        int $duration,
        string $component,
        string|null $process = null,
        float|null $memoryUsage = null,
        float|null $cpuUsage = null,
        float|null $diskUsage = null,
        int|null $activeConnections = null,
        bool $isCriticalDegradation = false,
        bool $isAffectingUsers = true,
        bool $mitigationAttempted = false,
        bool $mitigationSuccessful = false,
        User|null $initiatedBy = null
    ) {
        $this->issueType = $issueType;
        $this->metric = $metric;
        $this->currentValue = $currentValue;
        $this->thresholdValue = $thresholdValue;
        $this->duration = $duration;
        $this->component = $component;
        $this->process = $process;
        $this->memoryUsage = $memoryUsage;
        $this->cpuUsage = $cpuUsage;
        $this->diskUsage = $diskUsage;
        $this->activeConnections = $activeConnections;
        $this->isCriticalDegradation = $isCriticalDegradation;
        $this->isAffectingUsers = $isAffectingUsers;
        $this->mitigationAttempted = $mitigationAttempted;
        $this->mitigationSuccessful = $mitigationSuccessful;

        $context = [
            'issue_type' => $issueType,
            'metric' => $metric,
            'current_value' => $currentValue,
            'threshold_value' => $thresholdValue,
            'duration' => $duration,
            'component' => $component,
            'process' => $process,
            'memory_usage' => $memoryUsage,
            'cpu_usage' => $cpuUsage,
            'disk_usage' => $diskUsage,
            'active_connections' => $activeConnections,
            'is_critical_degradation' => $isCriticalDegradation,
            'is_affecting_users' => $isAffectingUsers,
            'mitigation_attempted' => $mitigationAttempted,
            'mitigation_successful' => $mitigationSuccessful,
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
        return 'System';
    }

    /**
     * Get the severity level of this event
     *
     * @return string The severity level
     */
    public function getSeverity(): string
    {
        // Critical for critical degradation or long-duration issues
        if ($this->isCriticalDegradation || $this->duration >= 300) {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->isAffectingUsers && $this->duration >= 60) {
            return self::SEVERITY_HIGH;
        } elseif ($this->isHighImpactMetric() || $this->duration >= 30) {
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
        if ($this->isCriticalDegradation) {
            return "Critical Performance Degradation Detected";
        } elseif ($this->isAffectingUsers) {
            return "Performance Issue Affecting Users";
        } elseif ($this->duration > 60) {
            return "Sustained Performance Issue Detected";
        }
        
        return "Performance Issue Detected";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "Performance issue detected in {$this->component}. ";
        
        $description .= "Metric '{$this->metric}' exceeded threshold: ";
        $description .= "current value ({$this->currentValue}) > threshold ({$this->thresholdValue}). ";
        
        $description .= "Issue duration: {$this->duration} seconds. ";
        
        if ($this->process) {
            $description .= "Affected process: {$this->process}. ";
        }
        
        if ($this->memoryUsage) {
            $description .= "Memory usage: {$this->memoryUsage}MB. ";
        }
        
        if ($this->cpuUsage) {
            $description .= "CPU usage: {$this->cpuUsage}%. ";
        }
        
        if ($this->diskUsage) {
            $description .= "Disk usage: {$this->diskUsage}%. ";
        }
        
        if ($this->activeConnections) {
            $description .= "Active connections: {$this->activeConnections}. ";
        }
        
        if ($this->isCriticalDegradation) {
            $description .= "This represents a critical degradation in system performance. ";
        } elseif ($this->isAffectingUsers) {
            $description .= "User experience is being impacted by this issue. ";
        }
        
        if ($this->mitigationAttempted) {
            $mitigationStatus = $this->mitigationSuccessful ? "successful" : "failed";
            $description .= "Automatic mitigation was attempted and {$mitigationStatus}. ";
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
        return route('admin.system.performance') . '?component=' . $this->component;
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use critical queue for critical degradation
        if ($this->isCriticalDegradation || $this->duration >= 300) {
            return 'system-critical-alerts';
        } elseif ($this->isAffectingUsers) {
            return 'system-high-alerts';
        }
        
        return 'system-alerts';
    }

    /**
     * Get the email subject for notifications
     *
     * @return string The email subject
     */
    public function getEmailSubject(): string
    {
        if ($this->isCriticalDegradation) {
            return "[CRITICAL] Critical Performance Degradation - {$this->component}";
        } elseif ($this->isAffectingUsers) {
            return "[HIGH] Performance Issue Affecting Users";
        } elseif ($this->duration > 60) {
            return "[MEDIUM] Sustained Performance Issue ({$this->duration}s)";
        }
        
        return "[SYSTEM] Performance Issue - {$this->metric}";
    }

    /**
     * Check if this is a high-impact metric
     *
     * @return bool True if this is a high-impact metric
     */
    private function isHighImpactMetric(): bool
    {
        $highImpactMetrics = [
            'response_time',
            'memory_usage',
            'cpu_usage',
            'disk_usage',
            'error_rate',
            'queue_size',
        ];

        return in_array($this->metric, $highImpactMetrics);
    }

    /**
     * Get the percentage by which the threshold was exceeded
     *
     * @return float The percentage exceedance
     */
    public function getThresholdExceedancePercentage(): float
    {
        if ($this->thresholdValue == 0) {
            return 0;
        }

        return (($this->currentValue - $this->thresholdValue) / $this->thresholdValue) * 100;
    }

    /**
     * Determine if immediate intervention is required
     *
     * @return bool True if immediate intervention is required
     */
    public function requiresImmediateIntervention(): bool
    {
        return $this->isCriticalDegradation || 
               ($this->isAffectingUsers && $this->duration >= 120) ||
               $this->getThresholdExceedancePercentage() >= 200;
    }

    /**
     * Determine if automatic scaling should be triggered
     *
     * @return bool True if scaling should be triggered
     */
    public function shouldTriggerScaling(): bool
    {
        return ($this->metric === 'cpu_usage' && $this->currentValue > 80) ||
               ($this->metric === 'memory_usage' && $this->currentValue > 85) ||
               ($this->metric === 'active_connections' && $this->currentValue > 1000);
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
            'requires_immediate_action' => $this->requiresImmediateIntervention(),
            'recommended_actions' => $this->getRecommendedActions(),
            'impact_assessment' => $this->assessImpact(),
            'performance_category' => $this->categorizePerformanceIssue(),
            'mitigation_options' => $this->getMitigationOptions(),
        ];
    }

    /**
     * Calculate the urgency level based on various factors
     *
     * @return string The urgency level (low, medium, high, critical)
     */
    private function calculateUrgencyLevel(): string
    {
        if ($this->isCriticalDegradation) {
            return 'critical';
        } elseif ($this->duration >= 300 || $this->getThresholdExceedancePercentage() >= 200) {
            return 'critical';
        } elseif ($this->isAffectingUsers && $this->duration >= 60) {
            return 'high';
        } elseif ($this->isHighImpactMetric() || $this->duration >= 30) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the performance issue
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->metric === 'cpu_usage') {
            $actions[] = 'Check for CPU-intensive processes';
            $actions[] = 'Review application code for optimization';
            $actions[] = 'Consider horizontal scaling';
            
            if ($this->shouldTriggerScaling()) {
                $actions[] = 'Trigger auto-scaling';
            }
        }
        
        if ($this->metric === 'memory_usage') {
            $actions[] = 'Check for memory leaks';
            $actions[] = 'Review memory allocation patterns';
            $actions[] = 'Consider increasing memory allocation';
            
            if ($this->shouldTriggerScaling()) {
                $actions[] = 'Scale up memory resources';
            }
        }
        
        if ($this->metric === 'disk_usage') {
            $actions[] = 'Clean up temporary files';
            $actions[] = 'Archive old data';
            $actions[] = 'Consider disk space expansion';
        }
        
        if ($this->metric === 'response_time') {
            $actions[] = 'Analyze slow queries';
            $actions[] = 'Review application performance';
            $actions[] = 'Check network latency';
        }
        
        if ($this->metric === 'error_rate') {
            $actions[] = 'Review error logs';
            $actions[] = 'Check application stability';
            $actions[] = 'Investigate root causes';
        }
        
        if ($this->duration >= 60) {
            $actions[] = 'Monitor for sustained issues';
        }
        
        if ($this->isAffectingUsers) {
            $actions[] = 'Consider service degradation';
            $actions[] = 'Communicate with users about issues';
        }
        
        if (!$this->mitigationAttempted) {
            $actions[] = 'Attempt automatic mitigation';
        } elseif (!$this->mitigationSuccessful) {
            $actions[] = 'Manual intervention required';
            $actions[] = 'Escalate to system administrator';
        }
        
        return $actions;
    }

    /**
     * Assess the impact of this performance issue
     *
     * @return array Impact assessment details
     */
    private function assessImpact(): array
    {
        return [
            'user_impact' => $this->isAffectingUsers ? 'high' : 'medium',
            'system_impact' => $this->isCriticalDegradation ? 'high' : 'medium',
            'functionality_affected' => $this->getAffectedFunctionality(),
            'business_impact' => $this->assessBusinessImpact(),
        ];
    }

    /**
     * Get the functionality affected by this performance issue
     *
     * @return array List of affected functionality
     */
    private function getAffectedFunctionality(): array
    {
        $affected = ['system_performance'];
        
        if ($this->metric === 'response_time') {
            $affected[] = 'user_experience';
            $affected[] = 'api_response_times';
        }
        
        if ($this->metric === 'cpu_usage' || $this->metric === 'memory_usage') {
            $affected[] = 'application_processing';
            $affected[] = 'background_jobs';
        }
        
        if ($this->metric === 'disk_usage') {
            $affected[] = 'file_operations';
            $affected[] = 'data_storage';
        }
        
        if ($this->isAffectingUsers) {
            $affected[] = 'user_interactions';
            $affected[] = 'customer_experience';
        }
        
        return $affected;
    }

    /**
     * Assess the business impact
     *
     * @return string The business impact level
     */
    private function assessBusinessImpact(): string
    {
        if ($this->isCriticalDegradation || ($this->isAffectingUsers && $this->duration >= 120)) {
            return 'high';
        } elseif ($this->isAffectingUsers || $this->duration >= 60) {
            return 'medium';
        } elseif ($this->isHighImpactMetric()) {
            return 'low';
        }
        
        return 'minimal';
    }

    /**
     * Categorize the performance issue
     *
     * @return string The performance issue category
     */
    private function categorizePerformanceIssue(): string
    {
        if (str_contains($this->metric, 'cpu')) {
            return 'cpu_performance';
        } elseif (str_contains($this->metric, 'memory')) {
            return 'memory_performance';
        } elseif (str_contains($this->metric, 'disk')) {
            return 'disk_performance';
        } elseif (str_contains($this->metric, 'network') || str_contains($this->metric, 'response_time')) {
            return 'network_performance';
        } elseif (str_contains($this->metric, 'error')) {
            return 'error_performance';
        }
        
        return 'general_performance';
    }

    /**
     * Get mitigation options based on the performance issue
     *
     * @return array Available mitigation options
     */
    private function getMitigationOptions(): array
    {
        $options = [];
        
        if ($this->metric === 'cpu_usage') {
            $options = [
                'scale_horizontally',
                'optimize_code',
                'limit_cpu_intensive_tasks',
                'load_balance',
            ];
        } elseif ($this->metric === 'memory_usage') {
            $options = [
                'increase_memory',
                'optimize_memory_usage',
                'restart_services',
                'clear_cache',
            ];
        } elseif ($this->metric === 'disk_usage') {
            $options = [
                'cleanup_disk_space',
                'archive_data',
                'expand_storage',
                'compress_files',
            ];
        } elseif ($this->metric === 'response_time') {
            $options = [
                'optimize_database',
                'enable_caching',
                'cdn_acceleration',
                'load_balance',
            ];
        } else {
            $options = [
                'restart_service',
                'clear_cache',
                'scale_resources',
                'manual_intervention',
            ];
        }
        
        return $options;
    }
}