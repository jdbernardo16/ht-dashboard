<?php

namespace App\Events\Security;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;

/**
 * Event triggered when suspicious session activity is detected
 * 
 * This event is fired when unusual user session patterns are detected,
 * helping to identify potential account takeover or session hijacking attempts.
 */
class SecuritySuspiciousSessionEvent extends AdministrativeAlertEvent
{
    /**
     * The user whose session is suspicious
     */
    public User $user;

    /**
     * The session ID that triggered the alert
     */
    public string $sessionId;

    /**
     * The IP address of the suspicious session
     */
    public string $ipAddress;

    /**
     * The user agent string from the suspicious session
     */
    public string $userAgent;

    /**
     * The type of suspicious activity detected
     */
    public string $activityType;

    /**
     * Details about the suspicious activity
     */
    public array $activityDetails;

    /**
     * The previous/known IP address for this user
     */
    public string|null $previousIpAddress;

    /**
     * The previous/known user agent for this user
     */
    public string|null $previousUserAgent;

    /**
     * Whether this appears to be a session hijacking attempt
     */
    public bool $isSessionHijacking;

    /**
     * Whether this appears to be a concurrent session
     */
    public bool $isConcurrentSession;

    /**
     * The location data for the current session (if available)
     */
    public array|null $currentLocation;

    /**
     * The location data for the previous session (if available)
     */
    public array|null $previousLocation;

    /**
     * Create a new event instance
     *
     * @param User $user The user whose session is suspicious
     * @param string $sessionId The session ID
     * @param string $ipAddress The current IP address
     * @param string $userAgent The current user agent
     * @param string $activityType The type of suspicious activity
     * @param array $activityDetails Details about the activity
     * @param string|null $previousIpAddress The previous known IP
     * @param string|null $previousUserAgent The previous known user agent
     * @param array|null $currentLocation Current location data
     * @param array|null $previousLocation Previous location data
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(
        User $user,
        string $sessionId,
        string $ipAddress,
        string $userAgent,
        string $activityType,
        array $activityDetails = [],
        string|null $previousIpAddress = null,
        string|null $previousUserAgent = null,
        array|null $currentLocation = null,
        array|null $previousLocation = null,
        User|null $initiatedBy = null
    ) {
        $this->user = $user;
        $this->sessionId = $sessionId;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->activityType = $activityType;
        $this->activityDetails = $activityDetails;
        $this->previousIpAddress = $previousIpAddress;
        $this->previousUserAgent = $previousUserAgent;
        $this->currentLocation = $currentLocation;
        $this->previousLocation = $previousLocation;
        $this->isSessionHijacking = $this->detectSessionHijacking();
        $this->isConcurrentSession = $this->detectConcurrentSession();

        $context = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'activity_type' => $activityType,
            'activity_details' => $activityDetails,
            'previous_ip_address' => $previousIpAddress,
            'previous_user_agent' => $previousUserAgent,
            'is_session_hijacking' => $this->isSessionHijacking,
            'is_concurrent_session' => $this->isConcurrentSession,
            'current_location' => $currentLocation,
            'previous_location' => $previousLocation,
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
        // Critical for session hijacking or high-risk activity types
        if ($this->isSessionHijacking || $this->isHighRiskActivity()) {
            return self::SEVERITY_CRITICAL;
        } elseif ($this->isConcurrentSession || $this->isMediumRiskActivity()) {
            return self::SEVERITY_HIGH;
        } elseif ($this->isUnusualLocation()) {
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
        if ($this->isSessionHijacking) {
            return "Potential Session Hijacking Detected";
        } elseif ($this->isConcurrentSession) {
            return "Concurrent Session Activity Detected";
        } elseif ($this->isUnusualLocation()) {
            return "Unusual Login Location Detected";
        }
        
        return "Suspicious Session Activity Detected";
    }

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    public function getDescription(): string
    {
        $description = "Suspicious session activity detected for user '{$this->user->email}' (ID: {$this->user->id}). ";
        
        $description .= "Activity type: {$this->activityType}. ";
        
        if ($this->previousIpAddress && $this->previousIpAddress !== $this->ipAddress) {
            $description .= "IP address changed from {$this->previousIpAddress} to {$this->ipAddress}. ";
        }
        
        if ($this->previousUserAgent && $this->previousUserAgent !== $this->userAgent) {
            $description .= "Browser/device changed. ";
        }
        
        if ($this->isSessionHijacking) {
            $description .= "This appears to be a session hijacking attempt. ";
        } elseif ($this->isConcurrentSession) {
            $description .= "Multiple concurrent sessions detected. ";
        }
        
        if ($this->currentLocation && $this->previousLocation) {
            $description .= "Location changed from {$this->previousLocation['city']}, {$this->previousLocation['country']} ";
            $description .= "to {$this->currentLocation['city']}, {$this->currentLocation['country']}. ";
        }
        
        if (!empty($this->activityDetails)) {
            $description .= "Additional details: " . implode(', ', array_keys($this->activityDetails));
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
        return route('admin.users.show', $this->user->id) . '#sessions';
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        // Use critical queue for session hijacking
        if ($this->isSessionHijacking) {
            return 'security-critical-alerts';
        } elseif ($this->isConcurrentSession || $this->isHighRiskActivity()) {
            return 'security-high-alerts';
        }
        
        return 'security-alerts';
    }

    /**
     * Get the email subject for notifications
     *
     * @return string The email subject
     */
    public function getEmailSubject(): string
    {
        if ($this->isSessionHijacking) {
            return "[CRITICAL] Potential Session Hijacking Detected";
        } elseif ($this->isConcurrentSession) {
            return "[HIGH] Concurrent Session Activity";
        } elseif ($this->isUnusualLocation()) {
            return "[MEDIUM] Unusual Login Location";
        }
        
        return "[SECURITY] Suspicious Session Activity";
    }

    /**
     * Detect if this appears to be a session hijacking attempt
     *
     * @return bool True if session hijacking is suspected
     */
    private function detectSessionHijacking(): bool
    {
        // Check for classic session hijacking indicators
        $ipChanged = $this->previousIpAddress && $this->previousIpAddress !== $this->ipAddress;
        $userAgentChanged = $this->previousUserAgent && $this->previousUserAgent !== $this->userAgent;
        $geographicallyImpossible = $this->isGeographicallyImpossible();
        
        return ($ipChanged && $userAgentChanged) || $geographicallyImpossible;
    }

    /**
     * Detect if this appears to be a concurrent session
     *
     * @return bool True if concurrent session is suspected
     */
    private function detectConcurrentSession(): bool
    {
        return isset($this->activityDetails['concurrent_sessions']) && 
               $this->activityDetails['concurrent_sessions'] > 1;
    }

    /**
     * Check if the activity type is high risk
     *
     * @return bool True if high risk activity
     */
    private function isHighRiskActivity(): bool
    {
        $highRiskActivities = [
            'privilege_escalation',
            'admin_access_unusual_time',
            'mass_data_export',
            'sensitive_file_access',
            'configuration_change',
        ];

        return in_array($this->activityType, $highRiskActivities);
    }

    /**
     * Check if the activity type is medium risk
     *
     * @return bool True if medium risk activity
     */
    private function isMediumRiskActivity(): bool
    {
        $mediumRiskActivities = [
            'unusual_time_access',
            'rapid_succession_requests',
            'multiple_failed_actions',
            'atypical_user_behavior',
        ];

        return in_array($this->activityType, $mediumRiskActivities);
    }

    /**
     * Check if the login location is unusual
     *
     * @return bool True if location is unusual
     */
    private function isUnusualLocation(): bool
    {
        if (!$this->currentLocation || !$this->previousLocation) {
            return false;
        }
        
        // Check if country changed
        if ($this->currentLocation['country'] !== $this->previousLocation['country']) {
            return true;
        }
        
        // Check if distance is significant (simplified)
        return $this->calculateDistance($this->currentLocation, $this->previousLocation) > 1000; // 1000km
    }

    /**
     * Check if the location change is geographically impossible
     *
     * @return bool True if geographically impossible
     */
    private function isGeographicallyImpossible(): bool
    {
        if (!$this->currentLocation || !$this->previousLocation) {
            return false;
        }
        
        $distance = $this->calculateDistance($this->currentLocation, $this->previousLocation);
        $timeDiff = $this->getTimeDifference();
        
        // If distance is more than 2000km and time difference is less than 2 hours
        return $distance > 2000 && $timeDiff < 7200; // 2 hours in seconds
    }

    /**
     * Calculate distance between two locations (simplified)
     *
     * @param array $loc1 First location
     * @param array $loc2 Second location
     * @return float Distance in kilometers
     */
    private function calculateDistance(array $loc1, array $loc2): float
    {
        // This is a simplified calculation - in production, use proper geolocation
        $lat1 = $loc1['latitude'] ?? 0;
        $lon1 = $loc1['longitude'] ?? 0;
        $lat2 = $loc2['latitude'] ?? 0;
        $lon2 = $loc2['longitude'] ?? 0;
        
        if ($lat1 == 0 || $lon1 == 0 || $lat2 == 0 || $lon2 == 0) {
            return 0;
        }
        
        // Haversine formula (simplified)
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + 
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        
        return $miles * 1.609344; // Convert to kilometers
    }

    /**
     * Get time difference between sessions
     *
     * @return int Time difference in seconds
     */
    private function getTimeDifference(): int
    {
        return $this->activityDetails['time_since_last_session'] ?? 0;
    }

    /**
     * Determine if the user session should be terminated
     *
     * @return bool True if session should be terminated
     */
    public function shouldTerminateSession(): bool
    {
        return $this->isSessionHijacking || $this->isHighRiskActivity();
    }

    /**
     * Determine if the user should be forced to re-authenticate
     *
     * @return bool True if re-authentication is required
     */
    public function shouldForceReauth(): bool
    {
        return $this->isConcurrentSession || $this->isUnusualLocation() || $this->isMediumRiskActivity();
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
            'risk_score' => $this->calculateRiskScore(),
            'session_anomaly_type' => $this->detectAnomalyType(),
        ];
    }

    /**
     * Calculate the threat level based on various factors
     *
     * @return string The threat level (low, medium, high, critical)
     */
    private function calculateThreatLevel(): string
    {
        if ($this->isSessionHijacking) {
            return 'critical';
        } elseif ($this->isHighRiskActivity() || $this->isConcurrentSession) {
            return 'high';
        } elseif ($this->isUnusualLocation() || $this->isMediumRiskActivity()) {
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
        return $this->isSessionHijacking || $this->isHighRiskActivity();
    }

    /**
     * Get recommended actions based on the threat level
     *
     * @return array List of recommended actions
     */
    private function getRecommendedActions(): array
    {
        $actions = [];
        
        if ($this->isSessionHijacking) {
            $actions[] = 'Immediately terminate all user sessions';
            $actions[] = 'Force password reset';
            $actions[] = 'Contact user directly';
            $actions[] = 'Monitor account for additional suspicious activity';
        }
        
        if ($this->isConcurrentSession) {
            $actions[] = 'Review all active sessions';
            $actions[] = 'Terminate unauthorized sessions';
            $actions[] = 'Notify user of concurrent access';
        }
        
        if ($this->isUnusualLocation()) {
            $actions[] = 'Verify user identity';
            $actions[] = 'Send location verification email';
        }
        
        if ($this->shouldForceReauth()) {
            $actions[] = 'Force re-authentication on next access';
        }
        
        return $actions;
    }

    /**
     * Calculate a risk score for this session activity
     *
     * @return int Risk score from 0-100
     */
    private function calculateRiskScore(): int
    {
        $score = 0;
        
        if ($this->isSessionHijacking) $score += 50;
        if ($this->isHighRiskActivity()) $score += 30;
        if ($this->isConcurrentSession) $score += 25;
        if ($this->isUnusualLocation()) $score += 20;
        if ($this->isMediumRiskActivity()) $score += 15;
        if ($this->previousIpAddress !== $this->ipAddress) $score += 10;
        if ($this->previousUserAgent !== $this->userAgent) $score += 10;
        
        return min(100, $score);
    }

    /**
     * Detect the type of anomaly
     *
     * @return string The anomaly type
     */
    private function detectAnomalyType(): string
    {
        if ($this->isSessionHijacking) {
            return 'session_hijacking';
        } elseif ($this->isConcurrentSession) {
            return 'concurrent_session';
        } elseif ($this->isUnusualLocation()) {
            return 'unusual_location';
        } elseif ($this->isHighRiskActivity()) {
            return 'high_risk_activity';
        } elseif ($this->isMediumRiskActivity()) {
            return 'medium_risk_activity';
        }
        
        return 'unusual_behavior';
    }
}