<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnnouncementPolicy
{
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->is_admin;
    }
}
