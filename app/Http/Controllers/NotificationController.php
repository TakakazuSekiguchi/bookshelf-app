<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    public function read(string $notification)
    {
        $user = auth()->user();

        $notification = $user->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return redirect()->route('notifications.index')
            ->with('success', '通知を既読にしました。');
    }
}
