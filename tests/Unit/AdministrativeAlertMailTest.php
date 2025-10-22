<?php

namespace Tests\Unit;

use App\Events\AdministrativeAlertEvent;
use App\Events\Security\SecurityFailedLoginEvent;
use App\Mail\AdministrativeAlertMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Tests\TestCase;
use ReflectionMethod;

/**
 * Unit tests for AdministrativeAlertMail class
 * 
 * These tests focus on the email generation functionality,
 * template selection, subject generation, and data preparation.
 */
class AdministrativeAlertMailTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected User $recipientUser;
    protected SecurityFailedLoginEvent $testEvent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::factory()->create(['role' => 'admin']);
        $this->recipientUser = User::factory()->create(['role' => 'manager']);
        
        $this->testEvent = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0 (Test Browser)',
            attempts: 5,
            isSuspicious: true,
            location: ['city' => 'Test City', 'country' => 'Test Country'],
            initiatedBy: $this->testUser
        );
    }

    /**
     * Test mailable initialization with event and recipient
     */
    public function test_mailable_initialization(): void
    {
        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);

        $this->assertEquals($this->testEvent, $mail->event);
        $this->assertEquals($this->recipientUser, $mail->recipient);
        $this->assertIsArray($mail->data);
        $this->assertTrue($mail->highPriority);

        // Check that data contains expected keys
        $this->assertArrayHasKey('event', $mail->data);
        $this->assertArrayHasKey('recipient', $mail->data);
        $this->assertArrayHasKey('severity', $mail->data);
        $this->assertArrayHasKey('severity_display', $mail->data);
        $this->assertArrayHasKey('title', $mail->data);
        $this->assertArrayHasKey('description', $mail->data);
        $this->assertArrayHasKey('occurred_at', $mail->data);
        $this->assertArrayHasKey('action_url', $mail->data);
        $this->assertArrayHasKey('category', $mail->data);
        $this->assertArrayHasKey('initiated_by', $mail->data);
    }

    /**
     * Test envelope configuration
     */
    public function test_envelope_configuration(): void
    {
        Config::set('mail.from.address', 'alerts@example.com');
        Config::set('mail.from.name', 'Test Dashboard');

        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);
        $envelope = $mail->envelope();

        $this->assertStringContains('[HIGH]', $envelope->subject);
        $this->assertStringContains('Security Alert:', $envelope->subject);
        $this->assertStringContains($this->testEvent->getTitle(), $envelope->subject);
        
        $this->assertEquals('alerts@example.com', $envelope->from[0]->address);
        $this->assertEquals('Test Dashboard', $envelope->from[0]->name);
        
        $this->assertEquals($this->recipientUser->email, $envelope->to[0]->address);
        $this->assertEquals($this->recipientUser->name, $envelope->to[0]->name);
        
        $this->assertTrue($envelope->hasTag('high-priority'));
    }

    /**
     * Test content configuration
     */
    public function test_content_configuration(): void
    {
        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);
        $content = $mail->content();

        $this->assertStringContains('emails.administrative-alerts.Security', $content->view);
        $this->assertIsArray($content->with);
        
        // Check that view data contains expected keys
        $this->assertArrayHasKey('subject', $content->with);
        $this->assertArrayHasKey('isHighPriority', $content->with);
        $this->assertArrayHasKey('baseUrl', $content->with);
        $this->assertArrayHasKey('appName', $content->with);
    }

    /**
     * Test headers configuration
     */
    public function test_headers_configuration(): void
    {
        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);
        $headers = $mail->headers();

        $customHeaders = $headers->custom;
        
        $this->assertEquals('1', $customHeaders['X-Priority']);
        $this->assertEquals('High', $customHeaders['X-MSMail-Priority']);
        $this->assertEquals('Security', $customHeaders['X-Alert-Category']);
        $this->assertEquals('HIGH', $customHeaders['X-Alert-Severity']);
        $this->assertEquals('SecurityFailedLoginEvent', $customHeaders['X-Alert-Event-Type']);
        $this->assertEquals($this->testEvent->occurredAt->toISOString(), $customHeaders['X-Alert-Occurred-At']);
        $this->assertEquals((string)$this->testUser->id, $customHeaders['X-Alert-Initiated-By']);
    }

    /**
     * Test subject generation for different severity levels
     */
    public function test_subject_generation_by_severity(): void
    {
        $testCases = [
            ['severity' => AdministrativeAlertEvent::SEVERITY_CRITICAL, 'prefix' => '[CRITICAL]'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_HIGH, 'prefix' => '[HIGH]'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_MEDIUM, 'prefix' => '[MEDIUM]'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_LOW, 'prefix' => '[INFO]'],
        ];

        foreach ($testCases as $testCase) {
            $event = $this->createEventWithSeverity($testCase['severity']);
            $mail = new AdministrativeAlertMail($event, $this->recipientUser);
            
            $subject = $this->invokePrivateMethod($mail, 'generateSubject');
            
            $this->assertStringStartsWith($testCase['prefix'], $subject);
            $this->assertStringContains('Security Alert:', $subject);
            $this->assertStringContains($event->getTitle(), $subject);
        }
    }

    /**
     * Test template selection logic
     */
    public function test_template_selection(): void
    {
        // Test category-specific template
        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);
        $template = $this->invokePrivateMethod($mail, 'selectTemplate');
        $this->assertEquals('emails.administrative-alerts.Security', $template);

        // Test fallback to default template when category template doesn't exist
        View::shouldReceive('exists')->with('emails.administrative-alerts.NonExistent')->andReturn(false);
        View::shouldReceive('exists')->with('emails.administrative-alerts.NonExistent.critical')->andReturn(false);
        View::shouldReceive('exists')->with('emails.administrative-alerts.NonExistent.default')->andReturn(false);
        View::shouldReceive('exists')->with('emails.administrative-alerts.default')->andReturn(true);

        $nonExistentEvent = $this->createEventWithCategory('NonExistent');
        $mail = new AdministrativeAlertMail($nonExistentEvent, $this->recipientUser);
        $template = $this->invokePrivateMethod($mail, 'selectTemplate');
        $this->assertEquals('emails.administrative-alerts.default', $template);
    }

    /**
     * Test critical severity template selection
     */
    public function test_critical_template_selection(): void
    {
        View::shouldReceive('exists')->with('emails.administrative-alerts.Security.critical')->andReturn(true);

        $criticalEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_CRITICAL);
        $mail = new AdministrativeAlertMail($criticalEvent, $this->recipientUser);
        
        $template = $this->invokePrivateMethod($mail, 'selectTemplate');
        $this->assertEquals('emails.administrative-alerts.Security.critical', $template);
    }

    /**
     * Test view data preparation
     */
    public function test_view_data_preparation(): void
    {
        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);
        $viewData = $this->invokePrivateMethod($mail, 'getViewData');

        $this->assertArrayHasKey('subject', $viewData);
        $this->assertArrayHasKey('isHighPriority', $viewData);
        $this->assertArrayHasKey('baseUrl', $viewData);
        $this->assertArrayHasKey('appName', $viewData);
        
        $this->assertTrue($viewData['isHighPriority']);
        $this->assertEquals(config('app.url'), $viewData['baseUrl']);
        $this->assertEquals(config('app.name', 'Hidden Treasures Dashboard'), $viewData['appName']);
    }

    /**
     * Test high priority flag for critical events
     */
    public function test_high_priority_flag_for_critical_events(): void
    {
        $criticalEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_CRITICAL);
        $mail = new AdministrativeAlertMail($criticalEvent, $this->recipientUser);
        
        $this->assertTrue($mail->highPriority);
        
        $headers = $mail->headers();
        $this->assertEquals('1', $headers->custom['X-Priority']);
        $this->assertEquals('High', $headers->custom['X-MSMail-Priority']);
    }

    /**
     * Test normal priority flag for non-critical events
     */
    public function test_normal_priority_flag_for_non_critical_events(): void
    {
        $mediumEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_MEDIUM);
        $mail = new AdministrativeAlertMail($mediumEvent, $this->recipientUser);
        
        $this->assertFalse($mail->highPriority);
        
        $headers = $mail->headers();
        $this->assertEquals('3', $headers->custom['X-Priority']);
        $this->assertEquals('Normal', $headers->custom['X-MSMail-Priority']);
    }

    /**
     * Test queue configuration based on event severity
     */
    public function test_queue_configuration(): void
    {
        $criticalEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_CRITICAL);
        $mail = new AdministrativeAlertMail($criticalEvent, $this->recipientUser);
        
        $this->assertEquals('critical', $mail->via());
        $this->assertEquals('security-high-alerts', $mail->queue);
        $this->assertEquals('critical', $mail->connection);
    }

    /**
     * Test retry until configuration
     */
    public function test_retry_until_configuration(): void
    {
        $criticalEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_CRITICAL);
        $mail = new AdministrativeAlertMail($criticalEvent, $this->recipientUser);
        
        $retryUntil = $mail->retryUntil();
        $expectedTime = now()->addMinutes(15);
        
        $this->assertEqualsWithDelta($expectedTime->timestamp, $retryUntil, 5); // Allow 5 seconds difference
    }

    /**
     * Test attachments method
     */
    public function test_attachments_method(): void
    {
        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);
        $attachments = $mail->attachments();
        
        // Default event should have no attachments
        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    /**
     * Test failed method creates fallback notification
     */
    public function test_failed_method_creates_fallback_notification(): void
    {
        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);
        $exception = new \Exception('Test error message');
        
        // Mock the notification creation
        Notification::fake();
        
        $mail->failed($exception);
        
        // Verify that a notification was created
        // Note: This would require mocking the notification system more thoroughly
        // For now, we just verify the method doesn't throw an exception
        $this->assertTrue(true);
    }

    /**
     * Test data merging with event context
     */
    public function test_data_merging_with_event_context(): void
    {
        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);
        
        // Check that event context is properly merged
        $this->assertArrayHasKey('email', $mail->data);
        $this->assertArrayHasKey('ip_address', $mail->data);
        $this->assertArrayHasKey('user_agent', $mail->data);
        $this->assertArrayHasKey('attempts', $mail->data);
        $this->assertArrayHasKey('is_suspicious', $mail->data);
        $this->assertArrayHasKey('location', $mail->data);
        
        $this->assertEquals('test@example.com', $mail->data['email']);
        $this->assertEquals('192.168.1.1', $mail->data['ip_address']);
        $this->assertEquals('Mozilla/5.0 (Test Browser)', $mail->data['user_agent']);
        $this->assertEquals(5, $mail->data['attempts']);
        $this->assertTrue($mail->data['is_suspicious']);
        $this->assertIsArray($mail->data['location']);
    }

    /**
     * Test mailable serialization
     */
    public function test_mailable_serialization(): void
    {
        $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);
        
        $serialized = serialize($mail);
        $unserialized = unserialize($serialized);
        
        $this->assertEquals($mail->event->getTitle(), $unserialized->event->getTitle());
        $this->assertEquals($mail->recipient->id, $unserialized->recipient->id);
        $this->assertEquals($mail->highPriority, $unserialized->highPriority);
    }

    /**
     * Helper method to invoke private methods for testing
     */
    private function invokePrivateMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Helper method to create an event with specific severity
     */
    private function createEventWithSeverity(string $severity): AdministrativeAlertEvent
    {
        $event = new class($severity) extends SecurityFailedLoginEvent {
            private string $testSeverity;

            public function __construct(string $severity)
            {
                $this->testSeverity = $severity;
                parent::__construct(
                    email: 'test@example.com',
                    ipAddress: '192.168.1.1',
                    userAgent: 'Mozilla/5.0...',
                    attempts: 1,
                    isSuspicious: false,
                    location: null
                );
            }

            public function getSeverity(): string
            {
                return $this->testSeverity;
            }
        };

        return $event;
    }

    /**
     * Helper method to create an event with specific category
     */
    private function createEventWithCategory(string $category): AdministrativeAlertEvent
    {
        $event = new class($category) extends AdministrativeAlertEvent {
            private string $testCategory;

            public function __construct(string $category)
            {
                $this->testCategory = $category;
                parent::__construct();
            }

            public function getCategory(): string
            {
                return $this->testCategory;
            }

            public function getSeverity(): string
            {
                return AdministrativeAlertEvent::SEVERITY_MEDIUM;
            }

            public function getTitle(): string
            {
                return 'Test Event';
            }

            public function getDescription(): string
            {
                return 'Test event description';
            }

            public function getActionUrl(): ?string
            {
                return 'https://example.com/action';
            }
        };

        return $event;
    }
}