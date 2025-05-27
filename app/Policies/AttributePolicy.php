<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attribute;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // همه کاربران می‌توانند لیست ویژگی‌ها را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attribute $attribute): bool
    {
        return true; // همه کاربران می‌توانند جزئیات ویژگی را ببینند
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند ویژگی ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attribute $attribute): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attribute $attribute): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attribute $attribute): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attribute $attribute): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage attribute status.
     */
    public function manageStatus(User $user, Attribute $attribute): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage attribute values.
     */
    public function manageValues(User $user, Attribute $attribute): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view attribute statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage attribute categories.
     */
    public function manageCategories(User $user, Attribute $attribute): bool
    {
        return $user->is_admin;
    }
} 