<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        try {
            // Notify assigned user if task is assigned to someone other than creator
            if ($task->assigned_to && $task->assigned_to !== $task->user_id) {
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser) {
                    NotificationService::sendTaskAssignment($assignedUser, $task);
                }
            }

            // Notify managers/admins about new high-priority tasks
            if (in_array($task->priority, ['high', 'urgent'])) {
                $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                
                foreach ($managersAndAdmins as $user) {
                    // Don't notify the creator
                    if ($user->id !== $task->user_id) {
                        NotificationService::sendTaskUpdate(
                            $user,
                            $task->title,
                            "created with {$task->priority} priority",
                            [
                                'task_id' => $task->id,
                                'created_by' => $task->user->name,
                                'priority' => $task->priority,
                                'due_date' => $task->due_date?->format('Y-m-d'),
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send task creation notification', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        try {
            $changes = $task->getChanges();
            
            // Check if task was assigned to a new user
            if (isset($changes['assigned_to']) && $task->assigned_to) {
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser) {
                    NotificationService::sendTaskAssignment($assignedUser, $task);
                }
            }

            // Check if task was completed
            if (isset($changes['status']) && $task->status === 'completed') {
                // Notify the task creator/manager
                if ($task->user_id !== $task->assigned_to) {
                    $creator = User::find($task->user_id);
                    if ($creator) {
                        $completedBy = $task->assignedTo?->name ?? 'System';
                        NotificationService::sendTaskCompletion($creator, $task);
                    }
                }

                // Notify managers about task completion
                $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                foreach ($managersAndAdmins as $user) {
                    // Don't notify if they are the creator or assigned user
                    if ($user->id !== $task->user_id && $user->id !== $task->assigned_to) {
                        $completedBy = $task->assignedTo?->name ?? 'System';
                        NotificationService::sendTaskCompletion($user, $task);
                    }
                }
            }

            // Check if priority changed to high/urgent
            if (isset($changes['priority']) && in_array($task->priority, ['high', 'urgent'])) {
                $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                
                foreach ($managersAndAdmins as $user) {
                    // Don't notify the user who changed it
                    $authUserId = auth()->user() ? auth()->id() : null;
                    if ($user->id !== $authUserId) {
                        $updatedBy = auth()->user() ? auth()->user()->name : 'System';
                        NotificationService::sendTaskUpdate(
                            $user,
                            $task->title,
                            "priority changed to {$task->priority}",
                            [
                                'task_id' => $task->id,
                                'old_priority' => $changes['priority'],
                                'new_priority' => $task->priority,
                                'updated_by' => $updatedBy,
                            ]
                        );
                    }
                }
            }

            // Check if due date is approaching or overdue
            if (isset($changes['due_date']) || isset($changes['status'])) {
                if ($task->due_date && $task->status !== 'completed') {
                    $daysUntilDue = now()->diffInDays($task->due_date, false);
                    
                    // Notify if due date is within 2 days or overdue
                    if ($daysUntilDue <= 2 && $daysUntilDue >= -7) {
                        $assignedUser = $task->assignedTo;
                        if ($assignedUser) {
                            $statusText = $daysUntilDue < 0 ? 'overdue' : 'due soon';
                            NotificationService::sendReminder(
                                $assignedUser,
                                'Task Due Date',
                                "Task '{$task->title}' is {$statusText}",
                                [
                                    'task_id' => $task->id,
                                    'due_date' => $task->due_date->format('Y-m-d'),
                                    'days_until_due' => $daysUntilDue,
                                    'priority' => $task->priority,
                                ]
                            );
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send task update notification', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}