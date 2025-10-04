<?php

namespace Tests\Feature;

use App\Models\ContentPostMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'va',
        ]);

        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function it_can_upload_single_image_file()
    {
        Storage::fake('public');

        $imageFile = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $response = $this->postJson('/api/upload', [
            'file' => $imageFile,
            'type' => 'image',
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
            ])
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        $data = $response->json('data');

        // Verify file was stored
        Storage::disk('public')->assertExists($data['file_path']);

        // Note: We no longer create ContentPostMedia records in FileUploadController
        // Files uploaded through this controller are temporary and should be associated with content posts
        // through the ContentPostController's processMediaUploads method
    }

    /** @test */
    public function it_can_upload_multiple_files()
    {
        Storage::fake('public');

        $files = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.png'),
        ];

        $response = $this->postJson('/api/upload/multiple', [
            'files' => $files,
            'type' => 'image',
        ]);

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

        $data = $response->json('data');

        $this->assertCount(2, $data);

        foreach ($data as $fileData) {
            Storage::disk('public')->assertExists($fileData['file_path']);
        }
    }

    /** @test */
    public function it_validates_file_type_for_single_upload()
    {
        $invalidFile = UploadedFile::fake()->create('document.exe', 1000);

        $response = $this->postJson('/api/upload', [
            'file' => $invalidFile,
            'type' => 'image',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ])
            ->assertJson([
                'success' => false,
                'message' => 'File validation failed'
            ]);
    }

    /** @test */
    public function it_validates_file_size_for_single_upload()
    {
        $largeFile = UploadedFile::fake()->image('large-image.jpg')->size(15000); // 15MB

        $response = $this->postJson('/api/upload', [
            'file' => $largeFile,
            'type' => 'image',
            'max_size' => 2, // 2MB limit
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'File validation failed'
            ]);
    }

    /** @test */
    public function it_validates_required_parameters_for_single_upload()
    {
        $response = $this->postJson('/api/upload', [
            // Missing 'file' parameter
            'type' => 'image',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_can_delete_uploaded_file()
    {
        Storage::fake('public');

        // First upload a file
        $imageFile = UploadedFile::fake()->image('test-delete.jpg');
        $uploadResponse = $this->postJson('/api/upload', [
            'file' => $imageFile,
            'type' => 'image',
        ]);

        $fileData = $uploadResponse->json('data');
        $fileName = basename($fileData['file_path']);

        // Create a ContentPost first
        $client = new \App\Models\Client();
        $client->first_name = 'Test';
        $client->last_name = 'Client';
        $client->email = 'test@example.com';
        $client->save();

        $contentPost = \App\Models\ContentPost::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'platform' => ['website'],
            'content_type' => 'article',
            'title' => 'Test Content Post',
            'status' => 'draft',
        ]);

        // Create a ContentPostMedia record manually for testing deletion
        $media = ContentPostMedia::create([
            'content_post_id' => $contentPost->id,
            'user_id' => $this->user->id,
            'file_name' => $fileData['file_name'],
            'file_path' => $fileData['file_path'],
            'mime_type' => $fileData['mime_type'],
            'file_size' => $fileData['file_size'],
            'original_name' => $fileData['original_name'],
            'order' => 0,
            'is_primary' => false,
        ]);

        // Now delete the file
        $response = $this->deleteJson("/api/upload/{$fileName}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        // Verify file was deleted from storage
        Storage::disk('public')->assertMissing($fileData['file_path']);

        // Verify database record was deleted
        $this->assertDatabaseMissing('content_post_media', [
            'file_name' => $fileData['file_name']
        ]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_file()
    {
        $response = $this->deleteJson('/api/upload/nonexistent-file.jpg');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'File not found'
            ]);
    }

    /** @test */
    public function it_prevents_unauthorized_file_deletion()
    {
        Storage::fake('public');

        // Upload a file with first user
        $imageFile = UploadedFile::fake()->image('test-file.jpg');
        $uploadResponse = $this->postJson('/api/upload', [
            'file' => $imageFile,
            'type' => 'image',
        ]);

        $fileData = $uploadResponse->json('data');
        $fileName = basename($fileData['file_path']);

        // Create a ContentPost first
        $client = new \App\Models\Client();
        $client->first_name = 'Test';
        $client->last_name = 'Client';
        $client->email = 'test2@example.com';
        $client->save();

        $contentPost = \App\Models\ContentPost::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'platform' => ['website'],
            'content_type' => 'article',
            'title' => 'Test Content Post',
            'status' => 'draft',
        ]);

        // Create a ContentPostMedia record manually for testing deletion
        $media = ContentPostMedia::create([
            'content_post_id' => $contentPost->id,
            'user_id' => $this->user->id,
            'file_name' => $fileData['file_name'],
            'file_path' => $fileData['file_path'],
            'mime_type' => $fileData['mime_type'],
            'file_size' => $fileData['file_size'],
            'original_name' => $fileData['original_name'],
            'order' => 0,
            'is_primary' => false,
        ]);

        // Create a different user and try to delete the file
        $otherUser = User::factory()->create(['role' => 'va']);
        $this->actingAs($otherUser, 'sanctum');

        $response = $this->deleteJson("/api/upload/{$fileName}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to delete this file'
            ]);
    }

    /** @test */
    public function it_can_get_upload_configuration()
    {
        $response = $this->getJson('/api/upload/config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'max_sizes',
                    'allowed_types',
                    'mime_types'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'allowed_types' => ['image', 'video', 'pdf', 'xlsx', 'csv'],
                    'max_sizes' => [
                        'image' => 2048,
                        'video' => 5120,
                        'pdf' => 1024,
                        'xlsx' => 2048,
                        'csv' => 1024
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_handles_pdf_file_uploads()
    {
        Storage::fake('public');

        $pdfFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/upload', [
            'file' => $pdfFile,
            'type' => 'pdf',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        $data = $response->json('data');

        $this->assertEquals('application/pdf', $data['mime_type']);
        Storage::disk('public')->assertExists($data['file_path']);
    }

    /** @test */
    public function it_handles_excel_file_uploads()
    {
        Storage::fake('public');

        $excelFile = UploadedFile::fake()->create(
            'spreadsheet.xlsx',
            300,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $response = $this->postJson('/api/upload', [
            'file' => $excelFile,
            'type' => 'xlsx',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        $data = $response->json('data');

        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $data['mime_type']);
        Storage::disk('public')->assertExists($data['file_path']);
    }

    /** @test */
    public function it_handles_csv_file_uploads()
    {
        Storage::fake('public');

        $csvFile = UploadedFile::fake()->create('data.csv', 100, 'text/csv');

        $response = $this->postJson('/api/upload', [
            'file' => $csvFile,
            'type' => 'csv',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        $data = $response->json('data');

        $this->assertEquals('text/csv', $data['mime_type']);
        Storage::disk('public')->assertExists($data['file_path']);
    }

    /** @test */
    public function it_rejects_unsupported_file_types()
    {
        $unsupportedFile = UploadedFile::fake()->create('script.php', 100, 'application/x-php');

        $response = $this->postJson('/api/upload', [
            'file' => $unsupportedFile,
            'type' => 'image', // Trying to upload PHP as image
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'File validation failed'
            ]);
    }

    /** @test */
    public function it_handles_server_errors_gracefully()
    {
        // Simulate a storage error by using a non-existent disk
        config(['filesystems.disks.public.root' => '/non/existent/path']);

        $imageFile = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->postJson('/api/upload', [
            'file' => $imageFile,
            'type' => 'image',
        ]);

        $response->assertStatus(500)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ])
            ->assertJson([
                'success' => false,
                'message' => 'File upload failed'
            ]);

        // Restore original configuration
        config(['filesystems.disks.public.root' => storage_path('app/public')]);
    }
}
