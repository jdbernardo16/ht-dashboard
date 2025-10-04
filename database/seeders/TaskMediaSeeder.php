<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class TaskMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tasks and users
        $tasks = Task::all();
        $users = User::all();
        
        if ($tasks->isEmpty()) {
            $this->command->warn('No tasks found. Please run TaskSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Sample file data
        $sampleFiles = [
            [
                'file_name' => 'task_screenshot.png',
                'file_path' => 'tasks/screenshots/task_screenshot.png',
                'mime_type' => 'image/png',
                'file_size' => 245760,
                'original_name' => 'task_progress_screenshot.png',
                'description' => 'Screenshot showing task progress'
            ],
            [
                'file_name' => 'requirements.pdf',
                'file_path' => 'tasks/documents/requirements.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 524288,
                'original_name' => 'project_requirements.pdf',
                'description' => 'Project requirements document'
            ],
            [
                'file_name' => 'task_notes.docx',
                'file_path' => 'tasks/documents/task_notes.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_size' => 131072,
                'original_name' => 'meeting_notes.docx',
                'description' => 'Meeting notes and action items'
            ],
            [
                'file_name' => 'design_mockup.jpg',
                'file_path' => 'tasks/images/design_mockup.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 384512,
                'original_name' => 'homepage_design_v2.jpg',
                'description' => 'Design mockup for homepage'
            ],
            [
                'file_name' => 'spreadsheet.xlsx',
                'file_path' => 'tasks/documents/spreadsheet.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => 196608,
                'original_name' => 'budget_analysis.xlsx',
                'description' => 'Budget analysis spreadsheet'
            ]
        ];

        // Create media for about 60% of tasks
        $tasksWithMedia = $tasks->random((int)($tasks->count() * 0.6));

        foreach ($tasksWithMedia as $task) {
            // Each task can have 1-3 media files
            $mediaCount = rand(1, 3);
            
            for ($i = 0; $i < $mediaCount; $i++) {
                $fileData = Arr::random($sampleFiles);
                $user = $users->random();
                
                $media = [
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'file_name' => $fileData['file_name'],
                    'file_path' => $fileData['file_path'],
                    'mime_type' => $fileData['mime_type'],
                    'file_size' => $fileData['file_size'],
                    'original_name' => $fileData['original_name'],
                    'description' => $fileData['description'],
                    'order' => $i + 1,
                    'is_primary' => $i === 0, // First file is primary
                    'created_at' => now()->subMinutes(rand(1, 1440)),
                    'updated_at' => now(),
                ];

                TaskMedia::create($media);
            }
        }
    }
}