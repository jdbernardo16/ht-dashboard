<?php

namespace Tests\Feature;

use App\Models\ContentPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentPostImageTest extends TestCase
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
    public function it_can_upload_and_process_image_when_creating_content_post()
    {
        Storage::fake('public');

        $imageFile = UploadedFile::fake()->image('test-image.jpg', 1200, 800);

        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post with Image',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has an image',
            'status' => 'draft',
            'image' => $imageFile,
        ]);

        $response->assertRedirect(route('content.web.index'));

        $contentPost = ContentPost::where('title', 'Test Content Post with Image')->first();

        // Verify the content post was created
        $this->assertNotNull($contentPost);

        // Verify the image path was stored
        $this->assertNotNull($contentPost->image);
        $this->assertStringStartsWith('content_images/', $contentPost->image);

        // Verify the processed image was stored on disk
        Storage::disk('public')->assertExists($contentPost->image);

        // Verify thumbnail was created
        $thumbnailPath = 'content_images/thumbnails/thumb_' . basename($contentPost->image);
        Storage::disk('public')->assertExists($thumbnailPath);
    }

    /** @test */
    public function it_can_update_image_when_editing_content_post()
    {
        Storage::fake('public');

        // Create a content post first
        $contentPost = ContentPost::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Content Post'
        ]);

        $originalImage = UploadedFile::fake()->image('original.jpg', 1000, 600);
        $originalResult = app(\App\Services\ImageService::class)->processImage($originalImage, 'content_images');
        $contentPost->update(['image' => $originalResult['path']]);

        // Now update with a new image
        $newImage = UploadedFile::fake()->image('updated-image.png', 1500, 1000);

        $response = $this->put(route('content.web.update', $contentPost->id), [
            'title' => 'Updated Content Post with New Image',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has an updated image',
            'status' => 'draft',
            'image' => $newImage,
        ]);

        $response->assertRedirect();

        $contentPost->refresh();

        // Verify the old image was deleted
        Storage::disk('public')->assertMissing($originalResult['path']);

        // Verify the new image was stored
        $this->assertNotNull($contentPost->image);
        $this->assertStringStartsWith('content_images/', $contentPost->image);
        Storage::disk('public')->assertExists($contentPost->image);

        // Verify new thumbnail was created
        $thumbnailPath = 'content_images/thumbnails/thumb_' . basename($contentPost->image);
        Storage::disk('public')->assertExists($thumbnailPath);
    }

    /** @test */
    public function it_validates_image_file_types_for_content_posts()
    {
        // Try to upload an invalid file type as image
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post with Invalid Image',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has invalid image type',
            'status' => 'draft',
            'image' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('image');
    }

    /** @test */
    public function it_validates_image_file_size_for_content_posts()
    {
        // Try to upload a file that's too large (15MB) - use create instead of image for better size control
        $largeFile = UploadedFile::fake()->create('large-image.jpg', 15000); // 15MB

        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post with Large Image',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has a large image',
            'status' => 'draft',
            'image' => $largeFile,
        ]);

        $response->assertSessionHasErrors('image');
    }

    /** @test */
    public function it_handles_content_post_creation_without_image()
    {
        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post without Image',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has no image',
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('content.web.index'));

        $contentPost = ContentPost::where('title', 'Test Content Post without Image')->first();

        $this->assertNotNull($contentPost);
        $this->assertNull($contentPost->image);
    }

    /** @test */
    public function it_processes_image_with_correct_dimensions_and_quality()
    {
        Storage::fake('public');

        // Create a large image that should be resized
        $largeImage = UploadedFile::fake()->image('large-test.jpg', 3000, 2000);

        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->user->id,
            'title' => 'Test Content Post with Large Image',
            'platform' => ['website'],
            'content_type' => 'post',
            'description' => 'This content post has a large image to be resized',
            'status' => 'draft',
            'image' => $largeImage,
        ]);

        $response->assertRedirect();

        $contentPost = ContentPost::where('title', 'Test Content Post with Large Image')->first();

        // Verify the image was processed and stored
        $this->assertNotNull($contentPost->image);
        Storage::disk('public')->assertExists($contentPost->image);

        // Get image info to verify dimensions were constrained
        $imageService = app(\App\Services\ImageService::class);
        $imageInfo = $imageService->getImageInfo($contentPost->image);

        // The image should have been resized to fit within max dimensions (1920x1080)
        $this->assertLessThanOrEqual(1920, $imageInfo['width']);
        $this->assertLessThanOrEqual(1080, $imageInfo['height']);
    }
}