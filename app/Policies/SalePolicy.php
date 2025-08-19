<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Determine whether the user can view any sales.
     */
    public function viewAny(User $user): bool
    {
        // Allow admin, manager and va roles to view sales
        return $user->isAdmin() || $user->isManager() || $user->isVA();
    }

    /**
     * Determine whether the user can view the sale.
     */
    public function view(User $user, Sale $sale): bool
    {
        // Allow if user is admin/manager or owns the sale
        return $user->isAdmin() || $user->isManager() ||
            $sale->user_id === $user->id;
    }

    /**
     * Determine whether the user can create sales.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isVA();
    }

    /**
     * Determine whether the user can update the sale.
     */
    public function update(User $user, Sale $sale): bool
    {
        return $user->isAdmin() || $user->isManager() ||
            $sale->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the sale.
     */
    public function delete(User $user, Sale $sale): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
