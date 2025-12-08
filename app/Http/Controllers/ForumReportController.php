<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumReport;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ForumReportController extends Controller
{
    public function reportPost(Request $request, ForumPost $post)
    {
        // Check if user has already reported this post
        $existingReport = ForumReport::where([
            'user_id' => auth()->id(),
            'reportable_type' => ForumPost::class,
            'reportable_id' => $post->id,
        ])->first();

        if ($existingReport) {
            return redirect()->back()->with('error', 'You have already reported this post.');
        }

        // Rate limiting: max 5 reports per day
        $reportsToday = ForumReport::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->count();

        if ($reportsToday >= 5) {
            return redirect()->back()->with('error', 'You have reached the daily report limit.');
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        ForumReport::create([
            'user_id' => auth()->id(),
            'reportable_type' => ForumPost::class,
            'reportable_id' => $post->id,
            'reason' => $request->reason,
        ]);

        AuditLog::log('post_reported', $post->user_id, ['post_id' => $post->id, 'reason' => $request->reason]);

        return redirect()->back()->with('success', 'Post reported successfully. Our moderators will review it.');
    }

    public function reportComment(Request $request, ForumComment $comment)
    {
        $existingReport = ForumReport::where([
            'user_id' => auth()->id(),
            'reportable_type' => ForumComment::class,
            'reportable_id' => $comment->id,
        ])->first();

        if ($existingReport) {
            return redirect()->back()->with('error', 'You have already reported this comment.');
        }

        $reportsToday = ForumReport::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->count();

        if ($reportsToday >= 5) {
            return redirect()->back()->with('error', 'You have reached the daily report limit.');
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        ForumReport::create([
            'user_id' => auth()->id(),
            'reportable_type' => ForumComment::class,
            'reportable_id' => $comment->id,
            'reason' => $request->reason,
        ]);

        AuditLog::log('comment_reported', $comment->user_id, ['comment_id' => $comment->id, 'reason' => $request->reason]);

        return redirect()->back()->with('success', 'Comment reported successfully. Our moderators will review it.');
    }
}
