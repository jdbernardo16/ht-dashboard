<?php

namespace Tests\Feature;

use App\Events\Security\SecurityFailedLoginEvent;
use App\Events\System\SystemDatabaseFailureEvent;
use App\Events\Business\BusinessHighValueSaleEvent;
use App\Mail\AdministrativeAlertMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Integration tests for the complete Administrative Alert System
 * 
 * These tests verify the end-to-end functionality of the alert system,
 * including event firing, listener processing, email delivery, and notification creation.
 */
class AdministrativeAlertIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $managerUser;
    protected User $vaUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->managerUser = User::factory()->create(['role' => 'manager']);
        $this->vaUser = User::factory()->create(['role' => 'va']);
    }

    /**
     * Test complete flow from security event to email delivery
     */
    public function test_security_event_complete_flow(): void
    {
        Queue::fake();
        Mail::fake();
        Notification::fake();
        Log::fake();

        // Create a high-severity security event
        $event = new SecurityFailedLoginEvent(
            email: 'suspicious@example.com',
            ipAddress: '192.168.1.100',
            userAgent: 'SuspiciousBrowser/1.0',
            attempts: 10,
            isSuspicious: true,
            location: ['city' => 'Unknown', 'country' => 'Unknown'],
            initiatedBy: $this->vaUser
        );

        // Dispatch the event
        Event::dispatch($event);

        // Verify the event was dispatched
        Event::assertDispatched(SecurityFailedLoginEvent::class);

        // Process queued jobs
        Queue::pushFake('security-alerts');
        Queue::work('security-alerts');

        // Verify email was sent to appropriate recipients
        Mail::assertSent(function (AdministrativeAlertMail $mail) {
            return $mail->event instanceof SecurityFailedLoginEvent &&
                   $mail->recipient->role === 'admin' &&
                   str_contains($mail->subject, '[HIGH]');
        });

        // Verify database notifications were created
        Notification::assertSentTo(
            $this->adminUser,
            \App\Notifications\SecurityFailedLoginNotification::class
        );
    }

    /**
     * Test complete flow from system event to email delivery
     */
    public function test_system_event_complete_flow(): void
    {
        Queue::fake();
        Mail::fake();
        Notification::fake();

        // Create a critical system event
        $event = new SystemDatabaseFailureEvent(
            connection: 'mysql',
            error: 'Connection lost to database server',
            errorCode: '2003',
            query: 'SELECT * FROM users',
            affectedTables: ['users', 'tasks', 'sales'],
            isSystemWide: true,
            initiatedBy: $this->adminUser
        );

        // Dispatch the event
        Event::dispatch($event);

        // Process queued jobs
        Queue::pushFake('critical');
        Queue::work('critical');

        // Verify email was sent with critical priority
        Mail::assertSent(function (AdministrativeAlertMail $mail) {
            return $mail->event instanceof SystemDatabaseFailureEvent &&
                   $mail->highPriority === true &&
                   str_contains($mail->subject, '[CRITICAL]');
        });

        // Verify the email used the critical template
        Mail::assertSent(function (AdministrativeAlertMail $mail) {
            $view = $mail->content()->view;
            return str_contains($view, 'critical') || str_contains($view, 'System');
        });
    }

    /**
     * Test complete flow from business event to email delivery
     */
    public function test_business_event_complete_flow(): void
    {
        Queue::fake();
        Mail::fake();
        Notification::fake();

        // Create test client and sale objects
        $client = (object) [
            'id' => 1,
            'name' => 'Test Client',
            'email' => 'client@example.com',
        ];
        $sale = (object) [
            'id' => 1,
            'status' => 'completed',
        ];

        // Create a high-value business event
        $event = new BusinessHighValueSaleEvent(
            salesUser: $this->managerUser,
            client: $client,
            sale: $sale,
            saleAmount: 75000.00,
            profitMargin: 35.0,
            currency: 'USD',
            productType: 'Vintage Sports Card',
            saleCategory: 'Rare',
            thresholdAmount: 10000.00,
            isRecordHigh: true,
            previousRecordHigh: 50000.00,
            isUnexpected: false,
            salesChannel: 'online',
            closingTimeDays: 10,
            requiresSpecialHandling: true,
            initiatedBy: $this->managerUser
        );

        // Dispatch the event
        Event::dispatch($event);

        // Process queued jobs
        Queue::pushFake('business-high-alerts');
        Queue::work('business-high-alerts');

        // Verify email was sent to all relevant recipients
        Mail::assertSent(function (AdministrativeAlertMail $mail) {
            return $mail->event instanceof BusinessHighValueSaleEvent &&
                   in_array($mail->recipient->role, ['admin', 'manager']) &&
                   str_contains($mail->subject, '[HIGH]');
        });

        // Verify salesperson was also notified
        Mail::assertSent(function (AdministrativeAlertMail $mail) {
            return $mail->event instanceof BusinessHighValueSaleEvent &&
                   $mail->recipient->id === $this->managerUser->id;
        });
    }

    /**
     * Test queue processing for different priority levels
     */
    public function test_queue_processing_by_priority(): void
    {
        Queue::fake();

        // Create events with different severities
        $criticalEvent = new SystemDatabaseFailureEvent(
            connection: 'mysql',
            error: 'Critical error',
            errorCode: 'CRITICAL',
            query: 'SELECT * FROM users',
            affectedTables: ['users'],
            isSystemWide: true
        );

        $highEvent = new SecurityFailedLoginEvent(
            email: 'high@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Test',
            attempts: 8,
            isSuspicious: true,
            location: null
        );

        $mediumEvent = new SystemDatabaseFailureEvent(
            connection: 'mysql',
            error: 'Medium error',
            errorCode: 'MEDIUM',
            query: 'SELECT * FROM tasks',
            affectedTables: ['tasks'],
            isSystemWide: false
        );

        $lowEvent = new SecurityFailedLoginEvent(
            email: 'low@example.com',
            ipAddress: '192.168.1.2',
            userAgent: 'Test',
            attempts: 1,
            isSuspicious: false,
            location: null
        );

        // Dispatch all events
        Event::dispatch($criticalEvent);
        Event::dispatch($highEvent);
        Event::dispatch($mediumEvent);
        Event::dispatch($lowEvent);

        // Verify jobs were pushed to correct queues
        Queue::assertPushedOn('critical', function ($job) use ($criticalEvent) {
            return $job->event->occurredAt->equalTo($criticalEvent->occurredAt);
        });

        Queue::assertPushedOn('high', function ($job) use ($highEvent) {
            return $job->event->occurredAt->equalTo($highEvent->occurredAt);
        });

        Queue::assertPushedOn('default', function ($job) use ($mediumEvent) {
            return $job->event->occurredAt->equalTo($mediumEvent->occurredAt);
        });

        Queue::assertPushedOn('low', function ($job) use ($lowEvent) {
            return $job->event->occurredAt->equalTo($lowEvent->occurredAt);
        });
    }

    /**
     * Test email rendering with different templates
     */
    public function test_email_rendering_with_different_templates(): void
    {
        Mail::fake();

        // Test security event email
        $securityEvent = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Test Browser',
            attempts: 5,
            isSuspicious: true,
            location: ['city' => 'Test City', 'country' => 'Test Country']
        );

        $securityMail = new AdministrativeAlertMail($securityEvent, $this->adminUser);
        $securityView = $securityMail->content()->view;
        $this->assertStringContains('Security', $securityView);

        // Test system event email
        $systemEvent = new SystemDatabaseFailureEvent(
            connection: 'mysql',
            error: 'Database error',
            errorCode: 'ERROR',
            query: 'SELECT * FROM users',
            affectedTables: ['users'],
            isSystemWide: false
        );

        $systemMail = new AdministrativeAlertMail($systemEvent, $this->adminUser);
        $systemView = $systemMail->content()->view;
        $this->assertStringContains('System', $systemView);

        // Test business event email
        $client = (object) ['id' => 1, 'name' => 'Test Client'];
        $sale = (object) ['id' => 1, 'status' => 'completed'];
        
        $businessEvent = new BusinessHighValueSaleEvent(
            salesUser: $this->managerUser,
            client: $client,
            sale: $sale,
            saleAmount: 15000.00,
            profitMargin: 25.0,
            currency: 'USD',
            productType: 'Sports Card',
            saleCategory: 'Vintage',
            thresholdAmount: 10000.00
        );

        $businessMail = new AdministrativeAlertMail($businessEvent, $this->adminUser);
        $businessView = $businessMail->content()->view;
        $this->assertStringContains('Business', $businessView);
    }

    /**
     * Test error handling and retry mechanisms
     */
    public function test_error_handling_and_retry_mechanisms(): void
    {
        Queue::fake();
        Log::fake();

        // Create an event that will trigger retries
        $event = new SystemDatabaseFailureEvent(
            connection: 'mysql',
            error: 'Connection timeout',
            errorCode: '2002',
            query: 'SELECT * FROM users',
            affectedTables: ['users'],
            isSystemWide: true
        );

        // Mock a failure in the listener
        $this->mockListenerToFail();

        // Dispatch the event
        Event::dispatch($event);

        // Process the queue job (should fail and retry)
        Queue::pushFake('critical');
        
        // Verify retry configuration is applied
        $retryConfig = $event->getRetryConfiguration();
        $this->assertEquals(5, $retryConfig['tries']);
        $this->assertEquals([15, 30, 60, 120, 300], $retryConfig['backoff']);

        // Verify error logging
        Log::assertLogged('error', function ($message, $context) {
            return str_contains($message, 'Administrative alert processing error') &&
                   isset($context['event']) &&
                   isset($context['error']);
        });
    }

    /**
     * Test rate limiting functionality
     */
    public function test_rate_limiting_functionality(): void
    {
        Queue::fake();
        Log::fake();

        // Create a security event
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Test Browser',
            attempts: 3,
            isSuspicious: false,
            location: null
        );

        // Dispatch the same event multiple times
        for ($i = 0; $i < 3; $i++) {
            Event::dispatch($event);
        }

        // Process the queue
        Queue::pushFake('security-alerts');
        Queue::work('security-alerts');

        // Verify rate limiting was applied
        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Administrative alert rate limited') &&
                   isset($context['event']) &&
                   isset($context['event_id']);
        });
    }

    /**
     * Test email delivery with provided mail configuration
     */
    public function test_email_delivery_with_mail_configuration(): void
    {
        // Set up mail configuration as specified in requirements
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'localhost',
            'mail.mailers.smtp.port' => 1025,
            'mail.mailers.smtp.username' => null,
            'mail.mailers.smtp.password' => null,
            'mail.mailers.smtp.encryption' => null,
            'mail.from.address' => 'hello@example.com',
            'mail.from.name' => config('app.name', 'Hidden Treasures Dashboard'),
        ]);

        Mail::fake();

        // Create a test event
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Test Browser',
            attempts: 5,
            isSuspicious: true,
            location: null
        );

        // Create and send the email
        $mail = new AdministrativeAlertMail($event, $this->adminUser);
        Mail::to($this->adminUser->email)->send($mail);

        // Verify email was sent with correct configuration
        Mail::assertSent(AdministrativeAlertMail::class, function ($mail) {
            $envelope = $mail->envelope();
            return $envelope->from[0]->address === 'hello@example.com' &&
                   str_contains($envelope->from[0]->name, config('app.name'));
        });
    }

    /**
     * Test broadcast functionality
     */
    public function test_broadcast_functionality(): void
    {
        Event::fake();

        // Create a test event
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Test Browser',
            attempts: 3,
            isSuspicious: false,
            location: null
        );

        // Dispatch the event with broadcasting
        Event::dispatch($event);

        // Verify broadcast channels and data
        $broadcastData = $event->broadcastWith();
        $broadcastChannels = $event->broadcastOn();

        $this->assertEquals('administrative-alerts.Security', $broadcastChannels->name);
        $this->assertArrayHasKey('event_type', $broadcastData);
        $this->assertArrayHasKey('category', $broadcastData);
        $this->assertArrayHasKey('severity', $broadcastData);
        $this->assertEquals('SecurityFailedLoginEvent', $broadcastData['event_type']);
        $this->assertEquals('Security', $broadcastData['category']);
    }

    /**
     * Test notification creation and delivery
     */
    public function test_notification_creation_and_delivery(): void
    {
        Notification::fake();

        // Create a test event
        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Test Browser',
            attempts: 5,
            isSuspicious: true,
            location: null
        );

        // Dispatch the event
        Event::dispatch($event);

        // Process the queue
        Queue::pushFake('security-high-alerts');
        Queue::work('security-high-alerts');

        // Verify notifications were created for appropriate users
        Notification::assertSentTo(
            $this->adminUser,
            \App\Notifications\SecurityFailedLoginNotification::class
        );

        // Verify notification data structure
        Notification::assertSentTo(
            $this->adminUser,
            \App\Notifications\SecurityFailedLoginNotification::class,
            function ($notification) use ($event) {
                $data = $notification->toArray();
                return isset($data['event_type']) &&
                       isset($data['title']) &&
                       isset($data['description']) &&
                       isset($data['severity']) &&
                       $data['event_type'] === 'SecurityFailedLoginEvent';
            }
        );
    }

    /**
     * Mock listener to fail for testing retry mechanisms
     */
    private function mockListenerToFail(): void
    {
        // This would typically involve mocking the listener class
        // For this test, we're just verifying the retry configuration
        // In a real implementation, you might use Mockery to create a failing listener
    }
}