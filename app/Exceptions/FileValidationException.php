<?php

namespace App\Exceptions;

class FileValidationException extends FileUploadException
{
    protected array $validationErrors;
    
    public function __construct(array $validationErrors, string $message = 'File validation failed', string $context = 'validation')
    {
        $this->validationErrors = $validationErrors;
        parent::__construct($message, ['validation_errors' => $validationErrors], $context);
    }
    
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
    
    public function getFirstError(): ?string
    {
        if (empty($this->validationErrors)) {
            return null;
        }
        
        // Flatten nested error arrays and get the first error
        $allErrors = [];
        foreach ($this->validationErrors as $errors) {
            if (is_array($errors)) {
                $allErrors = array_merge($allErrors, $errors);
            } else {
                $allErrors[] = $errors;
            }
        }
        
        return $allErrors[0] ?? null;
    }
}