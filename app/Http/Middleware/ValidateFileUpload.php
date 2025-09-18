<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ValidateFileUpload
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if request contains files
        if (!$request->hasFile('files') && !$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'No files found in request',
                'errors' => ['No files were uploaded']
            ], 422);
        }

        // Validate file count for multiple uploads
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            if (count($files) > 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many files',
                    'errors' => ['Maximum 10 files allowed per upload']
                ], 422);
            }
        }

        // Check for potential malicious file names
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if (!$this->isValidFileName($file->getClientOriginalName())) {
                    Log::warning('Potential malicious file name detected', [
                        'file_name' => $file->getClientOriginalName(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid file name',
                        'errors' => ['File name contains invalid characters']
                    ], 422);
                }
            }
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            if (!$this->isValidFileName($file->getClientOriginalName())) {
                Log::warning('Potential malicious file name detected', [
                    'file_name' => $file->getClientOriginalName(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file name',
                    'errors' => ['File name contains invalid characters']
                ], 422);
            }
        }

        return $next($request);
    }

    protected function isValidFileName(string $fileName): bool
    {
        // Prevent directory traversal and other malicious patterns
        $invalidPatterns = [
            '/\.\./', // directory traversal
            '/\/\//', // double slashes
            '/\\\/',  // backslashes
            '/\.php$/i', // PHP files
            '/\.(exe|bat|cmd|sh|bash|dll)$/i', // executable files
            '/[\x00-\x1f\x7f-\xff]/', // control characters and non-ASCII
        ];

        foreach ($invalidPatterns as $pattern) {
            if (preg_match($pattern, $fileName)) {
                return false;
            }
        }

        // Check for valid file name characters
        if (!preg_match('/^[a-zA-Z0-9_\-\s\.\(\)\[\]]+$/', $fileName)) {
            return false;
        }

        return true;
    }

    protected function sanitizeFileName(string $fileName): string
    {
        // Remove any potentially dangerous characters
        $sanitized = preg_replace('/[^\w\s\-\.\(\)\[\]]/', '', $fileName);
        $sanitized = preg_replace('/\s+/', '_', $sanitized);
        
        // Limit length
        if (strlen($sanitized) > 255) {
            $extension = pathinfo($sanitized, PATHINFO_EXTENSION);
            $name = pathinfo($sanitized, PATHINFO_FILENAME);
            $name = substr($name, 0, 255 - strlen($extension) - 1);
            $sanitized = $name . '.' . $extension;
        }

        return $sanitized;
    }
}