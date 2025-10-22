<?php

namespace App\Events\System;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a file upload failure is detected
 * 
 * This event is fired when file uploads fail due to system issues,
 * helping to identify storage problems or configuration issues.
 */
class SystemFileUploadFailureEvent extends AdministrativeAlertEvent
{
    /**
     * The type of upload failure that occurred
     */
    public string $failureType;

    /**
     * The original filename that was being uploaded
     */
    public string $originalFilename;

    /**
     * The file size in bytes
     */
    public int $fileSize;

    /**
     * The file MIME type
     */
    public string $mimeType;

    /**
     * The destination path where the file was being uploaded
     */
    public string $destinationPath;

    /**
     * The storage disk being used
     */
    public string $storageDisk;

    /**
     * The error message from the upload attempt
     */
    public string $errorMessage;

    /**
     * The error code from the upload attempt
     */
    public string|int $errorCode;

    /**
     * The user who attempted the upload
     */
    public User|null $user;

    /**
     * The IP address from which the upload was attempted
     */
    public string $ipAddress;

    /**
     * Whether this is a storage space issue
     */
    public bool $isStorageSpaceIssue;

    /**
     * Whether this is a permissions issue
     */
    public bool $isPermissionsIssue;

    /**
     * Whether this is a file size limit issue
     */
    public bool $isFileSizeIssue;

    /**
     * Whether this is a file type restriction issue
     */
    public bool $isFileTypeIssue;

    /**
     * The number of consecutive upload failures
     */
    public int $consecutiveFailures;

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
     * @param string $originalFilename The original filename
     * @param int $fileSize The file size in bytes
     * @param string $mimeType The file MIME type
     * @param string $destinationPath The destination path
     * @param string $storageDisk The storage disk
     * @param string $errorMessage The error message
     * @param string|int $errorCode The error code
     * @param User|null $user The user who attempted upload
     * @param string $ipAddress The source IP address
     * @param bool $isStorageSpaceIssue Whether this is storage space issue
     * @param bool $isPermissionsIssue Whether this is permissions issue
     * @param bool $isFileSizeIssue Whether this is file size issue
     * @param bool $isFileTypeIssue Whether this is file type issue
     * @param int $consecutiveFailures Number of consecutive failures
     * @param bool $recoveryAttempted Whether recovery was attempted
     * @param bool $recoverySuccessful Whether recovery was successful
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        string $failureType,
        string $originalFilename,
        int $fileSize,
        string $mimeType,
        string $destinationPath,
        string $storageDisk,
        string $errorMessage,
        string|int $errorCode,
        User|null $user,
        string $ipAddress,
        bool $isStorageSpaceIssue = false,
        bool $isPermissionsIssue = false,
        bool $isFileSizeIssue = false,
        bool $isFileTypeIssue = false,
        int $consecutiveFailures = 1,
        bool $recoveryAttempted = false,
        bool $recoverySuccessful = false,
        User|null $initiatedBy = null
    ) {
        $this->failureType = $failureType;
        $this->originalFilename = $originalFilename;
        $this->fileSize = $fileSize;
        $this->mimeType = $mimeType;
        $this->destinationPath = $destinationPath;
        $this->storageDisk = $storageDisk;
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
        $this->user = $user;
        $this->ipAddress = $ipAddress;
        $this->isStorageSpaceIssue = $isStorageSpaceIssue;
        $this->isPermissionsIssue = $isPermissionsIssue;
        $this->isFileSizeIssue = $isFileSizeIssue;
        $this->isFileTypeIssue = $isFileTypeIssue;
        $this->consecutiveFailures = $consecutiveFailures;
        $this->recoveryAttempted = $recoveryAttempted;
        $this->recoverySuccessful = $recoverySuccessful;

        $context = [
            'failure_type' => $failureType,
            'original_filename' => $originalFilename,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'destination_path' => $destinationPath,
            'storage_disk' => $storageDisk,
            'error_message' => $errorMessage,
            'error_code' => $errorCode,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'ip_address' => $ipAddress,
            'is_storage_space_issue' => $isStorageSpaceIssue,
            'is_permissions_issue' => $isPermissionsIssue,
            'is_file_size_issue' => $isFileSizeIssue,
            'is_file_type_issue' => $isFileTypeIssue,
            'consecutive_failures' => $consecutiveFailures,
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
        // Critical for storage space issues or multiple consecutive failures
        if ($this->isStorageSpaceIssue || $this->consecutiveFailures >= 10) {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->isPermissionsIssue || $this->consecutiveFailures >= 5) {
            return self::SEVERITY_HIGH;
        } elseif ($this->consecutiveFailures >= 3 || $this->isSystemWideIssue()) {
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
        if ($this->isStorageSpaceIssue) {
            return "Storage Space Exhaustion - File Upload Failed";
        } elseif ($this->isPermissionsIssue) {
            return "File Permissions Error - Upload Failed";
        } elseif ($this->consecutiveFailures > 1) {
            return "Multiple File Upload Failures Detected";
        }
        
        return "File Upload Failure";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "File upload failed for '{$this->originalFilename}' ({$this->formatFileSize($this->fileSize)}). ";
        
        $description .= "Error: {$this->errorMessage} (Code: {$this->errorCode}). ";
        
        $userInfo = $this->user ? "User '{$this->user->email}'" : "Unauthenticated user";
        $description .= "Upload attempted by {$userInfo} from {$this->ipAddress}. ";
        
        $description .= "Destination: {$this->storageDisk}://{$this->destinationPath}. ";
        
        if ($this->isStorageSpaceIssue) {
            $description .= "This failure is due to insufficient storage space. ";
        } elseif ($this->isPermissionsIssue) {
            $description .= "This failure is due to file system permissions. ";
        } elseif ($this->isFileSizeIssue) {
            $description .= "This failure is due to file size limitations. ";
        } elseif ($this->isFileTypeIssue) {
            $description .= "This failure is due to file type restrictions. ";
        }
        
        if ($this->consecutiveFailures > 1) {
            $description .= "This is the {$this->consecutiveFailures}th consecutive failure. ";
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
        if ($this->isStorageSpaceIssue) {
            return route('admin.system.storage');
        } elseif ($this->user) {
            return route('admin.users.show', $this->user->id);
        }
        
        return route('admin.system.uploads');
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use critical queue for storage space issues
        if ($this->isStorageSpaceIssue || $this->consecutiveFailures >= 10) {
            return 'system-critical-alerts';
        } elseif ($this->isPermissionsIssue || $this->consecutiveFailures >= 5) {
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
        if ($this->isStorageSpaceIssue) {
            return "[CRITICAL] Storage Space Exhaustion - Upload Failures";
        } elseif ($this->isPermissionsIssue) {
            return "[HIGH] File Permissions Error - Uploads Failing";
        } elseif ($this->consecutiveFailures > 1) {
            return "[MEDIUM] Multiple Upload Failures ({$this->consecutiveFailures} failures)";
        }
        
        return "[SYSTEM] File Upload Failure";
    }

    /**
     * Format file size for human readable display
     *
     * @param int $bytes Size in bytes
     * @return string Formatted size
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Check if this appears to be a system-wide issue
     *
     * @return bool True if this appears system-wide
     */
    private function isSystemWideIssue(): bool
    {
        return $this->consecutiveFailures >= 5 || 
               $this->isStorageSpaceIssue || 
               $this->isPermissionsIssue;
    }

    /**
     * Determine if immediate intervention is required
     *
     * @return bool True if immediate intervention is required
     */
    public function requiresImmediateIntervention(): bool
    {
        return $this->isStorageSpaceIssue || 
               $this->consecutiveFailures >= 10 ||
               ($this->isPermissionsIssue && $this->consecutiveFailures >= 3);
    }

    /**
     * Determine if storage cleanup should be triggered
     *
     * @return bool True if storage cleanup should be triggered
     */
    public function shouldTriggerStorageCleanup(): bool
    {
        return $this->isStorageSpaceIssue || $this->consecutiveFailures >= 5;
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
        if ($this->isStorageSpaceIssue) {
            return 'critical';
        } elseif ($this->consecutiveFailures >= 10) {
            return 'critical';
        } elseif ($this->isPermissionsIssue || $this->consecutiveFailures >= 5) {
            return 'high';
        } elseif ($this->consecutiveFailures >= 3) {
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
        
        if ($this->isStorageSpaceIssue) {
            $actions[] = 'Check available disk space';
            $actions[] = 'Run storage cleanup';
            $actions[] = 'Review storage quotas';
            $actions[] = 'Consider storage expansion';
        }
        
        if ($this->isPermissionsIssue) {
            $actions[] = 'Check file system permissions';
            $actions[] = 'Verify directory ownership';
            $actions[] = 'Review web server user permissions';
        }
        
        if ($this->isFileSizeIssue) {
            $actions[] = 'Review upload size limits';
            $actions[] = 'Check PHP upload limits';
            $actions[] = 'Verify web server limits';
        }
        
        if ($this->isFileTypeIssue) {
            $actions[] = 'Review allowed file types';
            $actions[] = 'Check MIME type validation';
            $actions[] = 'Update file type restrictions';
        }
        
        if ($this->consecutiveFailures >= 5) {
            $actions[] = 'Monitor for system-wide issues';
            $actions[] = 'Check storage system health';
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
            'user_impact' => $this->consecutiveFailures >= 5 ? 'high' : 'medium',
            'system_impact' => $this->isSystemWideIssue() ? 'high' : 'low',
            'functionality_affected' => $this->getAffectedFunctionality(),
            'user_experience_impact' => $this->isStorageSpaceIssue ? 'severe' : 'moderate',
        ];
    }

    /**
     * Get the functionality affected by this failure
     *
     * @return array List of affected functionality
     */
    private function getAffectedFunctionality(): array
    {
        $affected = ['file_uploads'];
        
        if ($this->isStorageSpaceIssue) {
            $affected = array_merge($affected, ['file_storage', 'backups', 'logging']);
        }
        
        if ($this->consecutiveFailures >= 5) {
            $affected[] = 'user_content_creation';
            $affected[] = 'media_management';
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
        if ($this->isStorageSpaceIssue) {
            return 'storage_capacity';
        } elseif ($this->isPermissionsIssue) {
            return 'permissions';
        } elseif ($this->isFileSizeIssue) {
            return 'size_limit';
        } elseif ($this->isFileTypeIssue) {
            return 'file_type_restriction';
        } elseif (str_contains($this->failureType, 'network')) {
            return 'network';
        } elseif (str_contains($this->failureType, 'timeout')) {
            return 'timeout';
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
        
        if ($this->isStorageSpaceIssue) {
            $options = [
                'cleanup_temp_files',
                'cleanup_old_uploads',
                'compress_existing_files',
                'expand_storage',
            ];
        } elseif ($this->isPermissionsIssue) {
            $options = [
                'fix_permissions',
                'change_ownership',
                'update_acl',
            ];
        } elseif ($this->isFileSizeIssue) {
            $options = [
                'increase_upload_limit',
                'compress_before_upload',
                'chunk_upload',
            ];
        } else {
            $options = [
                'retry_upload',
                'switch_storage_disk',
                'manual_intervention',
            ];
        }
        
        return $options;
    }
}