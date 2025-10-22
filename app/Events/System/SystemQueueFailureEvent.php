<?php

namespace App\Events\System;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a queue failure is detected
 * 
 * This event is fired when background job processing fails,
 * helping to identify queue system problems or job execution issues.
 */
class SystemQueueFailureEvent extends AdministrativeAlertEvent
{
    /**
     * The type of queue failure that occurred
     */
    public string $failureType;

    /**
     * The queue name where the failure occurred
     */
    public string $queueName;

    /**
     * The job class that failed
     */
    public string $jobClass;

    /**
     * The job ID (if available)
     */
    public string|null $jobId;

    /**
     * The error message from the failed job
     */
    public string $errorMessage;

    /**
     * The error code from the failed job
     */
    public string|int $errorCode;

    /**
     * The exception that was thrown (if available)
     */
    public string|null $exceptionClass;

    /**
     * The number of retry attempts
     */
    public int $retryAttempts;

    /**
     * The maximum allowed retry attempts
     */
    public int $maxRetries;

    /**
     * Whether this is a queue connection failure
     */
    public bool $isConnectionFailure;

    /**
     * Whether this is a worker failure
     */
    public bool $isWorkerFailure;

    /**
     * Whether this is a job execution failure
     */
    public bool $isJobExecutionFailure;

    /**
     * The number of consecutive failures
     */
    public int $consecutiveFailures;

    /**
     * The number of jobs currently in the queue
     */
    public int $queueSize;

    /**
     * Whether automatic recovery was attempted
     */
    public bool $recoveryAttempted;

    /**
     * Whether the recovery was successful
     */
    public bool $recoverySuccessful;

    /**
     * Create a new event instance
     *
     * @param string $failureType The type of failure
     * @param string $queueName The queue name
     * @param string $jobClass The job class that failed
     * @param string|null $jobId The job ID
     * @param string $errorMessage The error message
     * @param string|int $errorCode The error code
     * @param string|null $exceptionClass The exception class
     * @param int $retryAttempts Number of retry attempts
     * @param int $maxRetries Maximum allowed retries
     * @param bool $isConnectionFailure Whether this is connection failure
     * @param bool $isWorkerFailure Whether this is worker failure
     * @param bool $isJobExecutionFailure Whether this is job execution failure
     * @param int $consecutiveFailures Number of consecutive failures
     * @param int $queueSize Current queue size
     * @param bool $recoveryAttempted Whether recovery was attempted
     * @param bool $recoverySuccessful Whether recovery was successful
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        string $failureType,
        string $queueName,
        string $jobClass,
        string|null $jobId,
        string $errorMessage,
        string|int $errorCode,
        string|null $exceptionClass = null,
        int $retryAttempts = 0,
        int $maxRetries = 3,
        bool $isConnectionFailure = false,
        bool $isWorkerFailure = false,
        bool $isJobExecutionFailure = true,
        int $consecutiveFailures = 1,
        int $queueSize = 0,
        bool $recoveryAttempted = false,
        bool $recoverySuccessful = false,
        User|null $initiatedBy = null
    ) {
        $this->failureType = $failureType;
        $this->queueName = $queueName;
        $this->jobClass = $jobClass;
        $this->jobId = $jobId;
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
        $this->exceptionClass = $exceptionClass;
        $this->retryAttempts = $retryAttempts;
        $this->maxRetries = $maxRetries;
        $this->isConnectionFailure = $isConnectionFailure;
        $this->isWorkerFailure = $isWorkerFailure;
        $this->isJobExecutionFailure = $isJobExecutionFailure;
        $this->consecutiveFailures = $consecutiveFailures;
        $this->queueSize = $queueSize;
        $this->recoveryAttempted = $recoveryAttempted;
        $this->recoverySuccessful = $recoverySuccessful;

        $context = [
            'failure_type' => $failureType,
            'queue_name' => $queueName,
            'job_class' => $jobClass,
            'job_id' => $jobId,
            'error_message' => $errorMessage,
            'error_code' => $errorCode,
            'exception_class' => $exceptionClass,
            'retry_attempts' => $retryAttempts,
            'max_retries' => $maxRetries,
            'is_connection_failure' => $isConnectionFailure,
            'is_worker_failure' => $isWorkerFailure,
            'is_job_execution_failure' => $isJobExecutionFailure,
            'consecutive_failures' => $consecutiveFailures,
            'queue_size' => $queueSize,
            'recovery_attempted' => $recoveryAttempted,
            'recovery_successful' => $recoverySuccessful,
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
        // Critical for connection failures or worker failures
        if ($this->isConnectionFailure || $this->isWorkerFailure) {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->consecutiveFailures >= 10 || $this->isQueueBacklogged()) {
            return self::SEVERITY_HIGH;
        } elseif ($this->consecutiveFailures >= 5 || $this->isCriticalQueue()) {
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
        if ($this->isConnectionFailure) {
            return "Queue Connection Failure";
        } elseif ($this->isWorkerFailure) {
            return "Queue Worker Failure";
        } elseif ($this->consecutiveFailures > 1) {
            return "Multiple Queue Failures Detected";
        } elseif ($this->isQueueBacklogged()) {
            return "Queue Backlog Detected";
        }
        
        return "Queue Job Failure";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "Queue failure detected on queue '{$this->queueName}'. ";
        
        $description .= "Failed job: {$this->jobClass}";
        if ($this->jobId) {
            $description .= " (ID: {$this->jobId})";
        }
        $description .= ". ";
        
        $description .= "Error: {$this->errorMessage} (Code: {$this->errorCode}). ";
        
        if ($this->exceptionClass) {
            $description .= "Exception: {$this->exceptionClass}. ";
        }
        
        $description .= "Retry attempts: {$this->retryAttempts}/{$this->maxRetries}. ";
        
        if ($this->isConnectionFailure) {
            $description .= "This is a queue connection failure affecting job processing. ";
        } elseif ($this->isWorkerFailure) {
            $description .= "This is a queue worker failure. ";
        }
        
        if ($this->consecutiveFailures > 1) {
            $description .= "This is the {$this->consecutiveFailures}th consecutive failure. ";
        }
        
        if ($this->queueSize > 0) {
            $description .= "Current queue size: {$this->queueSize} jobs. ";
        }
        
        if ($this->isQueueBacklogged()) {
            $description .= "Queue appears to be backlogged. ";
        }
        
        if ($this->recoveryAttempted) {
            $recoveryStatus = $this->recoverySuccessful ? "successful" : "failed";
            $description .= "Recovery was attempted and {$recoveryStatus}. ";
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
        return route('admin.system.queues') . '?queue=' . $this->queueName;
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use critical queue for connection or worker failures
        if ($this->isConnectionFailure || $this->isWorkerFailure) {
            return 'system-critical-alerts';
        } elseif ($this->consecutiveFailures >= 5 || $this->isQueueBacklogged()) {
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
        if ($this->isConnectionFailure) {
            return "[CRITICAL] Queue Connection Failure - {$this->queueName}";
        } elseif ($this->isWorkerFailure) {
            return "[CRITICAL] Queue Worker Failure";
        } elseif ($this->consecutiveFailures > 1) {
            return "[HIGH] Multiple Queue Failures ({$this->consecutiveFailures} failures)";
        } elseif ($this->isQueueBacklogged()) {
            return "[MEDIUM] Queue Backlog Alert - {$this->queueName}";
        }
        
        return "[SYSTEM] Queue Job Failure";
    }

    /**
     * Check if the queue is backlogged
     *
     * @return bool True if queue is backlogged
     */
    private function isQueueBacklogged(): bool
    {
        return $this->queueSize > 100; // Consider more than 100 jobs as backlogged
    }

    /**
     * Check if this is a critical queue
     *
     * @return bool True if this is a critical queue
     */
    private function isCriticalQueue(): bool
    {
        $criticalQueues = [
            'critical',
            'high',
            'notifications',
            'emails',
            'security-alerts',
            'system-critical-alerts',
        ];

        return in_array($this->queueName, $criticalQueues);
    }

    /**
     * Determine if immediate intervention is required
     *
     * @return bool True if immediate intervention is required
     */
    public function requiresImmediateIntervention(): bool
    {
        return $this->isConnectionFailure || 
               $this->isWorkerFailure || 
               ($this->consecutiveFailures >= 10) ||
               ($this->isCriticalQueue() && $this->consecutiveFailures >= 5);
    }

    /**
     * Determine if queue workers should be restarted
     *
     * @return bool True if workers should be restarted
     */
    public function shouldRestartWorkers(): bool
    {
        return $this->isWorkerFailure || 
               ($this->consecutiveFailures >= 5) ||
               ($this->isJobExecutionFailure && $this->retryAttempts >= $this->maxRetries);
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
            'failure_category' => $this->categorizeFailure(),
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
        if ($this->isConnectionFailure || $this->isWorkerFailure) {
            return 'critical';
        } elseif ($this->consecutiveFailures >= 10) {
            return 'critical';
        } elseif ($this->isCriticalQueue() && $this->consecutiveFailures >= 5) {
            return 'high';
        } elseif ($this->consecutiveFailures >= 5 || $this->isQueueBacklogged()) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the failure type
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->isConnectionFailure) {
            $actions[] = 'Check queue connection status';
            $actions[] = 'Verify queue server availability';
            $actions[] = 'Review connection configuration';
            $actions[] = 'Test queue connectivity';
        }
        
        if ($this->isWorkerFailure) {
            $actions[] = 'Restart queue workers';
            $actions[] = 'Check worker process status';
            $actions[] = 'Review worker logs';
            $actions[] = 'Monitor worker memory usage';
        }
        
        if ($this->isJobExecutionFailure) {
            $actions[] = 'Review job implementation';
            $actions[] = 'Check job dependencies';
            $actions[] = 'Analyze job failure patterns';
            
            if ($this->retryAttempts >= $this->maxRetries) {
                $actions[] = 'Move job to failed queue';
                $actions[] = 'Manual job intervention required';
            }
        }
        
        if ($this->isQueueBacklogged()) {
            $actions[] = 'Scale queue workers';
            $actions[] = 'Process high-priority jobs first';
            $actions[] = 'Consider job prioritization';
        }
        
        if ($this->consecutiveFailures >= 5) {
            $actions[] = 'Monitor queue system health';
            $actions[] = 'Check for system-wide issues';
        }
        
        if (!$this->recoveryAttempted) {
            $actions[] = 'Attempt automatic recovery';
        } elseif (!$this->recoverySuccessful) {
            $actions[] = 'Manual intervention required';
            $actions[] = 'Escalate to system administrator';
        }
        
        return $actions;
    }

    /**
     * Assess the impact of this failure
     *
     * @return array Impact assessment details
     */
    private function assessImpact(): array
    {
        return [
            'system_impact' => $this->isConnectionFailure ? 'high' : 'medium',
            'user_impact' => $this->isCriticalQueue() ? 'high' : 'low',
            'functionality_affected' => $this->getAffectedFunctionality(),
            'business_impact' => $this->assessBusinessImpact(),
        ];
    }

    /**
     * Get the functionality affected by this failure
     *
     * @return array List of affected functionality
     */
    private function getAffectedFunctionality(): array
    {
        $affected = ['background_jobs'];
        
        if ($this->isCriticalQueue()) {
            $affected = array_merge($affected, [
                'notifications',
                'email_delivery',
                'security_alerts',
                'system_monitoring',
            ]);
        }
        
        if ($this->isConnectionFailure) {
            $affected[] = 'all_queue_processing';
        }
        
        if ($this->isQueueBacklogged()) {
            $affected[] = 'job_processing_performance';
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
        if ($this->isCriticalQueue() || $this->isConnectionFailure) {
            return 'high';
        } elseif ($this->isQueueBacklogged()) {
            return 'medium';
        } elseif ($this->consecutiveFailures >= 3) {
            return 'low';
        }
        
        return 'minimal';
    }

    /**
     * Categorize the failure type
     *
     * @return string The failure category
     */
    private function categorizeFailure(): string
    {
        if ($this->isConnectionFailure) {
            return 'connectivity';
        } elseif ($this->isWorkerFailure) {
            return 'worker_process';
        } elseif ($this->isJobExecutionFailure) {
            return 'job_execution';
        } elseif (str_contains($this->failureType, 'timeout')) {
            return 'timeout';
        } elseif (str_contains($this->failureType, 'memory')) {
            return 'memory';
        } elseif (str_contains($this->failureType, 'dependency')) {
            return 'dependency';
        }
        
        return 'general';
    }

    /**
     * Get recovery options based on the failure type
     *
     * @return array Available recovery options
     */
    private function getRecoveryOptions(): array
    {
        $options = [];
        
        if ($this->isConnectionFailure) {
            $options = [
                'reconnect_queue',
                'restart_queue_service',
                'switch_to_backup_queue',
                'manual_intervention',
            ];
        } elseif ($this->isWorkerFailure) {
            $options = [
                'restart_workers',
                'scale_workers',
                'reset_worker_state',
            ];
        } elseif ($this->isJobExecutionFailure) {
            $options = [
                'retry_job',
                'move_to_failed_queue',
                'modify_job_parameters',
                'manual_job_intervention',
            ];
        } else {
            $options = [
                'clear_queue_backlog',
                'prioritize_jobs',
                'scale_processing',
            ];
        }
        
        return $options;
    }
}