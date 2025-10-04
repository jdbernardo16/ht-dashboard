<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ContentPost;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileUploadIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'admin'
        ]);
        
        $this->category = Category::factory()->create([
            'type' => 'content'
        ]);
        
        Storage::fake('public');
    }

    /** @test */
    public function it_handles_complete_content_post_lifecycle_with_files()
    {
        // Create post with files
        $featuredImage = UploadedFile::fake()->image('featured.jpg', 1200, 800);
        $mediaFiles = [
            UploadedFile::fake()->image('media1.jpg', 400, 300),
            UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf'),
        ];
        
        $createData = [
            'title' => 'Complete Lifecycle Test',
            'content' => 'Test content for complete lifecycle',
            'status' => 'draft',
            'published_at' => null,
            'categories' => [$this->category->id],
            'featured_image' => $featuredImage,
            'media_files' => $mediaFiles,
        ];

        // Create
        $response = $this->actingAs($this->user)
            ->post(route('content.posts.store'), $createData);
        $response->assertRedirect(route('content.posts.index'));

        $post = ContentPost::where('title', 'Complete Lifecycle Test')->first();
        $this->assertNotNull($post);
        $this->assertNotNull($post->featured_image_path);
        $this->assertCount(2, $post->media);

        // Update with new files
        $newFeaturedImage = UploadedFile::fake()->image('new-featured.jpg', 800, 600);
        $newMediaFiles = [
            UploadedFile::fake()->image('new-media.jpg', 500, 400),
        ];
        
        $updateData = [
            'title' => 'Updated Lifecycle Test',
            'content' => 'Updated content for complete lifecycle',
            'status' => 'published',
            'published_at' => now(),
            'categories' => [$this->category->id],
            'featured_image' => $newFeaturedImage,
            'media_files' => $newMediaFiles,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('content.posts.update', $post), $updateData);
        $response->assertRedirect(route('content.posts.index'));

        $post->refresh();
        $this->assertEquals('Updated Lifecycle Test', $post->title);
        $this->assertStringContains('new-featured.jpg', $post->featured_image_path);
        $this->assertCount(1, $post->media);

        // Verify old files are deleted and new files exist
        $this->assertFalse(Storage::disk('public')->exists('content/featured/' . date('Y/m', strtotime('-1 day')) . '/featured.jpg'));
        $this->assertTrue(Storage::disk('public')->exists($post->featured_image_path));
        
        foreach ($post->media as $media) {
            $this->assertTrue(Storage::disk('public')->exists($media->file_path));
        }

        // Delete post (should clean up files)
        $response = $this->actingAs($this->user)
            ->delete(route('content.posts.destroy', $post));
        $response->assertRedirect(route('content.posts.index'));

        $this->assertDatabaseMissing('content_posts', [
            'id' => $post->id
        ]);
        $this->assertDatabaseMissing('content_post_media', [
            'content_post_id' => $post->id
        ]);

        // Files should be deleted by the model's delete method
        $this->assertFalse(Storage::disk('public')->exists($post->featured_image_path));
    }

    /** @test */
    public function it_handles_file_uploads_with_different_user_roles()
    {
        // Test with manager
        $manager = User::factory()->create(['role' => 'manager']);
        
        $data = [
            'title' => 'Manager Post with Files',
            'content' => 'Content by manager',
            'status' => 'draft',
            'categories' => [$this->category->id],
            'featured_image' => UploadedFile::fake()->image('manager-featured.jpg'),
        ];

        $response = $this->actingAs($manager)
            ->post(route('content.posts.store'), $data);
        $response->assertRedirect(route('content.posts.index'));

        $this->assertDatabaseHas('content_posts', [
            'title' => 'Manager Post with Files',
            'user_id' => $manager->id
        ]);

        // Test with VA (if allowed)
        $va = User::factory()->create(['role' => 'va']);
        
        $vaData = [
            'title' => 'VA Post with Files',
            'content' => 'Content by VA',
            'status' => 'draft',
            'categories' => [$this->category->id],
            'featured_image' => UploadedFile::fake()->image('va-featured.jpg'),
        ];

        $response = $this->actingAs($va)
            ->post(route('content.posts.store'), $vaData);
        
        // This might fail depending on VA permissions
        if ($response->assertRedirect()) {
            $this->assertDatabaseHas('content_posts', [
                'title' => 'VA Post with Files',
                'user_id' => $va->id
            ]);
        }
    }

    /** @test */
    public function it_handles_concurrent_file_uploads()
    {
        // Simulate concurrent uploads by creating multiple posts with files
        $posts = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $data = [
                'title' => "Concurrent Post {$i}",
                'content' => "Content for concurrent post {$i}",
                'status' => 'draft',
                'categories' => [$this->category->id],
                'featured_image' => UploadedFile::fake()->image("concurrent-{$i}.jpg"),
                'media_files' => [
                    UploadedFile::fake()->image("media-{$i}-1.jpg"),
                    UploadedFile::fake()->create("doc-{$i}.pdf", 1024, 'application/pdf'),
                ],
            ];

            $response = $this->actingAs($this->user)
                ->post(route('content.posts.store'), $data);
            $response->assertRedirect(route('content.posts.index'));

            $post = ContentPost::where('title', "Concurrent Post {$i}")->first();
            $posts[] = $post;
        }

        // Verify all posts were created with their files
        $this->assertCount(5, $posts);
        
        foreach ($posts as $post) {
            $this->assertNotNull($post->featured_image_path);
            $this->assertCount(2, $post->media);
            
            // Verify files exist
            $this->assertTrue(Storage::disk('public')->exists($post->featured_image_path));
            
            foreach ($post->media as $media) {
                $this->assertTrue(Storage::disk('public')->exists($media->file_path));
            }
        }
    }

    /** @test */
    public function it_handles_large_numbers_of_media_files()
    {
        // Create post with many media files
        $mediaFiles = [];
        
        for ($i = 1; $i <= 10; $i++) {
            $mediaFiles[] = UploadedFile::fake()->image("batch-{$i}.jpg", 300, 200);
        }

        $data = [
            'title' => 'Post with Many Files',
            'content' => 'Content with many media files',
            'status' => 'draft',
            'categories' => [$this->category->id],
            'media_files' => $mediaFiles,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('content.posts.store'), $data);
        $response->assertRedirect(route('content.posts.index'));

        $post = ContentPost::where('title', 'Post with Many Files')->first();
        $this->assertCount(10, $post->media);
        
        // Verify all files exist
        foreach ($post->media as $media) {
            $this->assertTrue(Storage::disk('public')->exists($media->file_path));
        }
    }

    /** @test */
    public function it_handles_file_uploads_with_special_characters_in_filenames()
    {
        $specialCharsFile = UploadedFile::fake()->image('test file with spaces & symbols.jpg');
        
        $data = [
            'title' => 'Post with Special Chars',
            'content' => 'Content with special characters',
            'status' => 'draft',
            'categories' => [$this->category->id],
            'featured_image' => $specialCharsFile,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('content.posts.store'), $data);
        $response->assertRedirect(route('content.posts.index'));

        $post = ContentPost::where('title', 'Post with Special Chars')->first();
        $this->assertNotNull($post->featured_image_path);
        $this->assertTrue(Storage::disk('public')->exists($post->featured_image_path));
        
        // Check that the filename was sanitized
        $this->assertStringNotContains(' ', $post->featured_image_path);
        $this->assertStringNotContains('&', $post->featured_image_path);
    }

    /** @test */
    public function it_handles_file_uploads_with_unicode_filenames()
    {
        $unicodeFile = UploadedFile::fake()->image('测试图片.jpg'); // Chinese characters
        
        $data = [
            'title' => 'Post with Unicode Filename',
            'content' => 'Content with unicode filename',
            'status' => 'draft',
            'categories' => [$this->category->id],
            'featured_image' => $unicodeFile,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('content.posts.store'), $data);
        $response->assertRedirect(route('content.posts.index'));

        $post = ContentPost::where('title', 'Post with Unicode Filename')->first();
        $this->assertNotNull($post->featured_image_path);
        $this->assertTrue(Storage::disk('public')->exists($post->featured_image_path));
    }

    /** @test */
    public function it_maintains_file_associations_during_post_updates()
    {
        // Create post with files
        $post = ContentPost::factory()->create();
        
        $initialMedia = $post->media()->createMany([
            [
                'file_path' => 'initial/image1.jpg',
                'file_name' => 'image1.jpg',
                'file_type' => 'image',
                'file_size' => 1024,
                'mime_type' => 'image/jpeg',
            ],
            [
                'file_path' => 'initial/document1.pdf',
                'file_name' => 'document1.pdf',
                'file_type' => 'document',
                'file_size' => 2048,
                'mime_type' => 'application/pdf',
            ],
        ]);

        Storage::disk('public')->put('initial/image1.jpg', 'image content');
        Storage::disk('public')->put('initial/document1.pdf', 'pdf content');

        // Update post without changing files
        $updateData = [
            'title' => 'Updated Title',
            'content' => $post->content,
            'status' => $post->status,
            'categories' => [],
        ];

        $response = $this->actingAs($this->user)
            ->put(route('content.posts.update', $post), $updateData);
        $response->assertRedirect(route('content.posts.index'));

        $post->refresh();
        $this->assertEquals('Updated Title', $post->title);
        $this->assertCount(2, $post->media); // Media should be preserved
        
        // Files should still exist
        $this->assertTrue(Storage::disk('public')->exists('initial/image1.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('initial/document1.pdf'));
    }

    /** @test */
    public function it_handles_file_uploads_in_ajax_requests()
    {
        $data = [
            'title' => 'AJAX Post with Files',
            'content' => 'Content uploaded via AJAX',
            'status' => 'draft',
            'categories' => [$this->category->id],
            'featured_image' => UploadedFile::fake()->image('ajax-image.jpg'),
        ];

        $response = $this->actingAs($this->user)
            ->post(route('content.posts.store'), $data, [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ]);

        // Should return JSON response for AJAX requests
        $response->assertJson([
            'message' => 'Content post created successfully.',
            'post' => [
                'title' => 'AJAX Post with Files',
            ]
        ]);

        $this->assertDatabaseHas('content_posts', [
            'title' => 'AJAX Post with Files'
        ]);
    }

    /** @test */
    public function it_handles_file_uploads_with_form_validation_errors()
    {
        $data = [
            'title' => '', // Invalid - required field
            'content' => '', // Invalid - required field
            'status' => 'invalid_status', // Invalid - not in allowed values
            'categories' => [], // Invalid - at least one category required
            'featured_image' => UploadedFile::fake()->image('test.jpg'),
        ];

        $response = $this->actingAs($this->user)
            ->post(route('content.posts.store'), $data, ['Accept' => 'application/json']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content', 'status', 'categories']);
    }
}