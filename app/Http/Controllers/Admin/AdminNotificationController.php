<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(): View
    {
        return view('admin.notifications.index');
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'  => 'required|in:order,dispute,support,forum,system',
            'title' => 'required|string|max:255',
            'body'  => 'required|string|max:1000',
            'url'   => 'nullable|string|max:500',
        ]);

        NotificationService::broadcast(
            $validated['type'],
            $validated['title'],
            $validated['body'],
            $validated['url'] ?: null,
        );

        return back()->with('success', 'Notification broadcast to all users.');
    }
}
