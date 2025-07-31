<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function myNotifications()
    {
        return response()->json(auth()->user()->notifications);
    }
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'تم تعليم جميع الإشعارات كمقروءة']);
    }
        
}
