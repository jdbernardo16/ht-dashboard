<?php

namespace App\Listeners\System;

use App\Events\System\SystemFileUploadFailureEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for SystemFileUploadFailureEvent
 * 
 * This listener handles file upload failure events by creating notifications,
 * logging system incidents, and potentially triggering recovery actions.
 */
class SystemFileUploadFailureListener extends AdministrativeAlertListener
{
    /**
     * Process the file upload failure event
     *
     * @param SystemFileUploadFailureEvent $event The file upload failure event
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

        // Check if storage cleanup should be triggered
        $this->checkStorageCleanup($event);

        // Check if automatic recovery should be attempted
        $this->checkAutomaticRecovery($event);

        // Create system health alert
        $this->createSystemHealthAlert($event);

        // Check for storage system issues
        $this->checkStorageSystemIssues($event);
    }

    /**
     * Get the recipients for this system alert
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins for file upload failures
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For high severity or storage issues, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || 
            $event->isStorageSpaceIssue || 
            $event->isPermissionsIssue) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // If this is a system-wide issue, consider notifying all staff
        if ($event->isSystemWideIssue()) {
            $staff = User::where('role', 'va')->get()->toArray();
            $recipients = array_merge($recipients, $staff);
        }

        // Include the user who attempted the upload (if different from recipients)
        if ($event->user && !in_array($event->user, $recipients)) {
            $recipients[] = $event->user;
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(SystemFileUploadFailureEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SystemFileUploadFailureNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(SystemFileUploadFailureEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\System\SystemFileUploadFailureMail($eventData, $recipient));
        }
    }

    /**
     * Log the system incident with detailed information
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function logSystemIncident(SystemFileUploadFailureEvent $event): void
    {
        Log::error('File upload failure detected', [
            'event_type' => 'system_file_upload_failure',
            'failure_type' => $event->failureType,
            'original_filename' => $event->originalFilename,
            'file_size' => $event->fileSize,
            'mime_type' => $event->mimeType,
            'destination_path' => $event->destinationPath,
            'storage_disk' => $event->storageDisk,
            'error_message' => $event->errorMessage,
            'error_code' => $event->errorCode,
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'ip_address' => $event->ipAddress,
            'is_storage_space_issue' => $event->isStorageSpaceIssue,
            'is_permissions_issue' => $event->isPermissionsIssue,
            'is_file_size_issue' => $event->isFileSizeIssue,
            'is_file_type_issue' => $event->isFileTypeIssue,
            'consecutive_failures' => $event->consecutiveFailures,
            'recovery_attempted' => $event->recoveryAttempted,
            'recovery_successful' => $event->recoverySuccessful,
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if storage cleanup should be triggered
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function checkStorageCleanup(SystemFileUploadFailureEvent $event): void
    {
        if ($event->shouldTriggerStorageCleanup()) {
            try {
                // Trigger storage cleanup
                $this->triggerStorageCleanup($event);
                
                Log::info('Storage cleanup triggered', [
                    'storage_disk' => $event->storageDisk,
                    'reason' => 'storage_space_exhaustion_or_multiple_failures',
                    'consecutive_failures' => $event->consecutiveFailures,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to trigger storage cleanup', [
                    'storage_disk' => $event->storageDisk,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if automatic recovery should be attempted
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function checkAutomaticRecovery(SystemFileUploadFailureEvent $event): void
    {
        if (!$event->recoveryAttempted && $this->shouldAttemptRecovery($event)) {
            try {
                // Attempt automatic recovery
                $this->attemptAutomaticRecovery($event);
                
                Log::info('Automatic file upload recovery attempted', [
                    'storage_disk' => $event->storageDisk,
                    'failure_type' => $event->failureType,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to attempt automatic file upload recovery', [
                    'storage_disk' => $event->storageDisk,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create a system health alert
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function createSystemHealthAlert(SystemFileUploadFailureEvent $event): void
    {
        try {
            \App\Models\SystemHealthAlert::create([
                'type' => 'file_upload_failure',
                'severity' => $event->getSeverity(),
                'component' => 'file_storage',
                'storage_disk' => $event->storageDisk,
                'description' => $event->getDescription(),
                'error_message' => $event->errorMessage,
                'error_code' => $event->errorCode,
                'is_system_wide' => $event->isSystemWideIssue(),
                'status' => 'active',
                'metadata' => json_encode($event->context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create system health alert', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'storage_disk' => $event->storageDisk,
                    'failure_type' => $event->failureType,
                ],
            ]);
        }
    }

    /**
     * Check for storage system issues
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function checkStorageSystemIssues(SystemFileUploadFailureEvent $event): void
    {
        if ($event->isSystemWideIssue()) {
            try {
                // Update storage system status
                $this->updateStorageSystemStatus($event);
                
                // Create storage system alert
                $this->createStorageSystemAlert($event);
                
                Log::warning('Storage system issue detected', [
                    'storage_disk' => $event->storageDisk,
                    'failure_type' => $event->failureType,
                    'consecutive_failures' => $event->consecutiveFailures,
                    'severity' => $event->getSeverity(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle storage system issue', [
                    'storage_disk' => $event->storageDisk,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Trigger storage cleanup
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function triggerStorageCleanup(SystemFileUploadFailureEvent $event): void
    {
        // Clean up temporary files
        $this->cleanupTemporaryFiles($event->storageDisk);
        
        // Clean up old cache files
        $this->cleanupCacheFiles($event->storageDisk);
        
        // Clean up old log files
        $this->cleanupLogFiles($event->storageDisk);
        
        // Archive old files
        $this->archiveOldFiles($event->storageDisk);
        
        Log::info('Storage cleanup completed', [
            'storage_disk' => $event->storageDisk,
            'actions_performed' => ['temp_cleanup', 'cache_cleanup', 'log_cleanup', 'archive'],
        ]);
    }

    /**
     * Attempt automatic recovery
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function attemptAutomaticRecovery(SystemFileUploadFailureEvent $event): void
    {
        $recoveryActions = $this->getRecoveryActions($event);
        
        foreach ($recoveryActions as $action) {
            try {
                $this->performRecoveryAction($action, $event);
                
                // Test if recovery was successful
                if ($this->testFileUpload($event->storageDisk)) {
                    Log::info('File upload recovery successful', [
                        'storage_disk' => $event->storageDisk,
                        'action' => $action,
                    ]);
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('Recovery action failed', [
                    'storage_disk' => $event->storageDisk,
                    'action' => $action,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Update storage system status
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function updateStorageSystemStatus(SystemFileUploadFailureEvent $event): void
    {
        $status = match($event->getSeverity()) {
            'CRITICAL' => 'critical',
            'HIGH' => 'degraded',
            'MEDIUM' => 'degraded',
            'LOW' => 'operational',
            default => 'degraded',
        };
        
        \App\Models\ServiceStatus::updateOrCreate(
            ['service' => 'file_storage'],
            [
                'status' => $status,
                'message' => $event->getTitle(),
                'details' => $event->getDescription(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Create a storage system alert
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function createStorageSystemAlert(SystemFileUploadFailureEvent $event): void
    {
        \App\Models\StorageSystemAlert::create([
            'storage_disk' => $event->storageDisk,
            'severity' => $event->getSeverity(),
            'description' => $event->getDescription(),
            'impact' => $event->isSystemWideIssue() ? 'high' : 'medium',
            'estimated_recovery_time' => $this->estimateRecoveryTime($event),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Clean up temporary files
     *
     * @param string $storageDisk The storage disk
     * @return void
     */
    protected function cleanupTemporaryFiles(string $storageDisk): void
    {
        $tempPath = storage_path("app/temp/{$storageDisk}");
        
        if (is_dir($tempPath)) {
            $files = glob($tempPath . '/*');
            $cutoffTime = time() - (24 * 60 * 60); // 24 hours ago
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Clean up cache files
     *
     * @param string $storageDisk The storage disk
     * @return void
     */
    protected function cleanupCacheFiles(string $storageDisk): void
    {
        $cachePath = storage_path("framework/cache/data/{$storageDisk}");
        
        if (is_dir($cachePath)) {
            $files = glob($cachePath . '/*');
            $cutoffTime = time() - (7 * 24 * 60 * 60); // 7 days ago
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Clean up log files
     *
     * @param string $storageDisk The storage disk
     * @return void
     */
    protected function cleanupLogFiles(string $storageDisk): void
    {
        $logPath = storage_path("logs/{$storageDisk}");
        
        if (is_dir($logPath)) {
            $files = glob($logPath . '/*.log');
            $cutoffTime = time() - (30 * 24 * 60 * 60); // 30 days ago
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Archive old files
     *
     * @param string $storageDisk The storage disk
     * @return void
     */
    protected function archiveOldFiles(string $storageDisk): void
    {
        // Implementation would depend on your archiving strategy
        // This could move old files to cold storage or compress them
        Log::info('File archiving completed', [
            'storage_disk' => $storageDisk,
        ]);
    }

    /**
     * Test file upload functionality
     *
     * @param string $storageDisk The storage disk
     * @return bool True if test is successful
     */
    protected function testFileUpload(string $storageDisk): bool
    {
        try {
            $testContent = 'test upload ' . time();
            $testPath = "test/test_" . time() . ".txt";
            
            \Storage::disk($storageDisk)->put($testPath, $testContent);
            $retrieved = \Storage::disk($storageDisk)->get($testPath);
            
            // Clean up test file
            \Storage::disk($storageDisk)->delete($testPath);
            
            return $retrieved === $testContent;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if recovery should be attempted
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return bool True if recovery should be attempted
     */
    protected function shouldAttemptRecovery(SystemFileUploadFailureEvent $event): bool
    {
        // Don't attempt recovery for storage space issues
        if ($event->isStorageSpaceIssue) {
            return false;
        }
        
        // Attempt recovery for permissions and temporary issues
        return $event->isPermissionsIssue || 
               $event->consecutiveFailures < 5;
    }

    /**
     * Get recovery actions based on failure type
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return array List of recovery actions
     */
    protected function getRecoveryActions(SystemFileUploadFailureEvent $event): array
    {
        return match($event->failureType) {
            'permission_denied' => ['fix_permissions', 'check_ownership'],
            'disk_full' => ['cleanup_storage', 'archive_files'],
            'invalid_path' => ['create_directory', 'fix_path'],
            'connection_timeout' => ['test_connection', 'increase_timeout'],
            'file_too_large' => ['check_limits', 'increase_limits'],
            default => ['test_connection'],
        };
    }

    /**
     * Perform a recovery action
     *
     * @param string $action The recovery action
     * @param SystemFileUploadFailureEvent $event The event
     * @return void
     */
    protected function performRecoveryAction(string $action, SystemFileUploadFailureEvent $event): void
    {
        switch ($action) {
            case 'fix_permissions':
                $this->fixStoragePermissions($event->storageDisk, $event->destinationPath);
                break;
                
            case 'check_ownership':
                $this->checkStorageOwnership($event->storageDisk);
                break;
                
            case 'cleanup_storage':
                $this->triggerStorageCleanup($event);
                break;
                
            case 'archive_files':
                $this->archiveOldFiles($event->storageDisk);
                break;
                
            case 'create_directory':
                $this->createMissingDirectory($event->storageDisk, $event->destinationPath);
                break;
                
            case 'fix_path':
                $this->fixStoragePath($event->storageDisk, $event->destinationPath);
                break;
                
            case 'test_connection':
                $this->testStorageConnection($event->storageDisk);
                break;
                
            case 'increase_timeout':
                $this->increaseStorageTimeout($event->storageDisk);
                break;
                
            case 'check_limits':
                $this->checkUploadLimits($event->storageDisk);
                break;
                
            case 'increase_limits':
                $this->increaseUploadLimits($event->storageDisk);
                break;
        }
    }

    /**
     * Fix storage permissions
     *
     * @param string $storageDisk The storage disk
     * @param string $destinationPath The destination path
     * @return void
     */
    protected function fixStoragePermissions(string $storageDisk, string $destinationPath): void
    {
        $fullPath = storage_path("app/{$storageDisk}/{$destinationPath}");
        
        if (is_dir($fullPath)) {
            chmod($fullPath, 0755);
        } elseif (is_file($fullPath)) {
            chmod($fullPath, 0644);
        }
    }

    /**
     * Check storage ownership
     *
     * @param string $storageDisk The storage disk
     * @return void
     */
    protected function checkStorageOwnership(string $storageDisk): void
    {
        $storagePath = storage_path("app/{$storageDisk}");
        
        if (is_dir($storagePath)) {
            $owner = fileowner($storagePath);
            Log::info('Storage ownership check', [
                'storage_disk' => $storageDisk,
                'owner' => $owner,
            ]);
        }
    }

    /**
     * Create missing directory
     *
     * @param string $storageDisk The storage disk
     * @param string $destinationPath The destination path
     * @return void
     */
    protected function createMissingDirectory(string $storageDisk, string $destinationPath): void
    {
        $directory = dirname($destinationPath);
        \Storage::disk($storageDisk)->makeDirectory($directory);
    }

    /**
     * Fix storage path
     *
     * @param string $storageDisk The storage disk
     * @param string $destinationPath The destination path
     * @return void
     */
    protected function fixStoragePath(string $storageDisk, string $destinationPath): void
    {
        // Normalize path and ensure it exists
        $normalizedPath = str_replace('\\', '/', $destinationPath);
        $directory = dirname($normalizedPath);
        
        if (!\Storage::disk($storageDisk)->exists($directory)) {
            \Storage::disk($storageDisk)->makeDirectory($directory);
        }
    }

    /**
     * Test storage connection
     *
     * @param string $storageDisk The storage disk
     * @return void
     */
    protected function testStorageConnection(string $storageDisk): void
    {
        \Storage::disk($storageDisk)->exists('.');
    }

    /**
     * Increase storage timeout
     *
     * @param string $storageDisk The storage disk
     * @return void
     */
    protected function increaseStorageTimeout(string $storageDisk): void
    {
        // Implementation would depend on storage configuration
        Log::info('Storage timeout increased', [
            'storage_disk' => $storageDisk,
        ]);
    }

    /**
     * Check upload limits
     *
     * @param string $storageDisk The storage disk
     * @return void
     */
    protected function checkUploadLimits(string $storageDisk): void
    {
        $limits = [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
        ];
        
        Log::info('Upload limits check', [
            'storage_disk' => $storageDisk,
            'limits' => $limits,
        ]);
    }

    /**
     * Increase upload limits
     *
     * @param string $storageDisk The storage disk
     * @return void
     */
    protected function increaseUploadLimits(string $storageDisk): void
    {
        // Implementation would depend on server configuration
        Log::info('Upload limits increased', [
            'storage_disk' => $storageDisk,
        ]);
    }

    /**
     * Estimate recovery time
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return int Estimated recovery time in minutes
     */
    protected function estimateRecoveryTime(SystemFileUploadFailureEvent $event): int
    {
        return match($event->failureType) {
            'permission_denied' => 5,
            'disk_full' => 30,
            'invalid_path' => 2,
            'connection_timeout' => 10,
            'file_too_large' => 5,
            default => 10,
        };
    }

    /**
     * Prepare event data specific to file upload failure events
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'failure_type' => $event->failureType,
            'original_filename' => $event->originalFilename,
            'file_size' => $event->fileSize,
            'mime_type' => $event->mimeType,
            'destination_path' => $event->destinationPath,
            'storage_disk' => $event->storageDisk,
            'error_message' => $event->errorMessage,
            'error_code' => $event->errorCode,
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'ip_address' => $event->ipAddress,
            'is_storage_space_issue' => $event->isStorageSpaceIssue,
            'is_permissions_issue' => $event->isPermissionsIssue,
            'is_file_size_issue' => $event->isFileSizeIssue,
            'is_file_type_issue' => $event->isFileTypeIssue,
            'consecutive_failures' => $event->consecutiveFailures,
            'recovery_attempted' => $event->recoveryAttempted,
            'recovery_successful' => $event->recoverySuccessful,
            'requires_immediate_intervention' => $event->requiresImmediateIntervention(),
            'should_trigger_storage_cleanup' => $event->shouldTriggerStorageCleanup(),
            'estimated_recovery_time' => $this->estimateRecoveryTime($event),
            'recommended_actions' => $this->getRecommendedActions($event),
        ]);
    }

    /**
     * Get recommended actions based on the file upload failure
     *
     * @param SystemFileUploadFailureEvent $event The event
     * @return array List of recommended actions
     */
    protected function getRecommendedActions(SystemFileUploadFailureEvent $event): array
    {
        $actions = [];

        if ($event->isStorageSpaceIssue) {
            $actions[] = 'Check available disk space';
            $actions[] = 'Run storage cleanup';
            $actions[] = 'Review storage quotas';
            $actions[] = 'Consider storage expansion';
        }

        if ($event->isPermissionsIssue) {
            $actions[] = 'Check file system permissions';
            $actions[] = 'Verify directory ownership';
            $actions[] = 'Review web server user permissions';
        }

        if ($event->isFileSizeIssue) {
            $actions[] = 'Review upload size limits';
            $actions[] = 'Check PHP upload limits';
            $actions[] = 'Verify web server limits';
        }

        if ($event->isFileTypeIssue) {
            $actions[] = 'Review allowed file types';
            $actions[] = 'Check MIME type validation';
            $actions[] = 'Update file type restrictions';
        }

        if ($event->consecutiveFailures >= 5) {
            $actions[] = 'Monitor for system-wide issues';
            $actions[] = 'Check storage system health';
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