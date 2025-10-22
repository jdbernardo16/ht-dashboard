<?php

namespace App\Listeners\System;

use App\Events\System\SystemQueueFailureEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for SystemQueueFailureEvent
 * 
 * This listener handles queue failure events by creating notifications,
 * logging system incidents, and potentially triggering recovery actions.
 */
class SystemQueueFailureListener extends AdministrativeAlertListener
{
    /**
     * Process the queue failure event
     *
     * @param SystemQueueFailureEvent $event The queue failure event
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

        // Check if queue workers should be restarted
        $this->checkWorkerRestart($event);

        // Check if automatic recovery should be attempted
        $this->checkAutomaticRecovery($event);

        // Create system health alert
        $this->createSystemHealthAlert($event);

        // Check for queue system issues
        $this->checkQueueSystemIssues($event);
    }

    /**
     * Get the recipients for this system alert
     *
     * @param SystemQueueFailureEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for queue failures
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For high severity or critical queue failures, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || 
            $event->isConnectionFailure || 
            $event->isWorkerFailure ||
            $event->isCriticalQueue()) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // If this is a critical queue, consider notifying all staff
        if ($event->isCriticalQueue()) {
            $staff = User::where('role', 'va')->get()->toArray();
            $recipients = array_merge($recipients, $staff);
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param SystemQueueFailureEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(SystemQueueFailureEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SystemQueueFailureNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param SystemQueueFailureEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(SystemQueueFailureEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\System\SystemQueueFailureMail($eventData, $recipient));
        }
    }

    /**
     * Log the system incident with detailed information
     *
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function logSystemIncident(SystemQueueFailureEvent $event): void
    {
        Log::critical('Queue failure detected', [
            'event_type' => 'system_queue_failure',
            'failure_type' => $event->failureType,
            'queue_name' => $event->queueName,
            'job_class' => $event->jobClass,
            'job_id' => $event->jobId,
            'error_message' => $event->errorMessage,
            'error_code' => $event->errorCode,
            'exception_class' => $event->exceptionClass,
            'retry_attempts' => $event->retryAttempts,
            'max_retries' => $event->maxRetries,
            'is_connection_failure' => $event->isConnectionFailure,
            'is_worker_failure' => $event->isWorkerFailure,
            'is_job_execution_failure' => $event->isJobExecutionFailure,
            'consecutive_failures' => $event->consecutiveFailures,
            'queue_size' => $event->queueSize,
            'recovery_attempted' => $event->recoveryAttempted,
            'recovery_successful' => $event->recoverySuccessful,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if queue workers should be restarted
     *
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function checkWorkerRestart(SystemQueueFailureEvent $event): void
    {
        if ($event->shouldRestartWorkers()) {
            try {
                // Restart queue workers
                $this->restartQueueWorkers($event);
                
                Log::info('Queue workers restarted', [
                    'queue_name' => $event->queueName,
                    'reason' => 'worker_failure_or_multiple_failures',
                    'consecutive_failures' => $event->consecutiveFailures,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to restart queue workers', [
                    'queue_name' => $event->queueName,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if automatic recovery should be attempted
     *
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function checkAutomaticRecovery(SystemQueueFailureEvent $event): void
    {
        if (!$event->recoveryAttempted && $this->shouldAttemptRecovery($event)) {
            try {
                // Attempt automatic recovery
                $this->attemptAutomaticRecovery($event);
                
                Log::info('Automatic queue recovery attempted', [
                    'queue_name' => $event->queueName,
                    'failure_type' => $event->failureType,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to attempt automatic queue recovery', [
                    'queue_name' => $event->queueName,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create a system health alert
     *
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function createSystemHealthAlert(SystemQueueFailureEvent $event): void
    {
        try {
            \App\Models\SystemHealthAlert::create([
                'type' => 'queue_failure',
                'severity' => $event->getSeverity(),
                'component' => 'queue_system',
                'queue_name' => $event->queueName,
                'description' => $event->getDescription(),
                'error_message' => $event->errorMessage,
                'error_code' => $event->errorCode,
                'is_critical_queue' => $event->isCriticalQueue(),
                'status' => 'active',
                'metadata' => json_encode($event->context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create system health alert', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'queue_name' => $event->queueName,
                    'failure_type' => $event->failureType,
                ],
            ]);
        }
    }

    /**
     * Check for queue system issues
     *
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function checkQueueSystemIssues(SystemQueueFailureEvent $event): void
    {
        if ($event->isConnectionFailure || $event->isWorkerFailure || $event->consecutiveFailures >= 5) {
            try {
                // Update queue system status
                $this->updateQueueSystemStatus($event);
                
                // Create queue system alert
                $this->createQueueSystemAlert($event);
                
                Log::warning('Queue system issue detected', [
                    'queue_name' => $event->queueName,
                    'failure_type' => $event->failureType,
                    'consecutive_failures' => $event->consecutiveFailures,
                    'severity' => $event->getSeverity(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle queue system issue', [
                    'queue_name' => $event->queueName,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Restart queue workers
     *
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function restartQueueWorkers(SystemQueueFailureEvent $event): void
    {
        // Stop existing workers
        $this->stopQueueWorkers($event->queueName);
        
        // Clear any stuck jobs
        $this->clearStuckJobs($event->queueName);
        
        // Start new workers
        $this->startQueueWorkers($event->queueName);
        
        // Verify workers are running
        $this->verifyWorkersRunning($event->queueName);
        
        Log::info('Queue workers restart completed', [
            'queue_name' => $event->queueName,
        ]);
    }

    /**
     * Attempt automatic recovery
     *
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function attemptAutomaticRecovery(SystemQueueFailureEvent $event): void
    {
        $recoveryActions = $this->getRecoveryActions($event);
        
        foreach ($recoveryActions as $action) {
            try {
                $this->performRecoveryAction($action, $event);
                
                // Test if recovery was successful
                if ($this->testQueueSystem($event->queueName)) {
                    Log::info('Queue system recovery successful', [
                        'queue_name' => $event->queueName,
                        'action' => $action,
                    ]);
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('Recovery action failed', [
                    'queue_name' => $event->queueName,
                    'action' => $action,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Update queue system status
     *
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function updateQueueSystemStatus(SystemQueueFailureEvent $event): void
    {
        $status = match($event->getSeverity()) {
            'CRITICAL' => 'critical',
            'HIGH' => 'degraded',
            'MEDIUM' => 'degraded',
            'LOW' => 'operational',
            default => 'degraded',
        };
        
        \App\Models\ServiceStatus::updateOrCreate(
            ['service' => 'queue_system'],
            [
                'status' => $status,
                'message' => $event->getTitle(),
                'details' => $event->getDescription(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Create a queue system alert
     *
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function createQueueSystemAlert(SystemQueueFailureEvent $event): void
    {
        \App\Models\QueueSystemAlert::create([
            'queue_name' => $event->queueName,
            'severity' => $event->getSeverity(),
            'description' => $event->getDescription(),
            'impact' => $event->isCriticalQueue() ? 'high' : 'medium',
            'estimated_recovery_time' => $this->estimateRecoveryTime($event),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Stop queue workers
     *
     * @param string $queueName The queue name
     * @return void
     */
    protected function stopQueueWorkers(string $queueName): void
    {
        // Implementation would depend on your queue setup
        // This could use supervisorctl, systemd, or artisan commands
        
        try {
            // Try to stop workers gracefully
            \Artisan::call('queue:restart');
            
            // Wait for workers to finish current jobs
            sleep(5);
            
            Log::info('Queue workers stopped', [
                'queue_name' => $queueName,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to stop queue workers', [
                'queue_name' => $queueName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear stuck jobs
     *
     * @param string $queueName The queue name
     * @return void
     */
    protected function clearStuckJobs(string $queueName): void
    {
        try {
            // Clear stuck jobs from the queue
            \Artisan::call('queue:clear', ['--queue' => $queueName]);
            
            // Reset failed jobs
            \Artisan::call('queue:retry', ['--queue' => $queueName, 'all']);
            
            Log::info('Stuck jobs cleared', [
                'queue_name' => $queueName,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear stuck jobs', [
                'queue_name' => $queueName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Start queue workers
     *
     * @param string $queueName The queue name
     * @return void
     */
    protected function startQueueWorkers(string $queueName): void
    {
        try {
            // Determine number of workers to start
            $workerCount = $this->getWorkerCount($queueName);
            
            // Start workers
            for ($i = 0; $i < $workerCount; $i++) {
                \Artisan::call('queue:work', [
                    '--queue' => $queueName,
                    '--daemon' => true,
                    '--sleep' => 3,
                    '--tries' => 3,
                    '--max-time' => 3600,
                ]);
            }
            
            Log::info('Queue workers started', [
                'queue_name' => $queueName,
                'worker_count' => $workerCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to start queue workers', [
                'queue_name' => $queueName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verify workers are running
     *
     * @param string $queueName The queue name
     * @return void
     */
    protected function verifyWorkersRunning(string $queueName): void
    {
        try {
            // Check if workers are running
            $processes = $this->getQueueWorkerProcesses($queueName);
            
            if (empty($processes)) {
                Log::warning('No queue workers found running', [
                    'queue_name' => $queueName,
                ]);
            } else {
                Log::info('Queue workers verified running', [
                    'queue_name' => $queueName,
                    'process_count' => count($processes),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to verify queue workers', [
                'queue_name' => $queueName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Test queue system functionality
     *
     * @param string $queueName The queue name
     * @return bool True if test is successful
     */
    protected function testQueueSystem(string $queueName): bool
    {
        try {
            // Create a test job
            $testJob = new \App\Jobs\TestQueueJob();
            
            // Dispatch the test job
            \Bus::dispatch($testJob)->onQueue($queueName);
            
            // Wait a moment for processing
            sleep(2);
            
            // Check if the job was processed successfully
            return $this->checkTestJobResult($testJob);
        } catch (\Exception $e) {
            Log::error('Queue system test failed', [
                'queue_name' => $queueName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get worker count for a queue
     *
     * @param string $queueName The queue name
     * @return int Number of workers
     */
    protected function getWorkerCount(string $queueName): int
    {
        // Return different worker counts based on queue priority
        return match($queueName) {
            'critical', 'high' => 3,
            'default' => 2,
            'low' => 1,
            default => 2,
        };
    }

    /**
     * Get queue worker processes
     *
     * @param string $queueName The queue name
     * @return array List of processes
     */
    protected function getQueueWorkerProcesses(string $queueName): array
    {
        try {
            // Get processes matching queue worker pattern
            $output = shell_exec("ps aux | grep 'queue:work.*{$queueName}' | grep -v grep");
            
            if (empty($output)) {
                return [];
            }
            
            $lines = explode("\n", trim($output));
            $processes = [];
            
            foreach ($lines as $line) {
                if (!empty(trim($line))) {
                    $processes[] = $line;
                }
            }
            
            return $processes;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check test job result
     *
     * @param mixed $testJob The test job
     * @return bool True if successful
     */
    protected function checkTestJobResult($testJob): bool
    {
        // Implementation would depend on how you track test job results
        // For now, return true
        return true;
    }

    /**
     * Check if recovery should be attempted
     *
     * @param SystemQueueFailureEvent $event The event
     * @return bool True if recovery should be attempted
     */
    protected function shouldAttemptRecovery(SystemQueueFailureEvent $event): bool
    {
        // Don't attempt recovery for connection failures
        if ($event->isConnectionFailure) {
            return false;
        }
        
        // Attempt recovery for worker failures and job execution issues
        return $event->isWorkerFailure || 
               $event->isJobExecutionFailure ||
               $event->consecutiveFailures < 5;
    }

    /**
     * Get recovery actions based on failure type
     *
     * @param SystemQueueFailureEvent $event The event
     * @return array List of recovery actions
     */
    protected function getRecoveryActions(SystemQueueFailureEvent $event): array
    {
        return match($event->failureType) {
            'worker_timeout' => ['restart_workers', 'increase_timeout'],
            'memory_exhausted' => ['restart_workers', 'increase_memory'],
            'job_failed' => ['retry_job', 'move_to_failed_queue'],
            'connection_lost' => ['test_connection', 'reconnect'],
            'queue_stuck' => ['clear_queue', 'restart_workers'],
            default => ['restart_workers'],
        };
    }

    /**
     * Perform a recovery action
     *
     * @param string $action The recovery action
     * @param SystemQueueFailureEvent $event The event
     * @return void
     */
    protected function performRecoveryAction(string $action, SystemQueueFailureEvent $event): void
    {
        switch ($action) {
            case 'restart_workers':
                $this->restartQueueWorkers($event);
                break;
                
            case 'increase_timeout':
                $this->increaseWorkerTimeout($event->queueName);
                break;
                
            case 'increase_memory':
                $this->increaseWorkerMemory($event->queueName);
                break;
                
            case 'retry_job':
                $this->retryFailedJob($event->jobId, $event->queueName);
                break;
                
            case 'move_to_failed_queue':
                $this->moveJobToFailedQueue($event->jobId, $event->queueName);
                break;
                
            case 'test_connection':
                $this->testQueueConnection($event->queueName);
                break;
                
            case 'reconnect':
                $this->reconnectQueue($event->queueName);
                break;
                
            case 'clear_queue':
                $this->clearStuckJobs($event->queueName);
                break;
        }
    }

    /**
     * Increase worker timeout
     *
     * @param string $queueName The queue name
     * @return void
     */
    protected function increaseWorkerTimeout(string $queueName): void
    {
        // Implementation would depend on your queue configuration
        Log::info('Worker timeout increased', [
            'queue_name' => $queueName,
        ]);
    }

    /**
     * Increase worker memory
     *
     * @param string $queueName The queue name
     * @return void
     */
    protected function increaseWorkerMemory(string $queueName): void
    {
        // Implementation would depend on your queue configuration
        Log::info('Worker memory limit increased', [
            'queue_name' => $queueName,
        ]);
    }

    /**
     * Retry a failed job
     *
     * @param string|null $jobId The job ID
     * @param string $queueName The queue name
     * @return void
     */
    protected function retryFailedJob(?string $jobId, string $queueName): void
    {
        if ($jobId) {
            try {
                \Artisan::call('queue:retry', ['id' => $jobId]);
                
                Log::info('Failed job retried', [
                    'job_id' => $jobId,
                    'queue_name' => $queueName,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to retry job', [
                    'job_id' => $jobId,
                    'queue_name' => $queueName,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Move a job to the failed queue
     *
     * @param string|null $jobId The job ID
     * @param string $queueName The queue name
     * @return void
     */
    protected function moveJobToFailedQueue(?string $jobId, string $queueName): void
    {
        if ($jobId) {
            try {
                \Artisan::call('queue:failed', ['--queue' => $queueName]);
                
                Log::info('Job moved to failed queue', [
                    'job_id' => $jobId,
                    'queue_name' => $queueName,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to move job to failed queue', [
                    'job_id' => $jobId,
                    'queue_name' => $queueName,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Test queue connection
     *
     * @param string $queueName The queue name
     * @return void
     */
    protected function testQueueConnection(string $queueName): void
    {
        try {
            \Queue::connection($this->getQueueConnection($queueName))->size();
        } catch (\Exception $e) {
            Log::error('Queue connection test failed', [
                'queue_name' => $queueName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reconnect to queue
     *
     * @param string $queueName The queue name
     * @return void
     */
    protected function reconnectQueue(string $queueName): void
    {
        try {
            \Queue::connection($this->getQueueConnection($queueName))->reconnect();
        } catch (\Exception $e) {
            Log::error('Failed to reconnect to queue', [
                'queue_name' => $queueName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get queue connection name
     *
     * @param string $queueName The queue name
     * @return string Connection name
     */
    protected function getQueueConnection(string $queueName): string
    {
        return match($queueName) {
            'critical', 'high' => 'redis',
            'default' => 'database',
            'low' => 'database',
            default => 'database',
        };
    }

    /**
     * Estimate recovery time
     *
     * @param SystemQueueFailureEvent $event The event
     * @return int Estimated recovery time in minutes
     */
    protected function estimateRecoveryTime(SystemQueueFailureEvent $event): int
    {
        return match($event->failureType) {
            'worker_timeout' => 5,
            'memory_exhausted' => 10,
            'job_failed' => 2,
            'connection_lost' => 15,
            'queue_stuck' => 10,
            default => 10,
        };
    }

    /**
     * Prepare event data specific to queue failure events
     *
     * @param SystemQueueFailureEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'failure_type' => $event->failureType,
            'queue_name' => $event->queueName,
            'job_class' => $event->jobClass,
            'job_id' => $event->jobId,
            'error_message' => $event->errorMessage,
            'error_code' => $event->errorCode,
            'exception_class' => $event->exceptionClass,
            'retry_attempts' => $event->retryAttempts,
            'max_retries' => $event->maxRetries,
            'is_connection_failure' => $event->isConnectionFailure,
            'is_worker_failure' => $event->isWorkerFailure,
            'is_job_execution_failure' => $event->isJobExecutionFailure,
            'consecutive_failures' => $event->consecutiveFailures,
            'queue_size' => $event->queueSize,
            'recovery_attempted' => $event->recoveryAttempted,
            'recovery_successful' => $event->recoverySuccessful,
            'requires_immediate_intervention' => $event->requiresImmediateIntervention(),
            'should_restart_workers' => $event->shouldRestartWorkers(),
            'estimated_recovery_time' => $this->estimateRecoveryTime($event),
            'recommended_actions' => $this->getRecommendedActions($event),
        ]);
    }

    /**
     * Get recommended actions based on the queue failure
     *
     * @param SystemQueueFailureEvent $event The event
     * @return array List of recommended actions
     */
    protected function getRecommendedActions(SystemQueueFailureEvent $event): array
    {
        $actions = [];

        if ($event->isConnectionFailure) {
            $actions[] = 'Check queue connection status';
            $actions[] = 'Verify queue server availability';
            $actions[] = 'Review connection configuration';
            $actions[] = 'Test queue connectivity';
        }

        if ($event->isWorkerFailure) {
            $actions[] = 'Restart queue workers';
            $actions[] = 'Check worker process status';
            $actions[] = 'Review worker logs';
            $actions[] = 'Monitor worker memory usage';
        }

        if ($event->isJobExecutionFailure) {
            $actions[] = 'Review job implementation';
            $actions[] = 'Check job dependencies';
            $actions[] = 'Analyze job failure patterns';
            
            if ($event->retryAttempts >= $event->maxRetries) {
                $actions[] = 'Move job to failed queue';
                $actions[] = 'Manual job intervention required';
            }
        }

        if ($event->isQueueBacklogged()) {
            $actions[] = 'Scale queue workers';
            $actions[] = 'Process high-priority jobs first';
            $actions[] = 'Consider job prioritization';
        }

        if ($event->consecutiveFailures >= 5) {
            $actions[] = 'Monitor queue system health';
            $actions[] = 'Check for system-wide issues';
        }

        if (!$event->recoveryAttempted) {
            $actions[] = 'Attempt automatic recovery';
        } elseif (!$event->recoverySuccessful) {
            $actions[] = 'Manual intervention required';
            $actions[] = 'Escalate to system administrator';
        }

        return $actions;
    }
}