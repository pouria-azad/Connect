<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

trait HasOnlineStatus
{
    public function isOnline(): bool
    {
        $lastActivity = Cache::get('user-online-' . $this->id);
        return $lastActivity && Carbon::parse($lastActivity)->diffInMinutes() < 5;
    }

    public function updateLastActivity(): void
    {
        Cache::put('user-online-' . $this->id, now(), Carbon::now()->addMinutes(5));
    }

    public function getLastActivityAttribute(): ?string
    {
        return Cache::get('user-online-' . $this->id);
    }
} 