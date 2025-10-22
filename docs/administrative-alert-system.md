# Administrative Alert System

This document describes the comprehensive administrative alert system implemented in the application. The system monitors various security, system, user action, and business events, and triggers appropriate alerts to administrators and managers.

## Overview

The administrative alert system consists of:

1. **Event Classes** - Define different types of administrative alerts
2. **Listeners** - Handle the events and create notifications
3. **Triggers** - Code that dispatches events when specific conditions are met
4. **Helper Trait** - Provides convenient methods for triggering alerts
5. **Queue Configuration** - Ensures proper processing of alerts

## Event Categories

### Security Events

| Event                               | Description                                            | Trigger        |
| ----------------------------------- | ------------------------------------------------------ | -------------- |
| `SecurityFailedLoginEvent`          | Triggered when login attempts fail                     | LoginRequest   |
| `SecurityAccessViolationEvent`      | Triggered when unauthorized access is attempted        | Middleware     |
| `SecurityAdminAccountModifiedEvent` | Triggered when admin accounts are modified             | UserController |
| `SecuritySuspiciousSessionEvent`    | Triggered when suspicious session activity is detected | Custom         |

### System Events

| Event                          | Description                                        | Trigger                      |
| ------------------------------ | -------------------------------------------------- | ---------------------------- |
| `SystemDatabaseFailureEvent`   | Triggered when database operations fail            | Exception Handler            |
| `SystemFileUploadFailureEvent` | Triggered when file uploads fail                   | FileUploadController         |
| `SystemQueueFailureEvent`      | Triggered when queue jobs fail                     | FailedJobMonitor             |
| `SystemPerformanceIssueEvent`  | Triggered when performance thresholds are exceeded | PerformanceMonitorMiddleware |

### User Action Events

| Event                          | Description                                       | Trigger        |
| ------------------------------ | ------------------------------------------------- | -------------- |
| `UserAccountDeletedEvent`      | Triggered when user accounts are deleted          | UserController |
| `UserBulkOperationEvent`       | Triggered when bulk operations are performed      | Controllers    |
| `UserMassContentDeletionEvent` | Triggered when multiple content items are deleted | Controllers    |
| `UserGoalFailedEvent`          | Triggered when goals fail to complete             | GoalObserver   |

### Business Events

| Event                               | Description                                   | Trigger          |
| ----------------------------------- | --------------------------------------------- | ---------------- |
| `BusinessHighValueSaleEvent`        | Triggered when high-value sales are recorded  | SaleObserver     |
| `BusinessUnusualExpenseEvent`       | Triggered when unusual expenses are submitted | ExpenseObserver  |
| `BusinessPaymentStatusChangedEvent` | Triggered when payment statuses change        | Custom           |
| `BusinessClientDeletedEvent`        | Triggered when clients are deleted            | ClientController |

## Implementation Details

### Helper Trait

The `AdministrativeAlertsTrait` provides convenient methods for triggering alerts:

```php
use App\Traits\AdministrativeAlertsTrait;

class MyController extends Controller
{
    use AdministrativeAlertsTrait;

    public function someMethod()
    {
        // Trigger a failed login alert
        $this->triggerFailedLoginAlert(
            'user@example.com',
            '192.168.1.100',
            'Mozilla/5.0...',
            3,
            false,
            ['city' => 'Test City', 'country' => 'Test Country']
        );
    }
}
```

### Queue Configuration

Specialized queues are configured for different alert types:

-   `security-alerts` - Standard security alerts
-   `security-high-alerts` - High-priority security alerts
-   `system-alerts` - System-related alerts
-   `business-alerts` - Business-related alerts
-   `monitoring` - Monitoring and maintenance alerts

### Performance Monitoring

The `PerformanceMonitorMiddleware` automatically monitors request performance and triggers alerts when thresholds are exceeded:

-   Slow requests (> 5 seconds)
-   Very slow requests (> 10 seconds)
-   Critical requests (> 30 seconds)
-   High memory usage (> 128MB)

## Testing

Use the provided test command to verify all alert types:

```bash
# Test all alert types
php artisan app:test-administrative-alerts

# Test specific alert types
php artisan app:test-administrative-alerts --type=security
php artisan app:test-administrative-alerts --type=system
php artisan app:test-administrative-alerts --type=user-action
php artisan app:test-administrative-alerts --type=business
```

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Queue configuration
QUEUE_CONNECTION=database

# Performance monitoring thresholds
PERFORMANCE_SLOW_REQUEST_THRESHOLD=5000
PERFORMANCE_VERY_SLOW_REQUEST_THRESHOLD=10000
PERFORMANCE_CRITICAL_REQUEST_THRESHOLD=30000
PERFORMANCE_MEMORY_THRESHOLD=134217728
```

### Middleware Registration

The performance monitoring middleware is automatically registered in `bootstrap/app.php`.

## Notification Channels

Administrative alerts are sent through:

1. **Database Notifications** - Stored in the notifications table
2. **Email Notifications** - Sent to administrators and managers
3. **Real-time Notifications** - Displayed in the admin dashboard

## Rate Limiting

To prevent alert fatigue, the system implements rate limiting:

-   Failed login alerts are limited per IP/email combination
-   Performance alerts are limited per route
-   Bulk operation alerts are limited per user

## Security Considerations

1. **Sensitive Data Redaction** - Passwords, tokens, and other sensitive data are automatically redacted
2. **Access Control** - Only administrators can view detailed alert information
3. **Audit Trail** - All alert triggers are logged for audit purposes

## Troubleshooting

### Common Issues

1. **Alerts Not Triggering**

    - Check that the trait is imported: `use App\Traits\AdministrativeAlertsTrait;`
    - Verify the trait is used in the class: `use AdministrativeAlertsTrait;`
    - Check queue worker is running: `php artisan queue:work`

2. **Emails Not Sending**

    - Verify mail configuration in `.env`
    - Check mail queue: `php artisan queue:failed`
    - Test mail configuration: `php artisan tinker` → `Mail::raw('Test', fn($m) => $m->to('test@example.com'))->send();`

3. **Performance Alerts Not Triggering**
    - Verify middleware is registered in `bootstrap/app.php`
    - Check if route is in the skip list
    - Verify thresholds are exceeded

### Logs

Check these log files for troubleshooting:

-   `storage/logs/laravel.log` - General application logs
-   `storage/logs/queue.log` - Queue processing logs (if configured)

## Future Enhancements

Potential improvements to the administrative alert system:

1. **Alert Dashboard** - A dedicated dashboard for viewing and managing alerts
2. **Alert Aggregation** - Group similar alerts to reduce noise
3. **Custom Alert Rules** - Allow administrators to define custom alert conditions
4. **Integration with External Services** - Send alerts to Slack, Teams, or other services
5. **Machine Learning** - Use ML to detect anomalous patterns and predict potential issues
