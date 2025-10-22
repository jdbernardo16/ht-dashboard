<?php

namespace App\Mail;

use App\Events\AdministrativeAlertEvent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Headers;
use Exception;

/**
 * Base Mailable class for all administrative alert emails
 * 
 * This class handles the email delivery for all types of administrative alert events.
 * It supports dynamic template selection, subject generation, and proper queueing
 * based on event severity.
 */
class AdministrativeAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The administrative alert event
     */
    public AdministrativeAlertEvent $event;

    /**
     * The recipient of this email
     */
    public User $recipient;

    /**
     * Additional data for the email template
     */
    public array $data;

    /**
     * Whether this email should be sent with high priority
     */
    public bool $highPriority;

    /**
     * Create a new message instance.
     *
     * @param AdministrativeAlertEvent $event The alert event
     * @param User $recipient The email recipient
     */
    public function __construct(AdministrativeAlertEvent $event, User $recipient)
    {
        $this->event = $event;
        $this->recipient = $recipient;
        $this->data = array_merge($event->context, [
            'event' => $event,
            'recipient' => $recipient,
            'severity' => $event->getSeverity(),
            'severity_display' => $event->getSeverityDisplayName(),
            'severity_color' => $event->getSeverityColor(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'occurred_at' => $event->occurredAt,
            'action_url' => $event->getActionUrl(),
            'category' => $event->getCategory(),
            'initiated_by' => $event->initiatedBy,
        ]);
        
        // Set high priority for critical events
        $this->highPriority = $event->getSeverity() === AdministrativeAlertEvent::SEVERITY_CRITICAL;
        
        // Configure queue based on event severity
        $this->onQueue($event->getQueue());
        $this->onConnection($event->getConnection());
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->generateSubject(),
            from: new Address(
                Config::get('mail.from.address', 'alerts@hiddentreasures.com'),
                Config::get('mail.from.name', 'Hidden Treasures Dashboard')
            ),
            to: [
                new Address($this->recipient->email, $this->recipient->name)
            ],
        );

        // Set high priority for critical events
        if ($this->highPriority) {
            $envelope->tag('high-priority');
        }

        return $envelope;
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: $this->selectTemplate(),
            with: $this->getViewData(),
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Add event-specific attachments if needed
        if (method_exists($this->event, 'getEmailAttachments')) {
            $eventAttachments = $this->event->getEmailAttachments();
            if (is_array($eventAttachments)) {
                $attachments = array_merge($attachments, $eventAttachments);
            }
        }

        return $attachments;
    }

    /**
     * Get the message headers.
     *
     * @return Headers
     */
    public function headers(): Headers
    {
        $headers = [
            'X-Priority' => $this->highPriority ? '1' : '3',
            'X-MSMail-Priority' => $this->highPriority ? 'High' : 'Normal',
            'X-Alert-Category' => $this->event->getCategory(),
            'X-Alert-Severity' => $this->event->getSeverity(),
            'X-Alert-Event-Type' => class_basename($this->event),
            'X-Alert-Occurred-At' => $this->event->occurredAt->toISOString(),
        ];

        if ($this->event->initiatedBy) {
            $headers['X-Alert-Initiated-By'] = $this->event->initiatedBy->id;
        }

        return new Headers(
            messageId: null,
            references: [],
            custom: $headers
        );
    }

    /**
     * Generate the email subject based on event properties
     *
     * @return string The generated subject
     */
    protected function generateSubject(): string
    {
        $severityPrefix = match($this->event->getSeverity()) {
            AdministrativeAlertEvent::SEVERITY_CRITICAL => '[CRITICAL]',
            AdministrativeAlertEvent::SEVERITY_HIGH => '[HIGH]',
            AdministrativeAlertEvent::SEVERITY_MEDIUM => '[MEDIUM]',
            AdministrativeAlertEvent::SEVERITY_LOW => '[INFO]',
            default => '[ALERT]',
        };

        $categoryName = match($this->event->getCategory()) {
            'Security' => 'Security',
            'System' => 'System',
            'UserAction' => 'User Action',
            'Business' => 'Business',
            default => 'General',
        };

        return sprintf(
            '%s %s Alert: %s',
            $severityPrefix,
            $categoryName,
            $this->event->getTitle()
        );
    }

    /**
     * Select the appropriate email template based on event properties
     *
     * @return string The template path
     */
    protected function selectTemplate(): string
    {
        $baseTemplate = "emails.administrative-alerts.{$this->event->getCategory()}";
        
        // Check for recipient preference
        if (method_exists($this->recipient, 'prefersPlainText') && $this->recipient->prefersPlainText()) {
            $textTemplate = "{$baseTemplate}.text";
            if (View::exists($textTemplate)) {
                return $textTemplate;
            }
        }

        // Check for severity-specific template
        if ($this->event->getSeverity() === AdministrativeAlertEvent::SEVERITY_CRITICAL) {
            $criticalTemplate = "{$baseTemplate}.critical";
            if (View::exists($criticalTemplate)) {
                return $criticalTemplate;
            }
        }

        // Check for event-specific template
        $eventClass = class_basename($this->event);
        $eventTemplate = "{$baseTemplate}.{$eventClass}";
        if (View::exists($eventTemplate)) {
            return $eventTemplate;
        }

        // Fall back to category default
        $defaultTemplate = "{$baseTemplate}.default";
        if (View::exists($defaultTemplate)) {
            return $defaultTemplate;
        }

        // Final fallback to generic template
        return "emails.administrative-alerts.default";
    }

    /**
     * Get the data for the email template
     *
     * @return array The template data
     */
    protected function getViewData(): array
    {
        return array_merge($this->data, [
            'subject' => $this->generateSubject(),
            'isHighPriority' => $this->highPriority,
            'baseUrl' => config('app.url'),
            'appName' => config('app.name', 'Hidden Treasures Dashboard'),
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Administrative alert email failed to send', [
            'event_class' => get_class($this->event),
            'event_id' => $this->event->occurredAt->toISOString(),
            'recipient_id' => $this->recipient->id,
            'recipient_email' => $this->recipient->email,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Create a fallback notification in database
        try {
            $this->recipient->notifications()->create([
                'type' => 'App\Notifications\AdministrativeAlertFailed',
                'notifiable_type' => get_class($this->recipient),
                'notifiable_id' => $this->recipient->id,
                'data' => [
                    'event_type' => class_basename($this->event),
                    'title' => $this->event->getTitle(),
                    'description' => $this->event->getDescription(),
                    'email_error' => $exception->getMessage(),
                    'severity' => $this->event->getSeverity(),
                    'occurred_at' => $this->event->occurredAt->toISOString(),
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Exception $notificationException) {
            Log::error('Failed to create fallback notification', [
                'original_error' => $exception->getMessage(),
                'notification_error' => $notificationException->getMessage(),
            ]);
        }
    }

    /**
     * Determine which queues should be used for each event type
     *
     * @return string
     */
    public function via(): string
    {
        return match($this->event->getSeverity()) {
            AdministrativeAlertEvent::SEVERITY_CRITICAL => 'critical',
            AdministrativeAlertEvent::SEVERITY_HIGH => 'high',
            AdministrativeAlertEvent::SEVERITY_MEDIUM => 'default',
            AdministrativeAlertEvent::SEVERITY_LOW => 'low',
            default => 'default',
        };
    }

    /**
     * Get the retry configuration based on event severity
     *
     * @return array
     */
    public function retryUntil(): \DateTime
    {
        $retryConfig = $this->event->getRetryConfiguration();
        
        return now()->addMinutes(
            match($this->event->getSeverity()) {
                AdministrativeAlertEvent::SEVERITY_CRITICAL => 15,
                AdministrativeAlertEvent::SEVERITY_HIGH => 10,
                AdministrativeAlertEvent::SEVERITY_MEDIUM => 5,
                AdministrativeAlertEvent::SEVERITY_LOW => 2,
                default => 5,
            }
        );
    }
}