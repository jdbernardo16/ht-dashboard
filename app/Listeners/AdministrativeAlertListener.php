<?php

namespace App\Listeners;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Abstract base class for all administrative alert listeners
 * 
 * This class provides common functionality for handling administrative alert events,
 * including error handling, retry mechanisms, rate limiting, and logging capabilities.
 * All specific administrative alert listeners should extend this class.
 */
abstract class AdministrativeAlertListener implements ShouldQueue
{
    /**
     * The number of times the job may be attempted.
     */
    public int $tries;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int|array $backoff;

    /**
     * The queue connection that should handle the job.
     */
    public string $connection;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * The maximum number of seconds a child job may run.
     */
    public int $timeout = 120;

    /**
     * Indicates if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    /**
     * The number of seconds the job can run before timing out (per attempt).
     */
    public int $retryAfter = 60;

    /**
     * Create a new listener instance.
     */
    public function __construct()
    {
        // Default retry configuration
        $this->tries = 3;
        $this->backoff = [30, 60, 120];
        $this->connection = 'default';
        $this->queue = 'administrative-alerts';
    }

    /**
     * Handle the administrative alert event.
     *
     * @param AdministrativeAlertEvent $event The event to handle
     * @return void
     */
    public function handle(AdministrativeAlertEvent $event): void
    {
        try {
            // Check if this event should be rate limited
            if ($this->isRateLimited($event)) {
                Log::info('Administrative alert rate limited', [
                    'event' => get_class($event),
                    'event_id' => $event->occurredAt->timestamp,
                ]);
                return;
            }

            // Mark this event as sent for rate limiting
            $this->markAsSent($event);

            // Process the event
            $this->processEvent($event);

            // Log successful processing
            $this->logSuccess($event);

        } catch (Throwable $exception) {
            // Log the error
            $this->logError($event, $exception);

            // Re-throw to trigger queue retry mechanism
            throw $exception;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param AdministrativeAlertEvent $event The event that failed
     * @param Throwable $exception The exception that caused the failure
     * @return void
     */
    public function failed(AdministrativeAlertEvent $event, Throwable $exception): void
    {
        // Log the failure
        $this->logFailure($event, $exception);

        // Create fallback notification if configured
        $this->createFallbackNotification($event, $exception);

        // Escalate if needed
        $this->escalateIfNeeded($event, $exception);
    }

    /**
     * Process the administrative alert event.
     * This method should be implemented by concrete listener classes.
     *
     * @param AdministrativeAlertEvent $event The event to process
     * @return void
     */
    abstract protected function processEvent(AdministrativeAlertEvent $event): void;

    /**
     * Get the recipients for this alert.
     * This method should be implemented by concrete listener classes.
     *
     * @param AdministrativeAlertEvent $event The event
     * @return array Array of User objects
     */
    abstract protected function getRecipients(AdministrativeAlertEvent $event): array;

    /**
     * Configure the listener based on the event severity.
     *
     * @param AdministrativeAlertEvent $event The event
     * @return void
     */
    protected function configureForEvent(AdministrativeAlertEvent $event): void
    {
        $retryConfig = $event->getRetryConfiguration();
        
        $this->tries = $retryConfig['tries'];
        $this->backoff = $retryConfig['backoff'];
        $this->connection = $event->getConnection();
        $this->queue = $event->getQueue();

        // Adjust timeout based on severity
        $this->timeout = match($event->severity) {
            AdministrativeAlertEvent::SEVERITY_CRITICAL => 60,
            AdministrativeAlertEvent::SEVERITY_HIGH => 90,
            AdministrativeAlertEvent::SEVERITY_MEDIUM => 120,
            AdministrativeAlertEvent::SEVERITY_LOW => 180,
            default => 120,
        };
    }

    /**
     * Check if this event should be rate limited.
     *
     * @param AdministrativeAlertEvent $event The event to check
     * @return bool True if rate limited
     */
    protected function isRateLimited(AdministrativeAlertEvent $event): bool
    {
        $key = $this->getRateLimitKey($event);
        $ttl = $this->getRateLimitTtl($event);

        return Cache::has($key);
    }

    /**
     * Mark this event as sent for rate limiting purposes.
     *
     * @param AdministrativeAlertEvent $event The event to mark
     * @return void
     */
    protected function markAsSent(AdministrativeAlertEvent $event): void
    {
        $key = $this->getRateLimitKey($event);
        $ttl = $this->getRateLimitTtl($event);

        Cache::put($key, true, $ttl);
    }

    /**
     * Get the rate limit key for this event.
     *
     * @param AdministrativeAlertEvent $event The event
     * @return string The cache key
     */
    protected function getRateLimitKey(AdministrativeAlertEvent $event): string
    {
        $listenerClass = class_basename(static::class);
        return "admin_alerts:{$listenerClass}:" . get_class($event);
    }

    /**
     * Get the rate limit TTL for this event based on severity.
     *
     * @param AdministrativeAlertEvent $event The event
     * @return int TTL in seconds
     */
    protected function getRateLimitTtl(AdministrativeAlertEvent $event): int
    {
        return match($event->severity) {
            AdministrativeAlertEvent::SEVERITY_CRITICAL => 60,   // 1 minute
            AdministrativeAlertEvent::SEVERITY_HIGH => 300,      // 5 minutes
            AdministrativeAlertEvent::SEVERITY_MEDIUM => 900,    // 15 minutes
            AdministrativeAlertEvent::SEVERITY_LOW => 3600,      // 1 hour
            default => 300,
        };
    }

    /**
     * Log successful event processing.
     *
     * @param AdministrativeAlertEvent $event The event
     * @return void
     */
    protected function logSuccess(AdministrativeAlertEvent $event): void
    {
        Log::info('Administrative alert processed successfully', [
            'event' => get_class($event),
            'listener' => static::class,
            'event_id' => $event->occurredAt->timestamp,
            'severity' => $event->severity,
            'category' => $event->category,
        ]);
    }

    /**
     * Log an error that occurred during processing.
     *
     * @param AdministrativeAlertEvent $event The event
     * @param Throwable $exception The exception
     * @return void
     */
    protected function logError(AdministrativeAlertEvent $event, Throwable $exception): void
    {
        Log::error('Administrative alert processing error', [
            'event' => get_class($event),
            'listener' => static::class,
            'event_id' => $event->occurredAt->timestamp,
            'severity' => $event->severity,
            'category' => $event->category,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Log a job failure.
     *
     * @param AdministrativeAlertEvent $event The event
     * @param Throwable $exception The exception
     * @return void
     */
    protected function logFailure(AdministrativeAlertEvent $event, Throwable $exception): void
    {
        Log::critical('Administrative alert listener failed', [
            'event' => get_class($event),
            'listener' => static::class,
            'event_id' => $event->occurredAt->timestamp,
            'severity' => $event->severity,
            'category' => $event->category,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);
    }

    /**
     * Create a fallback notification when the primary notification fails.
     *
     * @param AdministrativeAlertEvent $event The event
     * @param Throwable $exception The exception
     * @return void
     */
    protected function createFallbackNotification(AdministrativeAlertEvent $event, Throwable $exception): void
    {
        try {
            // Create a simple database notification as fallback
            $recipients = $this->getAdminUsers();
            
            foreach ($recipients as $recipient) {
                $recipient->notify(
                    new \App\Notifications\AdministrativeAlertFailedNotification(
                        $event,
                        $exception,
                        static::class
                    )
                );
            }
        } catch (Throwable $fallbackException) {
            Log::critical('Failed to create fallback notification', [
                'original_error' => $exception->getMessage(),
                'fallback_error' => $fallbackException->getMessage(),
                'event' => get_class($event),
            ]);
        }
    }

    /**
     * Escalate the alert if needed based on severity and failure count.
     *
     * @param AdministrativeAlertEvent $event The event
     * @param Throwable $exception The exception
     * @return void
     */
    protected function escalateIfNeeded(AdministrativeAlertEvent $event, Throwable $exception): void
    {
        // Escalate critical events or repeated failures
        if ($event->severity === AdministrativeAlertEvent::SEVERITY_CRITICAL || 
            $this->attempts() >= $this->tries) {
            
            try {
                // Create escalation notification
                $recipients = $this->getSuperAdminUsers();
                
                foreach ($recipients as $recipient) {
                    $recipient->notify(
                        new \App\Notifications\AdministrativeAlertEscalationNotification(
                            $event,
                            $exception,
                            static::class,
                            $this->attempts()
                        )
                    );
                }
            } catch (Throwable $escalationException) {
                Log::critical('Failed to escalate alert', [
                    'escalation_error' => $escalationException->getMessage(),
                    'original_error' => $exception->getMessage(),
                    'event' => get_class($event),
                ]);
            }
        }
    }

    /**
     * Get admin users for notifications.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getAdminUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('role', 'admin')->get();
    }

    /**
     * Get super admin users for escalations.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getSuperAdminUsers(): \Illuminate\Database\Eloquent\Collection
    {
        // For now, return all admins. In a real system, you might have a super_admin role
        return User::where('role', 'admin')->get();
    }

    /**
     * Prepare event data for Mailable class.
     *
     * @param AdministrativeAlertEvent $event The event
     * @return array Prepared data
     */
    protected function prepareEventData(AdministrativeAlertEvent $event): array
    {
        return [
            'event_type' => class_basename($event),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'severity' => $event->severity,
            'severity_display_name' => $event->getSeverityDisplayName(),
            'severity_color' => $event->getSeverityColor(),
            'category' => $event->category,
            'occurred_at' => $event->occurredAt,
            'initiated_by' => $event->initiatedBy,
            'action_url' => $event->getActionUrl(),
            'context' => $this->sanitizeContext($event->context),
            'requires_immediate_attention' => $event->requiresImmediateAttention(),
            'should_send_email' => $event->shouldSendEmail(),
        ];
    }

    /**
     * Sanitize context data for safe display in notifications.
     *
     * @param array $context The raw context data
     * @return array Sanitized context data
     */
    protected function sanitizeContext(array $context): array
    {
        $sensitiveKeys = [
            'password', 'token', 'secret', 'key', 'api_key', 'private_key',
            'credit_card', 'ssn', 'social_security', 'bank_account'
        ];

        $sanitized = [];
        
        foreach ($context as $key => $value) {
            if (is_string($key) && $this->containsSensitiveKey($key, $sensitiveKeys)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeContext($value);
            } elseif (is_string($value) && strlen($value) > 500) {
                $sanitized[$key] = substr($value, 0, 500) . '... [TRUNCATED]';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Check if a key contains sensitive information.
     *
     * @param string $key The key to check
     * @param array $sensitiveKeys Array of sensitive key patterns
     * @return bool True if key is sensitive
     */
    private function containsSensitiveKey(string $key, array $sensitiveKeys): bool
    {
        $key = strtolower($key);
        
        foreach ($sensitiveKeys as $sensitiveKey) {
            if (str_contains($key, strtolower($sensitiveKey))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get the retry delay for the next attempt.
     *
     * @param int $attempt The current attempt number
     * @return int Delay in seconds
     */
    public function retryUntil(): int
    {
        return now()->addHours(2)->timestamp;
    }

    /**
     * Get the tags for the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return [
            'admin-alert',
            'listener:' . class_basename(static::class),
        ];
    }
}