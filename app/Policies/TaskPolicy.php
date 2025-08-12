<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        // Allow admin, manager and va roles to view tasks
        return $user->isAdmin() || $user->isManager() || $user->isVA();
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        // Allow if user is admin/manager or owns/assigned the task
        return $user->isAdmin() || $user->isManager() || 
               $task->user_id === $user->id || 
               $task->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->isAdmin() || $user->isManager() || 
               $task->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}