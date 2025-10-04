<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ValidFileType implements ValidationRule
{
    protected array $allowedTypes;
    protected string $customMessage;

    public function __construct(array $allowedTypes, string $customMessage = '')
    {
        $this->allowedTypes = $allowedTypes;
        $this->customMessage = $customMessage;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The :attribute must be a file.');
            return;
        }

        $mimeType = $value->getMimeType();
        
        // Check if MIME type is in allowed list
        if (!in_array($mimeType, $this->allowedTypes)) {
            $message = $this->customMessage ?: 
                "The :attribute must be one of: " . implode(', ', $this->allowedTypes);
            $fail($message);
            return;
        }

        // Additional content validation for images
        if (str_starts_with($mimeType, 'image/')) {
            $this->validateImageContent($value, $fail);
        }

        // Additional validation for documents
        if (in_array($mimeType, [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv'
        ])) {
            $this->validateDocumentContent($value, $fail);
        }
    }

    /**
     * Validate image content to prevent exploits
     */
    protected function validateImageContent(UploadedFile $file, Closure $fail): void
    {
        try {
            // Check if it's actually a valid image
            $imageInfo = @getimagesize($file->getPathname());
            
            if (!$imageInfo) {
                $fail('The :attribute is not a valid image file.');
                return;
            }

            // Check for common image exploit patterns
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $file->getPathname());
            finfo_close($finfo);

            if (!str_starts_with($detectedMime, 'image/')) {
                $fail('The :attribute contains invalid content.');
                return;
            }

            // Validate image dimensions
            [$width, $height] = $imageInfo;
            $maxDimensions = config('validation.file_upload.max_image_dimensions', [4000, 4000]);
            $minDimensions = config('validation.file_upload.min_image_dimensions', [100, 100]);

            if ($width > $maxDimensions[0] || $height > $maxDimensions[1]) {
                $fail("The :attribute dimensions are too large. Maximum size is {$maxDimensions[0]}x{$maxDimensions[1]} pixels.");
                return;
            }

            if ($width < $minDimensions[0] || $height < $minDimensions[1]) {
                $fail("The :attribute dimensions are too small. Minimum size is {$minDimensions[0]}x{$minDimensions[1]} pixels.");
                return;
            }

        } catch (\Exception $e) {
            Log::error('Image validation failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            $fail('The :attribute could not be validated.');
        }
    }

    /**
     * Validate document content
     */
    protected function validateDocumentContent(UploadedFile $file, Closure $fail): void
    {
        try {
            $mimeType = $file->getMimeType();
            
            // Validate PDF content
            if ($mimeType === 'application/pdf') {
                $this->validatePdfContent($file, $fail);
            }
            
            // Validate Excel content
            elseif (in_array($mimeType, [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel'
            ])) {
                $this->validateExcelContent($file, $fail);
            }
            
            // Validate CSV content
            elseif ($mimeType === 'text/csv') {
                $this->validateCsvContent($file, $fail);
            }

        } catch (\Exception $e) {
            Log::error('Document validation failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            $fail('The :attribute could not be validated.');
        }
    }

    /**
     * Validate PDF content
     */
    protected function validatePdfContent(UploadedFile $file, Closure $fail): void
    {
        // Check file signature
        $handle = fopen($file->getPathname(), 'rb');
        $signature = fread($handle, 4);
        fclose($handle);

        if ($signature !== '%PDF') {
            $fail('The :attribute is not a valid PDF file.');
            return;
        }

        // Check file size (PDF files shouldn't be too large for web use)
        $maxSize = config('validation.file_upload.max_size', 10 * 1024 * 1024);
        if ($file->getSize() > $maxSize) {
            $fail('The :attribute is too large for web upload.');
            return;
        }
    }

    /**
     * Validate Excel content
     */
    protected function validateExcelContent(UploadedFile $file, Closure $fail): void
    {
        // Check file signature for Excel files
        $handle = fopen($file->getPathname(), 'rb');
        $signature = fread($handle, 4);
        fclose($handle);

        // Excel files have specific signatures
        $validSignatures = [
            "\xD0\xCF\x11\xE0", // Old Excel format
            "PK\x03\x04",       // New Excel format (ZIP-based)
        ];

        $isValidSignature = false;
        foreach ($validSignatures as $validSignature) {
            if (str_starts_with($signature, $validSignature)) {
                $isValidSignature = true;
                break;
            }
        }

        if (!$isValidSignature) {
            $fail('The :attribute is not a valid Excel file.');
            return;
        }
    }

    /**
     * Validate CSV content
     */
    protected function validateCsvContent(UploadedFile $file, Closure $fail): void
    {
        // Read first few lines to validate CSV structure
        $handle = fopen($file->getPathname(), 'r');
        if (!$handle) {
            $fail('The :attribute could not be read.');
            return;
        }

        $firstLine = fgets($handle);
        fclose($handle);

        if ($firstLine === false) {
            $fail('The :attribute appears to be empty.');
            return;
        }

        // Basic CSV validation - should contain commas or be a single value
        $trimmedLine = trim($firstLine);
        if (empty($trimmedLine)) {
            $fail('The :attribute appears to be empty.');
            return;
        }
    }
}