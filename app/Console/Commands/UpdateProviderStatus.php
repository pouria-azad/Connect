<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateProviderStatus extends Command
{
    protected $signature = 'provider:update-status';
    protected $description = 'Update provider online status';

    public function handle(): void
    {
        $providers = User::where('user_type', 'provider')
            ->whereHas('provider')
            ->get();

        foreach ($providers as $provider) {
            if ($provider->isOnline()) {
                $provider->provider->update([
                    'last_activity_at' => $provider->last_activity
                ]);
            }
        }

        $this->info('Provider status updated successfully.');
    }
} 