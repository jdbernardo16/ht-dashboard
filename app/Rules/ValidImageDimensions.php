<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ValidImageDimensions implements ValidationRule
{
    protected int $minWidth;
    protected int $minHeight;
    protected int $maxWidth;
    protected int $maxHeight;
    protected string $customMessage;

    public function __construct(
        int $minWidth = 100,
        int $minHeight = 100,
        int $maxWidth = 4000,
        int $maxHeight = 4000,
        string $customMessage = ''
    ) {
        $this->minWidth = $minWidth;
        $this->minHeight = $minHeight;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
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

        // Check if it's actually an image
        $mimeType = $value->getMimeType();
        if (!str_starts_with($mimeType, 'image/')) {
            $fail('The :attribute must be an image file.');
            return;
        }

        try {
            // Get image dimensions
            $imageInfo = @getimagesize($value->getPathname());
            
            if (!$imageInfo) {
                $fail('The :attribute is not a valid image file.');
                return;
            }

            [$width, $height] = $imageInfo;

            // Validate minimum dimensions
            if ($width < $this->minWidth || $height < $this->minHeight) {
                $message = $this->customMessage ?: 
                    "The :attribute must be at least {$this->minWidth}x{$this->minHeight} pixels.";
                $fail($message);
                return;
            }

            // Validate maximum dimensions
            if ($width > $this->maxWidth || $height > $this->maxHeight) {
                $message = $this->customMessage ?: 
                    "The :attribute may not be larger than {$this->maxWidth}x{$this->maxHeight} pixels.";
                $fail($message);
                return;
            }

            // Additional validation for aspect ratio if needed
            $this->validateAspectRatio($width, $height, $fail);

        } catch (\Exception $e) {
            Log::error('Image dimension validation failed', [
                'file' => $value->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            $fail('The :attribute dimensions could not be validated.');
        }
    }

    /**
     * Validate aspect ratio (optional)
     */
    protected function validateAspectRatio(int $width, int $height, Closure $fail): void
    {
        // Check for extremely unusual aspect ratios that might indicate issues
        $aspectRatio = $width / $height;
        
        // Prevent extremely wide or tall images
        if ($aspectRatio > 10 || $aspectRatio < 0.1) {
            $fail('The :attribute has an unusual aspect ratio and may not display correctly.');
        }

        // Check for square images (optional validation)
        // if (abs($aspectRatio - 1.0) < 0.1) {
        //     // This is approximately square
        // }
    }

    /**
     * Get the validation error message
     */
    public function message(): string
    {
        if ($this->customMessage) {
            return $this->customMessage;
        }

        return "The :attribute must be between {$this->minWidth}x{$this->minHeight} and {$this->maxWidth}x{$this->maxHeight} pixels.";
    }
}