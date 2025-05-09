<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnnouncementPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
