# BaseFileUploader Backend Documentation

## Table of Contents

-   [Backend Overview](#backend-overview)
-   [FileUploader Service](#fileuploader-service)
-   [ParentPageService File Processing](#parentpageservice-file-processing)
-   [DynamicPageUploadController](#dynamicpageuploadcontroller)
-   [File Processing Flow](#file-processing-flow)
-   [File Field Mapping](#file-field-mapping)
-   [Storage Configuration](#storage-configuration)
-   [Error Handling](#error-handling)
-   [Security Considerations](#security-considerations)
-   [Backend Implementation Examples](#backend-implementation-examples)

## Backend Overview

The backend component of the BaseFileUploader system is built on Laravel and handles file processing, validation, storage, and URL generation. It works in conjunction with the PRXBackend package's FileUploader service to provide a comprehensive file management solution.

### Key Components

1. **FileUploader Service** - Core file handling from PRXBackend package
2. **ParentPageService** - Processes file uploads in content blocks
3. **DynamicPageUploadController** - Handles direct file upload endpoints
4. **Storage Configuration** - Manages file storage and retrieval

### Dependencies

-   Laravel framework
-   PRXBackend package (for FileUploader service)
-   Laravel Filesystem
-   Laravel Validation

## FileUploader Service

The FileUploader service is provided by the PRXBackend package and handles the core file operations including storage, URL generation, and file management.

### Basic Usage

```php
use PRAXXYS\Backend\Facades\FileUploader;

// Store a file
$uploadedFile = FileUploader::store($file, 'dynamic-pages');

// Returns an object with:
// - id: File identifier
// - url: Public URL to access the file
// - filename: Original filename
```

### Return Structure

The `FileUploader::store()` method returns an object with the following structure:

```php
{
    "id": 1,
    "url": "https://example.com/storage/dynamic-pages/filename.jpg",
    "filename": "original-filename.jpg"
}
```

### Storage Locations

Files are stored in configurable locations based on the second parameter:

```php
// Store in dynamic-pages directory
$uploadedFile = FileUploader::store($file, 'dynamic-pages');

// Store in products directory
$uploadedFile = FileUploader::store($file, 'products');

// Store in user-avatars directory
$uploadedFile = FileUploader::store($file, 'user-avatars');
```

## ParentPageService File Processing

The ParentPageService handles file processing within content blocks, particularly for dynamic pages with nested data structures.

**Location**: `app/Services/Admin/Contents/ParentPageService.php`

### Key Methods

#### processStoreData(array $data)

Processes data before storing new records, including file uploads:

```php
public function processStoreData(array $data): array
{
    // Process file uploads in content blocks
    $data = $this->processBlockFileUploads($data);

    // Additional processing logic...

    return $data;
}
```

#### processUpdateData(array $data)

Processes data before updating existing records:

```php
public function processUpdateData(array $data): array
{
    // Process file uploads in content blocks
    $data = $this->processBlockFileUploads($data);

    // Additional processing logic...

    return $data;
}
```

#### processBlockFileUploads(array $data)

The core method that handles file uploads within content blocks:

```php
protected function processBlockFileUploads(array $data): array
{
    $processedData = $data;

    // File fields that need to be processed
    $fileFields = [
        'background_image_file' => 'background_image',
        'image_file' => 'image',
        'side_image_file' => 'side_image',
        'featured_image_file' => 'featured_image',
        'primary_image_file' => 'primary_image',
        'secondary_image_file' => 'secondary_image',
    ];

    foreach ($fileFields as $fileField => $pathField) {
        if (isset($processedData[$fileField]) && $processedData[$fileField]) {
            // Check if it's a valid file upload
            if ($processedData[$fileField] instanceof \Illuminate\Http\UploadedFile) {
                $uploadedFile = \PRAXXYS\Backend\Facades\FileUploader::store(
                    $processedData[$fileField],
                    'dynamic-pages'
                );
                if ($uploadedFile) {
                    // Use the full URL from the uploaded file object
                    $processedData[$pathField] = $uploadedFile->url;
                }
            }
            // Remove the file field after processing
            unset($processedData[$fileField]);
        }
    }

    // Process nested arrays (like items, sections, etc.)
    foreach ($processedData as $key => $value) {
        if (is_array($value)) {
            if ($this->isArrayOfObjects($value)) {
                // Process array of objects (like CTA sections, feature items, etc.)
                $processedData[$key] = collect($value)->map(function ($item) {
                    return is_array($item) ? $this->processBlockFileUploads($item) : $item;
                })->toArray();
            }
        }
    }

    return $processedData;
}
```

### File Field Mapping

The service uses a mapping system to associate file objects with their corresponding path fields:

```php
$fileFields = [
    'background_image_file' => 'background_image',
    'image_file' => 'image',
    'side_image_file' => 'side_image',
    'featured_image_file' => 'featured_image',
    'primary_image_file' => 'primary_image',
    'secondary_image_file' => 'secondary_image',
];
```

This mapping follows the pattern:

-   `{field}_file` - The file object from the form
-   `{field}` - The path/URL field to store in the database

### Nested Array Processing

The service recursively processes nested arrays to handle complex content structures:

```php
// Process nested arrays (like items, sections, etc.)
foreach ($processedData as $key => $value) {
    if (is_array($value)) {
        if ($this->isArrayOfObjects($value)) {
            // Process array of objects (like CTA sections, feature items, etc.)
            $processedData[$key] = collect($value)->map(function ($item) {
                return is_array($item) ? $this->processBlockFileUploads($item) : $item;
            })->toArray();
        }
    }
}
```

## DynamicPageUploadController

The DynamicPageUploadController provides direct file upload endpoints for scenarios where files need to be uploaded independently of form submissions.

**Location**: `app/Http/Controllers/Admin/Contents/DynamicPageUploadController.php`

### Endpoints

#### POST /admin/contents/items/dynamic-pages/upload/image

Single image upload endpoint:

```php
public function uploadImage(Request $request): JsonResponse
{
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    try {
        $uploadedFile = FileUploader::store($request->file('image'), 'dynamic-pages');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $uploadedFile->id,
                'url' => $uploadedFile->url,
                'filename' => $uploadedFile->filename,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to upload image: ' . $e->getMessage(),
        ], 500);
    }
}
```

#### POST /admin/contents/items/dynamic-pages/upload/images

Multiple image upload endpoint:

```php
public function uploadMultipleImages(Request $request): JsonResponse
{
    $request->validate([
        'images' => 'required|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    try {
        $uploadedFiles = [];

        foreach ($request->file('images') as $image) {
            $uploadedFile = FileUploader::store($image, 'dynamic-pages');
            $uploadedFiles[] = [
                'id' => $uploadedFile->id,
                'url' => $uploadedFile->url,
                'filename' => $uploadedFile->filename,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $uploadedFiles,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to upload images: ' . $e->getMessage(),
        ], 500);
    }
}
```

### Route Configuration

The controller routes are configured in the routes file:

```php
Route::prefix('/dynamic-pages/upload')
    ->name('dynamic-pages.upload.')
    ->controller(DynamicPageUploadController::class)
    ->group(function () {
        Route::post('/image', 'uploadImage')->name('image')->middleware('can:can-create-parent-pages');
        Route::post('/images', 'uploadMultipleImages')->name('images')->middleware('can:can-create-parent-pages');
    });
```

### Validation Rules

The controller applies validation rules to ensure file security and integrity:

```php
// Single image validation
$request->validate([
    'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
]);

// Multiple images validation
$request->validate([
    'images' => 'required|array',
    'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
]);
```

## File Processing Flow

### Step 1: Form Submission

1. Frontend submits form with file objects
2. Laravel receives request with `UploadedFile` objects
3. Controller delegates to service for processing

### Step 2: Service Processing

1. Service identifies file fields using the mapping system
2. Each file field is processed individually
3. `FileUploader::store()` handles the actual file storage
4. File objects are replaced with URLs in the data array

### Step 3: File Storage

1. FileUploader stores the file in the specified directory
2. Generates a unique filename if needed
3. Creates a database record for the file
4. Returns the file object with URL and metadata

### Step 4: Database Storage

1. Processed data (with file URLs) is saved to the database
2. File paths are stored as strings in JSON content blocks
3. File object fields are removed from the data

### Step 5: Response Handling

1. Success/error response is returned to the frontend
2. Frontend updates UI based on the response

## File Field Mapping

### Standard Mapping Pattern

The backend uses a consistent pattern for mapping file objects to their stored paths:

```php
$fileFields = [
    'background_image_file' => 'background_image',
    'image_file' => 'image',
    'side_image_file' => 'side_image',
    'featured_image_file' => 'featured_image',
    'primary_image_file' => 'primary_image',
    'secondary_image_file' => 'secondary_image',
];
```

### Custom Field Mapping

You can extend the mapping to include custom fields:

```php
// Add custom fields to the mapping
$fileFields = [
    // Standard fields
    'background_image_file' => 'background_image',
    'image_file' => 'image',

    // Custom fields
    'logo_file' => 'logo',
    'document_file' => 'document',
    'avatar_file' => 'avatar',
];
```

### Processing Logic

The processing logic follows this pattern:

```php
foreach ($fileFields as $fileField => $pathField) {
    if (isset($processedData[$fileField]) && $processedData[$fileField]) {
        // Check if it's a valid file upload
        if ($processedData[$fileField] instanceof \Illuminate\Http\UploadedFile) {
            $uploadedFile = \PRAXXYS\Backend\Facades\FileUploader::store(
                $processedData[$fileField],
                'dynamic-pages'
            );
            if ($uploadedFile) {
                // Use the full URL from the uploaded file object
                $processedData[$pathField] = $uploadedFile->url;
            }
        }
        // Remove the file field after processing
        unset($processedData[$fileField]);
    }
}
```

## Storage Configuration

### Filesystem Configuration

The FileUploader service uses Laravel's filesystem configuration. Ensure your `config/filesystems.php` is properly configured:

```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],

    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],

    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
    ],
],
```

### Storage Link

Make sure the storage link is created to make files publicly accessible:

```bash
php artisan storage:link
```

### Custom Storage Directories

You can specify custom storage directories when storing files:

```php
// Store in custom directory
$uploadedFile = FileUploader::store($file, 'custom-directory');

// Store with subdirectories
$uploadedFile = FileUploader::store($file, 'products/images');
```

## Error Handling

### Controller-Level Error Handling

The DynamicPageUploadController implements comprehensive error handling:

```php
try {
    $uploadedFile = FileUploader::store($request->file('image'), 'dynamic-pages');

    return response()->json([
        'success' => true,
        'data' => [
            'id' => $uploadedFile->id,
            'url' => $uploadedFile->url,
            'filename' => $uploadedFile->filename,
        ],
    ]);
} catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'message' => 'Failed to upload image: ' . $e->getMessage(),
    ], 500);
}
```

### Service-Level Error Handling

The ParentPageService includes validation checks:

```php
if ($processedData[$fileField] instanceof \Illuminate\Http\UploadedFile) {
    $uploadedFile = \PRAXXYS\Backend\Facades\FileUploader::store(
        $processedData[$fileField],
        'dynamic-pages'
    );
    if ($uploadedFile) {
        $processedData[$pathField] = $uploadedFile->url;
    }
}
```

### Validation Error Responses

The controller returns structured error responses:

```php
// Validation failure
return response()->json([
    'success' => false,
    'message' => 'Validation failed',
    'errors' => $validator->errors(),
], 422);

// Upload failure
return response()->json([
    'success' => false,
    'message' => 'Failed to upload image: ' . $e->getMessage(),
], 500);
```

## Security Considerations

### File Type Validation

Always validate file types on the backend:

```php
$request->validate([
    'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
]);
```

### File Size Limits

Implement file size limits to prevent abuse:

```php
$request->validate([
    'image' => 'required|image|max:2048', // 2MB limit
]);
```

### Authentication and Authorization

Protect upload endpoints with proper middleware:

```php
Route::post('/image', 'uploadImage')
    ->name('image')
    ->middleware('can:can-create-parent-pages');
```

### File Sanitization

The FileUploader service handles file name sanitization, but you can add additional validation:

```php
// Validate file name
$fileName = $file->getClientOriginalName();
if (!preg_match('/^[a-zA-Z0-9._-]+$/', $fileName)) {
    throw new \Exception('Invalid file name');
}
```

## Backend Implementation Examples

### Custom File Processing Service

```php
<?php

namespace App\Services;

use PRAXXYS\Backend\Facades\FileUploader;
use Illuminate\Http\UploadedFile;

class CustomFileService
{
    protected $fileFields = [
        'logo_file' => 'logo',
        'banner_file' => 'banner',
        'document_file' => 'document',
    ];

    public function processFiles(array $data, string $storagePath = 'custom'): array
    {
        $processedData = $data;

        foreach ($this->fileFields as $fileField => $pathField) {
            if (isset($processedData[$fileField]) && $processedData[$fileField] instanceof UploadedFile) {
                $uploadedFile = FileUploader::store(
                    $processedData[$fileField],
                    $storagePath
                );

                if ($uploadedFile) {
                    $processedData[$pathField] = $uploadedFile->url;
                }

                unset($processedData[$fileField]);
            }
        }

        return $processedData;
    }

    public function processNestedFiles(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->processFiles($value, 'nested');
            }
        }

        return $data;
    }
}
```

### Custom Upload Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PRAXXYS\Backend\Facades\FileUploader;

class CustomUploadController extends Controller
{
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,pdf,doc,docx|max:10240',
            'type' => 'required|string|in:profile,document,other',
        ]);

        try {
            $storagePath = match($request->type) {
                'profile' => 'profiles',
                'document' => 'documents',
                default => 'misc'
            };

            $uploadedFile = FileUploader::store(
                $request->file('file'),
                $storagePath
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $uploadedFile->id,
                    'url' => $uploadedFile->url,
                    'filename' => $uploadedFile->filename,
                    'type' => $request->type,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
```

### Extending ParentPageService

```php
<?php

namespace App\Services\Admin\Contents;

use App\Services\Admin\Contents\ParentPageService as BaseParentPageService;

class ExtendedParentPageService extends BaseParentPageService
{
    protected function processBlockFileUploads(array $data): array
    {
        // Call parent method first
        $data = parent::processBlockFileUploads($data);

        // Add custom file fields
        $customFileFields = [
            'custom_image_file' => 'custom_image',
            'attachment_file' => 'attachment',
        ];

        foreach ($customFileFields as $fileField => $pathField) {
            if (isset($data[$fileField]) && $data[$fileField]) {
                if ($data[$fileField] instanceof \Illuminate\Http\UploadedFile) {
                    $uploadedFile = \PRAXXYS\Backend\Facades\FileUploader::store(
                        $data[$fileField],
                        'custom-uploads'
                    );
                    if ($uploadedFile) {
                        $data[$pathField] = $uploadedFile->url;
                    }
                }
                unset($data[$fileField]);
            }
        }

        return $data;
    }
}
```

This comprehensive backend documentation provides all the necessary information for developers to understand, implement, and extend the backend file processing system for the BaseFileUploader component.
