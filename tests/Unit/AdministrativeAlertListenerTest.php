<?php

namespace Tests\Unit;

use App\Events\AdministrativeAlertEvent;
use App\Events\Security\SecurityFailedLoginEvent;
use App\Listeners\AdministrativeAlertListener;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Exception;
use ReflectionClass;

/**
 * Unit tests for AdministrativeAlertListener base class
 * 
 * These tests focus on the core listener functionality including
 * rate limiting, error handling, retry mechanisms, and logging.
 */
class AdministrativeAlertListenerTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected SecurityFailedLoginEvent $testEvent;
    protected TestAdministrativeAlertListener $testListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::factory()->create(['role' => 'admin']);
        
        $this->testEvent = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0 (Test Browser)',
            attempts: 5,
            isSuspicious: true,
            location: ['city' => 'Test City', 'country' => 'Test Country'],
            initiatedBy: $this->testUser
        );

        $this->testListener = new TestAdministrativeAlertListener();
    }

    /**
     * Test listener default configuration
     */
    public function test_listener_default_configuration(): void
    {
        $this->assertEquals(3, $this->testListener->tries);
        $this->assertEquals([30, 60, 120], $this->testListener->backoff);
        $this->assertEquals('default', $this->testListener->connection);
        $this->assertEquals('administrative-alerts', $this->testListener->queue);
        $this->assertEquals(3, $this->testListener->maxExceptions);
        $this->assertEquals(120, $this->testListener->timeout);
        $this->assertTrue($this->testListener->failOnTimeout);
        $this->assertEquals(60, $this->testListener->retryAfter);
    }

    /**
     * Test event configuration based on severity
     */
    public function test_event_configuration_based_on_severity(): void
    {
        $testCases = [
            [
                'severity' => AdministrativeAlertEvent::SEVERITY_CRITICAL,
                'expected_tries' => 5,
                'expected_backoff' => [15, 30, 60, 120, 300],
                'expected_connection' => 'critical',
                'expected_timeout' => 60,
            ],
            [
                'severity' => AdministrativeAlertEvent::SEVERITY_HIGH,
                'expected_tries' => 3,
                'expected_backoff' => [30, 60, 120],
                'expected_connection' => 'high',
                'expected_timeout' => 90,
            ],
            [
                'severity' => AdministrativeAlertEvent::SEVERITY_MEDIUM,
                'expected_tries' => 2,
                'expected_backoff' => [60, 120],
                'expected_connection' => 'default',
                'expected_timeout' => 120,
            ],
            [
                'severity' => AdministrativeAlertEvent::SEVERITY_LOW,
                'expected_tries' => 1,
                'expected_backoff' => [60],
                'expected_connection' => 'low',
                'expected_timeout' => 180,
            ],
        ];

        foreach ($testCases as $testCase) {
            $event = $this->createEventWithSeverity($testCase['severity']);
            $listener = new TestAdministrativeAlertListener();
            
            $listener->configureForEvent($event);
            
            $this->assertEquals($testCase['expected_tries'], $listener->tries);
            $this->assertEquals($testCase['expected_backoff'], $listener->backoff);
            $this->assertEquals($testCase['expected_connection'], $listener->connection);
            $this->assertEquals($testCase['expected_timeout'], $listener->timeout);
        }
    }

    /**
     * Test rate limiting functionality
     */
    public function test_rate_limiting_functionality(): void
    {
        $cacheKey = $this->invokePrivateMethod($this->testListener, 'getRateLimitKey', [$this->testEvent]);
        
        // Initially, should not be rate limited
        $this->assertFalse($this->invokePrivateMethod($this->testListener, 'isRateLimited', [$this->testEvent]));
        
        // Mark as sent
        $this->invokePrivateMethod($this->testListener, 'markAsSent', [$this->testEvent]);
        
        // Now should be rate limited
        $this->assertTrue($this->invokePrivateMethod($this->testListener, 'isRateLimited', [$this->testEvent]));
        
        // Verify cache key structure
        $this->assertStringContains('TestAdministrativeAlertListener', $cacheKey);
        $this->assertStringContains('SecurityFailedLoginEvent', $cacheKey);
    }

    /**
     * Test rate limiting TTL based on severity
     */
    public function test_rate_limiting_ttl_by_severity(): void
    {
        $testCases = [
            ['severity' => AdministrativeAlertEvent::SEVERITY_CRITICAL, 'expected_ttl' => 60],
            ['severity' => AdministrativeAlertEvent::SEVERITY_HIGH, 'expected_ttl' => 300],
            ['severity' => AdministrativeAlertEvent::SEVERITY_MEDIUM, 'expected_ttl' => 900],
            ['severity' => AdministrativeAlertEvent::SEVERITY_LOW, 'expected_ttl' => 3600],
        ];

        foreach ($testCases as $testCase) {
            $event = $this->createEventWithSeverity($testCase['severity']);
            $ttl = $this->invokePrivateMethod($this->testListener, 'getRateLimitTtl', [$event]);
            
            $this->assertEquals($testCase['expected_ttl'], $ttl);
        }
    }

    /**
     * Test successful event processing
     */
    public function test_successful_event_processing(): void
    {
        Log::shouldReceive('info')->once()->with(
            'Administrative alert processed successfully',
            \Mockery::type('array')
        );

        $this->testListener->handle($this->testEvent);

        $this->assertTrue($this->testListener->processEventCalled);
        $this->assertEquals($this->testEvent, $this->testListener->processedEvent);
    }

    /**
     * Test event processing with rate limiting
     */
    public function test_event_processing_with_rate_limiting(): void
    {
        Log::shouldReceive('info')->once()->with(
            'Administrative alert rate limited',
            \Mockery::type('array')
        );

        // Mark as sent to trigger rate limiting
        $this->invokePrivateMethod($this->testListener, 'markAsSent', [$this->testEvent]);
        
        $this->testListener->handle($this->testEvent);

        // Event should not be processed due to rate limiting
        $this->assertFalse($this->testListener->processEventCalled);
    }

    /**
     * Test event processing with exception
     */
    public function test_event_processing_with_exception(): void
    {
        $exception = new Exception('Test error');
        
        Log::shouldReceive('error')->once()->with(
            'Administrative alert processing error',
            \Mockery::type('array')
        );

        $this->testListener->shouldThrowException = $exception;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test error');

        $this->testListener->handle($this->testEvent);
    }

    /**
     * Test failed method creates fallback notification
     */
    public function test_failed_method_creates_fallback_notification(): void
    {
        $exception = new Exception('Test error');
        
        Log::shouldReceive('critical')->once()->with(
            'Administrative alert listener failed',
            \Mockery::type('array')
        );

        Notification::fake();

        $this->testListener->failed($this->testEvent, $exception);

        // Verify that the failure was logged
        $this->assertTrue($this->testListener->failedMethodCalled);
    }

    /**
     * Test context sanitization
     */
    public function test_context_sanitization(): void
    {
        $sensitiveContext = [
            'normal_field' => 'normal_value',
            'password' => 'secret123',
            'api_key' => 'sk-test-key',
            'long_text' => str_repeat('a', 600), // Longer than 500 chars
            'nested_array' => [
                'secret_key' => 'nested_secret',
                'normal_nested' => 'normal_value',
            ],
        ];

        $sanitized = $this->invokePrivateMethod(
            $this->testListener,
            'sanitizeContext',
            [$sensitiveContext]
        );

        $this->assertEquals('normal_value', $sanitized['normal_field']);
        $this->assertEquals('[REDACTED]', $sanitized['password']);
        $this->assertEquals('[REDACTED]', $sanitized['api_key']);
        $this->assertStringStartsWith('aaaaaaaaaa', $sanitized['long_text']);
        $this->assertStringEndsWith('... [TRUNCATED]', $sanitized['long_text']);
        $this->assertEquals('[REDACTED]', $sanitized['nested_array']['secret_key']);
        $this->assertEquals('normal_value', $sanitized['nested_array']['normal_nested']);
    }

    /**
     * Test event data preparation
     */
    public function test_event_data_preparation(): void
    {
        $eventData = $this->invokePrivateMethod(
            $this->testListener,
            'prepareEventData',
            [$this->testEvent]
        );

        $this->assertArrayHasKey('event_type', $eventData);
        $this->assertArrayHasKey('title', $eventData);
        $this->assertArrayHasKey('description', $eventData);
        $this->assertArrayHasKey('severity', $eventData);
        $this->assertArrayHasKey('severity_display_name', $eventData);
        $this->assertArrayHasKey('severity_color', $eventData);
        $this->assertArrayHasKey('category', $eventData);
        $this->assertArrayHasKey('occurred_at', $eventData);
        $this->assertArrayHasKey('initiated_by', $eventData);
        $this->assertArrayHasKey('action_url', $eventData);
        $this->assertArrayHasKey('context', $eventData);
        $this->assertArrayHasKey('requires_immediate_attention', $eventData);
        $this->assertArrayHasKey('should_send_email', $eventData);

        $this->assertEquals('SecurityFailedLoginEvent', $eventData['event_type']);
        $this->assertEquals('Security', $eventData['category']);
        $this->assertEquals('HIGH', $eventData['severity']);
    }

    /**
     * Test admin users retrieval
     */
    public function test_admin_users_retrieval(): void
    {
        User::factory()->count(3)->create(['role' => 'admin']);
        User::factory()->count(2)->create(['role' => 'manager']);
        User::factory()->count(1)->create(['role' => 'va']);

        $adminUsers = $this->invokePrivateMethod($this->testListener, 'getAdminUsers');

        $this->assertCount(4, $adminUsers); // 3 created + 1 from setUp
        $adminUsers->each(function ($user) {
            $this->assertEquals('admin', $user->role);
        });
    }

    /**
     * Test super admin users retrieval
     */
    public function test_super_admin_users_retrieval(): void
    {
        User::factory()->count(2)->create(['role' => 'admin']);
        User::factory()->count(1)->create(['role' => 'manager']);

        $superAdminUsers = $this->invokePrivateMethod($this->testListener, 'getSuperAdminUsers');

        // For now, returns all admins as super admins
        $this->assertCount(3, $superAdminUsers); // 2 created + 1 from setUp
        $superAdminUsers->each(function ($user) {
            $this->assertEquals('admin', $user->role);
        });
    }

    /**
     * Test job tags
     */
    public function test_job_tags(): void
    {
        $tags = $this->testListener->tags();

        $this->assertIsArray($tags);
        $this->assertContains('admin-alert', $tags);
        $this->assertContains('listener:TestAdministrativeAlertListener', $tags);
    }

    /**
     * Test retry until method
     */
    public function test_retry_until_method(): void
    {
        $retryUntil = $this->testListener->retryUntil();
        $expectedTime = now()->addHours(2);

        $this->assertEqualsWithDelta($expectedTime->timestamp, $retryUntil, 5); // Allow 5 seconds difference
    }

    /**
     * Test sensitive key detection
     */
    public function test_sensitive_key_detection(): void
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'api_key'];

        $testCases = [
            ['key' => 'password', 'expected' => true],
            ['key' => 'api_key', 'expected' => true],
            ['key' => 'secret_token', 'expected' => true],
            ['key' => 'private_key', 'expected' => true],
            ['key' => 'normal_field', 'expected' => false],
            ['key' => 'user_name', 'expected' => false],
            ['key' => 'PASSWORD', 'expected' => true], // Case insensitive
        ];

        foreach ($testCases as $testCase) {
            $result = $this->invokePrivateMethod(
                $this->testListener,
                'containsSensitiveKey',
                [$testCase['key'], $sensitiveKeys]
            );
            
            $this->assertEquals($testCase['expected'], $result, "Failed for key: {$testCase['key']}");
        }
    }

    /**
     * Test escalation logic for critical events
     */
    public function test_escalation_logic_for_critical_events(): void
    {
        $exception = new Exception('Critical error');
        
        Notification::fake();
        Log::shouldReceive('critical')->once()->with(
            'Failed to escalate alert',
            \Mockery::type('array')
        );

        // Test with critical event - should escalate
        $criticalEvent = $this->createEventWithSeverity(AdministrativeAlertEvent::SEVERITY_CRITICAL);
        $this->testListener->failed($criticalEvent, $exception);

        $this->assertTrue($this->testListener->escalationAttempted);
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
}

/**
 * Test implementation of AdministrativeAlertListener for testing purposes
 */
class TestAdministrativeAlertListener extends AdministrativeAlertListener
{
    public bool $processEventCalled = false;
    public ?AdministrativeAlertEvent $processedEvent = null;
    public ?\Throwable $shouldThrowException = null;
    public bool $failedMethodCalled = false;
    public bool $escalationAttempted = false;

    protected function processEvent(AdministrativeAlertEvent $event): void
    {
        $this->processEventCalled = true;
        $this->processedEvent = $event;

        if ($this->shouldThrowException) {
            throw $this->shouldThrowException;
        }
    }

    protected function getRecipients(AdministrativeAlertEvent $event): array
    {
        return User::where('role', 'admin')->get()->all();
    }

    /**
     * Override to test escalation logic
     */
    protected function escalateIfNeeded(AdministrativeAlertEvent $event, \Throwable $exception): void
    {
        $this->escalationAttempted = true;
        parent::escalateIfNeeded($event, $exception);
    }

    /**
     * Override to track failed method calls
     */
    protected function logFailure(AdministrativeAlertEvent $event, \Throwable $exception): void
    {
        $this->failedMethodCalled = true;
        parent::logFailure($event, $exception);
    }
}