<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * Test Controller for Administrative Alert System
 * 
 * This controller provides endpoints to trigger various administrative alert events
 * for testing purposes. All endpoints are protected and require appropriate permissions.
 */
class TestAlertController extends Controller
{
    /**
     * Display the alert testing dashboard
     */
    public function index()
    {
        $this->authorize('view-alert-testing');
        
        return view('admin.test-alerts.index', [
            'users' => User::all(),
            'eventTypes' => $this->getEventTypes(),
        ]);
    }

    /**
     * Trigger a Security Failed Login Event
     */
    public function triggerSecurityFailedLogin(Request $request): JsonResponse
    {
        $this->authorize('trigger-security-alerts');
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'ip_address' => 'required|ip',
            'user_agent' => 'required|string|max:500',
            'attempts' => 'required|integer|min:1|max:100',
            'is_suspicious' => 'boolean',
            'location' => 'array',
            'location.city' => 'string|max:100',
            'location.country' => 'string|max:100',
            'location.isp' => 'string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = new SecurityFailedLoginEvent(
                email: $request->input('email'),
                ipAddress: $request->input('ip_address'),
                userAgent: $request->input('user_agent'),
                attempts: $request->input('attempts'),
                isSuspicious: $request->input('is_suspicious', false),
                location: $request->input('location'),
                initiatedBy: auth()->user()
            );

            Event::dispatch($event);

            Log::info('Test SecurityFailedLoginEvent triggered', [
                'triggered_by' => auth()->user()->email,
                'event_data' => $request->all(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Security Failed Login event triggered successfully',
                'event_id' => $event->occurredAt->timestamp,
                'severity' => $event->getSeverity(),
                'category' => $event->getCategory(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger SecurityFailedLoginEvent', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->email,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger event: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger a Security Access Violation Event
     */
    public function triggerSecurityAccessViolation(Request $request): JsonResponse
    {
        $this->authorize('trigger-security-alerts');
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'resource' => 'required|string|max:200',
            'action' => 'required|string|max:200',
            'ip_address' => 'required|ip',
            'user_agent' => 'required|string|max:500',
            'severity' => 'required|in:CRITICAL,HIGH,MEDIUM,LOW',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::findOrFail($request->input('user_id'));
            
            $event = new SecurityAccessViolationEvent(
                userId: $user->id,
                resource: $request->input('resource'),
                action: $request->input('action'),
                ipAddress: $request->input('ip_address'),
                userAgent: $request->input('user_agent'),
                severity: $request->input('severity'),
                initiatedBy: auth()->user()
            );

            Event::dispatch($event);

            Log::info('Test SecurityAccessViolationEvent triggered', [
                'triggered_by' => auth()->user()->email,
                'event_data' => $request->all(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Security Access Violation event triggered successfully',
                'event_id' => $event->occurredAt->timestamp,
                'severity' => $event->getSeverity(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger SecurityAccessViolationEvent', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger event: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger a System Database Failure Event
     */
    public function triggerSystemDatabaseFailure(Request $request): JsonResponse
    {
        $this->authorize('trigger-system-alerts');
        
        $validator = Validator::make($request->all(), [
            'connection' => 'required|string|max:100',
            'error' => 'required|string|max:1000',
            'error_code' => 'required|string|max:50',
            'query' => 'string|max:2000',
            'affected_tables' => 'array',
            'affected_tables.*' => 'string|max:100',
            'is_system_wide' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = new SystemDatabaseFailureEvent(
                connection: $request->input('connection'),
                error: $request->input('error'),
                errorCode: $request->input('error_code'),
                query: $request->input('query'),
                affectedTables: $request->input('affected_tables', []),
                isSystemWide: $request->input('is_system_wide', false),
                initiatedBy: auth()->user()
            );

            Event::dispatch($event);

            Log::info('Test SystemDatabaseFailureEvent triggered', [
                'triggered_by' => auth()->user()->email,
                'event_data' => $request->all(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'System Database Failure event triggered successfully',
                'event_id' => $event->occurredAt->timestamp,
                'severity' => $event->getSeverity(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger SystemDatabaseFailureEvent', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger event: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger a Business High Value Sale Event
     */
    public function triggerBusinessHighValueSale(Request $request): JsonResponse
    {
        $this->authorize('trigger-business-alerts');
        
        $validator = Validator::make($request->all(), [
            'sales_user_id' => 'required|exists:users,id',
            'client_name' => 'required|string|max:200',
            'sale_amount' => 'required|numeric|min:0.01',
            'profit_margin' => 'required|numeric|min:0|max:100',
            'currency' => 'required|string|size:3',
            'product_type' => 'required|string|max:200',
            'sale_category' => 'required|string|max:200',
            'threshold_amount' => 'required|numeric|min:0.01',
            'is_record_high' => 'boolean',
            'is_unexpected' => 'boolean',
            'sales_channel' => 'string|max:100',
            'closing_time_days' => 'integer|min:0',
            'requires_special_handling' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $salesUser = User::findOrFail($request->input('sales_user_id'));
            
            $client = (object) [
                'id' => rand(1, 10000),
                'name' => $request->input('client_name'),
                'email' => 'client-' . uniqid() . '@example.com',
            ];
            
            $sale = (object) [
                'id' => rand(1, 10000),
                'status' => 'completed',
            ];

            $event = new BusinessHighValueSaleEvent(
                salesUser: $salesUser,
                client: $client,
                sale: $sale,
                saleAmount: $request->input('sale_amount'),
                profitMargin: $request->input('profit_margin'),
                currency: $request->input('currency'),
                productType: $request->input('product_type'),
                saleCategory: $request->input('sale_category'),
                thresholdAmount: $request->input('threshold_amount'),
                isRecordHigh: $request->input('is_record_high', false),
                previousRecordHigh: $request->input('previous_record_high'),
                isUnexpected: $request->input('is_unexpected', false),
                salesChannel: $request->input('sales_channel', 'direct'),
                closingTimeDays: $request->input('closing_time_days'),
                requiresSpecialHandling: $request->input('requires_special_handling', false),
                initiatedBy: auth()->user()
            );

            Event::dispatch($event);

            Log::info('Test BusinessHighValueSaleEvent triggered', [
                'triggered_by' => auth()->user()->email,
                'event_data' => $request->all(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Business High Value Sale event triggered successfully',
                'event_id' => $event->occurredAt->timestamp,
                'severity' => $event->getSeverity(),
                'threshold_exceeded_percentage' => $event->thresholdExceedancePercentage,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger BusinessHighValueSaleEvent', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger event: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger a User Account Deleted Event
     */
    public function triggerUserAccountDeleted(Request $request): JsonResponse
    {
        $this->authorize('trigger-user-action-alerts');
        
        $validator = Validator::make($request->all(), [
            'deleted_user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:500',
            'ip_address' => 'required|ip',
            'user_agent' => 'required|string|max:500',
            'soft_delete' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $deletedUser = User::findOrFail($request->input('deleted_user_id'));
            
            $event = new UserAccountDeletedEvent(
                deletedUser: $deletedUser,
                deletedBy: auth()->user(),
                reason: $request->input('reason'),
                ipAddress: $request->input('ip_address'),
                userAgent: $request->input('user_agent'),
                softDelete: $request->input('soft_delete', true),
                initiatedBy: auth()->user()
            );

            Event::dispatch($event);

            Log::info('Test UserAccountDeletedEvent triggered', [
                'triggered_by' => auth()->user()->email,
                'event_data' => $request->all(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User Account Deleted event triggered successfully',
                'event_id' => $event->occurredAt->timestamp,
                'severity' => $event->getSeverity(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger UserAccountDeletedEvent', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger event: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a test email notification
     */
    public function sendTestEmail(Request $request): JsonResponse
    {
        $this->authorize('send-test-emails');
        
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required|exists:users,id',
            'event_type' => 'required|in:security_failed_login,system_database_failure,business_high_value_sale,user_account_deleted',
            'severity' => 'required|in:CRITICAL,HIGH,MEDIUM,LOW',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $recipient = User::findOrFail($request->input('recipient_id'));
            
            // Create test event based on type
            $event = $this->createTestEvent($request->input('event_type'), $request->input('severity'));
            
            $mail = new AdministrativeAlertMail($event, $recipient);
            
            Mail::to($recipient->email)->send($mail);

            Log::info('Test alert email sent', [
                'recipient' => $recipient->email,
                'event_type' => $request->input('event_type'),
                'severity' => $request->input('severity'),
                'sent_by' => auth()->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $recipient->email,
                'recipient' => $recipient->email,
                'event_type' => $request->input('event_type'),
                'severity' => $request->input('severity'),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'error' => $e->getMessage(),
                'recipient_id' => $request->input('recipient_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system status for alert testing
     */
    public function getSystemStatus(): JsonResponse
    {
        $this->authorize('view-alert-testing');
        
        try {
            $status = [
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name'),
                ],
                'queue_config' => [
                    'default' => config('queue.default'),
                    'connections' => array_keys(config('queue.connections', [])),
                ],
                'user_counts' => [
                    'admin' => User::where('role', 'admin')->count(),
                    'manager' => User::where('role', 'manager')->count(),
                    'va' => User::where('role', 'va')->count(),
                    'total' => User::count(),
                ],
                'recent_logs' => Log::channel()->getLogger()->getHandlers() 
                    ? 'Logging enabled' 
                    : 'Logging disabled',
            ];

            return response()->json([
                'success' => true,
                'status' => $status,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available event types for testing
     */
    private function getEventTypes(): array
    {
        return [
            'security' => [
                'SecurityFailedLoginEvent' => 'Triggered when failed login attempts are detected',
                'SecurityAccessViolationEvent' => 'Triggered when access violations occur',
                'SecurityAdminAccountModifiedEvent' => 'Triggered when admin accounts are modified',
                'SecuritySuspiciousSessionEvent' => 'Triggered when suspicious activity is detected',
            ],
            'system' => [
                'SystemDatabaseFailureEvent' => 'Triggered when database failures occur',
                'SystemFileUploadFailureEvent' => 'Triggered when file uploads fail',
                'SystemQueueFailureEvent' => 'Triggered when queue jobs fail',
                'SystemPerformanceIssueEvent' => 'Triggered when performance issues are detected',
            ],
            'user_action' => [
                'UserAccountDeletedEvent' => 'Triggered when user accounts are deleted',
                'UserBulkOperationEvent' => 'Triggered when bulk operations are performed',
                'UserMassContentDeletionEvent' => 'Triggered when mass content deletion occurs',
                'UserGoalFailedEvent' => 'Triggered when user goals are not met',
            ],
            'business' => [
                'BusinessHighValueSaleEvent' => 'Triggered when high-value sales are completed',
                'BusinessUnusualExpenseEvent' => 'Triggered when unusual expenses are detected',
                'BusinessPaymentStatusChangedEvent' => 'Triggered when payment statuses change',
                'BusinessClientDeletedEvent' => 'Triggered when clients are deleted',
            ],
        ];
    }

    /**
     * Create a test event based on type and severity
     */
    private function createTestEvent(string $eventType, string $severity): AdministrativeAlertEvent
    {
        switch ($eventType) {
            case 'security_failed_login':
                return new SecurityFailedLoginEvent(
                    email: 'test@example.com',
                    ipAddress: '192.168.1.100',
                    userAgent: 'Test Browser',
                    attempts: 5,
                    isSuspicious: true,
                    location: ['city' => 'Test City', 'country' => 'Test Country'],
                    initiatedBy: auth()->user()
                );
                
            case 'system_database_failure':
                return new SystemDatabaseFailureEvent(
                    connection: 'test_connection',
                    error: 'Test database error',
                    errorCode: 'TEST_ERROR',
                    query: 'SELECT * FROM test_table',
                    affectedTables: ['users', 'tasks'],
                    isSystemWide: true,
                    initiatedBy: auth()->user()
                );
                
            case 'business_high_value_sale':
                $client = (object) [
                    'id' => 1,
                    'name' => 'Test Client',
                ];
                $sale = (object) [
                    'id' => 1,
                    'status' => 'completed',
                ];
                
                return new BusinessHighValueSaleEvent(
                    salesUser: auth()->user(),
                    client: $client,
                    sale: $sale,
                    saleAmount: 15000.00,
                    profitMargin: 25.0,
                    currency: 'USD',
                    productType: 'Test Product',
                    saleCategory: 'Test Category',
                    thresholdAmount: 10000.00,
                    isRecordHigh: false,
                    initiatedBy: auth()->user()
                );
                
            case 'user_account_deleted':
                return new UserAccountDeletedEvent(
                    deletedUser: User::factory()->create(),
                    deletedBy: auth()->user(),
                    reason: 'Test deletion',
                    ipAddress: '192.168.1.100',
                    userAgent: 'Test Browser',
                    softDelete: true,
                    initiatedBy: auth()->user()
                );
                
            default:
                throw new \InvalidArgumentException("Unknown event type: {$eventType}");
        }
    }
}