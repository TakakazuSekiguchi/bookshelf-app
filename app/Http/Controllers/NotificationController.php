<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $unreadNotificationCount = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
        
        return view('notifications.index', compact('unreadNotificationCount'));
    }
}
