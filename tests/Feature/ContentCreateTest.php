<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentCreateTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'role' => 'admin'
        ]);

        // Create a test client
        $this->client = Client::factory()->create();

        // Fake storage to prevent actual file uploads
        Storage::fake('public');
    }

    /** @test */
    public function it_can_create_content_post_with_image_upload()
    {
        $this->actingAs($this->user);

        // Create a test image file
        $image = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->client->id,
            'title' => 'Test Content Post',
            'platform' => ['facebook', 'instagram'],
            'content_type' => 'post',
            'description' => 'Test description',
            'status' => 'draft',
            'image' => $image,
        ]);

        $response->assertStatus(200); // Inertia render after successful creation

        // Verify the image was stored
        $this->assertDatabaseHas('content_posts', [
            'title' => 'Test Content Post',
            'client_id' => $this->client->id,
        ]);

        // Get the created content post
        $contentPost = \App\Models\ContentPost::where('title', 'Test Content Post')->first();

        // Verify the image path is saved
        $this->assertNotNull($contentPost->image);
        $this->assertStringContainsString('content_images', $contentPost->image);

        // Verify the file exists in storage
        $this->assertTrue(Storage::disk('public')->exists($contentPost->image));
    }

    /** @test */
    public function it_can_create_content_post_without_image()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->client->id,
            'title' => 'Test Content Post Without Image',
            'platform' => ['facebook'],
            'content_type' => 'post',
            'description' => 'Test description',
            'status' => 'draft',
        ]);

        $response->assertStatus(200); // Inertia render after successful creation

        // Verify the content post was created without an image
        $this->assertDatabaseHas('content_posts', [
            'title' => 'Test Content Post Without Image',
            'client_id' => $this->client->id,
            'image' => null,
        ]);
    }

    /** @test */
    public function it_validates_image_file_type()
    {
        $this->actingAs($this->user);

        // Create a test file with invalid type (txt instead of image)
        $invalidFile = UploadedFile::fake()->create('test-file.txt', 100);

        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->client->id,
            'title' => 'Test Content Post',
            'platform' => ['facebook'],
            'content_type' => 'post',
            'description' => 'Test description',
            'status' => 'draft',
            'image' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('image');
    }

    /** @test */
    public function it_validates_image_file_size()
    {
        $this->actingAs($this->user);

        // Create a test image file that's too large (15MB)
        $largeImage = UploadedFile::fake()->image('large-image.jpg', 800, 600)->size(15000);

        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->client->id,
            'title' => 'Test Content Post',
            'platform' => ['facebook'],
            'content_type' => 'post',
            'description' => 'Test description',
            'status' => 'draft',
            'image' => $largeImage,
        ]);

        $response->assertSessionHasErrors('image');
    }

    /** @test */
    public function it_handles_multiple_media_files()
    {
        $this->actingAs($this->user);

        // Create test files
        $image = UploadedFile::fake()->image('test-image.jpg', 800, 600);
        $pdf = UploadedFile::fake()->create('test-document.pdf', 100);

        $response = $this->post(route('content.web.store'), [
            'client_id' => $this->client->id,
            'title' => 'Test Content Post with Media',
            'platform' => ['facebook'],
            'content_type' => 'post',
            'description' => 'Test description',
            'status' => 'draft',
            'image' => $image,
            'media' => [$pdf],
        ]);

        $response->assertStatus(200); // Inertia render after successful creation

        // Verify the content post was created
        $this->assertDatabaseHas('content_posts', [
            'title' => 'Test Content Post with Media',
            'client_id' => $this->client->id,
        ]);

        // Get the created content post
        $contentPost = \App\Models\ContentPost::where('title', 'Test Content Post with Media')->first();

        // Verify media files were associated
        $this->assertDatabaseHas('content_post_media', [
            'content_post_id' => $contentPost->id,
        ]);
    }
}
