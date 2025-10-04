<?php

namespace Tests\Feature;

use App\Services\FileStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class FileUploadSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use fake storage for testing
        Storage::fake('public');
        
        // Create a test user
        $this->user = User::factory()->create([
            'role' => 'admin'
        ]);
    }

    /**
     * Test file upload configuration endpoint
     */
    public function test_file_upload_config_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('upload.config'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'max_size',
                    'max_size_mb',
                    'allowed_images',
                    'allowed_documents',
                    'max_image_dimensions',
                    'min_image_dimensions',
                    'mime_types'
                ]
            ]);
    }

    /**
     * Test single image upload
     */
    public function test_single_image_upload(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 1200, 800);

        $response = $this->actingAs($this->user)
            ->postJson(route('upload.single'), [
                'file' => $file,
                'type' => 'image'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'file_name',
                    'file_path',
                    'mime_type',
                    'file_size',
                    'original_name',
                    'url',
                    'thumbnail_url',
                    'width',
                    'height'
                ]
            ]);

        // Assert file was stored
        $data = $response->json('data');
        Storage::disk('public')->assertExists($data['file_path']);
    }

    /**
     * Test multiple file upload
     */
    public function test_multiple_file_upload(): void
    {
        $files = [
            UploadedFile::fake()->image('test1.jpg', 1200, 800),
            UploadedFile::fake()->image('test2.png', 800, 600),
            UploadedFile::fake()->image('test3.jpg', 600, 400)
        ];

        $response = $this->actingAs($this->user)
        ->postJson(route('upload.multiple'), [
            'files' => $files,
            'type' => 'image'
        ]);

    // Debug: Log the response to see what's happening
    if (!$response->isOk()) {
        dump($response->json());
    }

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'file_name',
                        'file_path',
                        'mime_type',
                        'file_size',
                        'original_name',
                        'url'
                    ]
                ]
            ]);

        // Assert files were stored
        $data = $response->json('data');
        foreach ($data as $file) {
            Storage::disk('public')->assertExists($file['file_path']);
        }
    }

    /**
     * Test file upload validation - invalid file type
     */
    public function test_file_upload_validation_invalid_type(): void
    {
        $file = UploadedFile::fake()->create('test.exe', 1000, 'application/x-executable');

        $response = $this->actingAs($this->user)
            ->postJson(route('upload.single'), [
                'file' => $file,
                'type' => 'image'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'File validation failed'
            ]);
    }

    /**
     * Test file upload validation - file too large
     */
    public function test_file_upload_validation_too_large(): void
    {
        $file = UploadedFile::fake()->image('test.jpg')->size(20 * 1024 * 1024); // 20MB

        $response = $this->actingAs($this->user)
            ->postJson(route('upload.single'), [
                'file' => $file,
                'type' => 'image'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'File validation failed'
            ]);
    }

    /**
     * Test file upload validation - image dimensions too small
     */
    public function test_file_upload_validation_dimensions_too_small(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 50, 50); // Too small

        $response = $this->actingAs($this->user)
            ->postJson(route('upload.single'), [
                'file' => $file,
                'type' => 'image'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'File validation failed'
            ]);
    }

    /**
     * test FileStorageService directly
     */
    public function test_file_storage_service(): void
    {
        $service = app(FileStorageService::class);
        $file = UploadedFile::fake()->image('test.jpg', 1200, 800);

        $result = $service->storeImage($file);

        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('original_name', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertArrayHasKey('file_size', $result);

        // Assert file was stored
        Storage::disk('public')->assertExists($result['path']);
    }

    /**
     * Test FileStorageService with media file
     */
    public function test_file_storage_service_media_file(): void
    {
        $service = app(FileStorageService::class);
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $result = $service->storeMediaFile($file);

        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('original_name', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertArrayHasKey('file_size', $result);

        // Assert file was stored
        Storage::disk('public')->assertExists($result['path']);
    }

    /**
     * Test file deletion
     */
    public function test_file_deletion(): void
    {
        $service = app(FileStorageService::class);
        $file = UploadedFile::fake()->image('test.jpg', 1200, 800);

        $result = $service->storeImage($file);
        $path = $result['path'];

        // Assert file exists
        Storage::disk('public')->assertExists($path);

        // Delete file
        $deleted = $service->deleteFile($path);

        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing($path);
    }

    /**
     * Test unauthorized access
     */
    public function test_unauthorized_access(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 1200, 800);

        $response = $this->postJson(route('upload.single'), [
            'file' => $file,
            'type' => 'image'
        ]);

        $response->assertStatus(401);
    }
}