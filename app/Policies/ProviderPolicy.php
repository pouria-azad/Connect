<?php
// app/Policies/ProviderPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Provider;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProviderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // همه کاربران می‌توانند لیست ارائه‌دهندگان را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Provider $provider): bool
    {
        return $user->is_admin || $user->id === $provider->user_id;
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
    public function update(User $user, Provider $provider): bool
    {
        return $user->is_admin || $user->id === $provider->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Provider $provider): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can verify the provider.
     */
    public function verify(User $user, Provider $provider): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage provider services.
     */
    public function manageServices(User $user, Provider $provider): bool
    {
        return $user->is_admin || $user->id === $provider->user_id;
    }

    /**
     * Determine whether the user can view provider reviews.
     */
    public function viewReviews(User $user, Provider $provider): bool
    {
        return true; // همه کاربران می‌توانند نظرات را ببینند
    }

    /**
     * Determine whether the user can add reviews.
     */
    public function addReview(User $user, Provider $provider): bool
    {
        return $user->id !== $provider->user_id; // ارائه‌دهنده نمی‌تواند به خودش نظر دهد
    }
}
