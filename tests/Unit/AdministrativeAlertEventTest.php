<?php

namespace Tests\Unit;

use App\Events\AdministrativeAlertEvent;
use App\Events\Security\SecurityFailedLoginEvent;
use App\Models\User;
use Tests\TestCase;
use ReflectionClass;

/**
 * Unit tests for AdministrativeAlertEvent base class
 * 
 * These tests focus on the core functionality of the base event class
 * that all administrative alert events extend from.
 */
class AdministrativeAlertEventTest extends TestCase
{
    protected User $testUser;
    protected array $testContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::factory()->create(['role' => 'admin']);
        $this->testContext = [
            'test_field' => 'test_value',
            'numeric_field' => 123,
            'array_field' => ['item1', 'item2'],
            'boolean_field' => true,
        ];
    }

    /**
     * Test that abstract methods are properly implemented
     */
    public function test_abstract_methods_implementation(): void
    {
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null,
            initiatedBy: $this->testUser
        );

        $this->assertIsString($event->getCategory());
        $this->assertIsString($event->getSeverity());
        $this->assertIsString($event->getTitle());
        $this->assertIsString($event->getDescription());
        $this->assertIsString($event->getActionUrl());
    }

    /**
     * Test severity constants are properly defined
     */
    public function test_severity_constants(): void
    {
        $this->assertEquals('CRITICAL', AdministrativeAlertEvent::SEVERITY_CRITICAL);
        $this->assertEquals('HIGH', AdministrativeAlertEvent::SEVERITY_HIGH);
        $this->assertEquals('MEDIUM', AdministrativeAlertEvent::SEVERITY_MEDIUM);
        $this->assertEquals('LOW', AdministrativeAlertEvent::SEVERITY_LOW);
    }

    /**
     * Test event initialization with context and user
     */
    public function test_event_initialization(): void
    {
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null,
            initiatedBy: $this->testUser
        );

        $this->assertEquals($this->testContext, $event->context);
        $this->assertEquals($this->testUser, $event->initiatedBy);
        $this->assertInstanceOf(\Carbon\Carbon::class, $event->occurredAt);
        $this->assertIsString($event->category);
        $this->assertIsString($event->severity);
    }

    /**
     * Test queue connection mapping based on severity
     */
    public function test_queue_connection_mapping(): void
    {
        $testCases = [
            ['severity' => AdministrativeAlertEvent::SEVERITY_CRITICAL, 'expected' => 'critical'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_HIGH, 'expected' => 'high'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_MEDIUM, 'expected' => 'default'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_LOW, 'expected' => 'low'],
        ];

        foreach ($testCases as $testCase) {
            $event = $this->createEventWithSeverity($testCase['severity']);
            $this->assertEquals($testCase['expected'], $event->getConnection());
        }
    }

    /**
     * Test queue name generation based on category
     */
    public function test_queue_name_generation(): void
    {
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null
        );

        $this->assertEquals('security-alerts', $event->getQueue());
    }

    /**
     * Test severity display names
     */
    public function test_severity_display_names(): void
    {
        $testCases = [
            ['severity' => AdministrativeAlertEvent::SEVERITY_CRITICAL, 'expected' => 'Critical'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_HIGH, 'expected' => 'High'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_MEDIUM, 'expected' => 'Medium'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_LOW, 'expected' => 'Low'],
        ];

        foreach ($testCases as $testCase) {
            $event = $this->createEventWithSeverity($testCase['severity']);
            $this->assertEquals($testCase['expected'], $event->getSeverityDisplayName());
        }
    }

    /**
     * Test severity color codes
     */
    public function test_severity_color_codes(): void
    {
        $testCases = [
            ['severity' => AdministrativeAlertEvent::SEVERITY_CRITICAL, 'expected' => '#dc2626'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_HIGH, 'expected' => '#ea580c'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_MEDIUM, 'expected' => '#ca8a04'],
            ['severity' => AdministrativeAlertEvent::SEVERITY_LOW, 'expected' => '#16a34a'],
        ];

        foreach ($testCases as $testCase) {
            $event = $this->createEventWithSeverity($testCase['severity']);
            $this->assertEquals($testCase['expected'], $event->getSeverityColor());
        }
    }

    /**
     * Test immediate attention requirement
     */
    public function test_immediate_attention_requirement(): void
    {
        $criticalEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_CRITICAL);
        $this->assertTrue($criticalEvent->requiresImmediateAttention());

        $highEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_HIGH);
        $this->assertFalse($highEvent->requiresImmediateAttention());

        $mediumEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_MEDIUM);
        $this->assertFalse($mediumEvent->requiresImmediateAttention());

        $lowEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_LOW);
        $this->assertFalse($lowEvent->requiresImmediateAttention());
    }

    /**
     * Test email sending requirements
     */
    public function test_email_sending_requirements(): void
    {
        $criticalEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_CRITICAL);
        $this->assertTrue($criticalEvent->shouldSendEmail());

        $highEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_HIGH);
        $this->assertTrue($highEvent->shouldSendEmail());

        $mediumEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_MEDIUM);
        $this->assertFalse($mediumEvent->shouldSendEmail());

        $lowEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_LOW);
        $this->assertFalse($lowEvent->shouldSendEmail());
    }

    /**
     * Test retry configuration based on severity
     */
    public function test_retry_configuration(): void
    {
        $criticalEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_CRITICAL);
        $criticalRetryConfig = $criticalEvent->getRetryConfiguration();
        $this->assertEquals(5, $criticalRetryConfig['tries']);
        $this->assertEquals([15, 30, 60, 120, 300], $criticalRetryConfig['backoff']);

        $highEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_HIGH);
        $highRetryConfig = $highEvent->getRetryConfiguration();
        $this->assertEquals(3, $highRetryConfig['tries']);
        $this->assertEquals([30, 60, 120], $highRetryConfig['backoff']);

        $mediumEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_MEDIUM);
        $mediumRetryConfig = $mediumEvent->getRetryConfiguration();
        $this->assertEquals(2, $mediumRetryConfig['tries']);
        $this->assertEquals([60, 120], $mediumRetryConfig['backoff']);

        $lowEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_LOW);
        $lowRetryConfig = $lowEvent->getRetryConfiguration();
        $this->assertEquals(1, $lowRetryConfig['tries']);
        $this->assertEquals([60], $lowRetryConfig['backoff']);
    }

    /**
     * Test broadcast channel generation
     */
    public function test_broadcast_channel_generation(): void
    {
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null
        );

        $channel = $event->broadcastOn();
        $this->assertEquals('administrative-alerts.Security', $channel->name);
    }

    /**
     * Test broadcast data structure
     */
    public function test_broadcast_data_structure(): void
    {
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null,
            initiatedBy: $this->testUser
        );

        $broadcastData = $event->broadcastWith();

        $this->assertArrayHasKey('event_type', $broadcastData);
        $this->assertArrayHasKey('category', $broadcastData);
        $this->assertArrayHasKey('severity', $broadcastData);
        $this->assertArrayHasKey('title', $broadcastData);
        $this->assertArrayHasKey('description', $broadcastData);
        $this->assertArrayHasKey('occurred_at', $broadcastData);
        $this->assertArrayHasKey('initiated_by', $broadcastData);
        $this->assertArrayHasKey('action_url', $broadcastData);

        $this->assertEquals('SecurityFailedLoginEvent', $broadcastData['event_type']);
        $this->assertEquals('Security', $broadcastData['category']);
        $this->assertEquals($event->getTitle(), $broadcastData['title']);
        $this->assertEquals($event->getDescription(), $broadcastData['description']);
        $this->assertEquals($event->occurredAt->toISOString(), $broadcastData['occurred_at']);
        $this->assertEquals($event->getActionUrl(), $broadcastData['action_url']);
    }

    /**
     * Test context data handling
     */
    public function test_context_data_handling(): void
    {
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null,
            initiatedBy: $this->testUser
        );

        $this->assertArrayHasKey('email', $event->context);
        $this->assertArrayHasKey('ip_address', $event->context);
        $this->assertArrayHasKey('user_agent', $event->context);
        $this->assertArrayHasKey('attempts', $event->context);
        $this->assertArrayHasKey('is_suspicious', $event->context);
        $this->assertArrayHasKey('location', $event->context);

        $this->assertEquals('test@example.com', $event->context['email']);
        $this->assertEquals('192.168.1.1', $event->context['ip_address']);
        $this->assertEquals('Mozilla/5.0...', $event->context['user_agent']);
        $this->assertEquals(3, $event->context['attempts']);
        $this->assertFalse($event->context['is_suspicious']);
        $this->assertNull($event->context['location']);
    }

    /**
     * Test event timestamp accuracy
     */
    public function test_event_timestamp_accuracy(): void
    {
        $beforeEvent = now();
        
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null
        );
        
        $afterEvent = now();

        $this->assertGreaterThanOrEqual($beforeEvent, $event->occurredAt);
        $this->assertLessThanOrEqual($afterEvent, $event->occurredAt);
        $this->assertLessThan(5, $event->occurredAt->diffInSeconds(now())); // Within 5 seconds
    }

    /**
     * Test event with null initiated by user
     */
    public function test_event_with_null_initiated_by(): void
    {
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null,
            initiatedBy: null
        );

        $this->assertNull($event->initiatedBy);
        
        $broadcastData = $event->broadcastWith();
        $this->assertNull($broadcastData['initiated_by']);
    }

    /**
     * Test event serialization
     */
    public function test_event_serialization(): void
    {
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null,
            initiatedBy: $this->testUser
        );

        $serialized = serialize($event);
        $unserialized = unserialize($serialized);

        $this->assertEquals($event->getTitle(), $unserialized->getTitle());
        $this->assertEquals($event->getDescription(), $unserialized->getDescription());
        $this->assertEquals($event->getSeverity(), $unserialized->getSeverity());
        $this->assertEquals($event->getCategory(), $unserialized->getCategory());
    }

    /**
     * Helper method to create an event with a specific severity
     * 
     * @param string $severity The severity level
     * @return AdministrativeAlertEvent
     */
    private function createEventWithSeverity(string $severity): AdministrativeAlertEvent
    {
        // Create a mock event that extends AdministrativeAlertEvent
        // with the specified severity
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
}