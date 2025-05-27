<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Statistics;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatisticsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند لیست آمارها را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Statistics $statistics): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند آمار جدید ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Statistics $statistics): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Statistics $statistics): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Statistics $statistics): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Statistics $statistics): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage statistics types.
     */
    public function manageTypes(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage statistics categories.
     */
    public function manageCategories(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage statistics status.
     */
    public function manageStatus(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage statistics logs.
     */
    public function manageLogs(User $user): bool
    {
        return $user->is_admin;
    }
} 