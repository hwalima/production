<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Mark a single notification as read (AJAX POST). */
    public function markRead(string $id)
    {
        $notif = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notif) {
            $notif->markAsRead();
        }

        return response()->json(['ok' => true]);
    }

    /** Mark all notifications as read (AJAX POST). */
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
