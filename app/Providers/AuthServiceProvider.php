<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\Provider;
use App\Models\Service;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\RequestFile;
use App\Models\ServiceRequest;
use App\Policies\AnnouncementPolicy;
use App\Policies\ChatMessagePolicy;
use App\Policies\ConversationPolicy;
use App\Policies\ProviderPolicy;
use App\Policies\ServicePolicy;
use App\Policies\SupportTicketPolicy;
use App\Policies\FilePolicy;
use App\Policies\ServiceRequestPolicy;
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
        Service::class  => ServicePolicy::class,
        Provider::class => ProviderPolicy::class,
        ChatMessage::class => ChatMessagePolicy::class,
        Conversation::class => ConversationPolicy::class,
        RequestFile::class => FilePolicy::class,
        ServiceRequest::class => ServiceRequestPolicy::class,
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
        // حذف Gate isAdmin
    }
}
