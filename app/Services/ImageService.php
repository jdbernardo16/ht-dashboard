<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class ImageService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Process and optimize an uploaded image
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function processImage(UploadedFile $file, string $directory = 'content_images', array $options = []): array
    {
        try {
            // Default options
            $defaults = [
                'max_width' => 1920,
                'max_height' => 1080,
                'quality' => 85,
                'format' => 'jpg',
                'create_thumbnail' => true,
                'thumbnail_size' => 300,
                'optimize' => true,
            ];

            $options = array_merge($defaults, $options);

            // Validate file
            $this->validateImage($file);

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file, $options['format']);
            $path = $directory . '/' . $filename;

            // Process main image
            $image = $this->imageManager->read($file);

            // Resize if necessary
            if ($image->width() > $options['max_width'] || $image->height() > $options['max_height']) {
                $image->scale(width: $options['max_width'], height: $options['max_height']);
            }

            // Optimize and save
            if ($options['optimize']) {
                $image->toJpeg($options['quality']);
            }

            // Save to storage
            $fullPath = Storage::disk('public')->put($path, $image->toJpeg($options['quality'])->toFilePointer());

            if (!$fullPath) {
                throw new Exception('Failed to save image to storage');
            }

            $result = [
                'original_name' => $file->getClientOriginalName(),
                'filename' => $filename,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'width' => $image->width(),
                'height' => $image->height(),
                'thumbnail_path' => null,
                'thumbnail_url' => null,
            ];

            // Create thumbnail if requested
            if ($options['create_thumbnail']) {
                $thumbnailResult = $this->createThumbnail($image, $directory, $filename, $options['thumbnail_size']);
                $result['thumbnail_path'] = $thumbnailResult['path'];
                $result['thumbnail_url'] = $thumbnailResult['url'];
            }

            Log::info('Image processed successfully', [
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'dimensions' => $image->width() . 'x' . $image->height()
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('Image processing failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);
            throw $e;
        }
    }

    /**
     * Create a thumbnail from an image
     *
     * @param mixed $image
     * @param string $directory
     * @param string $originalFilename
     * @param int $size
     * @return array
     */
    protected function createThumbnail($image, string $directory, string $originalFilename, int $size): array
    {
        try {
            // Create thumbnail
            $thumbnail = clone $image;
            $thumbnail->scale(width: $size, height: $size);

            // Generate thumbnail filename
            $thumbnailFilename = 'thumb_' . $originalFilename;
            $thumbnailPath = $directory . '/thumbnails/' . $thumbnailFilename;

            // Save thumbnail
            Storage::disk('public')->put($thumbnailPath, $thumbnail->toJpeg(80)->toFilePointer());

            return [
                'path' => $thumbnailPath,
                'url' => Storage::disk('public')->url($thumbnailPath),
            ];

        } catch (Exception $e) {
            Log::warning('Thumbnail creation failed', [
                'error' => $e->getMessage(),
                'original_filename' => $originalFilename
            ]);
            return ['path' => null, 'url' => null];
        }
    }

    /**
     * Delete an image and its thumbnail
     *
     * @param string $path
     * @return bool
     */
    public function deleteImage(string $path): bool
    {
        try {
            $deleted = Storage::disk('public')->delete($path);

            // Also try to delete thumbnail if it exists
            $thumbnailPath = $this->getThumbnailPath($path);
            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            Log::info('Image deleted successfully', ['path' => $path, 'deleted' => $deleted]);

            return $deleted;

        } catch (Exception $e) {
            Log::error('Image deletion failed', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            return false;
        }
    }

    /**
     * Delete multiple images
     *
     * @param array $paths
     * @return array
     */
    public function deleteImages(array $paths): array
    {
        $results = [];

        foreach ($paths as $path) {
            $results[$path] = $this->deleteImage($path);
        }

        return $results;
    }

    /**
     * Get thumbnail path for an image
     *
     * @param string $imagePath
     * @return string|null
     */
    protected function getThumbnailPath(string $imagePath): ?string
    {
        $pathInfo = pathinfo($imagePath);

        if ($pathInfo['dirname'] === '.') {
            return 'thumbnails/thumb_' . $pathInfo['basename'];
        }

        return $pathInfo['dirname'] . '/thumbnails/thumb_' . $pathInfo['basename'];
    }

    /**
     * Validate uploaded image
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    protected function validateImage(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('Invalid file uploaded');
        }

        // Check file size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new Exception('File size exceeds maximum limit of 10MB');
        }

        // Check MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed');
        }

        // Additional security check - verify image dimensions
        try {
            $image = $this->imageManager->read($file);
            if ($image->width() > 5000 || $image->height() > 5000) {
                throw new Exception('Image dimensions are too large (max 5000x5000 pixels)');
            }
        } catch (Exception $e) {
            throw new Exception('Invalid image file: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique filename
     *
     * @param UploadedFile $file
     * @param string $format
     * @return string
     */
    protected function generateUniqueFilename(UploadedFile $file, string $format): string
    {
        $extension = $format;
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Sanitize filename
        $originalName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $originalName);

        return time() . '_' . uniqid() . '_' . $originalName . '.' . $extension;
    }

    /**
     * Get image information
     *
     * @param string $path
     * @return array|null
     */
    public function getImageInfo(string $path): ?array
    {
        try {
            if (!Storage::disk('public')->exists($path)) {
                return null;
            }

            $file = Storage::disk('public')->get($path);
            $image = $this->imageManager->read($file);

            return [
                'width' => $image->width(),
                'height' => $image->height(),
                'size' => Storage::disk('public')->size($path),
                'mime_type' => Storage::disk('public')->mimeType($path),
                'url' => Storage::disk('public')->url($path),
            ];

        } catch (Exception $e) {
            Log::error('Failed to get image info', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            return null;
        }
    }

    /**
     * Optimize existing image
     *
     * @param string $path
     * @param array $options
     * @return bool
     */
    public function optimizeExistingImage(string $path, array $options = []): bool
    {
        try {
            if (!Storage::disk('public')->exists($path)) {
                return false;
            }

            $defaults = [
                'quality' => 85,
                'max_width' => 1920,
                'max_height' => 1080,
            ];

            $options = array_merge($defaults, $options);

            $file = Storage::disk('public')->get($path);
            $image = $this->imageManager->read($file);

            // Resize if necessary
            if ($image->width() > $options['max_width'] || $image->height() > $options['max_height']) {
                $image->scale(width: $options['max_width'], height: $options['max_height']);
            }

            // Save optimized version
            Storage::disk('public')->put($path, $image->toJpeg($options['quality'])->toFilePointer());

            Log::info('Image optimized successfully', ['path' => $path]);

            return true;

        } catch (Exception $e) {
            Log::error('Image optimization failed', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            return false;
        }
    }
}