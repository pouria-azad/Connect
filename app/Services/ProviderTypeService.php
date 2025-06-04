<?php

namespace App\Services;

class ProviderTypeService
{
    public function validate(?string $type = null): bool
    {
        $user = auth()->user();
        
        if (!$user || $user->user_type !== 'provider') {
            return false;
        }

        if ($type && (!$user->provider || $user->provider->provider_type !== $type)) {
            return false;
        }

        return true;
    }
} 