<?php

namespace App\Services;

use App\Models\User;

class NotificationService
{
    /**
     * @return array{data: mixed, unread_count: int}
     */
    public function forMember(User $user): array
    {
        return [
            'data' => $user->notifications()->latest()->paginate(20),
            'unread_count' => $user->unreadNotifications()->count(),
        ];
    }

    public function markRead(User $user, string $id): void
    {
        $notification = $user->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();
    }
}
