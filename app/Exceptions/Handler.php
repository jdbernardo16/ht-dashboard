<?php

namespace App\Exceptions;

use App\Traits\AdministrativeAlertsTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\ConnectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use AdministrativeAlertsTrait;
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
        NotFoundHttpException::class,
        MethodNotAllowedHttpException::class,
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
            
            // Trigger database failure alerts for database exceptions
            if ($e instanceof QueryException || $e instanceof ConnectionException) {
                try {
                    $query = 'Unknown';
                    if (method_exists($e, 'getSql')) {
                        $query = $e->getSql();
                    } elseif (isset($e->sql)) {
                        $query = $e->sql;
                    }
                    
                    $this->triggerDatabaseFailureAlert(
                        $query,
                        $e->getMessage(),
                        [
                            'exception_class' => get_class($e),
                            'bindings' => method_exists($e, 'getBindings') ? $e->getBindings() : [],
                            'connection' => $e->getConnectionName() ?? 'default',
                            'request_path' => request()->path(),
                            'request_method' => request()->method(),
                            'user_id' => auth()->id(),
                        ]
                    );
                } catch (\Exception $alertException) {
                    // Don't let alert failures break the error handling
                    \Log::error('Failed to trigger database failure alert', [
                        'original_error' => $e->getMessage(),
                        'alert_error' => $alertException->getMessage(),
                    ]);
                }
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception): Response|JsonResponse
    {
        // Handle file upload exceptions specifically
        if ($exception instanceof FileUploadException) {
            return $this->handleFileUploadException($exception, $request);
        }

        // Handle validation exceptions
        if ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception, $request);
        }

        // Handle authentication exceptions
        if ($exception instanceof AuthenticationException) {
            return $this->handleAuthenticationException($exception, $request);
        }

        // Handle authorization exceptions
        if ($exception instanceof AuthorizationException) {
            return $this->handleAuthorizationException($exception, $request);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle file upload exceptions
     */
    protected function handleFileUploadException(FileUploadException $exception, Request $request): JsonResponse|Response
    {
        // Log the full error for debugging
        \Log::error('File upload error', [
            'message' => $exception->getMessage(),
            'context' => $exception->getContext(),
            'details' => $exception->getDetails(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => auth()->id(),
            'request_data' => $request->except(['password', 'token']),
            'request_path' => $request->path(),
            'request_method' => $request->method(),
        ]);

        // Return user-friendly response
        $response = [
            'success' => false,
            'message' => $this->getUserFriendlyMessage($exception),
            'error_type' => 'file_upload',
            'context' => $exception->getContext(),
            'timestamp' => now()->toISOString(),
        ];

        // Add validation errors if available
        if ($exception instanceof FileValidationException) {
            $response['errors'] = $this->formatValidationErrors($exception->getValidationErrors());
        }

        // Add details for debugging in development
        if (app()->environment('local', 'testing')) {
            $response['details'] = $exception->getDetails();
            $response['trace'] = $exception->getTraceAsString();
        }

        // Return appropriate response format based on request type
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($response, 422);
        }

        // For web requests, redirect back with error
        return back()
            ->withInput()
            ->withErrors([
                'file_upload' => $this->getUserFriendlyMessage($exception)
            ]);
    }

    /**
     * Handle validation exceptions
     */
    protected function handleValidationException(ValidationException $exception, Request $request): JsonResponse|Response
    {
        \Log::warning('Validation error', [
            'errors' => $exception->errors(),
            'user_id' => auth()->id(),
            'request_path' => $request->path(),
            'request_method' => $request->method(),
        ]);

        $response = [
            'success' => false,
            'message' => 'Validation failed',
            'error_type' => 'validation',
            'errors' => $exception->errors(),
            'timestamp' => now()->toISOString(),
        ];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($response, 422);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle authentication exceptions
     */
    protected function handleAuthenticationException(AuthenticationException $exception, Request $request): JsonResponse|Response
    {
        \Log::warning('Authentication error', [
            'message' => $exception->getMessage(),
            'request_path' => $request->path(),
            'request_method' => $request->method(),
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error_type' => 'authentication',
                'timestamp' => now()->toISOString(),
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Handle authorization exceptions
     */
    protected function handleAuthorizationException(AuthorizationException $exception, Request $request): JsonResponse|Response
    {
        \Log::warning('Authorization error', [
            'message' => $exception->getMessage(),
            'user_id' => auth()->id(),
            'request_path' => $request->path(),
            'request_method' => $request->method(),
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform this action',
                'error_type' => 'authorization',
                'timestamp' => now()->toISOString(),
            ], 403);
        }

        abort(403, 'You are not authorized to perform this action');
    }

    /**
     * Get user-friendly error message
     */
    protected function getUserFriendlyMessage(FileUploadException $exception): string
    {
        $message = $exception->getMessage();
        $context = $exception->getContext();
        
        // Map technical messages to user-friendly ones
        $messageMap = [
            'File too large' => 'The file is too large. Please choose a smaller file.',
            'Invalid file type' => 'This file type is not supported.',
            'Storage quota exceeded' => 'Storage space is full. Please contact support.',
            'File upload failed' => 'Failed to upload file. Please try again.',
            'File processing failed' => 'Failed to process file. Please try again.',
            'Image processing failed' => 'Failed to process image. Please try a different image.',
            'File storage failed' => 'Failed to save file. Please try again.',
        ];
        
        // Check context-specific messages
        if ($context === 'image_processing') {
            $imageMessageMap = [
                'Invalid image' => 'The file is not a valid image.',
                'Image too large' => 'The image is too large. Maximum size is 10MB.',
                'Invalid image dimensions' => 'The image dimensions are invalid. Must be between 100x100 and 4000x4000 pixels.',
            ];
            
            foreach ($imageMessageMap as $technical => $friendly) {
                if (str_contains(strtolower($message), strtolower($technical))) {
                    return $friendly;
                }
            }
        }
        
        // Check general message mappings
        foreach ($messageMap as $technical => $friendly) {
            if (str_contains(strtolower($message), strtolower($technical))) {
                return $friendly;
            }
        }
        
        // Return original message if no mapping found, but clean it up
        return $this->cleanErrorMessage($message);
    }

    /**
     * Clean up error message for user display
     */
    protected function cleanErrorMessage(string $message): string
    {
        // Remove technical details and file paths
        $message = preg_replace('/\/[^\/]*\//', '/', $message);
        $message = preg_replace('/\b[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\b/i', '', $message);
        $message = preg_replace('/\b\d+\.\d+\.\d+\.\d+\b/', '', $message);
        
        // Capitalize first letter and add period if missing
        $message = ucfirst(trim($message));
        if (!str_ends_with($message, '.')) {
            $message .= '.';
        }
        
        return $message;
    }

    /**
     * Format validation errors for response
     */
    protected function formatValidationErrors(array $errors): array
    {
        $formatted = [];
        
        foreach ($errors as $field => $messages) {
            if (is_array($messages)) {
                $formatted[$field] = array_map([$this, 'cleanErrorMessage'], $messages);
            } else {
                $formatted[$field] = $this->cleanErrorMessage($messages);
            }
        }
        
        return $formatted;
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception): Response|JsonResponse
    {
        return $this->handleAuthenticationException($exception, $request);
    }
}