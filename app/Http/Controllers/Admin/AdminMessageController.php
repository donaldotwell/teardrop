<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastAdminMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMessageController extends Controller
{
    public function compose(): View
    {
        $counts = [
            'all'        => User::where('status', '!=', 'banned')
                                ->where('id', '!=', auth()->id())
                                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
                                ->count(),
            'vendors'    => User::where('status', '!=', 'banned')
                                ->whereHas('roles', fn($q) => $q->where('name', 'vendor'))
                                ->count(),
            'moderators' => User::where('status', '!=', 'banned')
                                ->whereHas('roles', fn($q) => $q->where('name', 'moderator'))
                                ->count(),
        ];

        return view('admin.messages.compose', compact('counts'));
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'scope' => 'required|in:all,vendors,moderators',
            'body'  => 'required|string|min:1|max:5000',
        ]);

        $admin = $request->user();
        $scope = $validated['scope'];
        $body  = $validated['body'];

        BroadcastAdminMessage::dispatch($admin->id, $scope, $body);

        $label = match ($scope) {
            'vendors'    => 'all vendors',
            'moderators' => 'all moderators',
            default      => 'all users',
        };

        return back()->with('success', "Message queued — it will be delivered to {$label} shortly.");
    }
}
