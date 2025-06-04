<?php

namespace App\Policies;

use App\Models\RequestFile;
use App\Models\User;

class FilePolicy
{
    // فقط صاحب درخواست یا خدمات‌دهنده مرتبط می‌تواند فایل را ببیند
    public function view(User $user, RequestFile $file)
    {
        if (!$file->relationLoaded('serviceRequest')) {
            $file->load('serviceRequest');
        }
        if (!$file->serviceRequest) {
            return false;
        }
        return $user->id === $file->serviceRequest->customer_user_id ||
               $user->id === $file->serviceRequest->service_provider_user_id;
    }

    // فقط صاحب درخواست می‌تواند فایل را حذف کند
    public function delete(User $user, RequestFile $file)
    {
        if (!$file->relationLoaded('serviceRequest')) {
            $file->load('serviceRequest');
        }
        if (!$file->serviceRequest) {
            return false;
        }
        return $user->id === $file->serviceRequest->customer_user_id;
    }
} 