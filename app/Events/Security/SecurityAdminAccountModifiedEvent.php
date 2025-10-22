<?php

namespace App\Events\Security;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when an admin account is modified
 * 
 * This event is fired when critical admin account properties are changed,
 * helping to track potential security breaches or unauthorized modifications.
 */
class SecurityAdminAccountModifiedEvent extends AdministrativeAlertEvent
{
    /**
     * The admin user whose account was modified
     */
    public User $adminUser;

    /**
     * The user who performed the modification
     */
    public User|null $modifiedBy;

    /**
     * The properties that were modified
     */
    public array $modifiedProperties;

    /**
     * The old values before modification
     */
    public array $oldValues;

    /**
     * The new values after modification
     */
    public array $newValues;

    /**
     * The IP address from which the modification originated
     */
    public string $ipAddress;

    /**
     * The user agent string from the request
     */
    public string $userAgent;

    /**
     * Whether this modification was self-initiated
     */
    public bool $isSelfModification;

    /**
     * Whether this modification appears suspicious
     */
    public bool $isSuspicious;

    /**
     * The reason provided for the modification (if any)
     */
    public string|null $reason;

    /**
     * Create a new event instance
     *
     * @param User $adminUser The admin user whose account was modified
     * @param array $modifiedProperties The properties that were changed
     * @param array $oldValues The old values
     * @param array $newValues The new values
     * @param string $ipAddress The source IP address
     * @param string $userAgent The browser user agent
     * @param User|null $modifiedBy The user who made the changes
     * @param string|null $reason The reason for modification
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        User $adminUser,
        array $modifiedProperties,
        array $oldValues,
        array $newValues,
        string $ipAddress,
        string $userAgent,
        User|null $modifiedBy = null,
        string|null $reason = null,
        User|null $initiatedBy = null
    ) {
        $this->adminUser = $adminUser;
        $this->modifiedProperties = $modifiedProperties;
        $this->oldValues = $oldValues;
        $this->newValues = $newValues;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->modifiedBy = $modifiedBy;
        $this->reason = $reason;
        $this->isSelfModification = $modifiedBy?->id === $adminUser->id;
        $this->isSuspicious = $this->determineIfSuspicious();

        $context = [
            'admin_user_id' => $adminUser->id,
            'admin_user_email' => $adminUser->email,
            'modified_by_id' => $modifiedBy?->id,
            'modified_by_email' => $modifiedBy?->email,
            'modified_properties' => $modifiedProperties,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'is_self_modification' => $this->isSelfModification,
            'is_suspicious' => $this->isSuspicious,
            'reason' => $reason,
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
        // Critical for role changes or suspicious modifications
        if ($this->isSuspicious || $this->hasRoleChange()) {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->hasSensitivePropertyChange() || !$this->isSelfModification) {
            return self::SEVERITY_HIGH;
        } elseif ($this->isSelfModification) {
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
        if ($this->isSuspicious) {
            return "Suspicious Admin Account Modification Detected";
        } elseif ($this->hasRoleChange()) {
            return "Admin Account Role Change Detected";
        } elseif (!$this->isSelfModification) {
            return "Admin Account Modified by Another User";
        }
        
        return "Admin Account Self-Modification";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $modifier = $this->modifiedBy 
            ? "User '{$this->modifiedBy->email}' (ID: {$this->modifiedBy->id})"
            : "Unknown user";
        
        $description = "{$modifier} modified admin account '{$this->adminUser->email}' (ID: {$this->adminUser->id}). ";
        
        $description .= "Changed properties: " . implode(', ', $this->modifiedProperties) . ". ";
        
        if ($this->hasRoleChange()) {
            $oldRole = $this->oldValues['role'] ?? 'unknown';
            $newRole = $this->newValues['role'] ?? 'unknown';
            $description .= "Role changed from '{$oldRole}' to '{$newRole}'. ";
        }
        
        if ($this->isSuspicious) {
            $description .= "This modification appears suspicious due to unusual patterns. ";
        }
        
        if ($this->reason) {
            $description .= "Reason provided: '{$this->reason}'. ";
        }
        
        $description .= "Source: {$this->ipAddress}";
        
        return $description;
    }

    /**
     * Get the URL for taking action on this event
     *
     * @return string|null The action URL
     */
    public function getActionUrl(): string|null
    {
        return route('admin.users.show', $this->adminUser->id);
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use critical queue for suspicious or role changes
        if ($this->isSuspicious || $this->hasRoleChange()) {
            return 'security-critical-alerts';
        }
        
        return 'security-high-alerts';
    }

    /**
     * Get the email subject for notifications
     *
     * @return string The email subject
     */
    public function getEmailSubject(): string
    {
        if ($this->isSuspicious) {
            return "[CRITICAL] Suspicious Admin Account Modification";
        } elseif ($this->hasRoleChange()) {
            return "[CRITICAL] Admin Account Role Change";
        } elseif (!$this->isSelfModification) {
            return "[HIGH] Admin Account Modified by Another User";
        }
        
        return "[SECURITY] Admin Account Self-Modification";
    }

    /**
     * Check if the modification includes role changes
     *
     * @return bool True if role was changed
     */
    private function hasRoleChange(): bool
    {
        return in_array('role', $this->modifiedProperties) ||
               in_array('is_admin', $this->modifiedProperties);
    }

    /**
     * Check if the modification includes sensitive properties
     *
     * @return bool True if sensitive properties were changed
     */
    private function hasSensitivePropertyChange(): bool
    {
        $sensitiveProperties = [
            'email',
            'password',
            'role',
            'is_admin',
            'permissions',
            'api_token',
            'two_factor_secret',
        ];

        return !empty(array_intersect($this->modifiedProperties, $sensitiveProperties));
    }

    /**
     * Determine if the modification appears suspicious
     *
     * @return bool True if modification is suspicious
     */
    private function determineIfSuspicious(): bool
    {
        // Suspicious if:
        // 1. Modified by unknown user
        // 2. Role change without proper reason
        // 3. Multiple sensitive properties changed at once
        // 4. Modified from unusual IP
        // 5. Modified immediately after account creation
        
        if (!$this->modifiedBy) {
            return true;
        }
        
        if ($this->hasRoleChange() && !$this->reason) {
            return true;
        }
        
        $sensitiveChangesCount = 0;
        $sensitiveProperties = [
            'email', 'password', 'role', 'is_admin', 'permissions', 'api_token'
        ];
        
        foreach ($this->modifiedProperties as $property) {
            if (in_array($property, $sensitiveProperties)) {
                $sensitiveChangesCount++;
            }
        }
        
        if ($sensitiveChangesCount >= 2) {
            return true;
        }
        
        // Check for unusual IP patterns (simplified)
        if ($this->isUnusualIp($this->ipAddress)) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if IP address appears unusual
     *
     * @param string $ip The IP address to check
     * @return bool True if IP appears unusual
     */
    private function isUnusualIp(string $ip): bool
    {
        // This is a simplified check - in production, you'd want more sophisticated logic
        $unusualRanges = [
            '10.0.0.',      // Internal VPN
            '192.168.',     // Internal network
            '172.16.',      // Internal network
        ];
        
        foreach ($unusualRanges as $range) {
            if (str_starts_with($ip, $range)) {
                return true;
            }
        }
        
        return false;
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
            'requires_action' => $this->requiresImmediateAction(),
            'recommended_actions' => $this->getRecommendedActions(),
            'has_role_change' => $this->hasRoleChange(),
            'has_sensitive_change' => $this->hasSensitivePropertyChange(),
            'modification_risk_score' => $this->calculateRiskScore(),
        ];
    }

    /**
     * Calculate the threat level based on various factors
     *
     * @return string The threat level (low, medium, high, critical)
     */
    private function calculateThreatLevel(): string
    {
        if ($this->isSuspicious && $this->hasRoleChange()) {
            return 'critical';
        } elseif ($this->isSuspicious || $this->hasRoleChange()) {
            return 'high';
        } elseif ($this->hasSensitivePropertyChange() || !$this->isSelfModification) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Determine if immediate action is required
     *
     * @return bool True if immediate action is required
     */
    private function requiresImmediateAction(): bool
    {
        return $this->isSuspicious || $this->hasRoleChange();
    }

    /**
     * Get recommended actions based on the threat level
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->isSuspicious) {
            $actions[] = 'Immediately verify identity of modifier';
            $actions[] = 'Consider temporary account suspension';
            $actions[] = 'Review recent account activity';
            $actions[] = 'Contact account owner directly';
        }
        
        if ($this->hasRoleChange()) {
            $actions[] = 'Verify role change was authorized';
            $actions[] = 'Review role change history';
            $actions[] = 'Document reason for role change';
        }
        
        if (!$this->isSelfModification) {
            $actions[] = 'Verify modifier had proper authorization';
            $actions[] = 'Review access logs for modifier';
        }
        
        if ($this->hasSensitivePropertyChange()) {
            $actions[] = 'Monitor for unusual account behavior';
        }
        
        return $actions;
    }

    /**
     * Calculate a risk score for this modification
     *
     * @return int Risk score from 0-100
     */
    private function calculateRiskScore(): int
    {
        $score = 0;
        
        if ($this->isSuspicious) $score += 40;
        if ($this->hasRoleChange()) $score += 30;
        if ($this->hasSensitivePropertyChange()) $score += 20;
        if (!$this->isSelfModification) $score += 10;
        if (!$this->reason) $score += 5;
        if ($this->isUnusualIp($this->ipAddress)) $score += 15;
        
        return min(100, $score);
    }
}