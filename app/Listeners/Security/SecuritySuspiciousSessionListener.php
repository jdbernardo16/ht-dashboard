<?php

namespace App\Listeners\Security;

use App\Events\Security\SecuritySuspiciousSessionEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for SecuritySuspiciousSessionEvent
 * 
 * This listener handles suspicious session events by creating notifications,
 * logging security incidents, and potentially triggering security responses.
 */
class SecuritySuspiciousSessionListener extends AdministrativeAlertListener
{
    /**
     * Process the suspicious session event
     *
     * @param SecuritySuspiciousSessionEvent $event The suspicious session event
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

        // Check if session should be terminated
        $this->checkSessionTermination($event);

        // Check for session hijacking indicators
        $this->checkSessionHijacking($event);

        // Create security audit trail entry
        $this->createAuditTrailEntry($event);

        // Check for anomalous behavior patterns
        $this->checkAnomalousBehavior($event);
    }

    /**
     * Get the recipients for this security alert
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        $recipients = [];

        // Always notify admins
        $admins = User::where('role', 'admin')->get()->toArray();
        $recipients = array_merge($recipients, $admins);

        // For high severity or admin users, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || 
            ($event->user && $event->user->role === 'admin')) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        // Include the affected user (so they're aware of suspicious activity)
        if ($event->user && !in_array($event->user, $recipients)) {
            $recipients[] = $event->user;
        }

        // If user has a manager, include them
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
     * @param SecuritySuspiciousSessionEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(SecuritySuspiciousSessionEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SecuritySuspiciousSessionNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(SecuritySuspiciousSessionEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\Security\SecuritySuspiciousSessionMail($eventData, $recipient));
        }
    }

    /**
     * Log the security incident with detailed information
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function logSecurityIncident(SecuritySuspiciousSessionEvent $event): void
    {
        Log::warning('Suspicious session activity detected', [
            'event_type' => 'security_suspicious_session',
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'session_id' => $event->sessionId,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'suspicious_indicators' => $event->getSuspiciousIndicators(),
            'is_concurrent_session' => $event->isConcurrentSession(),
            'is_impossible_travel' => $event->isImpossibleTravel(),
            'is_unusual_location' => $event->isUnusualLocation(),
            'is_unusual_device' => $event->isUnusualDevice(),
            'is_unusual_time' => $event->isUnusualTime(),
            'severity' => $event->getSeverity(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if the session should be terminated
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function checkSessionTermination(SecuritySuspiciousSessionEvent $event): void
    {
        if ($event->shouldTerminateSession()) {
            try {
                // Terminate the suspicious session
                $this->terminateSession($event->sessionId, $event->user);
                
                Log::info('Suspicious session terminated', [
                    'session_id' => $event->sessionId,
                    'user_id' => $event->user?->id,
                    'reason' => 'suspicious_activity_detected',
                    'indicators' => $event->getSuspiciousIndicators(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to terminate suspicious session', [
                    'session_id' => $event->sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check for session hijacking indicators
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function checkSessionHijacking(SecuritySuspiciousSessionEvent $event): void
    {
        if ($event->hasSessionHijackingIndicators()) {
            try {
                // Trigger session hijacking response
                $this->triggerSessionHijackingResponse($event);
                
                // Create critical security alert
                $this->createSessionHijackingAlert($event);
                
                Log::critical('Potential session hijacking detected', [
                    'session_id' => $event->sessionId,
                    'user_id' => $event->user?->id,
                    'ip_address' => $event->ipAddress,
                    'hijacking_indicators' => $event->getSessionHijackingIndicators(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle potential session hijacking', [
                    'session_id' => $event->sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create an audit trail entry for the security incident
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function createAuditTrailEntry(SecuritySuspiciousSessionEvent $event): void
    {
        try {
            // Create audit log entry
            \App\Models\SecurityAuditLog::create([
                'event_type' => 'suspicious_session',
                'user_id' => $event->user?->id,
                'session_id' => $event->sessionId,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'suspicious_indicators' => json_encode($event->getSuspiciousIndicators()),
                'severity' => $event->getSeverity(),
                'context' => json_encode($event->context),
                'occurred_at' => $event->occurredAt,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit trail entry for suspicious session', [
                'error' => $e->getMessage(),
                'event_data' => [
                    'session_id' => $event->sessionId,
                    'user_id' => $event->user?->id,
                ],
            ]);
        }
    }

    /**
     * Check for anomalous behavior patterns
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function checkAnomalousBehavior(SecuritySuspiciousSessionEvent $event): void
    {
        if ($event->hasAnomalousBehavior()) {
            try {
                // Trigger anomalous behavior response
                $this->triggerAnomalousBehaviorResponse($event);
                
                // Create behavioral analysis alert
                $this->createAnomalousBehaviorAlert($event);
                
                Log::warning('Anomalous user behavior detected', [
                    'session_id' => $event->sessionId,
                    'user_id' => $event->user?->id,
                    'ip_address' => $event->ipAddress,
                    'anomalous_patterns' => $event->getAnomalousPatterns(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle anomalous behavior', [
                    'session_id' => $event->sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Terminate a user session
     *
     * @param string $sessionId The session ID
     * @param User|null $user The user
     * @return void
     */
    protected function terminateSession(string $sessionId, ?User $user): void
    {
        // Invalidate the session
        session()->invalidate();
        
        // Remove from active sessions
        if ($user) {
            $user->sessions()->where('id', $sessionId)->delete();
        }
        
        // Clear session cache
        cache()->forget("session:{$sessionId}");
        
        // Log session termination
        if ($user) {
            Log::info('User session terminated', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'reason' => 'suspicious_activity',
            ]);
        }
    }

    /**
     * Trigger session hijacking response
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function triggerSessionHijackingResponse(SecuritySuspiciousSessionEvent $event): void
    {
        // Terminate all user sessions
        if ($event->user) {
            $this->terminateAllUserSessions($event->user);
        }

        // Block the IP address
        $this->blockIpAddress($event->ipAddress, 'CRITICAL');

        // Force password reset
        if ($event->user) {
            $this->forcePasswordReset($event->user);
        }

        // Require additional authentication
        if ($event->user) {
            $this->requireAdditionalAuthentication($event->user);
        }

        // Notify security team
        $this->notifySecurityTeam($event);
    }

    /**
     * Trigger anomalous behavior response
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function triggerAnomalousBehaviorResponse(SecuritySuspiciousSessionEvent $event): void
    {
        // Monitor the user more closely
        if ($event->user) {
            $this->enableEnhancedMonitoring($event->user);
        }

        // Require additional verification for sensitive actions
        if ($event->user) {
            $this->requireAdditionalVerification($event->user);
        }

        // Create security watch
        $this->createSecurityWatch($event);
    }

    /**
     * Terminate all user sessions
     *
     * @param User $user The user
     * @return void
     */
    protected function terminateAllUserSessions(User $user): void
    {
        $user->sessions()->delete();
        
        // Clear all session caches for this user
        $userSessions = cache()->get("user_sessions:{$user->id}", []);
        foreach ($userSessions as $sessionId) {
            cache()->forget("session:{$sessionId}");
        }
        
        cache()->forget("user_sessions:{$user->id}");
        
        Log::info('All user sessions terminated', [
            'user_id' => $user->id,
            'reason' => 'potential_session_hijacking',
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
            'reason' => 'session_hijacking_attempt',
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

        $user->notify(
            new \App\Notifications\ForcePasswordResetNotification()
        );
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
            '2fa_required_until' => now()->addDays(7),
        ]);
    }

    /**
     * Enable enhanced monitoring for a user
     *
     * @param User $user The user
     * @return void
     */
    protected function enableEnhancedMonitoring(User $user): void
    {
        $user->update([
            'enhanced_monitoring' => true,
            'monitoring_expires' => now()->addDays(7),
        ]);
    }

    /**
     * Require additional verification for sensitive actions
     *
     * @param User $user The user
     * @return void
     */
    protected function requireAdditionalVerification(User $user): void
    {
        $user->update([
            'requires_additional_verification' => true,
            'verification_expires' => now()->addDays(3),
        ]);
    }

    /**
     * Create a security watch
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function createSecurityWatch(SecuritySuspiciousSessionEvent $event): void
    {
        \App\Models\SecurityWatch::create([
            'user_id' => $event->user?->id,
            'watch_type' => 'anomalous_behavior',
            'ip_address' => $event->ipAddress,
            'session_id' => $event->sessionId,
            'reason' => 'Suspicious session activity detected',
            'severity' => $event->getSeverity(),
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
        ]);
    }

    /**
     * Notify the security team
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function notifySecurityTeam(SecuritySuspiciousSessionEvent $event): void
    {
        $eventData = $this->prepareEventData($event);
        
        // Notify all admins
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(
                new \App\Notifications\SecuritySessionHijackingAlertNotification($eventData)
            );
        }
    }

    /**
     * Create a session hijacking alert
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function createSessionHijackingAlert(SecuritySuspiciousSessionEvent $event): void
    {
        \App\Models\SecurityAlert::create([
            'type' => 'session_hijacking',
            'severity' => 'CRITICAL',
            'user_id' => $event->user?->id,
            'session_id' => $event->sessionId,
            'ip_address' => $event->ipAddress,
            'description' => $event->getDescription(),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Create an anomalous behavior alert
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return void
     */
    protected function createAnomalousBehaviorAlert(SecuritySuspiciousSessionEvent $event): void
    {
        \App\Models\SecurityAlert::create([
            'type' => 'anomalous_behavior',
            'severity' => 'MEDIUM',
            'user_id' => $event->user?->id,
            'session_id' => $event->sessionId,
            'ip_address' => $event->ipAddress,
            'description' => $event->getDescription(),
            'status' => 'active',
            'created_at' => now(),
        ]);
    }

    /**
     * Prepare event data specific to suspicious session events
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'user_id' => $event->user?->id,
            'user_email' => $event->user?->email,
            'session_id' => $event->sessionId,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'suspicious_indicators' => $event->getSuspiciousIndicators(),
            'is_concurrent_session' => $event->isConcurrentSession(),
            'is_impossible_travel' => $event->isImpossibleTravel(),
            'is_unusual_location' => $event->isUnusualLocation(),
            'is_unusual_device' => $event->isUnusualDevice(),
            'is_unusual_time' => $event->isUnusualTime(),
            'should_terminate_session' => $event->shouldTerminateSession(),
            'threat_level' => $this->calculateThreatLevel($event),
            'recommended_actions' => $this->getRecommendedActions($event),
        ]);
    }

    /**
     * Calculate the threat level for the suspicious session
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return string The threat level (low, medium, high, critical)
     */
    protected function calculateThreatLevel(SecuritySuspiciousSessionEvent $event): string
    {
        if ($event->hasSessionHijackingIndicators()) {
            return 'critical';
        } elseif ($event->isImpossibleTravel() || $event->isConcurrentSession()) {
            return 'high';
        } elseif ($event->isUnusualLocation() || $event->isUnusualDevice()) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the suspicious session
     *
     * @param SecuritySuspiciousSessionEvent $event The event
     * @return array List of recommended actions
     */
    protected function getRecommendedActions(SecuritySuspiciousSessionEvent $event): array
    {
        $actions = [];

        if ($event->hasSessionHijackingIndicators()) {
            $actions[] = 'Terminate all user sessions';
            $actions[] = 'Block IP address';
            $actions[] = 'Force password reset';
            $actions[] = 'Enable 2FA requirement';
            $actions[] = 'Full security audit';
        } elseif ($event->isImpossibleTravel()) {
            $actions[] = 'Verify user identity';
            $actions[] = 'Terminate current session';
            $actions[] = 'Require additional authentication';
        } elseif ($event->isConcurrentSession()) {
            $actions[] = 'Review active sessions';
            $actions[] = 'Terminate suspicious sessions';
            $actions[] = 'Notify user of concurrent access';
        } elseif ($event->isUnusualLocation()) {
            $actions[] = 'Verify location legitimacy';
            $actions[] = 'Consider location-based restrictions';
            $actions[] = 'Monitor future access patterns';
        } elseif ($event->isUnusualDevice()) {
            $actions[] = 'Verify device ownership';
            $actions[] = 'Consider device restrictions';
            $actions[] = 'Monitor device usage patterns';
        }

        if ($event->shouldTerminateSession()) {
            $actions[] = 'Terminate suspicious session';
        }

        if ($event->hasAnomalousBehavior()) {
            $actions[] = 'Enable enhanced monitoring';
            $actions[] = 'Require additional verification';
            $actions[] = 'Create security watch';
        }

        return $actions;
    }
}