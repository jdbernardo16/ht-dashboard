<?php

namespace App\Exceptions;

class FileStorageException extends FileUploadException
{
    protected string $filePath;
    protected string $operation;
    
    public function __construct(string $filePath, string $operation = 'storage', string $message = '', ?Exception $previous = null)
    {
        $this->filePath = $filePath;
        $this->operation = $operation;
        
        if (empty($message)) {
            $message = "File {$operation} failed for: {$filePath}";
        }
        
        parent::__construct($message, [
            'file_path' => $filePath,
            'operation' => $operation
        ], $operation, 0, $previous);
    }
    
    public function getFilePath(): string
    {
        return $this->filePath;
    }
    
    public function getOperation(): string
    {
        return $this->operation;
    }
}