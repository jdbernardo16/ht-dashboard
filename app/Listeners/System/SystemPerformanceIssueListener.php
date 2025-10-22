<?php

namespace App\Listeners\System;

use App\Events\System\SystemPerformanceIssueEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for SystemPerformanceIssueEvent
 * 
 * This listener handles performance issue events by creating notifications,
 * logging system incidents, and potentially triggering mitigation actions.
 */
class SystemPerformanceIssueListener extends AdministrativeAlertListener
{
    /**
     * Process the performance issue event
     *
     * @param SystemPerformanceIssueEvent $event The performance issue event
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

        // Check if automatic scaling should be triggered
        $this->checkAutoScaling($event);

        // Check if automatic mitigation should be attempted
        $this->checkAutomaticMitigation($event);

        // Create system health alert
        $this->createSystemHealthAlert($event);

        // Check for service degradation
        $this->checkServiceDegradation($event);
    }

    /**
     * Get the recipients for this system alert
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for performance issues
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For high severity or critical degradation, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || 
            $event->isCriticalDegradation || 
            $event->isAffectingUsers) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // If this is affecting users, consider notifying support staff
        if ($event->isAffectingUsers) {
            $support = User::where('role', 'va')->get()->toArray();
            $recipients = array_merge($recipients, $support);
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(SystemPerformanceIssueEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SystemPerformanceIssueNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(SystemPerformanceIssueEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\System\SystemPerformanceIssueMail($eventData, $recipient));
        }
    }

    /**
     * Log the system incident with detailed information
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function logSystemIncident(SystemPerformanceIssueEvent $event): void
    {
        Log::critical('Performance issue detected', [
            'event_type' => 'system_performance_issue',
            'issue_type' => $event->issueType,
            'metric' => $event->metric,
            'current_value' => $event->currentValue,
            'threshold_value' => $event->thresholdValue,
            'duration' => $event->duration,
            'component' => $event->component,
            'process' => $event->process,
            'memory_usage' => $event->memoryUsage,
            'cpu_usage' => $event->cpuUsage,
            'disk_usage' => $event->diskUsage,
            'active_connections' => $event->activeConnections,
            'is_critical_degradation' => $event->isCriticalDegradation,
            'is_affecting_users' => $event->isAffectingUsers,
            'mitigation_attempted' => $event->mitigationAttempted,
            'mitigation_successful' => $event->mitigationSuccessful,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if automatic scaling should be triggered
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function checkAutoScaling(SystemPerformanceIssueEvent $event): void
    {
        if ($event->shouldTriggerScaling()) {
            try {
                // Trigger automatic scaling
                $this->triggerAutoScaling($event);
                
                Log::info('Automatic scaling triggered', [
                    'metric' => $event->metric,
                    'current_value' => $event->currentValue,
                    'component' => $event->component,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to trigger automatic scaling', [
                    'metric' => $event->metric,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if automatic mitigation should be attempted
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function checkAutomaticMitigation(SystemPerformanceIssueEvent $event): void
    {
        if (!$event->mitigationAttempted && $this->shouldAttemptMitigation($event)) {
            try {
                // Attempt automatic mitigation
                $this->attemptAutomaticMitigation($event);
                
                Log::info('Automatic performance mitigation attempted', [
                    'metric' => $event->metric,
                    'component' => $event->component,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to attempt automatic performance mitigation', [
                    'metric' => $event->metric,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create a system health alert
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function createSystemHealthAlert(SystemPerformanceIssueEvent $event): void
    {
        try {
            \App\Models\SystemHealthAlert::create([
                'type' => 'performance_issue',
                'severity' => $event->getSeverity(),
                'component' => $event->component,
                'metric' => $event->metric,
                'description' => $event->getDescription(),
                'current_value' => $event->currentValue,
                'threshold_value' => $event->thresholdValue,
                'is_critical_degradation' => $event->isCriticalDegradation,
                'is_affecting_users' => $event->isAffectingUsers,
                'status' => 'active',
                'metadata' => json_encode($event->context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create system health alert', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'metric' => $event->metric,
                    'component' => $event->component,
                ],
            ]);
        }
    }

    /**
     * Check for service degradation
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function checkServiceDegradation(SystemPerformanceIssueEvent $event): void
    {
        if ($event->isCriticalDegradation || $event->isAffectingUsers) {
            try {
                // Update service status
                $this->updateServiceStatus($event);
                
                // Create service degradation alert
                $this->createServiceDegradationAlert($event);
                
                Log::warning('Service degradation due to performance issue', [
                    'metric' => $event->metric,
                    'component' => $event->component,
                    'is_affecting_users' => $event->isAffectingUsers,
                    'severity' => $event->getSeverity(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle service degradation', [
                    'metric' => $event->metric,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Trigger automatic scaling
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function triggerAutoScaling(SystemPerformanceIssueEvent $event): void
    {
        $scalingAction = $this->getScalingAction($event);
        
        match($scalingAction) {
            'scale_up_cpu' => $this->scaleUpCpu($event),
            'scale_up_memory' => $this->scaleUpMemory($event),
            'scale_up_connections' => $this->scaleUpConnections($event),
            'scale_horizontal' => $this->scaleHorizontal($event),
            default => null,
        };
        
        Log::info('Auto-scaling action completed', [
            'action' => $scalingAction,
            'metric' => $event->metric,
            'component' => $event->component,
        ]);
    }

    /**
     * Attempt automatic mitigation
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function attemptAutomaticMitigation(SystemPerformanceIssueEvent $event): void
    {
        $mitigationActions = $this->getMitigationActions($event);
        
        foreach ($mitigationActions as $action) {
            try {
                $this->performMitigationAction($action, $event);
                
                // Check if mitigation was successful
                if ($this->checkPerformanceImprovement($event)) {
                    Log::info('Performance mitigation successful', [
                        'metric' => $event->metric,
                        'action' => $action,
                    ]);
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('Mitigation action failed', [
                    'metric' => $event->metric,
                    'action' => $action,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Update service status
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function updateServiceStatus(SystemPerformanceIssueEvent $event): void
    {
        $status = match($event->getSeverity()) {
            'CRITICAL' => 'critical',
            'HIGH' => 'degraded',
            'MEDIUM' => 'degraded',
            'LOW' => 'operational',
            default => 'degraded',
        };
        
        \App\Models\ServiceStatus::updateOrCreate(
            ['service' => $event->component],
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
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function createServiceDegradationAlert(SystemPerformanceIssueEvent $event): void
    {
        \App\Models\ServiceDegradationAlert::create([
            'service' => $event->component,
            'severity' => $event->getSeverity(),
            'description' => $event->getDescription(),
            'metric' => $event->metric,
            'current_value' => $event->currentValue,
            'threshold_value' => $event->thresholdValue,
            'impact' => $event->isAffectingUsers ? 'high' : 'medium',
            'estimated_recovery_time' => $this->estimateRecoveryTime($event),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Get scaling action based on the performance issue
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return string The scaling action
     */
    protected function getScalingAction(SystemPerformanceIssueEvent $event): string
    {
        return match($event->metric) {
            'cpu_usage' => 'scale_up_cpu',
            'memory_usage' => 'scale_up_memory',
            'active_connections' => 'scale_up_connections',
            'response_time' => 'scale_horizontal',
            default => 'scale_horizontal',
        };
    }

    /**
     * Scale up CPU resources
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function scaleUpCpu(SystemPerformanceIssueEvent $event): void
    {
        // Implementation would depend on your infrastructure
        // This could use cloud provider APIs to increase CPU allocation
        
        Log::info('CPU scaling up initiated', [
            'component' => $event->component,
            'current_usage' => $event->currentValue,
        ]);
    }

    /**
     * Scale up memory resources
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function scaleUpMemory(SystemPerformanceIssueEvent $event): void
    {
        // Implementation would depend on your infrastructure
        // This could use cloud provider APIs to increase memory allocation
        
        Log::info('Memory scaling up initiated', [
            'component' => $event->component,
            'current_usage' => $event->currentValue,
        ]);
    }

    /**
     * Scale up connection resources
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function scaleUpConnections(SystemPerformanceIssueEvent $event): void
    {
        // Implementation would depend on your infrastructure
        // This could increase connection pool size or load balancer capacity
        
        Log::info('Connection scaling up initiated', [
            'component' => $event->component,
            'current_connections' => $event->currentValue,
        ]);
    }

    /**
     * Scale horizontally (add more instances)
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function scaleHorizontal(SystemPerformanceIssueEvent $event): void
    {
        // Implementation would depend on your infrastructure
        // This could use cloud provider APIs to add more instances
        
        Log::info('Horizontal scaling initiated', [
            'component' => $event->component,
            'metric' => $event->metric,
        ]);
    }

    /**
     * Get mitigation actions based on the performance issue
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return array List of mitigation actions
     */
    protected function getMitigationActions(SystemPerformanceIssueEvent $event): array
    {
        return match($event->metric) {
            'cpu_usage' => ['reduce_worker_processes', 'optimize_queries', 'enable_caching'],
            'memory_usage' => ['clear_cache', 'restart_memory_intensive_processes', 'optimize_memory_usage'],
            'disk_usage' => ['cleanup_temp_files', 'archive_old_data', 'optimize_disk_usage'],
            'response_time' => ['enable_caching', 'optimize_slow_queries', 'reduce_request_complexity'],
            'error_rate' => ['restart_failing_services', 'enable_circuit_breaker', 'reduce_load'],
            default => ['restart_services', 'clear_cache'],
        };
    }

    /**
     * Perform a mitigation action
     *
     * @param string $action The mitigation action
     * @param SystemPerformanceIssueEvent $event The event
     * @return void
     */
    protected function performMitigationAction(string $action, SystemPerformanceIssueEvent $event): void
    {
        switch ($action) {
            case 'reduce_worker_processes':
                $this->reduceWorkerProcesses($event->component);
                break;
                
            case 'optimize_queries':
                $this->optimizeDatabaseQueries($event->component);
                break;
                
            case 'enable_caching':
                $this->enableCaching($event->component);
                break;
                
            case 'clear_cache':
                $this->clearCache($event->component);
                break;
                
            case 'restart_memory_intensive_processes':
                $this->restartMemoryIntensiveProcesses($event->component);
                break;
                
            case 'optimize_memory_usage':
                $this->optimizeMemoryUsage($event->component);
                break;
                
            case 'cleanup_temp_files':
                $this->cleanupTempFiles($event->component);
                break;
                
            case 'archive_old_data':
                $this->archiveOldData($event->component);
                break;
                
            case 'optimize_disk_usage':
                $this->optimizeDiskUsage($event->component);
                break;
                
            case 'optimize_slow_queries':
                $this->optimizeSlowQueries($event->component);
                break;
                
            case 'reduce_request_complexity':
                $this->reduceRequestComplexity($event->component);
                break;
                
            case 'restart_failing_services':
                $this->restartFailingServices($event->component);
                break;
                
            case 'enable_circuit_breaker':
                $this->enableCircuitBreaker($event->component);
                break;
                
            case 'reduce_load':
                $this->reduceSystemLoad($event->component);
                break;
                
            case 'restart_services':
                $this->restartServices($event->component);
                break;
        }
    }

    /**
     * Check if performance has improved
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return bool True if performance improved
     */
    protected function checkPerformanceImprovement(SystemPerformanceIssueEvent $event): bool
    {
        try {
            // Wait a moment for changes to take effect
            sleep(5);
            
            // Get current metric value
            $currentValue = $this->getCurrentMetricValue($event->metric, $event->component);
            
            // Check if value has improved (decreased for most metrics)
            $improved = $currentValue < $event->currentValue;
            
            if ($improved) {
                Log::info('Performance improvement detected', [
                    'metric' => $event->metric,
                    'previous_value' => $event->currentValue,
                    'current_value' => $currentValue,
                    'threshold' => $event->thresholdValue,
                ]);
            }
            
            return $improved;
        } catch (\Exception $e) {
            Log::error('Failed to check performance improvement', [
                'metric' => $event->metric,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get current metric value
     *
     * @param string $metric The metric name
     * @param string $component The component name
     * @return float The current value
     */
    protected function getCurrentMetricValue(string $metric, string $component): float
    {
        // Implementation would depend on your monitoring system
        // This could query Prometheus, CloudWatch, or other monitoring systems
        
        return match($metric) {
            'cpu_usage' => $this->getCurrentCpuUsage($component),
            'memory_usage' => $this->getCurrentMemoryUsage($component),
            'disk_usage' => $this->getCurrentDiskUsage($component),
            'response_time' => $this->getCurrentResponseTime($component),
            'error_rate' => $this->getCurrentErrorRate($component),
            default => 0,
        };
    }

    /**
     * Get current CPU usage
     *
     * @param string $component The component name
     * @return float CPU usage percentage
     */
    protected function getCurrentCpuUsage(string $component): float
    {
        // Implementation would depend on your monitoring system
        return 0;
    }

    /**
     * Get current memory usage
     *
     * @param string $component The component name
     * @return float Memory usage percentage
     */
    protected function getCurrentMemoryUsage(string $component): float
    {
        // Implementation would depend on your monitoring system
        return 0;
    }

    /**
     * Get current disk usage
     *
     * @param string $component The component name
     * @return float Disk usage percentage
     */
    protected function getCurrentDiskUsage(string $component): float
    {
        // Implementation would depend on your monitoring system
        return 0;
    }

    /**
     * Get current response time
     *
     * @param string $component The component name
     * @return float Response time in milliseconds
     */
    protected function getCurrentResponseTime(string $component): float
    {
        // Implementation would depend on your monitoring system
        return 0;
    }

    /**
     * Get current error rate
     *
     * @param string $component The component name
     * @return float Error rate percentage
     */
    protected function getCurrentErrorRate(string $component): float
    {
        // Implementation would depend on your monitoring system
        return 0;
    }

    /**
     * Implementation of mitigation action methods
     */
    protected function reduceWorkerProcesses(string $component): void
    {
        Log::info('Reducing worker processes', ['component' => $component]);
    }

    protected function optimizeDatabaseQueries(string $component): void
    {
        Log::info('Optimizing database queries', ['component' => $component]);
    }

    protected function enableCaching(string $component): void
    {
        Log::info('Enabling caching', ['component' => $component]);
    }

    protected function clearCache(string $component): void
    {
        Log::info('Clearing cache', ['component' => $component]);
    }

    protected function restartMemoryIntensiveProcesses(string $component): void
    {
        Log::info('Restarting memory intensive processes', ['component' => $component]);
    }

    protected function optimizeMemoryUsage(string $component): void
    {
        Log::info('Optimizing memory usage', ['component' => $component]);
    }

    protected function cleanupTempFiles(string $component): void
    {
        Log::info('Cleaning up temporary files', ['component' => $component]);
    }

    protected function archiveOldData(string $component): void
    {
        Log::info('Archiving old data', ['component' => $component]);
    }

    protected function optimizeDiskUsage(string $component): void
    {
        Log::info('Optimizing disk usage', ['component' => $component]);
    }

    protected function optimizeSlowQueries(string $component): void
    {
        Log::info('Optimizing slow queries', ['component' => $component]);
    }

    protected function reduceRequestComplexity(string $component): void
    {
        Log::info('Reducing request complexity', ['component' => $component]);
    }

    protected function restartFailingServices(string $component): void
    {
        Log::info('Restarting failing services', ['component' => $component]);
    }

    protected function enableCircuitBreaker(string $component): void
    {
        Log::info('Enabling circuit breaker', ['component' => $component]);
    }

    protected function reduceSystemLoad(string $component): void
    {
        Log::info('Reducing system load', ['component' => $component]);
    }

    protected function restartServices(string $component): void
    {
        Log::info('Restarting services', ['component' => $component]);
    }

    /**
     * Check if mitigation should be attempted
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return bool True if mitigation should be attempted
     */
    protected function shouldAttemptMitigation(SystemPerformanceIssueEvent $event): bool
    {
        // Don't attempt mitigation for critical degradation
        if ($event->isCriticalDegradation) {
            return false;
        }
        
        // Attempt mitigation for moderate issues
        return in_array($event->getSeverity(), ['MEDIUM', 'LOW']);
    }

    /**
     * Estimate recovery time
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return int Estimated recovery time in minutes
     */
    protected function estimateRecoveryTime(SystemPerformanceIssueEvent $event): int
    {
        return match($event->metric) {
            'cpu_usage' => 10,
            'memory_usage' => 15,
            'disk_usage' => 30,
            'response_time' => 5,
            'error_rate' => 10,
            default => 15,
        };
    }

    /**
     * Prepare event data specific to performance issue events
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'issue_type' => $event->issueType,
            'metric' => $event->metric,
            'current_value' => $event->currentValue,
            'threshold_value' => $event->thresholdValue,
            'duration' => $event->duration,
            'component' => $event->component,
            'process' => $event->process,
            'memory_usage' => $event->memoryUsage,
            'cpu_usage' => $event->cpuUsage,
            'disk_usage' => $event->diskUsage,
            'active_connections' => $event->activeConnections,
            'is_critical_degradation' => $event->isCriticalDegradation,
            'is_affecting_users' => $event->isAffectingUsers,
            'mitigation_attempted' => $event->mitigationAttempted,
            'mitigation_successful' => $event->mitigationSuccessful,
            'requires_immediate_intervention' => $event->requiresImmediateIntervention(),
            'should_trigger_scaling' => $event->shouldTriggerScaling(),
            'threshold_exceedance_percentage' => $event->getThresholdExceedancePercentage(),
            'estimated_recovery_time' => $this->estimateRecoveryTime($event),
            'recommended_actions' => $this->getRecommendedActions($event),
        ]);
    }

    /**
     * Get recommended actions based on the performance issue
     *
     * @param SystemPerformanceIssueEvent $event The event
     * @return array List of recommended actions
     */
    protected function getRecommendedActions(SystemPerformanceIssueEvent $event): array
    {
        $actions = [];

        if ($event->metric === 'cpu_usage') {
            $actions[] = 'Check for CPU-intensive processes';
            $actions[] = 'Review application code for optimization';
            $actions[] = 'Consider horizontal scaling';
            
            if ($event->shouldTriggerScaling()) {
                $actions[] = 'Trigger auto-scaling';
            }
        }

        if ($event->metric === 'memory_usage') {
            $actions[] = 'Check for memory leaks';
            $actions[] = 'Review memory allocation patterns';
            $actions[] = 'Consider increasing memory allocation';
            
            if ($event->shouldTriggerScaling()) {
                $actions[] = 'Scale up memory resources';
            }
        }

        if ($event->metric === 'disk_usage') {
            $actions[] = 'Clean up temporary files';
            $actions[] = 'Archive old data';
            $actions[] = 'Consider disk space expansion';
        }

        if ($event->metric === 'response_time') {
            $actions[] = 'Analyze slow queries';
            $actions[] = 'Review application performance';
            $actions[] = 'Check network latency';
        }

        if ($event->metric === 'error_rate') {
            $actions[] = 'Review error logs';
            $actions[] = 'Check application stability';
            $actions[] = 'Investigate root causes';
        }

        if ($event->duration >= 60) {
            $actions[] = 'Monitor for sustained issues';
        }

        if ($event->isAffectingUsers) {
            $actions[] = 'Consider service degradation';
            $actions[] = 'Communicate with users about issues';
        }

        if (!$event->mitigationAttempted) {
            $actions[] = 'Attempt automatic mitigation';
        } elseif (!$event->mitigationSuccessful) {
            $actions[] = 'Manual intervention required';
            $actions[] = 'Escalate to system administrator';
        }

        return $actions;
    }
}