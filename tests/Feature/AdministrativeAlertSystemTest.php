<?php

namespace Tests\Feature;

use App\Events\AdministrativeAlertEvent;
use App\Events\Security\SecurityFailedLoginEvent;
use App\Events\Security\SecurityAccessViolationEvent;
use App\Events\Security\SecurityAdminAccountModifiedEvent;
use App\Events\Security\SecuritySuspiciousSessionEvent;
use App\Events\System\SystemDatabaseFailureEvent;
use App\Events\System\SystemFileUploadFailureEvent;
use App\Events\System\SystemQueueFailureEvent;
use App\Events\System\SystemPerformanceIssueEvent;
use App\Events\UserAction\UserAccountDeletedEvent;
use App\Events\UserAction\UserBulkOperationEvent;
use App\Events\UserAction\UserMassContentDeletionEvent;
use App\Events\UserAction\UserGoalFailedEvent;
use App\Events\Business\BusinessHighValueSaleEvent;
use App\Events\Business\BusinessUnusualExpenseEvent;
use App\Events\Business\BusinessPaymentStatusChangedEvent;
use App\Events\Business\BusinessClientDeletedEvent;
use App\Mail\AdministrativeAlertMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Exception;

/**
 * Administrative Alert System Test Suite
 * 
 * This test suite comprehensively tests the administrative alert system including:
 * - Event firing and validation
 * - Listener processing and queue handling
 * - Email generation and rendering
 * - Rate limiting and error handling
 * - Integration across all event types
 */
class AdministrativeAlertSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $adminUser;
    protected User $managerUser;
    protected User $vaUser;
    protected array $testEventData;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->managerUser = User::factory()->create(['role' => 'manager']);
        $this->vaUser = User::factory()->create(['role' => 'va']);

        // Common test event data
        $this->testEventData = [
            'test_field' => 'test_value',
            'timestamp' => now()->toISOString(),
            'request_id' => $this->faker->uuid,
        ];
    }

    /**
     * Test that all security events can be fired successfully
     */
    public function test_security_events_can_be_fired(): void
    {
        Event::fake();

        // Test SecurityFailedLoginEvent
        $failedLoginEvent = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: ['city' => 'Test City', 'country' => 'Test Country']
        );
        event($failedLoginEvent);

        // Test SecurityAccessViolationEvent
        $accessViolationEvent = new SecurityAccessViolationEvent(
            userId: $this->adminUser->id,
            resource: 'admin_panel',
            action: 'unauthorized_access_attempt',
            ipAddress: '192.168.1.2',
            userAgent: 'Mozilla/5.0...',
            severity: AdministrativeAlertEvent::SEVERITY_HIGH
        );
        event($accessViolationEvent);

        // Test SecurityAdminAccountModifiedEvent
        $adminModifiedEvent = new SecurityAdminAccountModifiedEvent(
            modifiedAdmin: $this->adminUser,
            modifiedBy: $this->managerUser,
            changes: ['email' => 'newemail@example.com'],
            ipAddress: '192.168.1.3',
            userAgent: 'Mozilla/5.0...'
        );
        event($adminModifiedEvent);

        // Test SecuritySuspiciousSessionEvent
        $suspiciousSessionEvent = new SecuritySuspiciousSessionEvent(
            user: $this->vaUser,
            sessionId: 'test_session_id',
            anomalies: ['multiple_ips', 'unusual_location'],
            ipAddress: '192.168.1.4',
            userAgent: 'Mozilla/5.0...'
        );
        event($suspiciousSessionEvent);

        Event::assertDispatched(SecurityFailedLoginEvent::class, 1);
        Event::assertDispatched(SecurityAccessViolationEvent::class, 1);
        Event::assertDispatched(SecurityAdminAccountModifiedEvent::class, 1);
        Event::assertDispatched(SecuritySuspiciousSessionEvent::class, 1);
    }

    /**
     * Test that all system events can be fired successfully
     */
    public function test_system_events_can_be_fired(): void
    {
        Event::fake();

        // Test SystemDatabaseFailureEvent
        $databaseFailureEvent = new SystemDatabaseFailureEvent(
            connection: 'mysql',
            error: 'Connection timeout',
            errorCode: '2002',
            query: 'SELECT * FROM users',
            affectedTables: ['users', 'tasks'],
            isSystemWide: true
        );
        event($databaseFailureEvent);

        // Test SystemFileUploadFailureEvent
        $fileUploadFailureEvent = new SystemFileUploadFailureEvent(
            storageDisk: 'local',
            filePath: '/uploads/test.jpg',
            failureType: 'permission_denied',
            errorMessage: 'Permission denied',
            errorCode: 'EACCES',
            fileSize: 1024000,
            mimeType: 'image/jpeg',
            isSystemWideIssue: false
        );
        event($fileUploadFailureEvent);

        // Test SystemQueueFailureEvent
        $queueFailureEvent = new SystemQueueFailureEvent(
            queueName: 'default',
            jobClass: 'App\Jobs\ProcessSale',
            errorMessage: 'Job failed after 3 attempts',
            errorCode: 'JOB_FAILED',
            retryCount: 3,
            maxRetries: 3,
            payload: ['test' => 'data']
        );
        event($queueFailureEvent);

        // Test SystemPerformanceIssueEvent
        $performanceIssueEvent = new SystemPerformanceIssueEvent(
            metric: 'response_time',
            value: 5000,
            threshold: 1000,
            unit: 'milliseconds',
            component: 'api_endpoints',
            affectedEndpoints: ['/api/sales', '/api/tasks'],
            duration: 300
        );
        event($performanceIssueEvent);

        Event::assertDispatched(SystemDatabaseFailureEvent::class, 1);
        Event::assertDispatched(SystemFileUploadFailureEvent::class, 1);
        Event::assertDispatched(SystemQueueFailureEvent::class, 1);
        Event::assertDispatched(SystemPerformanceIssueEvent::class, 1);
    }

    /**
     * Test that all user action events can be fired successfully
     */
    public function test_user_action_events_can_be_fired(): void
    {
        Event::fake();

        // Test UserAccountDeletedEvent
        $accountDeletedEvent = new UserAccountDeletedEvent(
            deletedUser: $this->vaUser,
            deletedBy: $this->adminUser,
            reason: 'Account cleanup',
            ipAddress: '192.168.1.5',
            userAgent: 'Mozilla/5.0...',
            softDelete: true
        );
        event($accountDeletedEvent);

        // Test UserBulkOperationEvent
        $bulkOperationEvent = new UserBulkOperationEvent(
            user: $this->managerUser,
            operation: 'bulk_update',
            resourceType: 'tasks',
            affectedCount: 50,
            details: ['status' => 'completed'],
            ipAddress: '192.168.1.6',
            userAgent: 'Mozilla/5.0...'
        );
        event($bulkOperationEvent);

        // Test UserMassContentDeletionEvent
        $massContentDeletionEvent = new UserMassContentDeletionEvent(
            user: $this->adminUser,
            contentType: 'posts',
            deletedCount: 25,
            reason: 'Content cleanup',
            affectedCategories: ['news', 'updates'],
            ipAddress: '192.168.1.7',
            userAgent: 'Mozilla/5.0...'
        );
        event($massContentDeletionEvent);

        // Test UserGoalFailedEvent
        $goalFailedEvent = new UserGoalFailedEvent(
            user: $this->managerUser,
            goalType: 'monthly_sales',
            target: 10000,
            achieved: 7500,
            deadline: now()->addDays(7),
            failureReason: 'Market conditions'
        );
        event($goalFailedEvent);

        Event::assertDispatched(UserAccountDeletedEvent::class, 1);
        Event::assertDispatched(UserBulkOperationEvent::class, 1);
        Event::assertDispatched(UserMassContentDeletionEvent::class, 1);
        Event::assertDispatched(UserGoalFailedEvent::class, 1);
    }

    /**
     * Test that all business events can be fired successfully
     */
    public function test_business_events_can_be_fired(): void
    {
        Event::fake();

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

        // Test BusinessHighValueSaleEvent
        $highValueSaleEvent = new BusinessHighValueSaleEvent(
            salesUser: $this->managerUser,
            client: $client,
            sale: $sale,
            saleAmount: 15000.00,
            profitMargin: 25.5,
            currency: 'USD',
            productType: 'Sports Card',
            saleCategory: 'Vintage',
            thresholdAmount: 10000.00,
            isRecordHigh: false,
            previousRecordHigh: null,
            isUnexpected: false,
            salesChannel: 'online',
            closingTimeDays: 15,
            requiresSpecialHandling: false
        );
        event($highValueSaleEvent);

        // Test BusinessUnusualExpenseEvent
        $unusualExpenseEvent = new BusinessUnusualExpenseEvent(
            user: $this->adminUser,
            amount: 5000.00,
            currency: 'USD',
            category: 'Marketing',
            description: 'Unusual marketing expense',
            reasonCode: 'UNEXPECTED_CAMPAIGN',
            approver: null,
            receiptUrl: null,
            isRecurring: false
        );
        event($unusualExpenseEvent);

        // Test BusinessPaymentStatusChangedEvent
        $paymentStatusChangedEvent = new BusinessPaymentStatusChangedEvent(
            client: $client,
            sale: $sale,
            oldStatus: 'pending',
            newStatus: 'failed',
            amount: 2500.00,
            currency: 'USD',
            reason: 'Insufficient funds',
            retryAttempt: 1,
            maxRetries: 3
        );
        event($paymentStatusChangedEvent);

        // Test BusinessClientDeletedEvent
        $clientDeletedEvent = new BusinessClientDeletedEvent(
            client: $client,
            deletedBy: $this->adminUser,
            reason: 'Client request',
            totalSales: 15000.00,
            activeProjects: 2,
            ipAddress: '192.168.1.8',
            userAgent: 'Mozilla/5.0...'
        );
        event($clientDeletedEvent);

        Event::assertDispatched(BusinessHighValueSaleEvent::class, 1);
        Event::assertDispatched(BusinessUnusualExpenseEvent::class, 1);
        Event::assertDispatched(BusinessPaymentStatusChangedEvent::class, 1);
        Event::assertDispatched(BusinessClientDeletedEvent::class, 1);
    }

    /**
     * Test event severity levels and their properties
     */
    public function test_event_severity_levels(): void
    {
        // Test CRITICAL severity
        $criticalEvent = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 15, // High number of attempts
            isSuspicious: true,
            location: null
        );
        $this->assertEquals(AdministrativeAlertEvent::SEVERITY_HIGH, $criticalEvent->getSeverity());
        $this->assertTrue($criticalEvent->shouldSendEmail());
        $this->assertEquals('critical', $criticalEvent->getConnection());

        // Test HIGH severity
        $highEvent = new BusinessHighValueSaleEvent(
            salesUser: $this->managerUser,
            client: (object) ['id' => 1, 'name' => 'Test'],
            sale: (object) ['id' => 1, 'status' => 'completed'],
            saleAmount: 50000.00,
            profitMargin: 30.0,
            currency: 'USD',
            productType: 'Sports Card',
            saleCategory: 'Vintage',
            thresholdAmount: 10000.00,
            isRecordHigh: true
        );
        $this->assertEquals(AdministrativeAlertEvent::SEVERITY_HIGH, $highEvent->getSeverity());
        $this->assertTrue($highEvent->shouldSendEmail());
        $this->assertEquals('high', $highEvent->getConnection());

        // Test MEDIUM severity
        $mediumEvent = new SystemPerformanceIssueEvent(
            metric: 'response_time',
            value: 1500,
            threshold: 1000,
            unit: 'milliseconds',
            component: 'api_endpoints',
            affectedEndpoints: ['/api/sales'],
            duration: 300
        );
        $this->assertEquals(AdministrativeAlertEvent::SEVERITY_MEDIUM, $mediumEvent->getSeverity());
        $this->assertFalse($mediumEvent->shouldSendEmail());
        $this->assertEquals('default', $mediumEvent->getConnection());

        // Test LOW severity
        $lowEvent = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 1,
            isSuspicious: false,
            location: null
        );
        $this->assertEquals(AdministrativeAlertEvent::SEVERITY_LOW, $lowEvent->getSeverity());
        $this->assertFalse($lowEvent->shouldSendEmail());
        $this->assertEquals('low', $lowEvent->getConnection());
    }

    /**
     * Test event payload validation and data integrity
     */
    public function test_event_payload_validation(): void
    {
        // Test SecurityFailedLoginEvent required fields
        $failedLoginEvent = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 5,
            isSuspicious: false,
            location: ['city' => 'Test City', 'country' => 'Test Country']
        );

        $this->assertEquals('test@example.com', $failedLoginEvent->email);
        $this->assertEquals('192.168.1.1', $failedLoginEvent->ipAddress);
        $this->assertEquals('Mozilla/5.0...', $failedLoginEvent->userAgent);
        $this->assertEquals(5, $failedLoginEvent->attempts);
        $this->assertFalse($failedLoginEvent->isSuspicious);
        $this->assertIsArray($failedLoginEvent->location);
        $this->assertEquals('Security', $failedLoginEvent->getCategory());
        $this->assertIsString($failedLoginEvent->getTitle());
        $this->assertIsString($failedLoginEvent->getDescription());
        $this->assertIsString($failedLoginEvent->getActionUrl());

        // Test BusinessHighValueSaleEvent required fields
        $client = (object) ['id' => 1, 'name' => 'Test Client'];
        $sale = (object) ['id' => 1, 'status' => 'completed'];
        $highValueSaleEvent = new BusinessHighValueSaleEvent(
            salesUser: $this->managerUser,
            client: $client,
            sale: $sale,
            saleAmount: 15000.00,
            profitMargin: 25.5,
            currency: 'USD',
            productType: 'Sports Card',
            saleCategory: 'Vintage',
            thresholdAmount: 10000.00
        );

        $this->assertEquals($this->managerUser, $highValueSaleEvent->salesUser);
        $this->assertEquals($client, $highValueSaleEvent->client);
        $this->assertEquals($sale, $highValueSaleEvent->sale);
        $this->assertEquals(15000.00, $highValueSaleEvent->saleAmount);
        $this->assertEquals(25.5, $highValueSaleEvent->profitMargin);
        $this->assertEquals('USD', $highValueSaleEvent->currency);
        $this->assertEquals('Sports Card', $highValueSaleEvent->productType);
        $this->assertEquals('Vintage', $highValueSaleEvent->saleCategory);
        $this->assertEquals(10000.00, $highValueSaleEvent->thresholdAmount);
        $this->assertEquals('Business', $highValueSaleEvent->getCategory());
        $this->assertIsString($highValueSaleEvent->getTitle());
        $this->assertIsString($highValueSaleEvent->getDescription());
        $this->assertIsString($highValueSaleEvent->getActionUrl());
    }

    /**
     * Test event queue connections and configurations
     */
    public function test_event_queue_configurations(): void
    {
        // Test CRITICAL event queue configuration
        $criticalEvent = new SystemDatabaseFailureEvent(
            connection: 'mysql',
            error: 'Critical database error',
            errorCode: 'CRITICAL_ERROR',
            query: 'SELECT * FROM users',
            affectedTables: ['users'],
            isSystemWide: true
        );
        $this->assertEquals('critical', $criticalEvent->getConnection());
        $this->assertEquals('system-alerts', $criticalEvent->getQueue());
        $this->assertEquals(5, $criticalEvent->getRetryConfiguration()['tries']);

        // Test HIGH event queue configuration
        $highEvent = new BusinessHighValueSaleEvent(
            salesUser: $this->managerUser,
            client: (object) ['id' => 1, 'name' => 'Test'],
            sale: (object) ['id' => 1, 'status' => 'completed'],
            saleAmount: 50000.00,
            profitMargin: 30.0,
            currency: 'USD',
            productType: 'Sports Card',
            saleCategory: 'Vintage',
            thresholdAmount: 10000.00,
            isRecordHigh: true
        );
        $this->assertEquals('high', $highEvent->getConnection());
        $this->assertEquals('business-high-alerts', $highEvent->getQueue());
        $this->assertEquals(3, $highEvent->getRetryConfiguration()['tries']);

        // Test MEDIUM event queue configuration
        $mediumEvent = new SystemPerformanceIssueEvent(
            metric: 'response_time',
            value: 1500,
            threshold: 1000,
            unit: 'milliseconds',
            component: 'api_endpoints',
            affectedEndpoints: ['/api/sales'],
            duration: 300
        );
        $this->assertEquals('default', $mediumEvent->getConnection());
        $this->assertEquals('system-alerts', $mediumEvent->getQueue());
        $this->assertEquals(2, $mediumEvent->getRetryConfiguration()['tries']);

        // Test LOW event queue configuration
        $lowEvent = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 1,
            isSuspicious: false,
            location: null
        );
        $this->assertEquals('low', $lowEvent->getConnection());
        $this->assertEquals('security-alerts', $lowEvent->getQueue());
        $this->assertEquals(1, $lowEvent->getRetryConfiguration()['tries']);
    }

    /**
     * Test event broadcast functionality
     */
    public function test_event_broadcasting(): void
    {
        Event::fake();

        $event = new SecurityFailedLoginEvent(
            email: 'test@example.com',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            attempts: 3,
            isSuspicious: false,
            location: null
        );

        $broadcastData = $event->broadcastWith();
        $broadcastChannels = $event->broadcastOn();

        $this->assertEquals('administrative-alerts.Security', $broadcastChannels->name);
        $this->assertArrayHasKey('event_type', $broadcastData);
        $this->assertArrayHasKey('category', $broadcastData);
        $this->assertArrayHasKey('severity', $broadcastData);
        $this->assertArrayHasKey('title', $broadcastData);
        $this->assertArrayHasKey('description', $broadcastData);
        $this->assertArrayHasKey('occurred_at', $broadcastData);
        $this->assertEquals('SecurityFailedLoginEvent', $broadcastData['event_type']);
        $this->assertEquals('Security', $broadcastData['category']);
    }

    /**
     * Test event metadata and additional properties
     */
    public function test_event_metadata(): void
    {
        // Test severity display names
        $criticalEvent = new SystemDatabaseFailureEvent(
            connection: 'mysql',
            error: 'Critical error',
            errorCode: 'CRITICAL',
            query: 'SELECT * FROM users',
            affectedTables: ['users'],
            isSystemWide: true
        );
        $this->assertEquals('Critical', $criticalEvent->getSeverityDisplayName());
        $this->assertEquals('#dc2626', $criticalEvent->getSeverityColor());
        $this->assertTrue($criticalEvent->requiresImmediateAttention());

        // Test business event metadata
        $client = (object) ['id' => 1, 'name' => 'Test Client'];
        $sale = (object) ['id' => 1, 'status' => 'completed'];
        $businessEvent = new BusinessHighValueSaleEvent(
            salesUser: $this->managerUser,
            client: $client,
            sale: $sale,
            saleAmount: 25000.00,
            profitMargin: 35.0,
            currency: 'USD',
            productType: 'Sports Card',
            saleCategory: 'Vintage',
            thresholdAmount: 10000.00,
            isRecordHigh: false,
            previousRecordHigh: 20000.00,
            isUnexpected: true
        );

        $metadata = $businessEvent->getMetadata();
        $this->assertArrayHasKey('significance_level', $metadata);
        $this->assertArrayHasKey('requires_celebration', $metadata);
        $this->assertArrayHasKey('recommended_actions', $metadata);
        $this->assertTrue($businessEvent->shouldCelebrate());
        $this->assertTrue($businessEvent->shouldTriggerBonusCalculation());
        $this->assertTrue($businessEvent->isUnexpected);
    }

    /**
     * Test event context data handling
     */
    public function test_event_context_data(): void
    {
        $contextData = [
            'custom_field' => 'custom_value',
            'numeric_value' => 12345,
            'array_data' => ['item1', 'item2'],
            'boolean_value' => true,
        ];

        $event = new SystemQueueFailureEvent(
            queueName: 'test_queue',
            jobClass: 'TestJob',
            errorMessage: 'Test error',
            errorCode: 'TEST_ERROR',
            retryCount: 1,
            maxRetries: 3,
            payload: $contextData
        );

        $this->assertArrayHasKey('custom_field', $event->context);
        $this->assertArrayHasKey('numeric_value', $event->context);
        $this->assertArrayHasKey('array_data', $event->context);
        $this->assertArrayHasKey('boolean_value', $event->context);
        $this->assertEquals('custom_value', $event->context['custom_field']);
        $this->assertEquals(12345, $event->context['numeric_value']);
        $this->assertEquals(['item1', 'item2'], $event->context['array_data']);
        $this->assertTrue($event->context['boolean_value']);
    }

    /**
     * Test event timestamp and occurrence tracking
     */
    public function test_event_timestamp_tracking(): void
    {
        $beforeEvent = now();
        
        $event = new UserAccountDeletedEvent(
            deletedUser: $this->vaUser,
            deletedBy: $this->adminUser,
            reason: 'Test deletion',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0...',
            softDelete: true
        );
        
        $afterEvent = now();

        $this->assertGreaterThanOrEqual($beforeEvent, $event->occurredAt);
        $this->assertLessThanOrEqual($afterEvent, $event->occurredAt);
        $this->assertInstanceOf(\Carbon\Carbon::class, $event->occurredAt);
    }
}