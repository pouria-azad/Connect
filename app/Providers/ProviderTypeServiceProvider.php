<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ProviderTypeService;

class ProviderTypeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProviderTypeService::class, function ($app) {
            return new ProviderTypeService();
        });
    }

    public function boot(): void
    {
        //
    }
} 