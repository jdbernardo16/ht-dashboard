<?php

namespace App\Listeners\Security;

use App\Events\Security\SecurityAdminAccountModifiedEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for SecurityAdminAccountModifiedEvent
 * 
 * This listener handles admin account modification events by creating notifications,
 * logging security events, and potentially triggering security responses.
 */
class SecurityAdminAccountModifiedListener extends AdministrativeAlertListener
{
    /**
     * Process the admin account modification event
     *
     * @param SecurityAdminAccountModifiedEvent $event The admin account modification event
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

        // Log the security event
        $this->logSecurityEvent($event);

        // Check if this is a suspicious modification
        $this->checkSuspiciousModification($event);

        // Create security audit trail entry
        $this->createAuditTrailEntry($event);

        // Check for account takeover indicators
        $this->checkAccountTakeoverIndicators($event);
    }

    /**
     * Get the recipients for this security alert
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify all admins except the modified user
        $admins = User::where('role', 'admin')
            ->where('id', '!=', $event->modifiedAdmin->id)
            ->get()
            ->toArray();
        $recipients = array_merge($recipients, $admins);

        // For critical changes, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH'])) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // If the modification was done by another admin, include them (for confirmation)
        if ($event->modifiedBy && $event->modifiedBy->id !== $event->modifiedAdmin->id) {
            if (!in_array($event->modifiedBy, $recipients)) {
                $recipients[] = $event->modifiedBy;
            }
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(SecurityAdminAccountModifiedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SecurityAdminAccountModifiedNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(SecurityAdminAccountModifiedEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\Security\SecurityAdminAccountModifiedMail($eventData, $recipient));
        }
    }

    /**
     * Log the security event with detailed information
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function logSecurityEvent(SecurityAdminAccountModifiedEvent $event): void
    {
        Log::warning('Admin account modification detected', [
            'event_type' => 'security_admin_account_modified',
            'modified_admin_id' => $event->modifiedAdmin->id,
            'modified_admin_email' => $event->modifiedAdmin->email,
            'modified_by_id' => $event->modifiedBy?->id,
            'modified_by_email' => $event->modifiedBy?->email,
            'modifications' => $event->modifications,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'is_self_modification' => $event->isSelfModification(),
            'is_critical_change' => $event->isCriticalChange(),
            'is_suspicious' => $event->isSuspicious(),
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if this modification is suspicious and requires additional action
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function checkSuspiciousModification(SecurityAdminAccountModifiedEvent $event): void
    {
        if ($event->isSuspicious()) {
            try {
                // Trigger security response
                $this->triggerSuspiciousModificationResponse($event);
                
                // Create security alert
                $this->createSuspiciousModificationAlert($event);
                
                Log::critical('Suspicious admin account modification detected', [
                    'modified_admin_id' => $event->modifiedAdmin->id,
                    'modified_admin_email' => $event->modifiedAdmin->email,
                    'modified_by_id' => $event->modifiedBy?->id,
                    'modifications' => $event->modifications,
                    'ip_address' => $event->ipAddress,
                    'suspicious_indicators' => $event->getSuspiciousIndicators(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle suspicious admin modification', [
                    'modified_admin_id' => $event->modifiedAdmin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create an audit trail entry for the security event
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function createAuditTrailEntry(SecurityAdminAccountModifiedEvent $event): void
    {
        try {
            // Create audit log entry
            \App\Models\SecurityAuditLog::create([
                'event_type' => 'admin_account_modified',
                'user_id' => $event->modifiedBy?->id,
                'target_user_id' => $event->modifiedAdmin->id,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'modifications' => json_encode($event->modifications),
                'severity' => $event->getSeverity(),
                'context' => json_encode($event->context),
                'occurred_at' => $event->occurredAt,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit trail entry for admin modification', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'modified_admin_id' => $event->modifiedAdmin->id,
                    'modified_by_id' => $event->modifiedBy?->id,
                ],
            ]);
        }
    }

    /**
     * Check for account takeover indicators
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function checkAccountTakeoverIndicators(SecurityAdminAccountModifiedEvent $event): void
    {
        if ($event->hasAccountTakeoverIndicators()) {
            try {
                // Trigger immediate security response
                $this->triggerAccountTakeoverResponse($event);
                
                // Create critical security alert
                $this->createAccountTakeoverAlert($event);
                
                Log::critical('Potential admin account takeover detected', [
                    'modified_admin_id' => $event->modifiedAdmin->id,
                    'modified_admin_email' => $event->modifiedAdmin->email,
                    'modified_by_id' => $event->modifiedBy?->id,
                    'ip_address' => $event->ipAddress,
                    'takeover_indicators' => $event->getAccountTakeoverIndicators(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle potential account takeover', [
                    'modified_admin_id' => $event->modifiedAdmin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Trigger response for suspicious modifications
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function triggerSuspiciousModificationResponse(SecurityAdminAccountModifiedEvent $event): void
    {
        // Require additional authentication for the modified admin
        $this->requireAdditionalAuthentication($event->modifiedAdmin);

        // Temporarily restrict admin privileges
        $this->temporarilyRestrictPrivileges($event->modifiedAdmin);

        // Notify security team
        $this->notifySecurityTeam($event);
    }

    /**
     * Trigger response for potential account takeover
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function triggerAccountTakeoverResponse(SecurityAdminAccountModifiedEvent $event): void
    {
        // Immediately suspend the modified admin account
        $this->suspendAdminAccount($event->modifiedAdmin, 'potential_takeover');

        // Block the IP address
        $this->blockIpAddress($event->ipAddress, 'CRITICAL');

        // Force password reset
        $this->forcePasswordReset($event->modifiedAdmin);

        // Notify all admins
        $this->notifyAllAdmins($event);
    }

    /**
     * Require additional authentication for a user
     *
     * @param User $user The user
     * @return void
     */
    protected function requireAdditionalAuthentication(User $user): void
    {
        $user->update([
            'requires_2fa' => true,
            '2fa_required_until' => now()->addHours(24),
        ]);
    }

    /**
     * Temporarily restrict admin privileges
     *
     * @param User $user The admin user
     * @return void
     */
    protected function temporarilyRestrictPrivileges(User $user): void
    {
        $user->update([
            'privileges_restricted' => true,
            'privileges_restricted_until' => now()->addHours(6),
            'restriction_reason' => 'Suspicious account activity detected',
        ]);
    }

    /**
     * Suspend an admin account
     *
     * @param User $user The admin to suspend
     * @param string $reason The suspension reason
     * @return void
     */
    protected function suspendAdminAccount(User $user, string $reason): void
    {
        $user->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_until' => now()->addDays(7),
            'suspension_reason' => $reason,
        ]);
    }

    /**
     * Block an IP address
     *
     * @param string $ipAddress The IP to block
     * @param string $severity The severity level
     * @return void
     */
    protected function blockIpAddress(string $ipAddress, string $severity): void
    {
        $blockDuration = match($severity) {
            'CRITICAL' => 7 * 24 * 60 * 60,  // 7 days
            'HIGH' => 3 * 24 * 60 * 60,     // 3 days
            'MEDIUM' => 24 * 60 * 60,       // 1 day
            default => 12 * 60 * 60,        // 12 hours
        };

        cache()->put("blocked_ip:{$ipAddress}", [
            'blocked_at' => now(),
            'duration' => $blockDuration,
            'reason' => 'suspicious_admin_modification',
            'severity' => $severity,
        ], $blockDuration);
    }

    /**
     * Force password reset for a user
     *
     * @param User $user The user
     * @return void
     */
    protected function forcePasswordReset(User $user): void
    {
        $user->update([
            'password_reset_required' => true,
            'password_reset_token' => \Str::random(60),
            'password_reset_expires' => now()->addHours(24),
        ]);

        // Send password reset email
        $user->notify(
            new \App\Notifications\ForcePasswordResetNotification()
        );
    }

    /**
     * Notify the security team
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function notifySecurityTeam(SecurityAdminAccountModifiedEvent $event): void
    {
        $eventData = $this->prepareEventData($event);
        
        // Notify all admins
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(
                new \App\Notifications\SecuritySuspiciousAdminModificationNotification($eventData)
            );
        }
    }

    /**
     * Notify all admins
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function notifyAllAdmins(SecurityAdminAccountModifiedEvent $event): void
    {
        $eventData = $this->prepareEventData($event);
        
        // Notify all admins
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(
                new \App\Notifications\SecurityAdminAccountTakeoverAlertNotification($eventData)
            );
        }
    }

    /**
     * Create a suspicious modification alert
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function createSuspiciousModificationAlert(SecurityAdminAccountModifiedEvent $event): void
    {
        \App\Models\SecurityAlert::create([
            'type' => 'suspicious_admin_modification',
            'severity' => 'HIGH',
            'user_id' => $event->modifiedBy?->id,
            'target_user_id' => $event->modifiedAdmin->id,
            'ip_address' => $event->ipAddress,
            'description' => $event->getDescription(),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Create an account takeover alert
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return void
     */
    protected function createAccountTakeoverAlert(SecurityAdminAccountModifiedEvent $event): void
    {
        \App\Models\SecurityAlert::create([
            'type' => 'admin_account_takeover',
            'severity' => 'CRITICAL',
            'user_id' => $event->modifiedBy?->id,
            'target_user_id' => $event->modifiedAdmin->id,
            'ip_address' => $event->ipAddress,
            'description' => $event->getDescription(),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Prepare event data specific to admin account modification events
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'modified_admin_id' => $event->modifiedAdmin->id,
            'modified_admin_email' => $event->modifiedAdmin->email,
            'modified_by_id' => $event->modifiedBy?->id,
            'modified_by_email' => $event->modifiedBy?->email,
            'modifications' => $event->modifications,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'is_self_modification' => $event->isSelfModification(),
            'is_critical_change' => $event->isCriticalChange(),
            'is_suspicious' => $event->isSuspicious(),
            'risk_level' => $this->calculateRiskLevel($event),
            'recommended_actions' => $this->getRecommendedActions($event),
        ]);
    }

    /**
     * Calculate the risk level for the modification
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return string The risk level (low, medium, high, critical)
     */
    protected function calculateRiskLevel(SecurityAdminAccountModifiedEvent $event): string
    {
        if ($event->hasAccountTakeoverIndicators()) {
            return 'critical';
        } elseif ($event->isSuspicious() || $event->isCriticalChange()) {
            return 'high';
        } elseif (!$event->isSelfModification()) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the modification
     *
     * @param SecurityAdminAccountModifiedEvent $event The event
     * @return array List of recommended actions
     */
    protected function getRecommendedActions(SecurityAdminAccountModifiedEvent $event): array
    {
        $actions = [];

        if ($event->hasAccountTakeoverIndicators()) {
            $actions[] = 'Immediate account suspension';
            $actions[] = 'Block IP address';
            $actions[] = 'Force password reset';
            $actions[] = 'Full security audit';
        } elseif ($event->isSuspicious()) {
            $actions[] = 'Require additional authentication';
            $actions[] = 'Temporarily restrict privileges';
            $actions[] = 'Verify user identity';
            $actions[] = 'Monitor account activity';
        } elseif ($event->isCriticalChange()) {
            $actions[] = 'Verify change legitimacy';
            $actions[] = 'Document business reason';
            $actions[] = 'Review approval process';
        }

        if (!$event->isSelfModification()) {
            $actions[] = 'Contact modified admin for confirmation';
            $actions[] = 'Review authorization for changes';
        }

        if ($event->isCriticalChange()) {
            $actions[] = 'Consider rollback if unauthorized';
            $actions[] = 'Review system impact';
        }

        return $actions;
    }
}