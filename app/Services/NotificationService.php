<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;

class NotificationService
{
    public function sendNotification(User $user, string $type, string $title, string $body, array $data = []): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data ? json_encode($data) : null,
        ]);
    }

    public function markAsRead(int $notificationId): void
    {
        Notification::where('id', $notificationId)->update(['is_read' => true]);
    }

    public function getUnreadNotifications(User $user): array
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
} 