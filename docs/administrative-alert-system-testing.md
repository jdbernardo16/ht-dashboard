# Administrative Alert System Testing Guide

This document provides comprehensive instructions for testing the Administrative Alert System in the Hidden Treasures Dashboard.

## Overview

The Administrative Alert System is a comprehensive event-driven notification system that monitors security, system, user action, and business events across the application. This guide covers testing all components of the system to ensure proper functionality.

## Test Suite Structure

The test suite consists of the following components:

### 1. Feature Tests

-   **`tests/Feature/AdministrativeAlertSystemTest.php`** - Tests event firing, validation, and properties
-   **`tests/Feature/AdministrativeAlertIntegrationTest.php`** - End-to-end integration tests

### 2. Unit Tests

-   **`tests/Unit/AdministrativeAlertEventTest.php`** - Tests base event class functionality
-   **`tests/Unit/AdministrativeAlertMailTest.php`** - Tests email generation and rendering
-   **`tests/Unit/AdministrativeAlertListenerTest.php`** - Tests listener processing and error handling

### 3. Test Controller

-   **`app/Http/Controllers/TestAlertController.php`** - Manual testing endpoints

## Running Tests

### Running All Tests

```bash
# Run all administrative alert tests
php artisan test --testsuite=Feature,Unit --filter="AdministrativeAlert"

# Run with coverage
php artisan test --testsuite=Feature,Unit --filter="AdministrativeAlert" --coverage
```

### Running Specific Test Files

```bash
# Feature tests
php artisan test tests/Feature/AdministrativeAlertSystemTest.php
php artisan test tests/Feature/AdministrativeAlertIntegrationTest.php

# Unit tests
php artisan test tests/Unit/AdministrativeAlertEventTest.php
php artisan test tests/Unit/AdministrativeAlertMailTest.php
php artisan test tests/Unit/AdministrativeAlertListenerTest.php
```

### Running Specific Test Methods

```bash
# Test specific functionality
php artisan test --filter="test_security_events_can_be_fired"
php artisan test --filter="test_email_generation_and_rendering"
php artisan test --filter="test_queue_processing_by_priority"
```

## Test Categories

### 1. Event Firing Tests

These tests verify that all 16 event types can be fired successfully with proper data validation:

```php
// Test Security Events
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

    Event::assertDispatched(SecurityFailedLoginEvent::class, 1);
}
```

### 2. Severity Level Tests

Tests verify that events correctly determine their severity levels:

```php
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
}
```

### 3. Email Generation Tests

Tests verify email content, templates, and rendering:

```php
public function test_email_generation_and_rendering(): void
{
    $mail = new AdministrativeAlertMail($this->testEvent, $this->recipientUser);

    // Verify envelope configuration
    $envelope = $mail->envelope();
    $this->assertStringContains('[HIGH]', $envelope->subject);
    $this->assertStringContains('Security Alert:', $envelope->subject);

    // Verify content
    $content = $mail->content();
    $this->assertStringContains('emails.administrative-alerts.Security', $content->view);
}
```

### 4. Queue Processing Tests

Tests verify that events are queued correctly based on severity:

```php
public function test_queue_processing_by_priority(): void
{
    Queue::fake();

    // Create critical event
    $criticalEvent = new SystemDatabaseFailureEvent(/* ... */);
    Event::dispatch($criticalEvent);

    // Verify job was pushed to correct queue
    Queue::assertPushedOn('critical', function ($job) use ($criticalEvent) {
        return $job->event->occurredAt->equalTo($criticalEvent->occurredAt);
    });
}
```

### 5. Integration Tests

End-to-end tests verify the complete flow from event to notification:

```php
public function test_security_event_complete_flow(): void
{
    Queue::fake();
    Mail::fake();
    Notification::fake();

    // Create and dispatch event
    $event = new SecurityFailedLoginEvent(/* ... */);
    Event::dispatch($event);

    // Process queue
    Queue::pushFake('security-alerts');
    Queue::work('security-alerts');

    // Verify email was sent
    Mail::assertSent(function (AdministrativeAlertMail $mail) {
        return $mail->event instanceof SecurityFailedLoginEvent &&
               $mail->recipient->role === 'admin' &&
               str_contains($mail->subject, '[HIGH]');
    });
}
```

## Manual Testing

### Test Controller Endpoints

The system includes a test controller with endpoints for manual testing:

#### 1. Security Event Testing

```bash
# Trigger Security Failed Login Event
curl -X POST "http://localhost/admin/test-alerts/security/failed-login" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "email": "test@example.com",
    "ip_address": "192.168.1.100",
    "user_agent": "Test Browser",
    "attempts": 5,
    "is_suspicious": true,
    "location": {
      "city": "Test City",
      "country": "Test Country"
    }
  }'
```

#### 2. System Event Testing

```bash
# Trigger System Database Failure Event
curl -X POST "http://localhost/admin/test-alerts/system/database-failure" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "connection": "mysql",
    "error": "Connection timeout",
    "error_code": "2002",
    "query": "SELECT * FROM users",
    "affected_tables": ["users", "tasks"],
    "is_system_wide": true
  }'
```

#### 3. Business Event Testing

```bash
# Trigger Business High Value Sale Event
curl -X POST "http://localhost/admin/test-alerts/business/high-value-sale" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "sales_user_id": 2,
    "client_name": "Test Client",
    "sale_amount": 15000.00,
    "profit_margin": 25.0,
    "currency": "USD",
    "product_type": "Sports Card",
    "sale_category": "Vintage",
    "threshold_amount": 10000.00,
    "is_record_high": false,
    "is_unexpected": false
  }'
```

#### 4. Email Testing

```bash
# Send Test Email
curl -X POST "http://localhost/admin/test-alerts/send-test-email" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "recipient_id": 1,
    "event_type": "security_failed_login",
    "severity": "HIGH"
  }'
```

### System Status Check

```bash
# Get System Status
curl -X GET "http://localhost/admin/test-alerts/system-status" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Mail Configuration Testing

The system is configured to work with the following mail settings:

### SMTP Configuration for Testing

```env
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Using Mailhog for Testing

To test email functionality locally:

1. Start Mailhog with Docker:

```bash
docker run -p 1025:1025 -p 8025:8025 mailhog/mailhog
```

2. Configure your `.env` file:

```env
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_DRIVER=smtp
```

3. Access the Mailhog web interface at `http://localhost:8025`

### Testing Email Templates

To test email rendering without sending:

```php
// In a test or tinker session
$event = new SecurityFailedLoginEvent(
    email: 'test@example.com',
    ipAddress: '192.168.1.1',
    userAgent: 'Test Browser',
    attempts: 5,
    isSuspicious: true,
    location: ['city' => 'Test City', 'country' => 'Test Country']
);

$recipient = User::first();
$mail = new AdministrativeAlertMail($event, $recipient);

// Render the email
$html = $mail->render();
echo $html;
```

## Queue Testing

### Testing Queue Configuration

Verify queues are properly configured for each severity level:

```php
// In a test
$criticalEvent = new SystemDatabaseFailureEvent(/* ... */);
$this->assertEquals('critical', $criticalEvent->getConnection());

$highEvent = new BusinessHighValueSaleEvent(/* ... */);
$this->assertEquals('high', $highEvent->getConnection());

$mediumEvent = new SystemPerformanceIssueEvent(/* ... */);
$this->assertEquals('default', $mediumEvent->getConnection());

$lowEvent = new SecurityFailedLoginEvent(/* ... */);
$this->assertEquals('low', $lowEvent->getConnection());
```

### Testing Queue Processing

Process queues manually for testing:

```bash
# Process specific queue
php artisan queue:work --queue=critical
php artisan queue:work --queue=high
php artisan queue:work --queue=default
php artisan queue:work --queue=low

# Process all queues
php artisan queue:work
```

### Testing Failed Jobs

View and retry failed jobs:

```bash
# View failed jobs
php artisan queue:failed

# Retry specific failed job
php artisan queue:retry JOB_ID

# Retry all failed jobs
php artisan queue:retry all
```

## Error Handling Testing

### Testing Rate Limiting

```php
// Trigger multiple identical events quickly
for ($i = 0; $i < 5; $i++) {
    Event::dispatch(new SecurityFailedLoginEvent(/* ... */));
}

// Verify rate limiting was applied
Log::assertLogged('info', function ($message, $context) {
    return str_contains($message, 'Administrative alert rate limited');
});
```

### Testing Retry Mechanisms

```php
// Test retry configuration
$event = new SystemDatabaseFailureEvent(/* ... */);
$retryConfig = $event->getRetryConfiguration();
$this->assertEquals(5, $retryConfig['tries']);
$this->assertEquals([15, 30, 60, 120, 300], $retryConfig['backoff']);
```

### Testing Escalation

```php
// Test escalation for critical failures
$event = new SystemDatabaseFailureEvent(/* ... */);
$this->assertTrue($event->requiresImmediateAttention());
```

## Performance Testing

### Testing Large Volume Events

```php
// Trigger many events to test performance
for ($i = 0; $i < 1000; $i++) {
    Event::dispatch(new SecurityFailedLoginEvent(
        email: "test{$i}@example.com",
        ipAddress: "192.168.1.{$i}",
        userAgent: 'Test Browser',
        attempts: 1,
        isSuspicious: false,
        location: null
    ));
}
```

### Test Memory Usage

```php
// Monitor memory usage during processing
$memoryBefore = memory_get_usage();

// Process events
// ... your test code ...

$memoryAfter = memory_get_usage();
$memoryUsed = $memoryAfter - $memoryBefore;
echo "Memory used: " . ($memoryUsed / 1024 / 1024) . " MB\n";
```

## Troubleshooting

### Common Issues

1. **Events Not Firing**

    - Check EventServiceProvider mappings
    - Verify event class syntax
    - Check for typos in event names

2. **Listeners Not Processing**

    - Verify listener class exists
    - Check queue configuration
    - Verify listener implements ShouldQueue

3. **Emails Not Sending**

    - Check mail configuration
    - Verify queue worker is running
    - Check email templates exist

4. **Queue Jobs Failing**
    - Check error logs
    - Verify retry configuration
    - Test with sync queue for debugging

### Debugging Tips

1. **Enable Debug Mode**

```php
// In config/app.php
'debug' => env('APP_DEBUG', true),
```

2. **Use Sync Queue for Testing**

```php
// In tests
Queue::fake(); // To verify queueing
// OR
config(['queue.default' => 'sync']); // To process immediately
```

3. **Check Logs**

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View queue logs
php artisan queue:failed
```

4. **Test Individual Components**

```php
// Test event creation
$event = new SecurityFailedLoginEvent(/* ... */);
dd($event->getSeverity(), $event->getTitle(), $event->getDescription());

// Test email generation
$mail = new AdministrativeAlertMail($event, $recipient);
dd($mail->envelope(), $mail->content());
```

## Testing Checklist

### Pre-Deployment Checklist

-   [ ] All 16 event types can be fired successfully
-   [ ] Events correctly determine severity levels
-   [ ] Emails are generated with correct templates
-   [ ] Queue jobs are created for appropriate queues
-   [ ] Queue processing works correctly
-   [ ] Rate limiting prevents duplicate notifications
-   [ ] Error handling and retry mechanisms work
-   [ ] Email delivery functions with configured SMTP
-   [ ] Database notifications are created
-   [ ] System broadcasts work correctly
-   [ ] Integration tests pass for all event categories
-   [ ] Performance meets requirements under load

### Post-Deployment Verification

-   [ ] Monitor error logs for alert system failures
-   [ ] Verify email delivery in production
-   [ ] Check queue processing performance
-   [ ] Test actual user notifications
-   [ ] Verify system monitoring dashboards

## Additional Resources

-   [Laravel Event Documentation](https://laravel.com/docs/events)
-   [Laravel Queue Documentation](https://laravel.com/docs/queues)
-   [Laravel Mail Documentation](https://laravel.com/docs/mail)
-   [Laravel Testing Documentation](https://laravel.com/docs/testing)

For questions or issues with the alert system testing, refer to the system architecture documentation or contact the development team.
