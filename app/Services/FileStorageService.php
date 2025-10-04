<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FileStorageService
{
    protected string $disk = 'public';

    /**
     * Store file with organized directory structure
     */
    public function storeFile(UploadedFile $file, string $directory = 'uploads'): array
    {
        try {
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file);
            
            // Create organized directory structure
            $path = $this->buildDirectoryPath($directory, $file);
            $fullPath = $path . '/' . $filename;
            
            // Store file
            $storedPath = $file->storeAs($path, $filename, $this->disk);
            
            return [
                'path' => $storedPath,
                'url' => Storage::disk($this->disk)->url($storedPath),
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'directory' => $path,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to store file', [
                'original_name' => $file->getClientOriginalName(),
                'directory' => $directory,
                'error' => $e->getMessage()
            ]);
            throw new \Exception("Failed to store file: {$e->getMessage()}");
        }
    }

    /**
     * Store image with automatic optimization
     */
    public function storeImage(UploadedFile $file, string $directory = 'images'): array
    {
        $result = $this->storeFile($file, 'content/' . $directory);
        
        // Trigger image optimization
        try {
            $imageService = app(ImageService::class);
            
            if (app()->environment('production')) {
                // In production, you might want to queue this
                // dispatch(new OptimizeImageJob($result['path']));
                $imageService->optimizeExistingImage($result['path']);
            } else {
                // Optimize immediately in development
                $imageService->optimizeExistingImage($result['path']);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to optimize image', [
                'path' => $result['path'],
                'error' => $e->getMessage()
            ]);
            // Don't fail the upload if optimization fails
        }
        
        return $result;
    }

    /**
     * Store media file with type-specific handling
     */
    public function storeMediaFile(UploadedFile $file): array
    {
        $directory = 'content/media';
        
        // Add subdirectory based on file type
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $directory .= '/images';
        } elseif (in_array($file->getMimeType(), ['application/pdf'])) {
            $directory .= '/documents';
        } elseif (in_array($file->getMimeType(), [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv'
        ])) {
            $directory .= '/documents';
        } else {
            $directory .= '/other';
        }
        
        $result = $this->storeFile($file, $directory);
        
        // Optimize images and create thumbnails
        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                $imageService = app(ImageService::class);
                
                if (app()->environment('production')) {
                    // dispatch(new OptimizeImageJob($result['path']));
                    $imageService->optimizeExistingImage($result['path']);
                } else {
                    $imageService->optimizeExistingImage($result['path']);
                }
                
                // Create thumbnail
                $this->createThumbnail($result['path']);
            } catch (\Exception $e) {
                Log::warning('Failed to optimize media image', [
                    'path' => $result['path'],
                    'error' => $e->getMessage()
                ]);
                // Don't fail the upload if optimization fails
            }
        }
        
        return $result;
    }

    /**
     * Store multiple files
     */
    public function storeFiles(array $files, string $directory = 'uploads'): array
    {
        $results = [];
        
        foreach ($files as $index => $file) {
            try {
                $results[$index] = $this->storeFile($file, $directory);
            } catch (\Exception $e) {
                Log::error('Failed to store file in batch', [
                    'index' => $index,
                    'original_name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);
                
                $results[$index] = [
                    'error' => $e->getMessage(),
                    'original_name' => $file->getClientOriginalName()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Create thumbnail for image
     */
    protected function createThumbnail(string $imagePath): ?string
    {
        try {
            $thumbnailPath = $this->getThumbnailPath($imagePath);
            $imageService = app(ImageService::class);
            
            $success = $imageService->createThumbnailFromFile(
                $imagePath,
                $thumbnailPath,
                300,
                300
            );
            
            return $success ? $thumbnailPath : null;
        } catch (\Exception $e) {
            Log::error("Failed to create thumbnail for: {$imagePath}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        return Str::uuid() . '.' . $file->getClientOriginalExtension();
    }

    /**
     * Build directory path with date structure
     */
    protected function buildDirectoryPath(string $baseDirectory, UploadedFile $file): string
    {
        return $baseDirectory . '/' . date('Y/m');
    }

    /**
     * Get thumbnail path for image
     */
    protected function getThumbnailPath(string $imagePath): string
    {
        $parts = pathinfo($imagePath);
        $thumbnailDir = dirname($imagePath) . '/thumbnails';
        $thumbnailName = $parts['filename'] . '_thumb.' . $parts['extension'];
        
        return $thumbnailDir . '/' . $thumbnailName;
    }

    /**
     * Delete file and associated thumbnails
     */
    public function deleteFile(string $path): bool
    {
        try {
            $deleted = Storage::disk($this->disk)->delete($path);
            
            // Delete thumbnail if it's an image
            if (str_starts_with($path, 'content/media/images/')) {
                $thumbnailPath = $this->getThumbnailPath($path);
                Storage::disk($this->disk)->delete($thumbnailPath);
            }
            
            if ($deleted) {
                Log::info("File deleted: {$path}");
            }
            
            return $deleted;
            
        } catch (\Exception $e) {
            Log::error("Failed to delete file: {$path}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete multiple files
     */
    public function deleteFiles(array $paths): array
    {
        $results = [];
        
        foreach ($paths as $path) {
            $results[$path] = $this->deleteFile($path);
        }
        
        return $results;
    }

    /**
     * Get file URL
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Get file size
     */
    public function getFileSize(string $path): int
    {
        return Storage::disk($this->disk)->size($path);
    }

    /**
     * Move file to new location
     */
    public function moveFile(string $fromPath, string $toPath): bool
    {
        try {
            return Storage::disk($this->disk)->move($fromPath, $toPath);
        } catch (\Exception $e) {
            Log::error("Failed to move file from {$fromPath} to {$toPath}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create directory if it doesn't exist
     */
    public function ensureDirectory(string $directory): void
    {
        try {
            Storage::disk($this->disk)->makeDirectory($directory);
        } catch (\Exception $e) {
            Log::error("Failed to create directory: {$directory}", [
                'error' => $e->getMessage()
            ]);
        }
    }
}