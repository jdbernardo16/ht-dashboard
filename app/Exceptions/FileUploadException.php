<?php

namespace App\Exceptions;

use Exception;

class FileUploadException extends Exception
{
    protected array $details;
    protected string $context;
    
    public function __construct(string $message, array $details = [], string $context = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
        $this->context = $context;
    }
    
    public function getDetails(): array
    {
        return $this->details;
    }
    
    public function getContext(): string
    {
        return $this->context;
    }
    
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'context' => $this->getContext(),
            'details' => $this->getDetails(),
            'code' => $this->getCode()
        ];
    }
}