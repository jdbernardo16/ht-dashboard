<?php

namespace App\Policies;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GoalPolicy
{
    /**
     * Determine whether the user can view any goals.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isVA();
    }

    /**
     * Determine whether the user can view the goal.
     */
    public function view(User $user, Goal $goal): bool
    {
        return $user->isAdmin() || $user->isManager() || $goal->user_id === $user->id;
    }

    /**
     * Determine whether the user can create goals.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isVA();
    }

    /**
     * Determine whether the user can update the goal.
     */
    public function update(User $user, Goal $goal): bool
    {
        return $user->isAdmin() || $user->isManager() || $goal->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the goal.
     */
    public function delete(User $user, Goal $goal): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can restore the goal.
     */
    public function restore(User $user, Goal $goal): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can permanently delete the goal.
     */
    public function forceDelete(User $user, Goal $goal): bool
    {
        return $user->isAdmin();
    }
}
