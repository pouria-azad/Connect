<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Announcement;
use App\Models\SupportTicket;
use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Policies\SupportTicketPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        Announcement::class => AnnouncementPolicy::class,
        SupportTicket::class => SupportTicketPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('isAdmin', function ($user) {
            return $user instanceof \App\Models\Admin;
        });

    }
}
