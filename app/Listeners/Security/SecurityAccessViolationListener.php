<?php

namespace App\Listeners\Security;

use App\Events\Security\SecurityAccessViolationEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for SecurityAccessViolationEvent
 * 
 * This listener handles access violation events by creating notifications,
 * logging security incidents, and potentially triggering security responses.
 */
class SecurityAccessViolationListener extends AdministrativeAlertListener
{
    /**
     * Process the access violation event
     *
     * @param SecurityAccessViolationEvent $event The access violation event
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

        // Log the security incident
        $this->logSecurityIncident($event);

        // Check if user should be suspended
        $this->checkUserSuspension($event);

        // Check for privilege escalation patterns
        $this->checkPrivilegeEscalation($event);

        // Create security audit trail entry
        $this->createAuditTrailEntry($event);
    }

    /**
     * Get the recipients for this security alert
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        // For access violations, always notify admins
        $recipients = User::where('role', 'admin')->get()->toArray();

        // If high severity or involves admin resources, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || 
            $event->isAdminResource() || 
            $event->isPrivilegeEscalation()) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // If user account is involved, include their manager (if different from user)
        if ($event->user && $event->user->manager_id && $event->user->manager_id !== $event->user->id) {
            $manager = User::find($event->user->manager_id);
            if ($manager && !in_array($manager, $recipients)) {
                $recipients[] = $manager;
            }
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param SecurityAccessViolationEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(SecurityAccessViolationEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SecurityAccessViolationNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param SecurityAccessViolationEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(SecurityAccessViolationEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\Security\SecurityAccessViolationMail($eventData, $recipient));
        }
    }

    /**
     * Log the security incident with detailed information
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return void
     */
    protected function logSecurityIncident(SecurityAccessViolationEvent $event): void
    {
        Log::warning('Security access violation detected', [
            'event_type' => 'security_access_violation',
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'resource' => $event->resource,
            'action' => $event->action,
            'required_permission' => $event->requiredPermission,
            'user_permissions' => $event->userPermissions,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'is_admin_resource' => $event->isAdminResource(),
            'is_privilege_escalation' => $event->isPrivilegeEscalation(),
            'is_data_breach' => $event->isDataBreach(),
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if the user should be suspended based on the violation
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return void
     */
    protected function checkUserSuspension(SecurityAccessViolationEvent $event): void
    {
        if ($event->shouldSuspendUser()) {
            try {
                // Suspend the user account
                $this->suspendUserAccount($event->user, $event);
                
                Log::warning('User account suspended due to access violation', [
                    'user_id' => $event->user?->id,
                    'user_email' => $event->user?->email,
                    'reason' => 'access_violation',
                    'resource' => $event->resource,
                    'action' => $event->action,
                    'severity' => $event->getSeverity(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to suspend user account', [
                    'user_id' => $event->user?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check for privilege escalation patterns
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return void
     */
    protected function checkPrivilegeEscalation(SecurityAccessViolationEvent $event): void
    {
        if ($event->isPrivilegeEscalation()) {
            try {
                // Trigger immediate security response
                $this->triggerPrivilegeEscalationResponse($event);
                
                // Create critical security alert
                $this->createPrivilegeEscalationAlert($event);
                
                Log::critical('Privilege escalation attempt detected', [
                    'user_id' => $event->user?->id,
                    'user_email' => $event->user?->email,
                    'resource' => $event->resource,
                    'action' => $event->action,
                    'required_permission' => $event->requiredPermission,
                    'user_permissions' => $event->userPermissions,
                    'ip_address' => $event->ipAddress,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle privilege escalation attempt', [
                    'user_id' => $event->user?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create an audit trail entry for the security incident
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return void
     */
    protected function createAuditTrailEntry(SecurityAccessViolationEvent $event): void
    {
        try {
            // Create audit log entry
            \App\Models\SecurityAuditLog::create([
                'event_type' => 'access_violation',
                'user_id' => $event->user?->id,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'resource' => $event->resource,
                'action' => $event->action,
                'required_permission' => $event->requiredPermission,
                'user_permissions' => json_encode($event->userPermissions),
                'severity' => $event->getSeverity(),
                'context' => json_encode($event->context),
                'occurred_at' => $event->occurredAt,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit trail entry', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'user_id' => $event->user?->id,
                    'resource' => $event->resource,
                    'action' => $event->action,
                ],
            ]);
        }
    }

    /**
     * Suspend a user account
     *
     * @param User|null $user The user to suspend
     * @param SecurityAccessViolationEvent $event The violation event
     * @return void
     */
    protected function suspendUserAccount(?User $user, SecurityAccessViolationEvent $event): void
    {
        if (!$user) {
            return;
        }

        $suspensionDuration = match($event->getSeverity()) {
            'CRITICAL' => 7 * 24 * 60 * 60,  // 7 days
            'HIGH' => 3 * 24 * 60 * 60,     // 3 days
            'MEDIUM' => 24 * 60 * 60,       // 1 day
            default => 12 * 60 * 60,        // 12 hours
        };

        // Update user status
        $user->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_until' => now()->addSeconds($suspensionDuration),
            'suspension_reason' => 'Access violation: ' . $event->resource . ' - ' . $event->action,
        ]);

        // Create suspension notification for the user
        $user->notify(
            new \App\Notifications\AccountSuspendedNotification($event, $suspensionDuration)
        );
    }

    /**
     * Trigger privilege escalation response
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return void
     */
    protected function triggerPrivilegeEscalationResponse(SecurityAccessViolationEvent $event): void
    {
        // Immediately suspend the user if they exist
        if ($event->user) {
            $this->suspendUserAccount($event->user, $event);
        }

        // Block the IP address
        $this->blockIpAddress($event->ipAddress, 'CRITICAL');

        // Notify all security personnel
        $this->notifySecurityTeam($event);
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
            'reason' => 'privilege_escalation_attempt',
            'severity' => $severity,
        ], $blockDuration);
    }

    /**
     * Notify the security team
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return void
     */
    protected function notifySecurityTeam(SecurityAccessViolationEvent $event): void
    {
        $eventData = $this->prepareEventData($event);
        
        // Notify all admins immediately
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(
                new \App\Notifications\SecurityPrivilegeEscalationAlertNotification($eventData)
            );
        }
    }

    /**
     * Create a privilege escalation alert
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return void
     */
    protected function createPrivilegeEscalationAlert(SecurityAccessViolationEvent $event): void
    {
        // Create a high-priority security alert
        \App\Models\SecurityAlert::create([
            'type' => 'privilege_escalation',
            'severity' => 'CRITICAL',
            'user_id' => $event->user?->id,
            'ip_address' => $event->ipAddress,
            'resource' => $event->resource,
            'action' => $event->action,
            'description' => $event->getDescription(),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Prepare event data specific to access violation events
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'resource' => $event->resource,
            'action' => $event->action,
            'required_permission' => $event->requiredPermission,
            'user_permissions' => $event->userPermissions,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'is_admin_resource' => $event->isAdminResource(),
            'is_privilege_escalation' => $event->isPrivilegeEscalation(),
            'is_data_breach' => $event->isDataBreach(),
            'should_suspend_user' => $event->shouldSuspendUser(),
            'risk_score' => $this->calculateRiskScore($event),
            'recommended_actions' => $this->getRecommendedActions($event),
        ]);
    }

    /**
     * Calculate a risk score for the access violation
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return int Risk score (0-100)
     */
    protected function calculateRiskScore(SecurityAccessViolationEvent $event): int
    {
        $score = 0;

        // Base score for any violation
        $score += 20;

        // Additional points for high-risk factors
        if ($event->isPrivilegeEscalation()) {
            $score += 40;
        }

        if ($event->isAdminResource()) {
            $score += 30;
        }

        if ($event->isDataBreach()) {
            $score += 35;
        }

        if ($event->user && $event->user->hasRecentViolations()) {
            $score += 25;
        }

        if ($this->isSuspiciousIpAddress($event->ipAddress)) {
            $score += 20;
        }

        return min($score, 100);
    }

    /**
     * Check if an IP address is suspicious
     *
     * @param string $ipAddress The IP address
     * @return bool True if suspicious
     */
    protected function isSuspiciousIpAddress(string $ipAddress): bool
    {
        // Check against known malicious IP lists
        // Check for VPN/proxy usage
        // Check for geographic anomalies
        // For now, return false
        return false;
    }

    /**
     * Get recommended actions based on the violation
     *
     * @param SecurityAccessViolationEvent $event The event
     * @return array List of recommended actions
     */
    protected function getRecommendedActions(SecurityAccessViolationEvent $event): array
    {
        $actions = [];

        if ($event->isPrivilegeEscalation()) {
            $actions[] = 'Immediate user suspension';
            $actions[] = 'Block IP address';
            $actions[] = 'Full security audit';
            $actions[] = 'Review all user activity';
        }

        if ($event->isAdminResource()) {
            $actions[] = 'Review admin access logs';
            $actions[] = 'Verify admin permissions';
            $actions[] = 'Check for unauthorized access';
        }

        if ($event->isDataBreach()) {
            $actions[] = 'Assess data exposure';
            $actions[] = 'Notify affected users';
            $actions[] = 'Review data access policies';
        }

        if ($event->shouldSuspendUser()) {
            $actions[] = 'Suspend user account';
            $actions[] = 'Review user permissions';
            $actions[] = 'Schedule security review';
        }

        if ($event->user) {
            $actions[] = 'Contact user for explanation';
            $actions[] = 'Document incident';
        }

        return $actions;
    }
}