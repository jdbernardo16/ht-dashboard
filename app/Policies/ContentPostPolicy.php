<?php

namespace App\Policies;

use App\Models\ContentPost;
use App\Models\User;

class ContentPostPolicy
{
    /**
     * Determine whether the user can view any content posts.
     */
    public function viewAny(User $user): bool
    {
        // Allow admin, manager and va roles to view content posts
        return $user->isAdmin() || $user->isManager() || $user->isVA();
    }

    /**
     * Determine whether the user can view the content post.
     */
    public function view(User $user, ContentPost $contentPost): bool
    {
        // Allow if user is admin/manager or owns the content post
        return $user->isAdmin() || $user->isManager() ||
            $contentPost->user_id === $user->id;
    }

    /**
     * Determine whether the user can create content posts.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isVA();
    }

    /**
     * Determine whether the user can update the content post.
     */
    public function update(User $user, ContentPost $contentPost): bool
    {
        return $user->isAdmin() || $user->isManager() ||
            $contentPost->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the content post.
     */
    public function delete(User $user, ContentPost $contentPost): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
