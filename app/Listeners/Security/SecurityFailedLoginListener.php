<?php

namespace App\Listeners\Security;

use App\Events\Security\SecurityFailedLoginEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Listener for SecurityFailedLoginEvent
 * 
 * This listener handles failed login attempts by creating notifications,
 * logging security events, and potentially triggering security responses.
 */
class SecurityFailedLoginListener extends AdministrativeAlertListener
{
    /**
     * Process the failed login event
     *
     * @param SecurityFailedLoginEvent $event The failed login event
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

        // Check if IP should be blocked
        $this->checkIpBlocking($event);

        // Check for brute force patterns
        $this->checkBruteForcePatterns($event);
    }

    /**
     * Get the recipients for this security alert
     *
     * @param SecurityFailedLoginEvent $event The event
     * @return array Array of User objects
     */
    protected function getRecipients(\App\Events\AdministrativeAlertEvent $event): array
    {
        // For security events, always notify admins
        $recipients = User::where('role', 'admin')->get()->toArray();

        // If high severity or suspicious IP, also notify managers
        if (in_array($event->getSeverity(), ['CRITICAL', 'HIGH']) || $event->isSuspiciousIp()) {
            $managers = User::where('role', 'manager')->get()->toArray();
            $recipients = array_merge($recipients, $managers);
        }

        return $recipients;
    }

    /**
     * Create database notifications for recipients
     *
     * @param SecurityFailedLoginEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function createDatabaseNotifications(SecurityFailedLoginEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SecurityFailedLoginNotification($eventData)
            );
        }
    }

    /**
     * Send email notifications to recipients
     *
     * @param SecurityFailedLoginEvent $event The event
     * @param array $recipients Array of User objects
     * @return void
     */
    protected function sendEmailNotifications(SecurityFailedLoginEvent $event, array $recipients): void
    {
        $eventData = $this->prepareEventData($event);
        
        foreach ($recipients as $recipient) {
            // Queue email notification
            \Mail::to($recipient->email)
                ->queue(new \App\Mail\Security\SecurityFailedLoginMail($eventData, $recipient));
        }
    }

    /**
     * Log the security event with detailed information
     *
     * @param SecurityFailedLoginEvent $event The event
     * @return void
     */
    protected function logSecurityEvent(SecurityFailedLoginEvent $event): void
    {
        Log::warning('Failed login attempt detected', [
            'event_type' => 'security_failed_login',
            'email' => $event->email,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'attempts' => $event->attempts,
            'severity' => $event->getSeverity(),
            'is_suspicious_ip' => $event->isSuspiciousIp(),
            'is_known_attacker' => $event->isKnownAttacker(),
            'is_brute_force' => $event->isBruteForce(),
            'occurred_at' => $event->occurredAt->toISOString(),
        ]);
    }

    /**
     * Check if the IP should be blocked based on the event
     *
     * @param SecurityFailedLoginEvent $event The event
     * @return void
     */
    protected function checkIpBlocking(SecurityFailedLoginEvent $event): void
    {
        if ($event->shouldBlockIp()) {
            try {
                // Add IP to blocked list
                $this->blockIpAddress($event->ipAddress, $event->getSeverity());
                
                Log::info('IP address blocked due to failed login attempts', [
                    'ip_address' => $event->ipAddress,
                    'reason' => 'multiple_failed_logins',
                    'attempts' => $event->attempts,
                    'severity' => $event->getSeverity(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to block IP address', [
                    'ip_address' => $event->ipAddress,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check for brute force patterns and trigger appropriate responses
     *
     * @param SecurityFailedLoginEvent $event The event
     * @return void
     */
    protected function checkBruteForcePatterns(SecurityFailedLoginEvent $event): void
    {
        if ($event->isBruteForce()) {
            try {
                // Trigger brute force protection
                $this->enableBruteForceProtection($event->ipAddress);
                
                // Create high-priority alert for security team
                $this->createBruteForceAlert($event);
                
                Log::critical('Brute force attack detected', [
                    'ip_address' => $event->ipAddress,
                    'target_email' => $event->email,
                    'attempts' => $event->attempts,
                    'time_window' => $event->timeWindow,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to handle brute force attack', [
                    'ip_address' => $event->ipAddress,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Block an IP address in the firewall/security system
     *
     * @param string $ipAddress The IP to block
     * @param string $severity The severity level
     * @return void
     */
    protected function blockIpAddress(string $ipAddress, string $severity): void
    {
        // Implementation would depend on your security infrastructure
        // This could integrate with cloudflare, AWS WAF, or local firewall
        
        $blockDuration = match($severity) {
            'CRITICAL' => 24 * 60 * 60,  // 24 hours
            'HIGH' => 12 * 60 * 60,     // 12 hours
            'MEDIUM' => 2 * 60 * 60,    // 2 hours
            default => 30 * 60,         // 30 minutes
        };

        // Store block in cache/database
        cache()->put("blocked_ip:{$ipAddress}", [
            'blocked_at' => now(),
            'duration' => $blockDuration,
            'reason' => 'failed_login_attempts',
            'severity' => $severity,
        ], $blockDuration);
    }

    /**
     * Enable brute force protection for an IP
     *
     * @param string $ipAddress The IP to protect against
     * @return void
     */
    protected function enableBruteForceProtection(string $ipAddress): void
    {
        // Enable stricter rate limiting for this IP
        cache()->put("brute_force_protection:{$ipAddress}", [
            'enabled_at' => now(),
            'rate_limit' => 1, // 1 attempt per minute
            'duration' => 60 * 60, // 1 hour
        ], 60 * 60);
    }

    /**
     * Create a high-priority brute force alert
     *
     * @param SecurityFailedLoginEvent $event The event
     * @return void
     */
    protected function createBruteForceAlert(SecurityFailedLoginEvent $event): void
    {
        $eventData = $this->prepareEventData($event);
        
        // Notify all admins immediately
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(
                new \App\Notifications\SecurityBruteForceAlertNotification($eventData)
            );
        }
    }

    /**
     * Prepare event data specific to failed login events
     *
     * @param SecurityFailedLoginEvent $event The event
     * @return array Prepared event data
     */
    protected function prepareEventData(\App\Events\AdministrativeAlertEvent $event): array
    {
        $baseData = parent::prepareEventData($event);
        
        return array_merge($baseData, [
            'email' => $event->email,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'attempts' => $event->attempts,
            'time_window' => $event->timeWindow ?? null,
            'is_suspicious_ip' => $event->isSuspiciousIp(),
            'is_known_attacker' => $event->isKnownAttacker(),
            'is_brute_force' => $event->isBruteForce(),
            'should_block_ip' => $event->shouldBlockIp(),
            'geo_location' => $this->getGeoLocation($event->ipAddress),
            'threat_level' => $this->calculateThreatLevel($event),
        ]);
    }

    /**
     * Get geo location information for an IP address
     *
     * @param string $ipAddress The IP address
     * @return array|null Geo location data
     */
    protected function getGeoLocation(string $ipAddress): ?array
    {
        try {
            // Implementation would use a geo IP service
            // For now, return null
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get geo location for IP', [
                'ip_address' => $ipAddress,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate the threat level based on event characteristics
     *
     * @param SecurityFailedLoginEvent $event The event
     * @return string The threat level (low, medium, high, critical)
     */
    protected function calculateThreatLevel(SecurityFailedLoginEvent $event): string
    {
        if ($event->isKnownAttacker() || $event->isBruteForce()) {
            return 'critical';
        } elseif ($event->isSuspiciousIp() || $event->attempts >= 10) {
            return 'high';
        } elseif ($event->attempts >= 5) {
            return 'medium';
        }
        
        return 'low';
    }
}