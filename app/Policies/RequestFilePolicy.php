<?php

namespace App\Policies;

use App\Models\RequestFile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RequestFilePolicy
{
    use HandlesAuthorization;

    public function view(User $user, RequestFile $file)
    {
        return $user->id === $file->serviceRequest->customer_user_id || 
               $user->id === $file->serviceRequest->provider_user_id;
    }

    public function delete(User $user, RequestFile $file)
    {
        return $user->id === $file->serviceRequest->customer_user_id;
    }
} 