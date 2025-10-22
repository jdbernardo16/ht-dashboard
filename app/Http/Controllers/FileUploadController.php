<?php

namespace App\Http\Controllers;

use App\Models\ContentPostMedia;
use App\Services\FileStorageService;
use App\Traits\AdministrativeAlertsTrait;
use App\Rules\ValidFileType;
use App\Rules\ValidImageDimensions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FileUploadController extends Controller
{
    use AdministrativeAlertsTrait;
    protected $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }

    /**
     * Upload a single file
     */
    public function upload(Request $request)
    {
        // Validate request parameters first
        $validated = $request->validate([
            'file' => 'required|file',
            'type' => 'required|string|in:image,video,pdf,xlsx,csv',
            'max_size' => 'sometimes|integer|min:1',
        ]);

        try {
            $file = $request->file('file');
            $type = $request->input('type');
            $maxSize = $request->input('max_size');

            // Validate file type and size
            $this->validateFile($file, $type, $maxSize);

            // Process and store the file using FileStorageService
            $fileData = $this->processFile($file, $type);

            // Generate a unique ID for the file
            $fileId = uniqid('file_');

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => array_merge(['id' => $fileId], $fileData)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'File validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $fileName = $request->file('file')?->getClientOriginalName() ?? 'unknown';

            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $fileName,
                'type' => $type ?? 'unknown'
            ]);

            // Trigger file upload failure alert
            $this->triggerFileUploadFailureAlert(
                $fileName,
                $e->getMessage(),
                [
                    'file_type' => $type ?? 'unknown',
                    'file_size' => $request->file('file')?->getSize(),
                    'max_size' => $maxSize ?? 'not specified',
                    'user_id' => auth()->id(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'File upload failed',
                'errors' => ['An error occurred during file upload']
            ], 500);
        }
    }

    /**
     * Validate file based on type and constraints
     */
    protected function validateFile($file, string $type, ?int $maxSize = null): void
    {
        $maxFileSize = $maxSize ?: config('validation.file_upload.max_size', 10 * 1024 * 1024);
        
        if ($type === 'image') {
            $allowedTypes = config('validation.file_upload.allowed_images', ['jpeg', 'png', 'jpg', 'gif', 'webp']);
            $maxDimensions = config('validation.file_upload.max_image_dimensions', [4000, 4000]);
            $minDimensions = config('validation.file_upload.min_image_dimensions', [100, 100]);
            
            // Check file size
            if ($file->getSize() > $maxFileSize) {
                throw new \Illuminate\Validation\ValidationException(
                    validator()->make([], []),
                    ['The file may not be greater than ' . round($maxFileSize / 1024 / 1024, 2) . 'MB.']
                );
            }
            
            // Check MIME type
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                throw new \Illuminate\Validation\ValidationException(
                    validator()->make([], []),
                    ['The file must be a file of type: ' . implode(', ', $allowedTypes) . '.']
                );
            }
            
            // Check image dimensions
            $imageInfo = @getimagesize($file->getPathname());
            if (!$imageInfo) {
                throw new \Illuminate\Validation\ValidationException(
                    validator()->make([], []),
                    ['The file must be a valid image.']
                );
            }
            
            [$width, $height] = $imageInfo;
            if ($width < $minDimensions[0] || $height < $minDimensions[1]) {
                throw new \Illuminate\Validation\ValidationException(
                    validator()->make([], []),
                    ["The image dimensions are too small. Minimum size is {$minDimensions[0]}x{$minDimensions[1]} pixels."]
                );
            }
            
            if ($width > $maxDimensions[0] || $height > $maxDimensions[1]) {
                throw new \Illuminate\Validation\ValidationException(
                    validator()->make([], []),
                    ["The image dimensions are too large. Maximum size is {$maxDimensions[0]}x{$maxDimensions[1]} pixels."]
                );
            }
        } else {
            $allowedTypes = config('validation.file_upload.allowed_documents', ['pdf', 'xlsx', 'csv']);
            $allowedMimes = [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
                'text/csv'
            ];
            
            // Check file size
            if ($file->getSize() > $maxFileSize) {
                throw new \Illuminate\Validation\ValidationException(
                    validator()->make([], []),
                    ['The file may not be greater than ' . round($maxFileSize / 1024 / 1024, 2) . 'MB.']
                );
            }
            
            // Check MIME type
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                throw new \Illuminate\Validation\ValidationException(
                    validator()->make([], []),
                    ['The file must be a file of type: ' . implode(', ', $allowedMimes) . '.']
                );
            }
        }
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(Request $request)
    {
        try {
            $validated = $request->validate([
                'files' => 'required|array',
                'files.*' => 'file',
                'type' => 'required|string|in:image,video,pdf,xlsx,csv',
                'max_size' => 'sometimes|integer|min:1',
            ]);

            $files = $request->file('files');
            $type = $request->input('type');
            $maxSize = $request->input('max_size');

            $uploadedFiles = [];
            $failedFiles = [];

            foreach ($files as $index => $file) {
                try {
                    // Validate each file
                    $this->validateFile($file, $type, $maxSize);
                    
                    // Process and store the file
                    $fileData = $this->processFile($file, $type);
                    $fileId = uniqid('file_');
                    $uploadedFiles[] = array_merge(['id' => $fileId], $fileData);
                    
                } catch (\Illuminate\Validation\ValidationException $e) {
                    $failedFiles[] = [
                        'index' => $index,
                        'file' => $file->getClientOriginalName(),
                        'error' => 'Validation failed: ' . implode(', ', collect($e->errors())->flatten()->toArray())
                    ];
                } catch (\Exception $e) {
                    $failedFiles[] = [
                        'index' => $index,
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                    
                    // Trigger file upload failure alert for each failed file
                    $this->triggerFileUploadFailureAlert(
                        $file->getClientOriginalName(),
                        $e->getMessage(),
                        [
                            'file_type' => $type ?? 'unknown',
                            'file_size' => $file->getSize(),
                            'max_size' => $maxSize ?? 'not specified',
                            'user_id' => auth()->id(),
                            'batch_upload' => true,
                            'batch_index' => $index,
                        ]
                    );
                }
            }

            if (!empty($failedFiles) && empty($uploadedFiles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'All files failed validation',
                    'failed_files' => $failedFiles
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Files processed successfully',
                'data' => $uploadedFiles,
                'failed_files' => $failedFiles
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Multiple file upload failed', [
                'error' => $e->getMessage(),
                'type' => $type ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'File upload failed',
                'errors' => ['An error occurred during file upload']
            ], 500);
        }
    }

    /**
     * Process and store a file
     */
    protected function processFile($file, string $type)
    {
        try {
            if ($type === 'image') {
                // Use FileStorageService for images
                $result = $this->fileStorageService->storeImage($file);
                
                return [
                    'file_name' => $result['filename'],
                    'file_path' => $result['path'],
                    'mime_type' => $result['mime_type'],
                    'file_size' => $result['file_size'],
                    'original_name' => $result['original_name'],
                    'url' => $result['url'],
                    'thumbnail_url' => null, // Will be populated if thumbnail is created
                    'width' => null, // Will be populated if image dimensions are available
                    'height' => null,
                ];
            } else {
                // Use FileStorageService for media files
                $result = $this->fileStorageService->storeMediaFile($file);
                
                return [
                    'file_name' => $result['filename'],
                    'file_path' => $result['path'],
                    'mime_type' => $result['mime_type'],
                    'file_size' => $result['file_size'],
                    'original_name' => $result['original_name'],
                    'url' => $result['url'],
                    'thumbnail_url' => null,
                    'width' => null,
                    'height' => null,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to process file', [
                'original_name' => $file->getClientOriginalName(),
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            
            // Trigger file upload failure alert for processing errors
            $this->triggerFileUploadFailureAlert(
                $file->getClientOriginalName(),
                "File processing failed: {$e->getMessage()}",
                [
                    'file_type' => $type,
                    'file_size' => $file->getSize(),
                    'processing_stage' => 'process_file',
                    'user_id' => auth()->id(),
                ]
            );
            
            throw new \Exception("Failed to process file: {$e->getMessage()}");
        }
    }

    /**
     * Show file information
     */
    public function show(string $file)
    {
        // This would typically serve the file or return file info
        // For security reasons, we might want to implement signed URLs
        return response()->json([
            'success' => false,
            'message' => 'File access through direct URL is not implemented for security reasons'
        ], 403);
    }

    /**
     * Delete a file
     */
    public function destroy(string $file)
    {
        try {
            // Find the media record
            $media = ContentPostMedia::where('file_name', $file)
                ->orWhere('file_path', 'like', '%' . $file)
                ->first();

            if (!$media) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Check authorization (user can only delete their own files)
            if (Auth::id() !== $media->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this file'
                ], 403);
            }

            // Delete the file and database record
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'error' => $e->getMessage(),
                'file' => $file
            ]);

            // Trigger file upload failure alert for deletion errors
            $this->triggerFileUploadFailureAlert(
                $file,
                "File deletion failed: {$e->getMessage()}",
                [
                    'operation' => 'delete',
                    'user_id' => auth()->id(),
                    'media_id' => $media->id ?? null,
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'File deletion failed',
                'errors' => ['An error occurred during file deletion']
            ], 500);
        }
    }

    /**
     * Get file upload configuration
     */
    public function config()
    {
        $maxFileSize = config('validation.file_upload.max_size', 10 * 1024 * 1024);
        $allowedImages = config('validation.file_upload.allowed_images', ['jpeg', 'png', 'jpg', 'gif', 'webp']);
        $allowedDocuments = config('validation.file_upload.allowed_documents', ['pdf', 'xlsx', 'csv']);
        $maxDimensions = config('validation.file_upload.max_image_dimensions', [4000, 4000]);
        $minDimensions = config('validation.file_upload.min_image_dimensions', [100, 100]);
        
        $config = [
            'max_size' => $maxFileSize,
            'max_size_mb' => round($maxFileSize / 1024 / 1024, 2),
            'allowed_images' => $allowedImages,
            'allowed_documents' => $allowedDocuments,
            'max_image_dimensions' => $maxDimensions,
            'min_image_dimensions' => $minDimensions,
            'mime_types' => [
                'image' => [
                    'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'
                ],
                'document' => [
                    'application/pdf',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'text/csv'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }
}
