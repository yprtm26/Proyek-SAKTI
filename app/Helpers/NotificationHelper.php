<?php

use App\Models\Notification;

if (!function_exists('pushNotif')) {

    /**
     * Kirim notifikasi ke user
     */
    function pushNotif(
        int $userId,
        string $title,
        string $message,
        ?string $url = null
    ): void {
        Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'url'     => $url,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }
}
