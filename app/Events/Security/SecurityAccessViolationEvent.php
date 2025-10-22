<?php

namespace App\Events\Security;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when an access violation is detected
 * 
 * This event is fired when a user attempts to access resources they don't have
 * permission for, indicating potential security concerns or misconfiguration.
 */
class SecurityAccessViolationEvent extends AdministrativeAlertEvent
{
    /**
     * The user who attempted the unauthorized access
     */
    public User|null $user;

    /**
     * The resource that was being accessed
     */
    public string $resource;

    /**
     * The action that was attempted
     */
    public string $action;

    /**
     * The required permission that was missing
     */
    public string $requiredPermission;

    /**
     * The IP address from which the attempt originated
     */
    public string $ipAddress;

    /**
     * The user agent string from the request
     */
    public string $userAgent;

    /**
     * The route or endpoint that was accessed
     */
    public string $route;

    /**
     * Whether this appears to be a deliberate attack
     */
    public bool $isMalicious;

    /**
     * The number of similar violations from this user/IP
     */
    public int $violationCount;

    /**
     * Create a new event instance
     *
     * @param User|null $user The user who attempted the access
     * @param string $resource The resource being accessed
     * @param string $action The action being attempted
     * @param string $requiredPermission The permission that was required
     * @param string $ipAddress The source IP address
     * @param string $userAgent The browser user agent
     * @param string $route The route or endpoint
     * @param bool $isMalicious Whether this appears malicious
     * @param int $violationCount Number of previous violations
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        User|null $user,
        string $resource,
        string $action,
        string $requiredPermission,
        string $ipAddress,
        string $userAgent,
        string $route,
        bool $isMalicious = false,
        int $violationCount = 1,
        User|null $initiatedBy = null
    ) {
        $this->user = $user;
        $this->resource = $resource;
        $this->action = $action;
        $this->requiredPermission = $requiredPermission;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->route = $route;
        $this->isMalicious = $isMalicious;
        $this->violationCount = $violationCount;

        $context = [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'resource' => $resource,
            'action' => $action,
            'required_permission' => $requiredPermission,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'route' => $route,
            'is_malicious' => $isMalicious,
            'violation_count' => $violationCount,
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
        // Higher severity for malicious attempts or repeated violations
        if ($this->isMalicious || $this->violationCount >= 5) {
            return self::SEVERITY_HIGH;
        } elseif ($this->violationCount >= 3 || $this->isSensitiveResource()) {
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
        if ($this->isMalicious) {
            return "Malicious Access Violation Detected";
        } elseif ($this->violationCount > 1) {
            return "Repeated Access Violations Detected";
        }
        
        return "Access Violation Detected";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $userIdentifier = $this->user ? "User '{$this->user->email}' (ID: {$this->user->id})" : "Unauthenticated user";
        $description = "{$userIdentifier} attempted to {$this->action} {$this->resource} without required permission '{$this->requiredPermission}'.";
        
        if ($this->violationCount > 1) {
            $description .= " This is the {$this->violationCount}th violation from this user.";
        }
        
        if ($this->isMalicious) {
            $description .= " This appears to be a deliberate malicious attempt to bypass security controls.";
        }
        
        $description .= " Source: {$this->ipAddress} via {$this->route}";
        
        return $description;
    }

    /**
     * Get the URL for taking action on this event
     *
     * @return string|null The action URL
     */
    public function getActionUrl(): string|null
    {
        if ($this->user) {
            return route('admin.users.show', $this->user->id);
        }
        
        return route('admin.security.index');
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use high priority queue for malicious or repeated violations
        if ($this->isMalicious || $this->violationCount >= 3) {
            return 'security-high-alerts';
        }
        
        return 'security-alerts';
    }

    /**
     * Determine if this event should trigger account suspension
     *
     * @return bool True if account should be suspended
     */
    public function shouldSuspendAccount(): bool
    {
        return $this->isMalicious || $this->violationCount >= 10;
    }

    /**
     * Determine if this event should trigger IP blocking
     *
     * @return bool True if IP should be blocked
     */
    public function shouldBlockIp(): bool
    {
        return $this->isMalicious || $this->violationCount >= 7;
    }

    /**
     * Get the email subject for notifications
     *
     * @return string The email subject
     */
    public function getEmailSubject(): string
    {
        if ($this->isMalicious) {
            return "[CRITICAL] Malicious Access Violation Attempt";
        } elseif ($this->violationCount > 1) {
            return "[SECURITY ALERT] Repeated Access Violations ({$this->violationCount} violations)";
        }
        
        return "[SECURITY ALERT] Access Violation Detected";
    }

    /**
     * Check if the accessed resource is considered sensitive
     *
     * @return bool True if resource is sensitive
     */
    private function isSensitiveResource(): bool
    {
        $sensitiveResources = [
            'admin',
            'users',
            'security',
            'system',
            'database',
            'backup',
            'logs',
            'config',
        ];

        return in_array(strtolower($this->resource), $sensitiveResources) ||
               str_contains(strtolower($this->resource), 'admin') ||
               str_contains(strtolower($this->resource), 'system');
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
            'requires_action' => $this->shouldSuspendAccount() || $this->shouldBlockIp(),
            'recommended_actions' => $this->getRecommendedActions(),
            'is_sensitive_resource' => $this->isSensitiveResource(),
            'violation_pattern' => $this->detectViolationPattern(),
        ];
    }

    /**
     * Calculate the threat level based on various factors
     *
     * @return string The threat level (low, medium, high, critical)
     */
    private function calculateThreatLevel(): string
    {
        if ($this->isMalicious && $this->violationCount >= 5) {
            return 'critical';
        } elseif ($this->isMalicious || $this->violationCount >= 10) {
            return 'high';
        } elseif ($this->violationCount >= 5 || $this->isSensitiveResource()) {
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
        
        if ($this->shouldSuspendAccount() && $this->user) {
            $actions[] = 'Suspend user account';
        }
        
        if ($this->shouldBlockIp()) {
            $actions[] = 'Block IP address';
        }
        
        if ($this->isMalicious) {
            $actions[] = 'Investigate for potential security breach';
            $actions[] = 'Review audit logs for related activities';
        }
        
        if ($this->violationCount >= 3) {
            $actions[] = 'Monitor user activity closely';
        }
        
        if ($this->isSensitiveResource()) {
            $actions[] = 'Review access permissions for this resource';
        }
        
        return $actions;
    }

    /**
     * Detect potential violation patterns
     *
     * @return string The detected pattern type
     */
    private function detectViolationPattern(): string
    {
        if ($this->isMalicious) {
            return 'malicious_attack';
        } elseif ($this->violationCount >= 5) {
            return 'persistent_violation';
        } elseif ($this->isSensitiveResource()) {
            return 'targeted_attack';
        } elseif (str_contains($this->route, 'api')) {
            return 'api_abuse';
        }
        
        return 'accidental_access';
    }
}