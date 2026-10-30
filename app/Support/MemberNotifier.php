<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class MemberNotifier
{
    public static function send(
        User $user,
        string $title,
        string $message,
        string $type,
        ?string $url = null,
        array $metadata = [],
    ): void {
        $now = now();

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => [
                'title' => $title,
                'message' => $message,
                'url' => $url,
            ] + $metadata,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
