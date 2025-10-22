<?php

namespace App\Events\Security;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when a failed login attempt is detected
 * 
 * This event is fired when authentication fails, helping to track potential
 * brute force attacks or unauthorized access attempts.
 */
class SecurityFailedLoginEvent extends AdministrativeAlertEvent
{
    /**
     * The email address that was used in the failed login attempt
     */
    public string $email;

    /**
     * The IP address from which the login attempt originated
     */
    public string $ipAddress;

    /**
     * The user agent string from the request
     */
    public string $userAgent;

    /**
     * The number of failed attempts for this email/IP combination
     */
    public int $attempts;

    /**
     * Whether this appears to be a suspicious pattern
     */
    public bool $isSuspicious;

    /**
     * The location data if available (from IP geolocation)
     */
    public array|null $location;

    /**
     * Create a new event instance
     *
     * @param string $email The email address used in the attempt
     * @param string $ipAddress The source IP address
     * @param string $userAgent The browser user agent
     * @param int $attempts Number of failed attempts
     * @param bool $isSuspicious Whether this appears suspicious
     * @param array|null $location Location data from IP geolocation
     * @param User|null $initiatedBy The user who initiated this event (usually null for failed logins)
     */
    public function __construct(
        string $email,
        string $ipAddress,
        string $userAgent,
        int $attempts = 1,
        bool $isSuspicious = false,
        array|null $location = null,
        User|null $initiatedBy = null
    ) {
        $this->email = $email;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->attempts = $attempts;
        $this->isSuspicious = $isSuspicious;
        $this->location = $location;

        $context = [
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'attempts' => $attempts,
            'is_suspicious' => $isSuspicious,
            'location' => $location,
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
        return 'Security';
    }

    /**
     * Get the severity level of this event
     *
     * @return string The severity level
     */
    public function getSeverity(): string
    {
        // Higher severity for multiple attempts or suspicious patterns
        if ($this->attempts >= 10 || $this->isSuspicious) {
            return self::SEVERITY_HIGH;
        } elseif ($this->attempts >= 5) {
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
        if ($this->attempts > 1) {
            return "Multiple Failed Login Attempts Detected";
        }
        
        return "Failed Login Attempt Detected";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "A failed login attempt was detected for email '{$this->email}' from IP {$this->ipAddress}.";
        
        if ($this->attempts > 1) {
            $description .= " This is the {$this->attempts}th failed attempt.";
        }
        
        if ($this->isSuspicious) {
            $description .= " This pattern appears suspicious and may indicate a brute force attack.";
        }
        
        if ($this->location) {
            $description .= " Location: {$this->location['city']}, {$this->location['country']} ({$this->location['isp']}).";
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
        return route('admin.users.index') . '?search=' . urlencode($this->email);
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for suspicious or multiple attempts
        if ($this->attempts >= 5 || $this->isSuspicious) {
            return 'security-high-alerts';
        }
        
        return 'security-alerts';
    }

    /**
     * Determine if this event should trigger account lockout
     *
     * @return bool True if account should be locked
     */
    public function shouldLockAccount(): bool
    {
        return $this->attempts >= 10 || ($this->attempts >= 5 && $this->isSuspicious);
    }

    /**
     * Determine if this event should trigger IP blocking
     *
     * @return bool True if IP should be blocked
     */
    public function shouldBlockIp(): bool
    {
        return $this->attempts >= 15 || $this->isSuspicious;
    }

    /**
     * Get the email subject for notifications
     *
     * @return string The email subject
     */
    public function getEmailSubject(): string
    {
        if ($this->attempts > 1) {
            return "[SECURITY ALERT] Multiple Failed Login Attempts ({$this->attempts} attempts)";
        }
        
        return "[SECURITY ALERT] Failed Login Attempt Detected";
    }

    /**
     * Get additional metadata for logging and analytics
     *
     * @return array Additional metadata
     */
    public function getMetadata(): array
    {
        return [
            'threat_level' => $this->calculateThreatLevel(),
            'requires_action' => $this->shouldLockAccount() || $this->shouldBlockIp(),
            'recommended_actions' => $this->getRecommendedActions(),
        ];
    }

    /**
     * Calculate the threat level based on various factors
     *
     * @return string The threat level (low, medium, high, critical)
     */
    private function calculateThreatLevel(): string
    {
        if ($this->attempts >= 15 || ($this->attempts >= 10 && $this->isSuspicious)) {
            return 'critical';
        } elseif ($this->attempts >= 10 || ($this->attempts >= 5 && $this->isSuspicious)) {
            return 'high';
        } elseif ($this->attempts >= 5) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get recommended actions based on the threat level
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->shouldLockAccount()) {
            $actions[] = 'Lock user account';
        }
        
        if ($this->shouldBlockIp()) {
            $actions[] = 'Block IP address';
        }
        
        if ($this->attempts >= 3) {
            $actions[] = 'Monitor future login attempts';
        }
        
        if ($this->isSuspicious) {
            $actions[] = 'Investigate user agent pattern';
        }
        
        return $actions;
    }
}