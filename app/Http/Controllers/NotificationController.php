<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\TicketService;

class NotificationController extends Controller
{
    public function index(TicketService $tickets)
    {
        return view('notifications.index', [
            'title' => 'Notifications',
            'subtitle' => 'Unread and recent system notifications',
            'breadcrumbs' => [['label' => 'Desk', 'url' => route('home')], ['label' => 'System'], ['label' => 'Notifications']],
            'notifications' => AppNotification::where('user_id', $tickets->currentUserId())->latest('created_at')->paginate(30),
        ]);
    }

    public function markAsRead(AppNotification $notification)
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllAsRead(TicketService $tickets)
    {
        AppNotification::where('user_id', $tickets->currentUserId())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
