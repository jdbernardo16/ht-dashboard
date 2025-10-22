<?php

namespace App\Events;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Abstract base class for all administrative alert events
 * 
 * This class provides common functionality for all administrative alert events
 * including severity levels, queue connection management, and standard properties.
 */
abstract class AdministrativeAlertEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Severity levels for administrative alerts
     */
    public const SEVERITY_CRITICAL = 'CRITICAL';
    public const SEVERITY_HIGH = 'HIGH';
    public const SEVERITY_MEDIUM = 'MEDIUM';
    public const SEVERITY_LOW = 'LOW';

    /**
     * Queue connections based on severity
     */
    private const QUEUE_CONNECTIONS = [
        self::SEVERITY_CRITICAL => 'critical',
        self::SEVERITY_HIGH => 'high',
        self::SEVERITY_MEDIUM => 'default',
        self::SEVERITY_LOW => 'low',
    ];

    /**
     * The user who initiated this event (if applicable)
     */
    public User|null $initiatedBy = null;

    /**
     * The severity level of this event
     */
    public string $severity;

    /**
     * Additional context data for this event
     */
    public array $context = [];

    /**
     * When this event occurred
     */
    public Carbon $occurredAt;

    /**
     * The category of this event (Security, System, UserAction, Business)
     */
    public string $category;

    /**
     * Create a new event instance
     *
     * @param array $context Additional context data for the event
     * @param User|null $initiatedBy The user who initiated this event
     */
    public function __construct(array $context = [], User|null $initiatedBy = null)
    {
        $this->context = $context;
        $this->initiatedBy = $initiatedBy;
        $this->occurredAt = now();
        $this->category = $this->getCategory();
        $this->severity = $this->getSeverity();
    }

    /**
     * Get the category of this event
     *
     * @return string The event category (Security, System, UserAction, Business)
     */
    abstract public function getCategory(): string;

    /**
     * Get the severity level of this event
     *
     * @return string The severity level (CRITICAL, HIGH, MEDIUM, LOW)
     */
    abstract public function getSeverity(): string;

    /**
     * Get the title of this event for display purposes
     *
     * @return string The event title
     */
    abstract public function getTitle(): string;

    /**
     * Get a detailed description of this event
     *
     * @return string The event description
     */
    abstract public function getDescription(): string;

    /**
     * Get the URL for taking action on this event (if applicable)
     *
     * @return string|null The action URL or null if no action is needed
     */
    abstract public function getActionUrl(): string|null;

    /**
     * Get the queue connection based on severity
     *
     * @return string The queue connection name
     */
    public function getConnection(): string
    {
        return self::QUEUE_CONNECTIONS[$this->severity] ?? 'default';
    }

    /**
     * Get the queue name for this event
     *
     * @return string The queue name
     */
    public function getQueue(): string
    {
        return strtolower($this->category) . '-alerts';
    }

    /**
     * Broadcast the event on appropriate channels
     *
     * @return Channel|Channel[] The channels to broadcast on
     */
    public function broadcastOn(): Channel|array
    {
        return new PrivateChannel('administrative-alerts.' . $this->category);
    }

    /**
     * Get the data to broadcast with this event
     *
     * @return array The broadcast data
     */
    public function broadcastWith(): array
    {
        return [
            'event_type' => class_basename($this),
            'category' => $this->category,
            'severity' => $this->severity,
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'occurred_at' => $this->occurredAt->toISOString(),
            'initiated_by' => $this->initiatedBy?->only(['id', 'name', 'email']),
            'action_url' => $this->getActionUrl(),
        ];
    }

    /**
     * Get the display name for the severity level
     *
     * @return string The formatted severity name
     */
    public function getSeverityDisplayName(): string
    {
        return match($this->severity) {
            self::SEVERITY_CRITICAL => 'Critical',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_LOW => 'Low',
            default => 'Unknown',
        };
    }

    /**
     * Get the color associated with the severity level
     *
     * @return string The hex color code
     */
    public function getSeverityColor(): string
    {
        return match($this->severity) {
            self::SEVERITY_CRITICAL => '#dc2626', // red-600
            self::SEVERITY_HIGH => '#ea580c',      // orange-600
            self::SEVERITY_MEDIUM => '#ca8a04',    // amber-600
            self::SEVERITY_LOW => '#16a34a',       // green-600
            default => '#6b7280',                  // gray-500
        };
    }

    /**
     * Check if this event requires immediate attention
     *
     * @return bool True if immediate attention is required
     */
    public function requiresImmediateAttention(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    /**
     * Check if this event should trigger an email notification
     *
     * @return bool True if email notification should be sent
     */
    public function shouldSendEmail(): bool
    {
        return in_array($this->severity, [
            self::SEVERITY_CRITICAL,
            self::SEVERITY_HIGH,
        ]);
    }

    /**
     * Get the retry configuration for this event based on severity
     *
     * @return array The retry configuration
     */
    public function getRetryConfiguration(): array
    {
        return match($this->severity) {
            self::SEVERITY_CRITICAL => [
                'tries' => 5,
                'backoff' => [15, 30, 60, 120, 300],
            ],
            self::SEVERITY_HIGH => [
                'tries' => 3,
                'backoff' => [30, 60, 120],
            ],
            self::SEVERITY_MEDIUM => [
                'tries' => 2,
                'backoff' => [60, 120],
            ],
            self::SEVERITY_LOW => [
                'tries' => 1,
                'backoff' => [60],
            ],
            default => [
                'tries' => 3,
                'backoff' => [30, 60, 120],
            ],
        };
    }
}