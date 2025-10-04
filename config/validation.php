<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Upload Validation Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the validation rules for file uploads throughout
    | the application. You can customize limits, allowed types, and other
    | validation parameters here.
    |
    */

    'file_upload' => [
        // Maximum file size in bytes (default: 10MB)
        'max_size' => env('FILE_MAX_SIZE', 10 * 1024 * 1024),
        
        // Maximum number of files per upload
        'max_files' => env('FILE_MAX_COUNT', 10),
        
        // Allowed image extensions
        'allowed_images' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
        
        // Allowed document extensions
        'allowed_documents' => ['pdf', 'xlsx', 'csv', 'doc', 'docx'],
        
        // Maximum image dimensions [width, height]
        'max_image_dimensions' => [4000, 4000],
        
        // Minimum image dimensions [width, height]
        'min_image_dimensions' => [100, 100],
        
        // Image quality for optimization (0-100)
        'image_quality' => env('IMAGE_QUALITY', 85),
        
        // Thumbnail dimensions [width, height]
        'thumbnail_dimensions' => [300, 300],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | These settings control security-related validation for file uploads.
    | Enable or disable features based on your security requirements.
    |
    */

    'security' => [
        // Scan uploaded files for malware (requires ClamAV or similar)
        'scan_uploads' => env('SCAN_UPLOADS', false),
        
        // Quarantine suspicious files
        'quarantine_suspicious' => env('QUARANTINE_SUSPICIOUS', true),
        
        // Validate file content (not just extension)
        'validate_content' => env('VALIDATE_FILE_CONTENT', true),
        
        // Allow executable files (should be false for security)
        'allow_executables' => env('ALLOW_EXECUTABLES', false),
        
        // Check for common exploit patterns
        'check_exploits' => env('CHECK_FILE_EXPLOITS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how files are stored and organized.
    | Customize based on your storage preferences.
    |
    */

    'storage' => [
        // Default storage disk for uploads
        'disk' => env('FILE_STORAGE_DISK', 'public'),
        
        // Use date-based directory organization
        'use_date_directories' => env('USE_DATE_DIRECTORIES', true),
        
        // Directory structure format (Y/m, Y/m/d, etc.)
        'date_format' => env('DIRECTORY_DATE_FORMAT', 'Y/m'),
        
        // Generate UUID filenames (recommended for uniqueness)
        'use_uuid_names' => env('USE_UUID_NAMES', true),
        
        // Keep original filenames as metadata
        'keep_original_names' => env('KEEP_ORIGINAL_NAMES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how images are processed and optimized.
    | Adjust based on your performance and quality requirements.
    |
    */

    'image_processing' => [
        // Automatically optimize uploaded images
        'auto_optimize' => env('AUTO_OPTIMIZE_IMAGES', true),
        
        // Generate thumbnails for images
        'generate_thumbnails' => env('GENERATE_THUMBNAILS', true),
        
        // Resize large images to prevent storage issues
        'resize_large_images' => env('RESIZE_LARGE_IMAGES', true),
        
        // Maximum dimensions for resized images
        'max_resize_dimensions' => [
            'width' => env('MAX_IMAGE_WIDTH', 1920),
            'height' => env('MAX_IMAGE_HEIGHT', 1080),
        ],
        
        // Preserve aspect ratio when resizing
        'preserve_aspect_ratio' => env('PRESERVE_ASPECT_RATIO', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | Custom error messages for file upload validation failures.
    | These can be customized to match your application's tone.
    |
    */

    'messages' => [
        'file_too_large' => 'The file may not be greater than :max_size.',
        'invalid_file_type' => 'The file must be a file of type: :allowed_types.',
        'invalid_image' => 'The file must be a valid image.',
        'dimensions_invalid' => 'The image has invalid dimensions. Must be between :min_widthx:min_height and :max_widthx:max_height pixels.',
        'too_many_files' => 'You may not upload more than :max_files files.',
        'upload_failed' => 'Failed to upload the file. Please try again.',
        'storage_error' => 'Storage error occurred. Please contact support.',
        'security_violation' => 'File failed security validation.',
    ],
];