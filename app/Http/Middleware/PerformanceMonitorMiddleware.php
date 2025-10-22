<?php

namespace App\Http\Middleware;

use App\Traits\AdministrativeAlertsTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to monitor application performance and trigger alerts
 * 
 * This middleware tracks request execution time and triggers administrative
 * alerts when performance thresholds are exceeded.
 */
class PerformanceMonitorMiddleware
{
    use AdministrativeAlertsTrait;

    /**
     * Performance thresholds in milliseconds
     */
    private const THRESHOLDS = [
        'slow_request' => 5000,      // 5 seconds
        'very_slow_request' => 10000, // 10 seconds
        'critical_request' => 30000,  // 30 seconds
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Check if performance thresholds are exceeded
        $this->checkPerformanceThresholds($request, $executionTime);

        return $response;
    }

    /**
     * Check if performance thresholds are exceeded and trigger alerts
     *
     * @param Request $request
     * @param float $executionTime Execution time in milliseconds
     * @return void
     */
    private function checkPerformanceThresholds(Request $request, float $executionTime): void
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : $request->path();
        $method = $request->method();

        // Skip monitoring for certain routes (like health checks, asset loading, etc.)
        if ($this->shouldSkipMonitoring($routeName)) {
            return;
        }

        // Determine severity based on execution time
        if ($executionTime >= self::THRESHOLDS['critical_request']) {
            $this->triggerPerformanceIssueAlert(
                'response_time',
                round($executionTime, 2),
                self::THRESHOLDS['critical_request'] . 'ms',
                [
                    'route' => $routeName,
                    'method' => $method,
                    'severity' => 'critical',
                    'memory_usage' => memory_get_peak_usage(true),
                    'query_count' => $this->getQueryCount(),
                ]
            );
        } elseif ($executionTime >= self::THRESHOLDS['very_slow_request']) {
            $this->triggerPerformanceIssueAlert(
                'response_time',
                round($executionTime, 2),
                self::THRESHOLDS['very_slow_request'] . 'ms',
                [
                    'route' => $routeName,
                    'method' => $method,
                    'severity' => 'high',
                    'memory_usage' => memory_get_peak_usage(true),
                    'query_count' => $this->getQueryCount(),
                ]
            );
        } elseif ($executionTime >= self::THRESHOLDS['slow_request']) {
            $this->triggerPerformanceIssueAlert(
                'response_time',
                round($executionTime, 2),
                self::THRESHOLDS['slow_request'] . 'ms',
                [
                    'route' => $routeName,
                    'method' => $method,
                    'severity' => 'medium',
                    'memory_usage' => memory_get_peak_usage(true),
                    'query_count' => $this->getQueryCount(),
                ]
            );
        }

        // Check for high memory usage
        $memoryUsage = memory_get_peak_usage(true);
        $memoryThreshold = 128 * 1024 * 1024; // 128MB

        if ($memoryUsage >= $memoryThreshold) {
            $this->triggerPerformanceIssueAlert(
                'memory_usage',
                round($memoryUsage / 1024 / 1024, 2) . 'MB',
                '128MB',
                [
                    'route' => $routeName,
                    'method' => $method,
                    'execution_time' => round($executionTime, 2) . 'ms',
                    'memory_limit' => ini_get('memory_limit'),
                ]
            );
        }
    }

    /**
     * Determine if monitoring should be skipped for this route
     *
     * @param string|null $routeName
     * @return bool
     */
    private function shouldSkipMonitoring(?string $routeName): bool
    {
        $skipRoutes = [
            'health',
            'metrics',
            'telescope',
            'horizon',
            'debugbar',
            'debugbar.openhandler',
            'debugbar.clockwork',
            'debugbar.telescope',
        ];

        // Skip if route name matches any skip patterns
        foreach ($skipRoutes as $skipRoute) {
            if ($routeName && str_contains($routeName, $skipRoute)) {
                return true;
            }
        }

        // Skip asset requests
        $path = request()->path();
        if (str_contains($path, 'css') || str_contains($path, 'js') || str_contains($path, 'images')) {
            return true;
        }

        // Skip API routes that are expected to be fast
        if (str_starts_with($path, 'api/health') || str_starts_with($path, 'api/ping')) {
            return true;
        }

        return false;
    }

    /**
     * Get the number of database queries executed
     *
     * @return int
     */
    private function getQueryCount(): int
    {
        try {
            // This requires the database query logger to be enabled
            if (app()->bound('db')) {
                return \DB::getQueryLog() ? count(\DB::getQueryLog()) : 0;
            }
        } catch (\Exception $e) {
            // If we can't get query count, return 0
        }

        return 0;
    }
}