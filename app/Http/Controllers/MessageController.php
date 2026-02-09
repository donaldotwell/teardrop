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
        // Get the logged-in user
        $user = $request->user();

        // Fetch all user threads (both sent and received) with latest message
        $threads = $user->allThreads()
            ->with(['latestMessage.order', 'user', 'receiver'])
            ->paginate();

        return view('messages.index', compact('threads'));
    }

    public function show(Request $request, MessageThread $thread)
    {
        // Get the logged-in user
        $user = $request->user();

        // Ensure the logged-in user is part of the thread
        if ($thread->user_id !== $user->id && $thread->receiver_id !== $user->id) {
            abort(403, 'You are not authorized to view this thread.');
        }

        // Load the receiver of the message thread
        $thread->load('receiver');

        // Fetch all messages in the thread
        $messages = $thread->messages()
            ->with(['sender', 'receiver'])
            ->latest()
            ->paginate(10);

        return view('messages.show', compact('messages', 'thread'))
            ->with('success', 'Message sent successfully');
    }

    public function store(Request $request, MessageThread $thread)
    {
        // Get the logged-in user
        $user = $request->user();

        // Ensure the logged-in user is part of the thread
        if ($thread->user_id !== $user->id && $thread->receiver_id !== $user->id) {
            abort(403, 'You are not authorized to send a message to this thread.');
        }

        // Validate the request data
        $request->validate([
            'message' => 'required|string|max:255',
        ]);


        // Get the receiver of the message thread
        $receiver = $thread->receiver;

        // Validate the request data
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        // Create a new message
        DB::table('user_messages')->insert([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'message' => $request->message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('messages.show', $thread);
    }
}
