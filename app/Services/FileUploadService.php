<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\ContentPostMedia;

class FileUploadService
{
    protected FileValidationService $fileValidationService;
    protected ImageService $imageService;

    public function __construct(FileValidationService $fileValidationService, ImageService $imageService)
    {
        $this->fileValidationService = $fileValidationService;
        $this->imageService = $imageService;
    }

    /**
     * Process a single file upload
     */
    public function processFileUpload(UploadedFile $file, string $type, ?int $maxSize = null): array
    {
        try {
            // Validate the file
            $validationResult = $this->fileValidationService->validate($file, $type, $maxSize);

            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'message' => 'File validation failed',
                    'errors' => $validationResult['errors']
                ];
            }

            // Process and store the file
            return $this->processFile($file, $type);
        } catch (\Exception $e) {
            Log::error('File upload processing failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'type' => $type
            ]);

            return [
                'success' => false,
                'message' => 'File upload failed',
                'errors' => ['An error occurred during file upload: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Process multiple file uploads
     */
    public function processMultipleFileUploads(array $files, string $type, ?int $maxSize = null): array
    {
        try {
            $results = [];
            $failedFiles = [];
            $successfulFiles = [];

            foreach ($files as $index => $file) {
                if (!$file instanceof UploadedFile) {
                    $failedFiles[$index] = [
                        'file' => 'Unknown',
                        'errors' => ['Invalid file object']
                    ];
                    continue;
                }

                $result = $this->processFileUpload($file, $type, $maxSize);

                if ($result['success']) {
                    $successfulFiles[$index] = $result['data'];
                } else {
                    $failedFiles[$index] = [
                        'file' => $file->getClientOriginalName(),
                        'errors' => $result['errors']
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'Files processed',
                'data' => [
                    'successful_files' => $successfulFiles,
                    'failed_files' => $failedFiles
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Multiple file upload processing failed', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);

            return [
                'success' => false,
                'message' => 'Multiple file upload failed',
                'errors' => ['An error occurred during file upload: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Process and store a file
     */
    protected function processFile(UploadedFile $file, string $type): array
    {
        $mimeType = $file->getMimeType();
        $isImage = str_starts_with($mimeType, 'image/');

        if ($isImage) {
            // Process image with ImageService
            $imageResult = $this->imageService->processImage(
                $file,
                'uploads',
                [
                    'max_width' => 1920,
                    'max_height' => 1080,
                    'quality' => 85,
                    'create_thumbnail' => true,
                    'thumbnail_size' => 300,
                ]
            );

            $fileData = [
                'file_name' => $imageResult['filename'],
                'file_path' => $imageResult['path'],
                'mime_type' => $imageResult['mime_type'],
                'file_size' => $imageResult['size'],
                'original_name' => $imageResult['original_name'],
                'url' => asset('storage/' . $imageResult['path']),
                'thumbnail_url' => $imageResult['thumbnail_path'] ? asset('storage/' . $imageResult['thumbnail_path']) : null,
                'width' => $imageResult['width'] ?? null,
                'height' => $imageResult['height'] ?? null,
            ];
        } else {
            // Handle non-image files
            $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads', $fileName, 'public');

            $fileData = [
                'file_name' => $fileName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
                'url' => asset('storage/' . $filePath),
                'thumbnail_url' => null,
                'width' => null,
                'height' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'File processed successfully',
            'data' => $fileData
        ];
    }

    /**
     * Create a ContentPostMedia record for a processed file
     */
    public function createMediaRecord(array $fileData, int $contentPostId, ?int $userId = null, bool $isPrimary = false, int $order = 0): ContentPostMedia
    {
        return ContentPostMedia::create([
            'content_post_id' => $contentPostId,
            'user_id' => $userId ?? (Auth::check() ? Auth::id() : null),
            'file_name' => $fileData['file_name'],
            'file_path' => $fileData['file_path'],
            'mime_type' => $fileData['mime_type'],
            'file_size' => $fileData['file_size'],
            'original_name' => $fileData['original_name'],
            'is_primary' => $isPrimary,
            'order' => $order,
        ]);
    }

    /**
     * Delete a file and its database record
     */
    public function deleteFile(ContentPostMedia $media): bool
    {
        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

            // Delete thumbnail if it exists
            $thumbnailPath = str_replace('uploads/', 'uploads/thumbnails/', $media->file_path);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            // Delete the database record
            $media->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'error' => $e->getMessage(),
                'media_id' => $media->id,
                'file_path' => $media->file_path
            ]);

            return false;
        }
    }

    /**
     * Get standardized file upload configuration
     */
    public function getUploadConfig(): array
    {
        $config = [
            'max_sizes' => [],
            'allowed_types' => $this->fileValidationService->getAllowedTypes(),
            'mime_types' => []
        ];

        foreach ($config['allowed_types'] as $type) {
            $config['max_sizes'][$type] = $this->fileValidationService->getMaxSizeForType($type);
            $config['mime_types'][$type] = $this->fileValidationService->getMimeTypesForType($type);
        }

        return $config;
    }

    /**
     * Handle file upload from request with standardized error handling
     */
    public function handleFileUploadFromRequest($request, string $fieldName, string $type, ?int $maxSize = null): array
    {
        if (!$request->hasFile($fieldName)) {
            return [
                'success' => false,
                'message' => 'No file provided',
                'errors' => ['No file was uploaded']
            ];
        }

        $file = $request->file($fieldName);

        // Handle array format (from BaseFileUploader component)
        if (is_array($file)) {
            if (empty($file)) {
                return [
                    'success' => false,
                    'message' => 'No file provided',
                    'errors' => ['No file was uploaded']
                ];
            }
            $file = $file[0]; // Take the first file
        }

        return $this->processFileUpload($file, $type, $maxSize);
    }

    /**
     * Handle multiple file upload from request with standardized error handling
     */
    public function handleMultipleFileUploadFromRequest($request, string $fieldName, string $type, ?int $maxSize = null): array
    {
        if (!$request->hasFile($fieldName)) {
            return [
                'success' => false,
                'message' => 'No files provided',
                'errors' => ['No files were uploaded']
            ];
        }

        $files = $request->file($fieldName);

        // Ensure we have an array
        if (!is_array($files)) {
            $files = [$files];
        }

        if (empty($files)) {
            return [
                'success' => false,
                'message' => 'No files provided',
                'errors' => ['No files were uploaded']
            ];
        }

        return $this->processMultipleFileUploads($files, $type, $maxSize);
    }
}
