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

        $post = auth()->user()->forumPosts()->create([
            'title' => $request->title,
            'body' => $request->body,
            'last_activity_at' => now(),
        ]);

        AuditLog::log('post_created', null, ['post_id' => $post->id, 'title' => $post->title]);

        return redirect()->route('forum.posts.show', $post)->with('success', 'Post created successfully!');
    }

    public function show(ForumPost $post)
    {
        $post->load(['user', 'comments.user', 'comments.replies.user']);
        $post->incrementViews();

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
