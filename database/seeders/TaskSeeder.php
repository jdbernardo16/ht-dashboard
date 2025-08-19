<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Goal;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('role', ['admin', 'manager', 'va'])->get();
        $goals = Goal::all();

        $taskTitles = [
            'Complete Q3 marketing strategy',
            'Update website content and design',
            'Prepare client presentation materials',
            'Review and optimize sales funnel',
            'Create social media content calendar',
            'Conduct competitor analysis',
            'Develop new product launch plan',
            'Schedule client follow-up calls',
            'Update CRM with new leads',
            'Prepare monthly financial report',
            'Organize team meeting agenda',
            'Research new market opportunities',
            'Create email marketing campaign',
            'Update business plan projections',
            'Review and approve expense reports',
            'Coordinate with external vendors',
            'Prepare quarterly performance review',
            'Update project management system',
            'Create client onboarding process',
            'Develop training materials for new hires',
            'Review and update company policies',
            'Plan team building activities',
            'Analyze website traffic and conversions',
            'Prepare tax documentation',
            'Update inventory management system',
            'Create customer feedback survey',
            'Develop partnership proposals',
            'Review and negotiate contracts',
            'Plan content creation schedule',
            'Update social media profiles',
        ];

        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        $categories = ['Marketing', 'Sales', 'Operations', 'Finance', 'Client Relations', 'Product Development', 'Administration'];

        // Create tasks for the last 2 months
        $startDate = Carbon::now()->subMonths(2);
        $endDate = Carbon::now()->addWeeks(2);

        foreach ($users as $user) {
            // Create 25-35 tasks per user
            $tasksCount = rand(25, 35);

            for ($i = 0; $i < $tasksCount; $i++) {
                $dueDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );

                $createdDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $dueDate->timestamp - 86400)
                );

                $priority = $priorities[array_rand($priorities)];
                $status = $statuses[array_rand($statuses)];
                $category = $categories[array_rand($categories)];

                // Set completed_at if status is completed
                $completedAt = null;
                if ($status === 'completed') {
                    $completedAt = Carbon::createFromTimestamp(
                        rand($createdDate->timestamp, $dueDate->timestamp)
                    );
                }

                // Assign to random user (could be same user or different)
                $assignedTo = $users->random()->id;

                Task::create([
                    'title' => $taskTitles[array_rand($taskTitles)],
                    'description' => $this->generateTaskDescription($category),
                    'status' => $status,
                    'priority' => $priority,
                    'user_id' => $user->id,
                    'assigned_to' => $assignedTo,
                    'due_date' => $dueDate,
                    'completed_at' => $completedAt,
                    'category' => $category,
                    'estimated_hours' => rand(1, 20),
                    'actual_hours' => $status === 'completed' ? rand(1, 25) : null,
                    'tags' => $this->generateTaskTags($category),
                    'notes' => $this->generateTaskNotes($status),
                    'parent_task_id' => null,
                    'related_goal_id' => $goals->isEmpty() ? null : $goals->random()->id,
                    'is_recurring' => rand(0, 1),
                    'recurring_frequency' => rand(0, 1) ? 'weekly' : 'monthly',
                    'created_at' => $createdDate,
                    'updated_at' => $createdDate,
                ]);
            }
        }

        // Create some parent-child task relationships
        $this->createTaskHierarchy();
    }

    /**
     * Create parent-child task relationships
     */
    private function createTaskHierarchy(): void
    {
        $parentTasks = Task::where('status', '!=', 'completed')->limit(5)->get();

        foreach ($parentTasks as $parentTask) {
            // Create 2-3 subtasks for each parent
            $subtaskCount = rand(2, 3);

            for ($i = 1; $i <= $subtaskCount; $i++) {
                Task::create([
                    'title' => "Subtask {$i}: " . $parentTask->title,
                    'description' => "Detailed subtask for {$parentTask->title}",
                    'status' => 'pending',
                    'priority' => 'medium',
                    'user_id' => $parentTask->user_id,
                    'assigned_to' => $parentTask->assigned_to,
                    'due_date' => $parentTask->due_date->subDays($i),
                    'category' => $parentTask->category,
                    'estimated_hours' => rand(1, 5),
                    'parent_task_id' => $parentTask->id,
                    'related_goal_id' => $parentTask->related_goal_id,
                    'is_recurring' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Generate realistic task descriptions
     */
    private function generateTaskDescription(string $category): string
    {
        $descriptions = [
            'Marketing' => [
                'Develop comprehensive marketing strategy for Q4 launch',
                'Create social media content calendar for next month',
                'Analyze competitor marketing campaigns and create report',
                'Design email marketing campaign for product promotion',
                'Update website content and optimize for SEO',
            ],
            'Sales' => [
                'Follow up with leads from recent webinar',
                'Prepare sales presentation for potential client',
                'Update CRM with new prospect information',
                'Schedule discovery calls with qualified leads',
                'Create sales proposal for enterprise client',
            ],
            'Operations' => [
                'Review and optimize current business processes',
                'Update standard operating procedures documentation',
                'Coordinate with vendors for service delivery',
                'Implement new project management system',
                'Conduct quarterly performance review preparation',
            ],
            'Finance' => [
                'Prepare monthly financial statements',
                'Review and categorize business expenses',
                'Update budget projections for next quarter',
                'Process client invoices and follow up on payments',
                'Reconcile bank statements and credit card transactions',
            ],
            'Client Relations' => [
                'Schedule check-in call with key client',
                'Prepare client satisfaction survey',
                'Address client feedback and implement improvements',
                'Create client onboarding documentation',
                'Follow up on client support tickets',
            ],
            'Product Development' => [
                'Research new product features based on market feedback',
                'Create product roadmap for next release',
                'Test new product functionality and document bugs',
                'Coordinate with development team on feature implementation',
                'Prepare product launch marketing materials',
            ],
            'Administration' => [
                'Organize team meeting and prepare agenda',
                'Update employee handbook and policies',
                'Schedule team building activities',
                'Prepare quarterly performance reviews',
                'Coordinate office maintenance and supplies',
            ],
        ];

        return $descriptions[$category][array_rand($descriptions[$category])] ?? 'Complete assigned business task';
    }

    /**
     * Generate task tags
     */
    private function generateTaskTags(string $category): array
    {
        $tags = [
            'Marketing' => ['marketing', 'strategy', 'social-media', 'content'],
            'Sales' => ['sales', 'leads', 'client', 'presentation'],
            'Operations' => ['operations', 'process', 'optimization', 'workflow'],
            'Finance' => ['finance', 'budget', 'reporting', 'analysis'],
            'Client Relations' => ['client', 'support', 'satisfaction', 'communication'],
            'Product Development' => ['product', 'development', 'features', 'testing'],
            'Administration' => ['admin', 'meeting', 'organization', 'planning'],
        ];

        return $tags[$category] ?? ['general'];
    }

    /**
     * Generate task notes based on status
     */
    private function generateTaskNotes(string $status): string
    {
        $notes = [
            'pending' => 'Task is waiting to be started',
            'in_progress' => 'Currently working on this task',
            'completed' => 'Task completed successfully',
            'cancelled' => 'Task cancelled due to changing priorities',
            'on_hold' => 'Task temporarily on hold pending further information',
        ];

        return $notes[$status] ?? 'No additional notes';
    }
}
