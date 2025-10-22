<?php

namespace App\Jobs;

use App\Traits\AdministrativeAlertsTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to monitor and alert on failed queue jobs
 * 
 * This job is triggered when other jobs fail and creates
 * administrative alerts for critical system failures.
 */
class FailedJobMonitor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AdministrativeAlertsTrait;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int|array $backoff = [10, 30, 60];

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * The job that failed
     */
    public string $failedJobClass;

    /**
     * The exception that caused the failure
     */
    public \Throwable $exception;

    /**
     * Additional context about the failure
     */
    public array $context;

    /**
     * Create a new job instance.
     *
     * @param string $failedJobClass
     * @param \Throwable $exception
     * @param array $context
     */
    public function __construct(string $failedJobClass, \Throwable $exception, array $context = [])
    {
        $this->failedJobClass = $failedJobClass;
        $this->exception = $exception;
        $this->context = $context;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $errorMessage = $this->exception->getMessage();
            $errorTrace = $this->exception->getTraceAsString();

            // Determine if this is a critical failure
            $isCritical = $this->isCriticalFailure($this->failedJobClass, $this->exception);

            // Trigger queue failure alert
            $this->triggerQueueFailureAlert(
                $this->failedJobClass,
                $errorMessage,
                array_merge($this->context, [
                    'error_trace' => substr($errorTrace, 0, 1000), // Limit trace length
                    'is_critical' => $isCritical,
                    'exception_class' => get_class($this->exception),
                    'file' => $this->exception->getFile(),
                    'line' => $this->exception->getLine(),
                    'queue' => $this->queue,
                    'connection' => $this->connection,
                    'attempts' => $this->attempts(),
                ])
            );

            // If this is a critical failure, log it separately
            if ($isCritical) {
                Log::critical('Critical queue job failure detected', [
                    'job' => $this->failedJobClass,
                    'error' => $errorMessage,
                    'context' => $this->context,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to monitor failed job', [
                'failed_job' => $this->failedJobClass,
                'monitor_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed job monitor itself failed', [
            'monitored_job' => $this->failedJobClass,
            'monitor_error' => $exception->getMessage(),
        ]);
    }

    /**
     * Determine if a job failure is critical
     *
     * @param string $jobClass
     * @param \Throwable $exception
     * @return bool
     */
    private function isCriticalFailure(string $jobClass, \Throwable $exception): bool
    {
        // Define critical job patterns
        $criticalJobPatterns = [
            'Payment',
            'Invoice',
            'Billing',
            'Email', // Email jobs are critical for notifications
            'Notification',
            'Backup',
            'Database',
            'Security',
        ];

        // Check if job class contains critical patterns
        foreach ($criticalJobPatterns as $pattern) {
            if (str_contains(strtolower($jobClass), strtolower($pattern))) {
                return true;
            }
        }

        // Check for critical exception types
        $criticalExceptionPatterns = [
            'database',
            'connection',
            'timeout',
            'memory',
            'disk space',
            'permission',
            'authentication',
            'authorization',
        ];

        $errorMessage = strtolower($exception->getMessage());
        foreach ($criticalExceptionPatterns as $pattern) {
            if (str_contains($errorMessage, $pattern)) {
                return true;
            }
        }

        // Check for system-level exceptions
        $criticalExceptionClasses = [
            'PDOException',
            'Illuminate\Database\QueryException',
            'Illuminate\Database\ConnectionException',
            'Illuminate\Queue\MaxAttemptsExceededException',
        ];

        foreach ($criticalExceptionClasses as $exceptionClass) {
            if ($exception instanceof $exceptionClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return [
            'failed-job-monitor',
            'job:' . class_basename($this->failedJobClass),
        ];
    }
}