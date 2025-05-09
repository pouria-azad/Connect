<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\SupportTicket;
use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Policies\SupportTicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
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
        Gate::define('isAdmin', function (User $user) {
            return $user->hasRole(UserRole::Admin);
        });
    }

    protected $policies = [
        Announcement::class => AnnouncementPolicy::class,
        SupportTicket::class => SupportTicketPolicy::class,
    ];
}
