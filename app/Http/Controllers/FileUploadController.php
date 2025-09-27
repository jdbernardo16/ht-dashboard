<?php

namespace App\Http\Controllers;

use App\Models\ContentPostMedia;
use App\Services\FileValidationService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    protected $fileValidationService;
    protected $imageService;

    public function __construct(FileValidationService $fileValidationService, ImageService $imageService)
    {
        $this->fileValidationService = $fileValidationService;
        $this->imageService = $imageService;
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

            // Validate the file
            $validationResult = $this->fileValidationService->validate($file, $type, $maxSize);

            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'File validation failed',
                    'errors' => $validationResult['errors']
                ], 422);
            }

            // Process and store the file
            $fileData = $this->processFile($file, $type);

            // Generate a unique ID for the file
            $fileId = uniqid('file_');

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => array_merge(['id' => $fileId], $fileData)
            ]);
        } catch (\Exception $e) {
            $fileName = $request->file('file')?->getClientOriginalName() ?? 'unknown';

            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $fileName,
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

            // Validate all files
            $validationResults = $this->fileValidationService->validateMultiple($files, $type, $maxSize);

            $failedFiles = [];
            $validFiles = [];

            foreach ($validationResults as $index => $result) {
                if (!$result['valid']) {
                    $failedFiles[$index] = [
                        'file' => $files[$index]->getClientOriginalName(),
                        'errors' => $result['errors']
                    ];
                } else {
                    $validFiles[$index] = $files[$index];
                }
            }

            if (!empty($failedFiles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some files failed validation',
                    'failed_files' => $failedFiles,
                    'valid_files' => []
                ], 422);
            }

            // Process all valid files
            $uploadedFiles = [];
            foreach ($validFiles as $index => $file) {
                $fileData = $this->processFile($file, $type);
                // Generate a unique ID for each file
                $fileId = uniqid('file_');
                $uploadedFiles[] = array_merge(['id' => $fileId], $fileData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'data' => $uploadedFiles
            ]);
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

        // Note: We no longer create ContentPostMedia records here since they require a content_post_id
        // Files uploaded through this controller are temporary and should be associated with content posts
        // through the ContentPostController's processMediaUploads method

        return $fileData;
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
        $config = [
            'max_sizes' => [],
            'allowed_types' => $this->fileValidationService->getAllowedTypes(),
            'mime_types' => []
        ];

        foreach ($config['allowed_types'] as $type) {
            $config['max_sizes'][$type] = $this->fileValidationService->getMaxSizeForType($type);
            $config['mime_types'][$type] = $this->fileValidationService->getMimeTypesForType($type);
        }

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }
}
