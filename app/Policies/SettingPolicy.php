<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingPolicy
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
    public function view(User $user, Setting $setting): bool
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
    public function update(User $user, Setting $setting): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Setting $setting): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Setting $setting): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Setting $setting): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage setting groups.
     */
    public function manageGroups(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage setting cache.
     */
    public function manageCache(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view setting statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage setting backups.
     */
    public function manageBackups(User $user): bool
    {
        return $user->is_admin;
    }
} 