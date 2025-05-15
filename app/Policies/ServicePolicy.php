<?php
// app/Policies/ServicePolicy.php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    /**
     * هر کسی می‌تواند فهرست سرویس‌های عمومی را ببیند
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * مشاهده جزئیات یک سرویس برای همه آزاد است
     */
    public function view(?User $user, Service $service): bool
    {
        return true;
    }

    /**
     * فقط ادمین‌ها اجازه ایجاد سرویس را دارند
     */
    public function create($user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * فقط ادمین‌ها اجازه ویرایش سرویس را دارند
     */
    public function update($user, Service $service): bool
    {
        return $user instanceof Admin;
    }

    /**
     * فقط ادمین‌ها اجازه حذف سرویس را دارند
     */
    public function delete($user, Service $service): bool
    {
        return $user instanceof Admin;
    }
}
