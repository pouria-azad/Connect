<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Cache;
use Illuminate\Auth\Access\HandlesAuthorization;

class CachePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند لیست کش‌ها را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cache $cache): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند کش جدید ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cache $cache): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cache $cache): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cache $cache): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cache $cache): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage cache types.
     */
    public function manageTypes(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage cache storage.
     */
    public function manageStorage(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view cache statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage cache schedules.
     */
    public function manageSchedules(User $user): bool
    {
        return $user->is_admin;
    }
} 