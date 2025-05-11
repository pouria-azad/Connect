<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class AnnouncementPolicy
{
    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, Announcement $announcement): bool
    {
        return true;
    }

    public function delete(Admin $admin, Announcement $announcement): bool
    {
        return true;
    }
}
