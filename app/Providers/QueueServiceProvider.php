<?php

namespace App\Providers;

use App\Jobs\FailedJobMonitor;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for queue-related services
 * 
 * This provider registers queue event listeners and services
 * for monitoring and alerting on queue failures.
 */
class QueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Listen for failed jobs and trigger monitoring
        Queue::failing(function (JobFailed $event) {
            try {
                // Extract job information
                $jobData = $event->job->payload();
                $jobClass = $jobData['displayName'] ?? $jobData['job'] ?? 'Unknown Job';
                
                // Create and dispatch the failed job monitor
                FailedJobMonitor::dispatch(
                    $jobClass,
                    $event->exception,
                    [
                        'queue' => $event->job->getQueue(),
                        'connection' => $event->job->getConnectionName(),
                        'job_id' => $event->job->getJobId(),
                        'payload' => $jobData,
                        'failed_at' => now()->toISOString(),
                    ]
                )->onQueue('monitoring');
            } catch (\Exception $e) {
                // Log the error but don't let it break the queue
                \Log::error('Failed to dispatch failed job monitor', [
                    'error' => $e->getMessage(),
                    'original_job' => $event->job->getJobId(),
                ]);
            }
        });
    }
}