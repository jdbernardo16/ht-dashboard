<?php

namespace App\Listeners\System;

use App\Events\System\SystemDatabaseFailureEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for SystemDatabaseFailureEvent
 * 
 * This listener handles database failure events by creating notifications,
 * logging system incidents, and potentially triggering recovery actions.
 */
class SystemDatabaseFailureListener extends AdministrativeAlertListener
{
    /**
     * Process the database failure event
     *
     * @param SystemDatabaseFailureEvent $event The database failure event
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

        // Log the system incident
        $this->logSystemIncident($event);

        // Check if failover should be triggered
        $this->checkFailoverTrigger($event);

        // Check if automatic recovery should be attempted
        $this->checkAutomaticRecovery($event);

        // Create system health alert
        $this->createSystemHealthAlert($event);

        // Check for service degradation
        $this->checkServiceDegradation($event);
    }

    /**
     * Get the recipients for this system alert
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for database failures
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For high severity or connection failures, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || 
            $event->isConnectionFailure || 
            $event->isDataIntegrityIssue) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // If this is impacting users, consider notifying support staff
        if ($event->isImpactingUsers) {
            $support = User::where('role', 'va')->get()->toArray();
            $recipients = array_merge($recipients, $support);
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(SystemDatabaseFailureEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SystemDatabaseFailureNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(SystemDatabaseFailureEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\System\SystemDatabaseFailureMail($eventData, $recipient));
        }
    }

    /**
     * Log the system incident with detailed information
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function logSystemIncident(SystemDatabaseFailureEvent $event): void
    {
        Log::critical('Database failure detected', [
            'event_type' => 'system_database_failure',
            'failure_type' => $event->failureType,
            'connection' => $event->connection,
            'operation' => $event->operation,
            'error_message' => $event->errorMessage,
            'error_code' => $event->errorCode,
            'table' => $event->table,
            'affected_records' => $event->affectedRecords,
            'is_connection_failure' => $event->isConnectionFailure,
            'is_data_integrity_issue' => $event->isDataIntegrityIssue,
            'is_impacting_users' => $event->isImpactingUsers,
            'operation_duration' => $event->operationDuration,
            'recovery_attempted' => $event->recoveryAttempted,
            'recovery_successful' => $event->recoverySuccessful,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if failover should be triggered
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function checkFailoverTrigger(SystemDatabaseFailureEvent $event): void
    {
        if ($event->shouldTriggerFailover()) {
            try {
                // Trigger database failover
                $this->triggerDatabaseFailover($event);
                
                Log::warning('Database failover triggered', [
                    'connection' => $event->connection,
                    'failure_type' => $event->failureType,
                    'reason' => 'connection_failure_or_deadlock',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to trigger database failover', [
                    'connection' => $event->connection,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if automatic recovery should be attempted
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function checkAutomaticRecovery(SystemDatabaseFailureEvent $event): void
    {
        if (!$event->recoveryAttempted && $this->shouldAttemptRecovery($event)) {
            try {
                // Attempt automatic recovery
                $this->attemptAutomaticRecovery($event);
                
                Log::info('Automatic database recovery attempted', [
                    'connection' => $event->connection,
                    'failure_type' => $event->failureType,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to attempt automatic database recovery', [
                    'connection' => $event->connection,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create a system health alert
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function createSystemHealthAlert(SystemDatabaseFailureEvent $event): void
    {
        try {
            \App\Models\SystemHealthAlert::create([
                'type' => 'database_failure',
                'severity' => $event->getSeverity(),
                'component' => 'database',
                'connection' => $event->connection,
                'description' => $event->getDescription(),
                'error_message' => $event->errorMessage,
                'error_code' => $event->errorCode,
                'is_impacting_users' => $event->isImpactingUsers,
                'status' => 'active',
                'metadata' => json_encode($event->context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create system health alert', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'connection' => $event->connection,
                    'failure_type' => $event->failureType,
                ],
            ]);
        }
    }

    /**
     * Check for service degradation
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function checkServiceDegradation(SystemDatabaseFailureEvent $event): void
    {
        if ($event->isImpactingUsers || $event->isConnectionFailure) {
            try {
                // Update service status
                $this->updateServiceStatus($event);
                
                // Create service degradation alert
                $this->createServiceDegradationAlert($event);
                
                Log::warning('Service degradation due to database failure', [
                    'connection' => $event->connection,
                    'is_impacting_users' => $event->isImpactingUsers,
                    'severity' => $event->getSeverity(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle service degradation', [
                    'connection' => $event->connection,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Trigger database failover
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function triggerDatabaseFailover(SystemDatabaseFailureEvent $event): void
    {
        // Implementation would depend on your database setup
        // This could switch to a read replica, secondary database, or cloud failover
        
        // Update database configuration to use failover connection
        config(['database.default' => $this->getFailoverConnection($event->connection)]);
        
        // Test the failover connection
        $this->testFailoverConnection($event->connection);
        
        // Log the failover
        Log::critical('Database failover completed', [
            'primary_connection' => $event->connection,
            'failover_connection' => $this->getFailoverConnection($event->connection),
            'triggered_by' => $event->failureType,
        ]);
    }

    /**
     * Attempt automatic recovery
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function attemptAutomaticRecovery(SystemDatabaseFailureEvent $event): void
    {
        $recoveryActions = $this->getRecoveryActions($event);
        
        foreach ($recoveryActions as $action) {
            try {
                $this->performRecoveryAction($action, $event);
                
                // Test if recovery was successful
                if ($this->testDatabaseConnection($event->connection)) {
                    Log::info('Database recovery successful', [
                        'connection' => $event->connection,
                        'action' => $action,
                    ]);
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('Recovery action failed', [
                    'connection' => $event->connection,
                    'action' => $action,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Update service status
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function updateServiceStatus(SystemDatabaseFailureEvent $event): void
    {
        $status = match($event->getSeverity()) {
            'CRITICAL' => 'critical',
            'HIGH' => 'degraded',
            'MEDIUM' => 'degraded',
            'LOW' => 'operational',
            default => 'degraded',
        };
        
        \App\Models\ServiceStatus::updateOrCreate(
            ['service' => 'database'],
            [
                'status' => $status,
                'message' => $event->getTitle(),
                'details' => $event->getDescription(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Create a service degradation alert
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function createServiceDegradationAlert(SystemDatabaseFailureEvent $event): void
    {
        \App\Models\ServiceDegradationAlert::create([
            'service' => 'database',
            'severity' => $event->getSeverity(),
            'description' => $event->getDescription(),
            'impact' => $event->isImpactingUsers ? 'high' : 'medium',
            'estimated_recovery_time' => $this->estimateRecoveryTime($event),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Get the failover connection name
     *
     * @param string $primaryConnection The primary connection name
     * @return string The failover connection name
     */
    protected function getFailoverConnection(string $primaryConnection): string
    {
        $failoverMap = [
            'mysql' => 'mysql_failover',
            'pgsql' => 'pgsql_failover',
            'sqlite' => 'sqlite_failover',
        ];
        
        return $failoverMap[$primaryConnection] ?? 'mysql_failover';
    }

    /**
     * Test the failover connection
     *
     * @param string $primaryConnection The primary connection name
     * @return bool True if connection is successful
     */
    protected function testFailoverConnection(string $primaryConnection): bool
    {
        try {
            $failoverConnection = $this->getFailoverConnection($primaryConnection);
            \DB::connection($failoverConnection)->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('Failover connection test failed', [
                'connection' => $failoverConnection,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Test database connection
     *
     * @param string $connection The connection name
     * @return bool True if connection is successful
     */
    protected function testDatabaseConnection(string $connection): bool
    {
        try {
            \DB::connection($connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if recovery should be attempted
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return bool True if recovery should be attempted
     */
    protected function shouldAttemptRecovery(SystemDatabaseFailureEvent $event): bool
    {
        // Don't attempt recovery for data integrity issues
        if ($event->isDataIntegrityIssue) {
            return false;
        }
        
        // Attempt recovery for connection failures and timeout issues
        return in_array($event->failureType, [
            'connection_timeout',
            'deadlock',
            'connection_lost',
            'server_gone_away',
        ]);
    }

    /**
     * Get recovery actions based on failure type
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return array List of recovery actions
     */
    protected function getRecoveryActions(SystemDatabaseFailureEvent $event): array
    {
        return match($event->failureType) {
            'connection_timeout' => ['restart_connection_pool', 'increase_timeout'],
            'deadlock' => ['restart_transaction', 'optimize_query'],
            'connection_lost' => ['reconnect', 'restart_connection_pool'],
            'server_gone_away' => ['reconnect', 'ping_server'],
            default => ['reconnect'],
        };
    }

    /**
     * Perform a recovery action
     *
     * @param string $action The recovery action
     * @param SystemDatabaseFailureEvent $event The event
     * @return void
     */
    protected function performRecoveryAction(string $action, SystemDatabaseFailureEvent $event): void
    {
        switch ($action) {
            case 'reconnect':
                \DB::disconnect($event->connection);
                \DB::connection($event->connection)->reconnect();
                break;
                
            case 'restart_connection_pool':
                // Implementation depends on connection pool setup
                break;
                
            case 'increase_timeout':
                // Increase timeout settings
                config(["database.connections.{$event->connection}.options" => [
                    \PDO::ATTR_TIMEOUT => 30,
                ]]);
                break;
                
            case 'restart_transaction':
                // Rollback any pending transactions
                \DB::connection($event->connection)->rollBack();
                break;
                
            case 'optimize_query':
                // Log query for optimization
                Log::info('Query optimization needed', [
                    'query' => $event->operation,
                    'connection' => $event->connection,
                ]);
                break;
                
            case 'ping_server':
                // Check if database server is responding
                \DB::connection($event->connection)->select('SELECT 1');
                break;
        }
    }

    /**
     * Estimate recovery time
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return int Estimated recovery time in minutes
     */
    protected function estimateRecoveryTime(SystemDatabaseFailureEvent $event): int
    {
        return match($event->failureType) {
            'connection_timeout' => 5,
            'deadlock' => 2,
            'connection_lost' => 10,
            'server_gone_away' => 15,
            'data_corruption' => 60,
            'disk_full' => 30,
            default => 15,
        };
    }

    /**
     * Prepare event data specific to database failure events
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'failure_type' => $event->failureType,
            'connection' => $event->connection,
            'operation' => $event->operation,
            'error_message' => $event->errorMessage,
            'error_code' => $event->errorCode,
            'table' => $event->table,
            'affected_records' => $event->affectedRecords,
            'is_connection_failure' => $event->isConnectionFailure,
            'is_data_integrity_issue' => $event->isDataIntegrityIssue,
            'is_impacting_users' => $event->isImpactingUsers,
            'operation_duration' => $event->operationDuration,
            'recovery_attempted' => $event->recoveryAttempted,
            'recovery_successful' => $event->recoverySuccessful,
            'requires_immediate_intervention' => $event->requiresImmediateIntervention(),
            'should_trigger_failover' => $event->shouldTriggerFailover(),
            'estimated_recovery_time' => $this->estimateRecoveryTime($event),
            'recommended_actions' => $this->getRecommendedActions($event),
        ]);
    }

    /**
     * Get recommended actions based on the database failure
     *
     * @param SystemDatabaseFailureEvent $event The event
     * @return array List of recommended actions
     */
    protected function getRecommendedActions(SystemDatabaseFailureEvent $event): array
    {
        $actions = [];

        if ($event->isConnectionFailure) {
            $actions[] = 'Check database server status';
            $actions[] = 'Verify network connectivity';
            $actions[] = 'Review connection pool settings';
            
            if ($event->shouldTriggerFailover()) {
                $actions[] = 'Trigger database failover';
            }
        }

        if ($event->isDataIntegrityIssue) {
            $actions[] = 'Run database integrity checks';
            $actions[] = 'Review recent transactions';
            $actions[] = 'Check for data corruption';
            $actions[] = 'Consider database restore from backup';
        }

        if ($event->isImpactingUsers) {
            $actions[] = 'Monitor user impact metrics';
            $actions[] = 'Consider service degradation';
            $actions[] = 'Communicate with users about issues';
        }

        if (!$event->recoveryAttempted) {
            $actions[] = 'Attempt automatic recovery';
        } elseif (!$event->recoverySuccessful) {
            $actions[] = 'Manual intervention required';
            $actions[] = 'Escalate to database administrator';
        }

        if ($event->operationDuration && $event->operationDuration > 30000) {
            $actions[] = 'Optimize slow queries';
            $actions[] = 'Review database performance';
        }

        return $actions;
    }
}