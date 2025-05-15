<?php
// app/Policies/ProviderPolicy.php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;

class ProviderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the provider.
     */
    public function view(Authenticatable $user, Provider $provider): bool
    {
        if ($user instanceof Admin) {
            return true; // ادمین می‌تونه همه پرایدرها رو ببینه
        }
        if ($user instanceof User) {
            return $user->id === $provider->user_id; // فقط پرایدر خودش
        }
        return false;
    }

    /**
     * Determine whether the user can update the provider.
     */
    public function update(Authenticatable $user, Provider $provider): bool
    {
        if ($user instanceof Admin) {
            return true; // ادمین می‌تونه همه پرایدرها رو آپدیت کنه
        }
        if ($user instanceof User) {
            return $user->id === $provider->user_id; // فقط پرایدر خودش
        }
        return false;
    }

    /**
     * فقط ادمین می‌تواند پروایدر را حذف کند
     */
    public function delete(User $user, Provider $provider): bool
    {
        return $user instanceof Admin;
    }

    /**
     * فقط پروایدر خودش یا ادمین می‌تواند سرویس‌هایش را مدیریت کند
     */
    public function manageServices(User $user, Provider $provider): bool
    {
        return $user instanceof Admin
            || $user->id === $provider->user_id;
    }
}
