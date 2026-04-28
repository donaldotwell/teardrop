<?php

namespace App\Http\Controllers;

use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Fetch all threads the user participates in, then deduplicate by
        // normalised pair — handles legacy duplicate (A→B) + (B→A) threads.
        $all = $user->allThreads()
            ->with(['latestMessage.sender', 'user', 'receiver'])
            ->withCount(['messages as unread_count' => function ($q) use ($user) {
                $q->where('receiver_id', $user->id)->whereNull('read_at');
            }])
            ->orderByDesc('updated_at')
            ->get()
            ->unique(function ($thread) {
                $ids = [$thread->user_id, $thread->receiver_id];
                sort($ids);
                return implode('-', $ids);
            })
            ->values();

        $page    = (int) $request->get('page', 1);
        $perPage = 20;
        $threads = new \Illuminate\Pagination\LengthAwarePaginator(
            $all->forPage($page, $perPage),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('messages.index', compact('threads'));
    }

    public function show(Request $request, MessageThread $thread)
    {
        $user = $request->user();

        if ($thread->user_id !== $user->id && $thread->receiver_id !== $user->id) {
            abort(403, 'You are not authorized to view this thread.');
        }

        $thread->load(['user', 'receiver']);

        // Mark all unread messages sent to the current user as read
        $thread->messages()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $other = $thread->user_id === $user->id ? $thread->receiver : $thread->user;

        $messages = $thread->messages()
            ->with(['sender'])
            ->oldest()
            ->get();

        return view('messages.show', compact('messages', 'thread', 'other'));
    }

    public function store(Request $request, MessageThread $thread)
    {
        $user = $request->user();

        if ($thread->user_id !== $user->id && $thread->receiver_id !== $user->id) {
            abort(403, 'You are not authorized to send a message to this thread.');
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $receiverId = $thread->user_id === $user->id ? $thread->receiver_id : $thread->user_id;

        DB::table('user_messages')->insert([
            'thread_id'   => $thread->id,
            'sender_id'   => $user->id,
            'receiver_id' => $receiverId,
            'message'     => $request->message,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Touch the thread so ordering by updated_at works
        $thread->touch();

        return redirect()->route('messages.show', $thread);
    }
}
