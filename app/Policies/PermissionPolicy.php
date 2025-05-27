<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can assign permissions to roles.
     */
    public function assignPermissions(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage permission groups.
     */
    public function manageGroups(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage permission roles.
     */
    public function manageRoles(User $user, Permission $permission): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view permission statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage permission settings.
     */
    public function manageSettings(User $user, Permission $permission): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage permission cache.
     */
    public function manageCache(User $user): bool
    {
        return $user->is_admin;
    }
} 