<?php

namespace App\Traits;

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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Trait for easy dispatching of administrative alert events
 * 
 * This trait provides convenient methods for triggering administrative alerts
 * throughout the application without needing to manually import and instantiate
 * each event class.
 */
trait AdministrativeAlertsTrait
{
    /**
     * Trigger a failed login alert
     *
     * @param string $email The email that was used
     * @param string $ipAddress The source IP address
     * @param string $userAgent The browser user agent
     * @param int $attempts Number of failed attempts
     * @param bool $isSuspicious Whether this appears suspicious
     * @param array|null $location Location data from IP geolocation
     * @return void
     */
    protected function triggerFailedLoginAlert(
        string $email,
        string $ipAddress,
        string $userAgent,
        int $attempts = 1,
        bool $isSuspicious = false,
        ?array $location = null
    ): void {
        try {
            Event::dispatch(new SecurityFailedLoginEvent(
                $email,
                $ipAddress,
                $userAgent,
                $attempts,
                $isSuspicious,
                $location,
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger failed login alert', [
                'error' => $e->getMessage(),
                'email' => $email,
                'ip' => $ipAddress
            ]);
        }
    }

    /**
     * Trigger an access violation alert
     *
     * @param string $resource The resource that was accessed
     * @param string $action The action that was attempted
     * @param string $reason The reason for the violation
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerAccessViolationAlert(
        string $resource,
        string $action,
        string $reason,
        array $context = []
    ): void {
        try {
            Event::dispatch(new SecurityAccessViolationEvent(
                $resource,
                $action,
                $reason,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger access violation alert', [
                'error' => $e->getMessage(),
                'resource' => $resource,
                'action' => $action
            ]);
        }
    }

    /**
     * Trigger an admin account modification alert
     *
     * @param User $modifiedUser The user that was modified
     * @param array $changes The changes that were made
     * @return void
     */
    protected function triggerAdminAccountModifiedAlert(User $modifiedUser, array $changes): void
    {
        try {
            Event::dispatch(new SecurityAdminAccountModifiedEvent(
                $modifiedUser,
                $changes,
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger admin account modified alert', [
                'error' => $e->getMessage(),
                'modified_user_id' => $modifiedUser->id
            ]);
        }
    }

    /**
     * Trigger a suspicious session alert
     *
     * @param string $reason The reason for suspicion
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerSuspiciousSessionAlert(string $reason, array $context = []): void
    {
        try {
            Event::dispatch(new SecuritySuspiciousSessionEvent(
                $reason,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger suspicious session alert', [
                'error' => $e->getMessage(),
                'reason' => $reason
            ]);
        }
    }

    /**
     * Trigger a database failure alert
     *
     * @param string $query The query that failed
     * @param string $error The error message
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerDatabaseFailureAlert(string $query, string $error, array $context = []): void
    {
        try {
            Event::dispatch(new SystemDatabaseFailureEvent(
                $query,
                $error,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger database failure alert', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
        }
    }

    /**
     * Trigger a file upload failure alert
     *
     * @param string $fileName The name of the file that failed
     * @param string $error The error message
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerFileUploadFailureAlert(string $fileName, string $error, array $context = []): void
    {
        try {
            Event::dispatch(new SystemFileUploadFailureEvent(
                $fileName,
                $error,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger file upload failure alert', [
                'error' => $e->getMessage(),
                'file_name' => $fileName
            ]);
        }
    }

    /**
     * Trigger a queue failure alert
     *
     * @param string $job The job that failed
     * @param string $error The error message
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerQueueFailureAlert(string $job, string $error, array $context = []): void
    {
        try {
            Event::dispatch(new SystemQueueFailureEvent(
                $job,
                $error,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger queue failure alert', [
                'error' => $e->getMessage(),
                'job' => $job
            ]);
        }
    }

    /**
     * Trigger a performance issue alert
     *
     * @param string $metric The performance metric
     * @param mixed $value The value that triggered the alert
     * @param string $threshold The threshold that was exceeded
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerPerformanceIssueAlert(
        string $metric,
        mixed $value,
        string $threshold,
        array $context = []
    ): void {
        try {
            Event::dispatch(new SystemPerformanceIssueEvent(
                $metric,
                $value,
                $threshold,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger performance issue alert', [
                'error' => $e->getMessage(),
                'metric' => $metric
            ]);
        }
    }

    /**
     * Trigger a user account deleted alert
     *
     * @param User $deletedUser The user that was deleted
     * @param string $reason The reason for deletion
     * @return void
     */
    protected function triggerUserAccountDeletedAlert(User $deletedUser, string $reason = 'Manual deletion'): void
    {
        try {
            Event::dispatch(new UserAccountDeletedEvent(
                $deletedUser,
                $reason,
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger user account deleted alert', [
                'error' => $e->getMessage(),
                'deleted_user_id' => $deletedUser->id
            ]);
        }
    }

    /**
     * Trigger a bulk operation alert
     *
     * @param string $resourceType The type of resource
     * @param int $count The number of items affected
     * @param string $action The action performed
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerBulkOperationAlert(
        string $resourceType,
        int $count,
        string $action,
        array $context = []
    ): void {
        try {
            Event::dispatch(new UserBulkOperationEvent(
                $resourceType,
                $count,
                $action,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger bulk operation alert', [
                'error' => $e->getMessage(),
                'resource_type' => $resourceType,
                'count' => $count
            ]);
        }
    }

    /**
     * Trigger a mass content deletion alert
     *
     * @param int $count The number of items deleted
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerMassContentDeletionAlert(int $count, array $context = []): void
    {
        try {
            Event::dispatch(new UserMassContentDeletionEvent(
                $count,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger mass content deletion alert', [
                'error' => $e->getMessage(),
                'count' => $count
            ]);
        }
    }

    /**
     * Trigger a goal failed alert
     *
     * @param mixed $goal The goal that failed
     * @param string $reason The reason for failure
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerGoalFailedAlert(mixed $goal, string $reason, array $context = []): void
    {
        try {
            Event::dispatch(new UserGoalFailedEvent(
                $goal,
                $reason,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger goal failed alert', [
                'error' => $e->getMessage(),
                'goal_id' => $goal->id ?? 'unknown'
            ]);
        }
    }

    /**
     * Trigger a high value sale alert
     *
     * @param mixed $sale The sale record
     * @param float $threshold The threshold that was exceeded
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerHighValueSaleAlert(mixed $sale, float $threshold, array $context = []): void
    {
        try {
            Event::dispatch(new BusinessHighValueSaleEvent(
                $sale->user, // salesUser
                $sale->client ?? (object)['name' => 'Unknown Client'], // client
                $sale, // sale
                $sale->amount, // saleAmount
                0.15, // profitMargin (default 15%)
                'USD', // currency
                $sale->type, // productType
                'sales', // saleCategory
                $threshold, // thresholdAmount
                false, // isRecordHigh
                null, // previousRecordHigh
                false, // isUnexpected
                'direct', // salesChannel
                null, // closingTimeDays
                false, // requiresSpecialHandling
                auth()->user() // initiatedBy
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger high value sale alert', [
                'error' => $e->getMessage(),
                'sale_id' => $sale->id ?? 'unknown'
            ]);
        }
    }

    /**
     * Trigger an unusual expense alert
     *
     * @param mixed $expense The expense record
     * @param string $reason The reason it's unusual
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerUnusualExpenseAlert(mixed $expense, string $reason, array $context = []): void
    {
        try {
            Event::dispatch(new BusinessUnusualExpenseEvent(
                $expense->user, // user
                $expense, // expense
                $expense->amount, // expenseAmount
                $expense->category, // expenseCategory
                'USD', // currency
                null, // expectedAmount
                $reason, // reason
                false, // isApproved
                null, // approver
                false, // isRecurring
                null, // frequency
                false, // isSuspicious
                'medium', // riskLevel
                $expense->merchant ?? null, // vendor
                false, // requiresInvestigation
                auth()->user() // initiatedBy
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger unusual expense alert', [
                'error' => $e->getMessage(),
                'expense_id' => $expense->id ?? 'unknown'
            ]);
        }
    }

    /**
     * Trigger a payment status changed alert
     *
     * @param mixed $payment The payment record
     * @param string $oldStatus The old status
     * @param string $newStatus The new status
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerPaymentStatusChangedAlert(
        mixed $payment,
        string $oldStatus,
        string $newStatus,
        array $context = []
    ): void {
        try {
            Event::dispatch(new BusinessPaymentStatusChangedEvent(
                $payment,
                $oldStatus,
                $newStatus,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger payment status changed alert', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id ?? 'unknown'
            ]);
        }
    }

    /**
     * Trigger a client deleted alert
     *
     * @param mixed $client The client that was deleted
     * @param string $reason The reason for deletion
     * @param array $context Additional context data
     * @return void
     */
    protected function triggerClientDeletedAlert(mixed $client, string $reason = 'Manual deletion', array $context = []): void
    {
        try {
            Event::dispatch(new BusinessClientDeletedEvent(
                $client,
                $reason,
                array_merge($context, [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]),
                auth()->user()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to trigger client deleted alert', [
                'error' => $e->getMessage(),
                'client_id' => $client->id ?? 'unknown'
            ]);
        }
    }
}