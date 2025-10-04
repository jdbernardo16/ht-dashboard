<?php

namespace Tests\Unit\Rules;

use Tests\TestCase;
use App\Rules\ValidFileType;
use Illuminate\Http\UploadedFile;

class ValidFileTypeTest extends TestCase
{
    protected ValidFileType $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new ValidFileType(['image', 'document']);
    }

    /** @test */
    public function it_passes_for_valid_image_types()
    {
        $validImages = [
            UploadedFile::fake()->image('test.jpg'),
            UploadedFile::fake()->image('test.jpeg'),
            UploadedFile::fake()->image('test.png'),
            UploadedFile::fake()->image('test.gif'),
            UploadedFile::fake()->image('test.webp'),
        ];

        foreach ($validImages as $image) {
            $this->assertTrue($this->rule->passes('file', $image), "Failed for {$image->getClientOriginalName()}");
        }
    }

    /** @test */
    public function it_passes_for_valid_document_types()
    {
        $validDocuments = [
            UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf'),
            UploadedFile::fake()->create('test.doc', 1024, 'application/msword'),
            UploadedFile::fake()->create('test.docx', 1024, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            UploadedFile::fake()->create('test.xls', 1024, 'application/vnd.ms-excel'),
            UploadedFile::fake()->create('test.xlsx', 1024, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            UploadedFile::fake()->create('test.txt', 1024, 'text/plain'),
        ];

        foreach ($validDocuments as $document) {
            $this->assertTrue($this->rule->passes('file', $document), "Failed for {$document->getClientOriginalName()}");
        }
    }

    /** @test */
    public function it_fails_for_invalid_file_types()
    {
        $invalidFiles = [
            UploadedFile::fake()->create('test.zip', 1024, 'application/zip'),
            UploadedFile::fake()->create('test.exe', 1024, 'application/octet-stream'),
            UploadedFile::fake()->create('test.mp4', 1024, 'video/mp4'),
            UploadedFile::fake()->create('test.mp3', 1024, 'audio/mpeg'),
        ];

        foreach ($invalidFiles as $file) {
            $this->assertFalse($this->rule->passes('file', $file), "Should have failed for {$file->getClientOriginalName()}");
        }
    }

    /** @test */
    public function it_fails_for_files_without_mime_type()
    {
        $file = UploadedFile::fake()->create('test.unknown', 1024);
        
        $this->assertFalse($this->rule->passes('file', $file));
    }

    /** @test */
    public function it_provides_correct_error_message()
    {
        $file = UploadedFile::fake()->create('test.zip', 1024, 'application/zip');
        $this->rule->passes('file', $file);
        
        $message = $this->rule->message();
        $this->assertStringContains('allowed file types', $message);
        $this->assertStringContains('image', $message);
        $this->assertStringContains('document', $message);
    }

    /** @test */
    public function it_works_with_single_allowed_type()
    {
        $rule = new ValidFileType(['image']);
        
        $validImage = UploadedFile::fake()->image('test.jpg');
        $invalidDocument = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $this->assertTrue($rule->passes('file', $validImage));
        $this->assertFalse($rule->passes('file', $invalidDocument));
    }

    /** @test */
    public function it_works_with_custom_mime_types()
    {
        $rule = new ValidFileType(['custom'], ['application/custom-type']);
        
        $validFile = UploadedFile::fake()->create('test.custom', 1024, 'application/custom-type');
        $invalidFile = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $this->assertTrue($rule->passes('file', $validFile));
        $this->assertFalse($rule->passes('file', $invalidFile));
    }

    /** @test */
    public function it_handles_null_value()
    {
        $this->assertFalse($this->rule->passes('file', null));
    }

    /** @test */
    public function it_handles_empty_string()
    {
        $this->assertFalse($this->rule->passes('file', ''));
    }

    /** @test */
    public function it_handles_non_uploaded_file_objects()
    {
        $this->assertFalse($this->rule->passes('file', (object) ['name' => 'test.txt']));
    }
}