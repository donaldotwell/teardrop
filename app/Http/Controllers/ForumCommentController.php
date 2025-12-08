<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ForumCommentController extends Controller
{
    public function store(Request $request, ForumPost $post)
    {
        $request->validate([
            'body' => [
                'required',
                'string',
                'max:5000',
                function ($attribute, $value, $fail) {
                    if (preg_match('/https?:\/\/|www\.|\.com|\.org|\.net/i', $value)) {
                        $fail('Comments cannot contain links or URLs.');
                    }
                },
            ],
        ]);

        $comment = $post->allComments()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        AuditLog::log('comment_created', null, ['comment_id' => $comment->id, 'post_id' => $post->id]);

        return redirect()->route('forum.posts.show', $post)->with('success', 'Comment added successfully!');
    }

    public function reply(Request $request, ForumComment $comment)
    {
        $request->validate([
            'body' => [
                'required',
                'string',
                'max:5000',
                function ($attribute, $value, $fail) {
                    if (preg_match('/https?:\/\/|www\.|\.com|\.org|\.net/i', $value)) {
                        $fail('Replies cannot contain links or URLs.');
                    }
                },
            ],
        ]);

        $reply = ForumComment::create([
            'user_id' => auth()->id(),
            'forum_post_id' => $comment->forum_post_id,
            'parent_id' => $comment->id,
            'body' => $request->body,
        ]);

        AuditLog::log('comment_reply_created', null, ['reply_id' => $reply->id, 'parent_comment_id' => $comment->id]);

        return redirect()->route('forum.posts.show', $comment->post)->with('success', 'Reply added successfully!');
    }

    public function destroy(ForumComment $comment)
    {
        $this->authorize('delete', $comment);

        AuditLog::log('comment_deleted', null, ['comment_id' => $comment->id]);
        $comment->delete();

        return redirect()->route('forum.posts.show', $comment->post)->with('success', 'Comment deleted successfully!');
    }
}
