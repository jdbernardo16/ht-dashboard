<?php

namespace Tests\Feature;

use App\Models\ContentPost;
use App\Models\ContentPostMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentPostMediaTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'va',
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_upload_files_when_creating_a_content_post()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('content-image.jpg');
        
        $response = $this->post(route('content.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post with Media',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has attached files',
            'status' => 'draft',
            'media' => [$file],
        ]);

        $response->assertRedirect(route('content.index'));
        
        $contentPost = ContentPost::where('title', 'Test Content Post with Media')->first();
        
        // Verify the content post was created
        $this->assertNotNull($contentPost);
        
        // Verify the media was stored
        $this->assertDatabaseHas('content_post_media', [
            'content_post_id' => $contentPost->id,
            'user_id' => $this->user->id,
            'original_name' => 'content-image.jpg',
        ]);

        $media = ContentPostMedia::where('content_post_id', $contentPost->id)->first();
        
        // Verify the file was stored on disk
        Storage::disk('public')->assertExists($media->file_path);
    }

    /** @test */
    public function it_validates_file_types_for_content_posts()
    {
        // Try to upload an invalid file type
        $invalidFile = UploadedFile::fake()->create('document.exe', 1000);
        
        $response = $this->post(route('content.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post with Invalid File',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has invalid file type',
            'status' => 'draft',
            'media' => [$invalidFile],
        ]);

        $response->assertSessionHasErrors('media.0');
    }

    /** @test */
    public function it_validates_file_size_for_content_posts()
    {
        // Try to upload a file that's too large (15MB)
        $largeFile = UploadedFile::fake()->create('large-image.jpg', 15000); // 15MB
        
        $response = $this->post(route('content.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post with Large File',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has a file that is too large',
            'status' => 'draft',
            'media' => [$largeFile],
        ]);

        $response->assertSessionHasErrors('media.0');
    }

    /** @test */
    public function it_can_upload_multiple_files_to_content_post()
    {
        Storage::fake('public');

        $files = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.png'),
            UploadedFile::fake()->create('document.pdf', 500),
        ];
        
        $response = $this->post(route('content.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post with Multiple Files',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has multiple attached files',
            'status' => 'draft',
            'media' => $files,
        ]);

        $response->assertRedirect(route('content.index'));
        
        $contentPost = ContentPost::where('title', 'Test Content Post with Multiple Files')->first();
        
        // Verify all files were stored
        $this->assertCount(3, $contentPost->media);
        
        foreach ($contentPost->media as $media) {
            Storage::disk('public')->assertExists($media->file_path);
        }
    }

    /** @test */
    public function it_can_upload_files_when_updating_a_content_post()
    {
        Storage::fake('public');

        // Create a content post first
        $contentPost = ContentPost::factory()->create(['user_id' => $this->user->id]);
        
        $file = UploadedFile::fake()->image('update-image.jpg');
        
        $response = $this->put(route('content.update', $contentPost->id), [
            'title' => 'Updated Content Post with Media',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has attached files',
            'status' => 'draft',
            'media' => [$file],
        ]);

        $response->assertRedirect();
        
        // Verify the media was added to the existing content post
        $this->assertDatabaseHas('content_post_media', [
            'content_post_id' => $contentPost->id,
            'user_id' => $this->user->id,
            'original_name' => 'update-image.jpg',
        ]);
    }

    /** @test */
    public function it_handles_file_uploads_without_media_for_content_posts()
    {
        // Test that the content post can be created without any files
        $response = $this->post(route('content.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post without Media',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has no attached files',
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('content.index'));
        
        $this->assertDatabaseHas('content_posts', [
            'title' => 'Test Content Post without Media',
            'user_id' => $this->user->id,
        ]);
    }
}