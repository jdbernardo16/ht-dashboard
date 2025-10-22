<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// Administrative Alert Events
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

// Administrative Alert Listeners
use App\Listeners\Security\SecurityFailedLoginListener;
use App\Listeners\Security\SecurityAccessViolationListener;
use App\Listeners\Security\SecurityAdminAccountModifiedListener;
use App\Listeners\Security\SecuritySuspiciousSessionListener;
use App\Listeners\System\SystemDatabaseFailureListener;
use App\Listeners\System\SystemFileUploadFailureListener;
use App\Listeners\System\SystemQueueFailureListener;
use App\Listeners\System\SystemPerformanceIssueListener;
use App\Listeners\UserAction\UserAccountDeletedListener;
use App\Listeners\UserAction\UserBulkOperationListener;
use App\Listeners\UserAction\UserMassContentDeletionListener;
use App\Listeners\UserAction\UserGoalFailedListener;
use App\Listeners\Business\BusinessHighValueSaleListener;
use App\Listeners\Business\BusinessUnusualExpenseListener;
use App\Listeners\Business\BusinessPaymentStatusChangedListener;
use App\Listeners\Business\BusinessClientDeletedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Security Event Listeners
        SecurityFailedLoginEvent::class => [
            SecurityFailedLoginListener::class,
        ],

        SecurityAccessViolationEvent::class => [
            SecurityAccessViolationListener::class,
        ],

        SecurityAdminAccountModifiedEvent::class => [
            SecurityAdminAccountModifiedListener::class,
        ],

        SecuritySuspiciousSessionEvent::class => [
            SecuritySuspiciousSessionListener::class,
        ],

        // System Event Listeners
        SystemDatabaseFailureEvent::class => [
            SystemDatabaseFailureListener::class,
        ],

        SystemFileUploadFailureEvent::class => [
            SystemFileUploadFailureListener::class,
        ],

        SystemQueueFailureEvent::class => [
            SystemQueueFailureListener::class,
        ],

        SystemPerformanceIssueEvent::class => [
            SystemPerformanceIssueListener::class,
        ],

        // User Action Event Listeners
        UserAccountDeletedEvent::class => [
            UserAccountDeletedListener::class,
        ],

        UserBulkOperationEvent::class => [
            UserBulkOperationListener::class,
        ],

        UserMassContentDeletionEvent::class => [
            UserMassContentDeletionListener::class,
        ],

        UserGoalFailedEvent::class => [
            UserGoalFailedListener::class,
        ],

        // Business Event Listeners
        BusinessHighValueSaleEvent::class => [
            BusinessHighValueSaleListener::class,
        ],

        BusinessUnusualExpenseEvent::class => [
            BusinessUnusualExpenseListener::class,
        ],

        BusinessPaymentStatusChangedEvent::class => [
            BusinessPaymentStatusChangedListener::class,
        ],

        BusinessClientDeletedEvent::class => [
            BusinessClientDeletedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}