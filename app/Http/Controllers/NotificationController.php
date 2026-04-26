<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = AppNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, AppNotification $notification): RedirectResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        if ($notification->url) {
            return redirect($notification->url);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
