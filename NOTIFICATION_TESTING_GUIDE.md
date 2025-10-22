# Real-Time Notification System Testing Guide

This guide provides comprehensive instructions for testing the real-time notification system across all modules (tasks, expenses, goals, and sales).

## Prerequisites

### 1. Start the Reverb Server

The Reverb server must be running for real-time notifications to work:

```bash
# Make the script executable
chmod +x start-reverb.sh

# Start the Reverb server
./start-reverb.sh

# Or run directly:
php artisan reverb:start
```

**Expected Output:**

```
Starting Reverb WebSocket Server...
Reverb Server: ws://localhost:8080
Press Ctrl+C to stop the server
=================================
```

### 2. Start the Laravel Application

```bash
php artisan serve
```

### 3. Verify Configuration

Check that your `.env` file has the correct Reverb configuration:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=356518
REVERB_APP_KEY=xqfyetvrmz1afdfq1vvp
REVERB_APP_SECRET=cjq2v3iquyanngvj8rvv
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
```

## Testing Methods

### Method 1: Automated Test Script

Run the comprehensive test script:

```bash
php test_real_time_notifications.php
```

This script will:

-   Create test tasks, expenses, goals, and sales
-   Trigger various notification events
-   Clean up test data
-   Log all activities to Laravel logs

### Method 2: Manual Testing via Web Interface

#### Task Notifications Testing

1. **Create Task:**

    - Navigate to Tasks → Create New Task
    - Fill in task details with "high" or "urgent" priority
    - Assign to a different user
    - Save and observe notifications

2. **Update Task:**

    - Edit an existing task
    - Change priority to "high" or "urgent"
    - Change status to "completed"
    - Save and observe notifications

3. **Delete Task:**
    - Delete an existing task
    - Observe any notifications (if configured)

#### Expense Notifications Testing

1. **Create Expense:**

    - Navigate to Expenses → Create New Expense
    - Enter amount >= $500 to trigger high-value notification
    - Submit for approval
    - Observe notifications to managers/admins

2. **Approve/Reject Expense:**
    - Go to Expenses as admin/manager
    - Change expense status to "paid" or "rejected"
    - Observe notifications to expense creator

#### Goal Notifications Testing

1. **Create Goal:**

    - Navigate to Goals → Create New Goal
    - Set target value and deadline
    - Save and observe notifications

2. **Update Goal Progress:**

    - Edit goal and update current_value to trigger milestones:
        - 25% of target
        - 50% of target
        - 75% of target
        - 100% of target (completion)
    - Observe milestone notifications

3. **Goal Deadline:**
    - Set goal deadline within 7 days
    - Observe deadline reminder notifications

#### Sale Notifications Testing

1. **Create Sale:**

    - Navigate to Sales → Create New Sale
    - Enter sale amount >= $1000 to trigger high-value notification
    - Save and observe notifications

2. **Update Sale:**
    - Edit existing sale
    - Change amount or status
    - Observe notifications

## Verification Instructions

### Browser Console Verification

1. **Open Browser Developer Tools:**

    - Press F12 or right-click → Inspect
    - Go to Console tab

2. **WebSocket Connection Messages:**
   Look for these connection messages:

    ```javascript
    Connecting to notifications for user: [USER_ID]
    Echo configuration: {
      broadcaster: "reverb",
      key: "xqfyetvrmz1afdfq1vvp",
      wsHost: "localhost",
      wsPort: 8080
    }
    Successfully subscribed to notifications channel for user: [USER_ID]
    ```

3. **Real-time Notification Events:**
   Look for incoming notification messages:

    ```javascript
    Received real-time notification: {
      id: 123,
      type: "task_assigned",
      title: "Task Assigned",
      message: "You have been assigned a new task: Test Task",
      data: {task_id: 456},
      created_at: "2024-01-01T12:00:00.000000Z"
    }
    ```

4. **Connection Status:**
    - Green checkmark indicates active connection
    - Red X indicates connection issues
    - Check Network tab for WebSocket connection status

### Laravel Logs Verification

1. **Monitor Laravel Logs:**

    ```bash
    tail -f storage/logs/laravel.log
    ```

2. **Expected Log Messages:**

    ```
    [2024-01-01 12:00:00] local.INFO: Creating notification
    {
      "user_id": 1,
      "type": "task_assigned",
      "title": "Task Assigned",
      "message": "You have been assigned a new task: Test Task",
      "data_keys": ["task_id"]
    }

    [2024-01-01 12:00:00] local.INFO: Notification created successfully
    {
      "notification_id": 123,
      "type": "task_assigned",
      "user_id": 1
    }

    [2024-01-01 12:00:00] local.INFO: NotificationSent event created
    {
      "notification_id": 123,
      "type": "task_assigned",
      "title": "Task Assigned",
      "user_id": 1,
      "broadcast_connection": "reverb"
    }

    [2024-01-01 12:00:00] local.INFO: Notification broadcast channel created
    {
      "channel_name": "private-notifications.1",
      "notification_id": 123,
      "broadcast_driver": "reverb"
    }
    ```

### WebSocket Connection Verification

1. **Check Reverb Server Status:**

    ```bash
    ps aux | grep reverb
    ```

    Should show a running reverb process.

2. **Test WebSocket Connection:**
   Open browser console and run:

    ```javascript
    // Check if Echo is properly configured
    console.log("Echo config:", window.Echo.options);

    // Test connection
    window.Echo.connector.pusher.connection.bind("connected", () => {
        console.log("WebSocket connected successfully");
    });

    window.Echo.connector.pusher.connection.bind("disconnected", () => {
        console.log("WebSocket disconnected");
    });

    window.Echo.connector.pusher.connection.bind("error", (error) => {
        console.error("WebSocket connection error:", error);
    });
    ```

3. **Network Tab Verification:**
    - Open Network tab in DevTools
    - Filter by "WS" (WebSockets)
    - Look for connection to `ws://localhost:8080/app/xqfyetvrmz1afdfq1vvp`
    - Should show "Connected" status

## Troubleshooting

### Common Issues and Solutions

#### 1. Reverb Server Not Running

**Problem:** No WebSocket connection
**Solution:** Start Reverb server:

```bash
php artisan reverb:start
```

#### 2. Broadcasting Connection Issues

**Problem:** Events not broadcasting
**Solution:** Check `.env` configuration:

```env
BROADCAST_CONNECTION=reverb
```

#### 3. Authentication Issues

**Problem:** Private channel subscription failing
**Solution:** Ensure user is authenticated and check routes/channels.php

#### 4. Browser Notifications Not Showing

**Problem:** No browser notifications
**Solution:** Request notification permission:

```javascript
// In browser console
Notification.requestPermission().then((permission) => {
    console.log("Notification permission:", permission);
});
```

#### 5. Observers Not Triggering

**Problem:** No notifications on model events
**Solution:** Check AppServiceProvider.php for observer registration:

```php
Task::observe(TaskObserver::class);
Expense::observe(ExpenseObserver::class);
Goal::observe(GoalObserver::class);
Sale::observe(SaleObserver::class);
```

### Debug Mode Enhancement

Add enhanced logging by modifying the NotificationService:

```php
// In app/Services/NotificationService.php
public static function createNotification(User $user, string $type, string $title, string $message, array $data = []): Notification
{
    Log::info('Creating notification', [
        'user_id' => $user->id,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'data_keys' => array_keys($data),
        'timestamp' => now()->toISOString(),
        'broadcast_connection' => config('broadcasting.default'),
    ]);

    // ... rest of the method
}
```

## Expected Results

### Successful Test Indicators

1. **Browser Console:**

    - WebSocket connection established
    - Channel subscription successful
    - Real-time notification events received
    - Browser notifications appear (if permission granted)

2. **Laravel Logs:**

    - Notification creation logs
    - Event dispatch logs
    - Broadcast channel logs
    - No error messages

3. **UI Updates:**

    - Notification count updates in real-time
    - New notifications appear in notification list
    - Toast notifications show briefly

4. **Reverb Server:**
    - Shows active connections
    - Logs broadcast events
    - No connection errors

### Performance Expectations

-   **Latency:** < 500ms from model event to browser notification
-   **Reliability:** 99%+ successful delivery on local network
-   **Scalability:** Handles multiple concurrent users

## Test Checklist

-   [ ] Reverb server running
-   [ ] Laravel application running
-   [ ] User logged in to application
-   [ ] Browser console open for monitoring
-   [ ] Laravel logs being monitored
-   [ ] Task notifications tested (create/update/delete)
-   [ ] Expense notifications tested (create/approve/reject)
-   [ ] Goal notifications tested (create/milestones/deadline)
-   [ ] Sale notifications tested (create/high-value)
-   [ ] Browser notifications working
-   [ ] WebSocket connection stable
-   [ ] No errors in logs
-   [ ] Real-time updates working
-   [ ] Test data cleaned up

## Next Steps

After successful testing:

1. **Monitor Performance:** Track notification delivery times
2. **User Testing:** Have multiple users test simultaneously
3. **Load Testing:** Test with high volume of notifications
4. **Production Deployment:** Ensure production Reverb server is configured
5. **Documentation:** Update user documentation with notification features
