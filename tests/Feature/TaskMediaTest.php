<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Fake the storage
        Storage::fake('public');
    }

    /** @test */
    public function it_can_upload_files_when_creating_a_task()
    {
        // Create a fake image file
        $file = UploadedFile::fake()->image('task-image.jpg', 100, 100);
        
        $response = $this->post(route('tasks.store'), [
            'title' => 'Test Task with Media',
            'description' => 'This task has attached files',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addWeek()->format('Y-m-d'),
            'media' => [$file],
        ]);

        $response->assertRedirect(route('tasks.index'));
        
        // Verify the task was created
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task with Media',
            'user_id' => $this->user->id,
        ]);

        $task = Task::where('title', 'Test Task with Media')->first();
        
        // Verify the media was stored
        $this->assertDatabaseHas('task_media', [
            'task_id' => $task->id,
            'user_id' => $this->user->id,
            'original_name' => 'task-image.jpg',
        ]);

        // Verify the file was stored on disk
        $media = TaskMedia::where('task_id', $task->id)->first();
        Storage::disk('public')->assertExists($media->file_path);
    }

    /** @test */
    public function it_validates_file_types()
    {
        // Try to upload an invalid file type
        $invalidFile = UploadedFile::fake()->create('document.exe', 1000);
        
        $response = $this->post(route('tasks.store'), [
            'title' => 'Test Task with Invalid File',
            'description' => 'This task has invalid file type',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addWeek()->format('Y-m-d'),
            'media' => [$invalidFile],
        ]);

        $response->assertSessionHasErrors('media.0');
    }

    /** @test */
    public function it_validates_file_size()
    {
        // Try to upload a file that's too large (15MB)
        $largeFile = UploadedFile::fake()->create('large-image.jpg', 15000); // 15MB
        
        $response = $this->post(route('tasks.store'), [
            'title' => 'Test Task with Large File',
            'description' => 'This task has a file that is too large',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addWeek()->format('Y-m-d'),
            'media' => [$largeFile],
        ]);

        $response->assertSessionHasErrors('media.0');
    }

    /** @test */
    public function it_can_upload_multiple_files()
    {
        $files = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.png'),
            UploadedFile::fake()->create('document.pdf', 500),
        ];
        
        $response = $this->post(route('tasks.store'), [
            'title' => 'Test Task with Multiple Files',
            'description' => 'This task has multiple attached files',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addWeek()->format('Y-m-d'),
            'media' => $files,
        ]);

        $response->assertRedirect(route('tasks.index'));
        
        $task = Task::where('title', 'Test Task with Multiple Files')->first();
        
        // Verify all files were stored
        $this->assertCount(3, $task->media);
        
        foreach ($task->media as $media) {
            Storage::disk('public')->assertExists($media->file_path);
        }
    }

    /** @test */
    public function it_can_upload_files_when_updating_a_task()
    {
        // Create a task first
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $file = UploadedFile::fake()->image('update-image.jpg');
        
        $response = $this->put(route('tasks.update', $task->id), [
            'title' => 'Updated Task with Media',
            'media' => [$file],
        ]);

        $response->assertRedirect();
        
        // Verify the media was added to the existing task
        $this->assertDatabaseHas('task_media', [
            'task_id' => $task->id,
            'user_id' => $this->user->id,
            'original_name' => 'update-image.jpg',
        ]);
    }

    /** @test */
    public function it_handles_file_uploads_without_media()
    {
        // Test that the task can be created without any files
        $response = $this->post(route('tasks.store'), [
            'title' => 'Test Task without Media',
            'description' => 'This task has no attached files',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addWeek()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task without Media',
            'user_id' => $this->user->id,
        ]);
    }
}