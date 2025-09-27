<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class FileValidationService
{
    protected array $mimeTypeMap = [
        'image' => [
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ],
        'video' => [
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'wmv' => 'video/x-ms-wmv',
        ],
        'pdf' => [
            'pdf' => 'application/pdf',
        ],
        'xlsx' => [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
        ],
        'csv' => [
            'csv' => 'text/csv',
        ],
    ];

    public function validate(UploadedFile $file, string $type, ?int $maxSize = null): array
    {
        $rules = $this->getValidationRules($type, $maxSize);
        $validator = Validator::make(['file' => $file], ['file' => $rules]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
            ];
        }

        // Additional security checks
        if (!$this->isSafeFile($file)) {
            return [
                'valid' => false,
                'errors' => ['File type is not allowed for security reasons'],
            ];
        }

        return ['valid' => true, 'errors' => []];
    }

    public function getValidationRules(string $type, ?int $maxSize = null): array
    {
        $rules = [
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,svg,webp'],
            'video' => ['mimes:mp4,mov,avi,wmv'],
            'pdf' => ['mimes:pdf'],
            'xlsx' => ['mimes:xlsx,xls'],
            'csv' => ['mimes:csv'],
            'file' => ['file'], // Generic file type that accepts all supported types
        ];

        $typeRules = $rules[$type] ?? ['file'];

        if ($maxSize) {
            $typeRules[] = 'max:' . ($maxSize * 1024);
        } else {
            $typeRules[] = 'max:' . ($this->getMaxSizeForType($type) * 1024);
        }

        return $typeRules;
    }

    protected function isSafeFile(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        // Check if extension matches MIME type
        foreach ($this->mimeTypeMap as $type => $extensions) {
            if (isset($extensions[$extension]) && $extensions[$extension] === $mimeType) {
                return true;
            }
        }

        return false;
    }

    public function getMaxSizeForType(string $type): int
    {
        return match ($type) {
            'image' => 2048, // 2MB
            'video' => 5120, // 5MB
            'pdf' => 1024,   // 1MB
            'xlsx' => 2048,  // 2MB
            'csv' => 1024,   // 1MB
            default => 5120, // 5MB
        };
    }

    public function getAllowedTypes(): array
    {
        return array_keys($this->mimeTypeMap);
    }

    public function getMimeTypesForType(string $type): array
    {
        return $this->mimeTypeMap[$type] ?? [];
    }

    public function validateMultiple(array $files, string $type, ?int $maxSize = null): array
    {
        $results = [];

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $results[$index] = $this->validate($file, $type, $maxSize);
            } else {
                $results[$index] = [
                    'valid' => false,
                    'errors' => ['Invalid file object'],
                ];
            }
        }

        return $results;
    }
}
