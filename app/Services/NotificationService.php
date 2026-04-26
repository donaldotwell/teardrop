<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public static function send(User|int $user, string $type, string $title, string $body, ?string $url = null): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        AppNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'url'     => $url,
        ]);
    }

    /**
     * Send a notification to every non-banned user in chunks to avoid memory spikes.
     */
    public static function broadcast(string $type, string $title, string $body, ?string $url = null): void
    {
        $now = now();

        User::where('status', '!=', 'banned')
            ->select('id')
            ->chunkById(500, function ($users) use ($type, $title, $body, $url, $now) {
                $rows = $users->map(fn($u) => [
                    'uuid'       => (string) \Illuminate\Support\Str::uuid(),
                    'user_id'    => $u->id,
                    'type'       => $type,
                    'title'      => $title,
                    'body'       => $body,
                    'url'        => $url,
                    'read_at'    => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->toArray();

                DB::table('app_notifications')->insert($rows);
            });
    }
}
