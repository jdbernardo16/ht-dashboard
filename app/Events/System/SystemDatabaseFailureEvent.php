<?php

namespace App\Events\System;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a database failure is detected
 * 
 * This event is fired when critical database operations fail,
 * helping to identify potential data integrity issues or system problems.
 */
class SystemDatabaseFailureEvent extends AdministrativeAlertEvent
{
    /**
     * The type of database failure that occurred
     */
    public string $failureType;

    /**
     * The database connection name
     */
    public string $connection;

    /**
     * The query or operation that failed
     */
    public string $operation;

    /**
     * The error message from the database
     */
    public string $errorMessage;

    /**
     * The error code from the database
     */
    public string|int $errorCode;

    /**
     * The table or collection involved in the failure
     */
    public string|null $table;

    /**
     * The number of affected records (if applicable)
     */
    public int|null $affectedRecords;

    /**
     * Whether this is a connection failure
     */
    public bool $isConnectionFailure;

    /**
     * Whether this is a data integrity issue
     */
    public bool $isDataIntegrityIssue;

    /**
     * Whether this failure is currently impacting users
     */
    public bool $isImpactingUsers;

    /**
     * The duration of the failed operation in milliseconds
     */
    public int|null $operationDuration;

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
     * @param string $connection The database connection name
     * @param string $operation The operation that failed
     * @param string $errorMessage The error message
     * @param string|int $errorCode The error code
     * @param string|null $table The table involved
     * @param int|null $affectedRecords Number of affected records
     * @param bool $isConnectionFailure Whether this is a connection failure
     * @param bool $isDataIntegrityIssue Whether this is a data integrity issue
     * @param bool $isImpactingUsers Whether users are impacted
     * @param int|null $operationDuration Operation duration in milliseconds
     * @param bool $recoveryAttempted Whether recovery was attempted
     * @param bool $recoverySuccessful Whether recovery was successful
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        string $failureType,
        string $connection,
        string $operation,
        string $errorMessage,
        string|int $errorCode,
        string|null $table = null,
        int|null $affectedRecords = null,
        bool $isConnectionFailure = false,
        bool $isDataIntegrityIssue = false,
        bool $isImpactingUsers = true,
        int|null $operationDuration = null,
        bool $recoveryAttempted = false,
        bool $recoverySuccessful = false,
        User|null $initiatedBy = null
    ) {
        $this->failureType = $failureType;
        $this->connection = $connection;
        $this->operation = $operation;
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
        $this->table = $table;
        $this->affectedRecords = $affectedRecords;
        $this->isConnectionFailure = $isConnectionFailure;
        $this->isDataIntegrityIssue = $isDataIntegrityIssue;
        $this->isImpactingUsers = $isImpactingUsers;
        $this->operationDuration = $operationDuration;
        $this->recoveryAttempted = $recoveryAttempted;
        $this->recoverySuccessful = $recoverySuccessful;

        $context = [
            'failure_type' => $failureType,
            'connection' => $connection,
            'operation' => $operation,
            'error_message' => $errorMessage,
            'error_code' => $errorCode,
            'table' => $table,
            'affected_records' => $affectedRecords,
            'is_connection_failure' => $isConnectionFailure,
            'is_data_integrity_issue' => $isDataIntegrityIssue,
            'is_impacting_users' => $isImpactingUsers,
            'operation_duration' => $operationDuration,
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
        // Critical for connection failures or data integrity issues
        if ($this->isConnectionFailure || $this->isDataIntegrityIssue) {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->isImpactingUsers && !$this->recoverySuccessful) {
            return self::SEVERITY_HIGH;
        } elseif ($this->isCriticalTable()) {
            return self::SEVERITY_HIGH;
        } elseif ($this->isImpactingUsers) {
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
            return "Database Connection Failure";
        } elseif ($this->isDataIntegrityIssue) {
            return "Database Data Integrity Issue";
        } elseif ($this->isImpactingUsers) {
            return "Database Failure Impacting Users";
        }
        
        return "Database Operation Failure";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "Database failure detected on connection '{$this->connection}'. ";
        
        $description .= "Operation: {$this->operation}. ";
        $description .= "Error: {$this->errorMessage} (Code: {$this->errorCode}). ";
        
        if ($this->table) {
            $description .= "Table: {$this->table}. ";
        }
        
        if ($this->affectedRecords) {
            $description .= " potentially affected records: {$this->affectedRecords}. ";
        }
        
        if ($this->isConnectionFailure) {
            $description .= "This is a connection failure affecting database connectivity. ";
        } elseif ($this->isDataIntegrityIssue) {
            $description .= "This may indicate data corruption or integrity problems. ";
        }
        
        if ($this->isImpactingUsers) {
            $description .= "Users are currently being impacted by this failure. ";
        }
        
        if ($this->operationDuration) {
            $description .= "Operation duration: {$this->operationDuration}ms. ";
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
        return route('admin.system.database') . '?connection=' . $this->connection;
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use critical queue for connection failures or data integrity issues
        if ($this->isConnectionFailure || $this->isDataIntegrityIssue) {
            return 'system-critical-alerts';
        } elseif ($this->isImpactingUsers) {
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
            return "[CRITICAL] Database Connection Failure - {$this->connection}";
        } elseif ($this->isDataIntegrityIssue) {
            return "[CRITICAL] Database Data Integrity Issue";
        } elseif ($this->isImpactingUsers) {
            return "[HIGH] Database Failure Impacting Users";
        }
        
        return "[SYSTEM] Database Operation Failure";
    }

    /**
     * Check if the affected table is critical
     *
     * @return bool True if table is critical
     */
    private function isCriticalTable(): bool
    {
        $criticalTables = [
            'users',
            'migrations',
            'password_resets',
            'sessions',
            'cache',
            'jobs',
            'failed_jobs',
        ];

        return $this->table && in_array($this->table, $criticalTables);
    }

    /**
     * Determine if immediate intervention is required
     *
     * @return bool True if immediate intervention is required
     */
    public function requiresImmediateIntervention(): bool
    {
        return $this->isConnectionFailure || 
               $this->isDataIntegrityIssue || 
               ($this->isImpactingUsers && !$this->recoverySuccessful);
    }

    /**
     * Determine if failover should be triggered
     *
     * @return bool True if failover should be triggered
     */
    public function shouldTriggerFailover(): bool
    {
        return $this->isConnectionFailure && 
               in_array($this->failureType, ['connection_timeout', 'deadlock', 'max_connections']);
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
        if ($this->isConnectionFailure || $this->isDataIntegrityIssue) {
            return 'critical';
        } elseif ($this->isImpactingUsers && !$this->recoverySuccessful) {
            return 'high';
        } elseif ($this->isCriticalTable()) {
            return 'high';
        } elseif ($this->isImpactingUsers) {
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
            $actions[] = 'Check database server status';
            $actions[] = 'Verify network connectivity';
            $actions[] = 'Review connection pool settings';
            
            if ($this->shouldTriggerFailover()) {
                $actions[] = 'Consider database failover';
            }
        }
        
        if ($this->isDataIntegrityIssue) {
            $actions[] = 'Run database integrity checks';
            $actions[] = 'Review recent transactions';
            $actions[] = 'Check for data corruption';
            $actions[] = 'Consider database restore from backup';
        }
        
        if ($this->isImpactingUsers) {
            $actions[] = 'Monitor user impact metrics';
            $actions[] = 'Consider service degradation';
        }
        
        if (!$this->recoveryAttempted) {
            $actions[] = 'Attempt automatic recovery';
        } elseif (!$this->recoverySuccessful) {
            $actions[] = 'Manual intervention required';
            $actions[] = 'Escalate to database administrator';
        }
        
        if ($this->operationDuration && $this->operationDuration > 30000) {
            $actions[] = 'Optimize slow queries';
            $actions[] = 'Review database performance';
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
            'user_impact' => $this->isImpactingUsers ? 'high' : 'low',
            'data_risk' => $this->isDataIntegrityIssue ? 'high' : 'medium',
            'service_availability' => $this->isConnectionFailure ? 'degraded' : 'operational',
            'critical_functions_affected' => $this->getCriticalFunctionsAffected(),
        ];
    }

    /**
     * Get the critical functions affected by this failure
     *
     * @return array List of affected functions
     */
    private function getCriticalFunctionsAffected(): array
    {
        $affected = [];
        
        if ($this->isConnectionFailure) {
            $affected = ['authentication', 'data_storage', 'user_sessions', 'background_jobs'];
        } elseif ($this->table) {
            if ($this->table === 'users') {
                $affected = ['authentication', 'user_management'];
            } elseif ($this->table === 'sessions') {
                $affected = ['user_sessions', 'authentication'];
            } elseif ($this->table === 'jobs') {
                $affected = ['background_jobs', 'notifications'];
            }
        }
        
        return $affected;
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
        } elseif ($this->isDataIntegrityIssue) {
            return 'integrity';
        } elseif (str_contains($this->failureType, 'timeout')) {
            return 'performance';
        } elseif (str_contains($this->failureType, 'constraint')) {
            return 'constraint';
        } elseif (str_contains($this->failureType, 'syntax')) {
            return 'syntax';
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
                'retry_connection',
                'switch_to_backup_connection',
                'restart_database_service',
                'manual_intervention',
            ];
        } elseif ($this->isDataIntegrityIssue) {
            $options = [
                'run_integrity_check',
                'restore_from_backup',
                'manual_data_repair',
            ];
        } else {
            $options = [
                'retry_operation',
                'rollback_transaction',
                'manual_intervention',
            ];
        }
        
        return $options;
    }
}