<?php
// app/Policies/ServicePolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // همه کاربران می‌توانند لیست خدمات را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Service $service): bool
    {
        return true; // همه کاربران می‌توانند جزئیات خدمت را ببینند
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update($user, Service $service): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, Service $service): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Service $service): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Service $service): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage service categories.
     */
    public function manageCategories(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage service prices.
     */
    public function managePrices(User $user, Service $service): bool
    {
        return $user->is_admin;
    }
}
