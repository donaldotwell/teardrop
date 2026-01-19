<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ForumPostController extends Controller
{
    public function index(Request $request)
    {
        $query = ForumPost::with('user')
            ->approved()
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_activity_at');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by author
        if ($request->filled('author')) {
            $query->byAuthor($request->author);
        }

        $posts = $query->paginate(15)->withQueryString();

        return view('forum.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('forum.posts.create');
    }

    public function store(Request $request)
    {
        // Check if user has at least one completed order
        $hasCompletedOrder = auth()->user()->orders()->where('status', 'completed')->exists();

        if (!$hasCompletedOrder) {
            return redirect()->back()->withErrors([
                'error' => 'You must have at least one completed order before posting on the forum.'
            ])->withInput();
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => [
                'required',
                'string',
                'max:10000',
                function ($attribute, $value, $fail) {
                    // Block links
                    if (preg_match('/https?:\/\/|www\.|\.com|\.org|\.net/i', $value)) {
                        $fail('Posts cannot contain links or URLs.');
                    }
                },
            ],
        ]);

        // Auto-assign to a random moderator with the 'moderator' role
        $moderator = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'moderator');
        })->inRandomOrder()->first();

        $post = auth()->user()->forumPosts()->create([
            'title' => $request->title,
            'body' => $request->body,
            'status' => 'pending',
            'assigned_moderator_id' => $moderator?->id,
            'last_activity_at' => now(),
        ]);

        AuditLog::log('post_created', null, ['post_id' => $post->id, 'title' => $post->title, 'status' => 'pending']);

        return redirect()->route('forum.index')->with('success', 'Post submitted for moderation. It will appear after approval.');
    }

    public function show(ForumPost $post)
    {
        // Only show approved posts to regular users (moderators/admins can see all)
        if ($post->status !== 'approved' && !auth()->user()->hasAnyRole(['admin', 'moderator'])) {
            abort(404, 'Post not found or pending approval.');
        }

        $post->load(['user', 'comments.user', 'comments.replies.user']);

        // Only increment views for approved posts
        if ($post->status === 'approved') {
            $post->incrementViews();
        }

        return view('forum.posts.show', compact('post'));
    }

    public function edit(ForumPost $post)
    {
        $this->authorize('update', $post);
        return view('forum.posts.edit', compact('post'));
    }

    public function update(Request $request, ForumPost $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => [
                'required',
                'string',
                'max:10000',
                function ($attribute, $value, $fail) {
                    if (preg_match('/https?:\/\/|www\.|\.com|\.org|\.net/i', $value)) {
                        $fail('Posts cannot contain links or URLs.');
                    }
                },
            ],
        ]);

        $post->update([
            'title' => $request->title,
            'body' => $request->body,
        ]);

        AuditLog::log('post_updated', null, ['post_id' => $post->id]);

        return redirect()->route('forum.posts.show', $post)->with('success', 'Post updated successfully!');
    }

    public function destroy(ForumPost $post)
    {
        $this->authorize('delete', $post);

        AuditLog::log('post_deleted', null, ['post_id' => $post->id, 'title' => $post->title]);
        $post->delete();

        return redirect()->route('forum.index')->with('success', 'Post deleted successfully!');
    }
}
