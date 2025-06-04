<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceRequestPolicy
{
    use HandlesAuthorization;

    public function view(User $user, ServiceRequest $serviceRequest)
    {
        return $user->id === $serviceRequest->customer_user_id || 
               $user->id === $serviceRequest->service_provider_user_id ||
               $user->id === $serviceRequest->accepted_service_provider_user_id;
    }

    public function accept(User $user, ServiceRequest $serviceRequest)
    {
        return $user->user_type === 'provider' && 
               $serviceRequest->status === 'pending_sp_acceptance' &&
               $serviceRequest->request_type === 'public';
    }

    public function reject(User $user, ServiceRequest $serviceRequest)
    {
        return $user->user_type === 'provider' && 
               $serviceRequest->status === 'pending_sp_acceptance' &&
               $serviceRequest->request_type === 'public';
    }

    public function complete(User $user, ServiceRequest $serviceRequest)
    {
        return $user->user_type === 'provider' && 
               $user->id === $serviceRequest->accepted_service_provider_user_id &&
               $serviceRequest->status === 'accepted_by_sp';
    }

    public function cancel(User $user, ServiceRequest $serviceRequest)
    {
        return $user->id === $serviceRequest->customer_user_id && 
               !in_array($serviceRequest->status, ['completed', 'expired']);
    }
} 