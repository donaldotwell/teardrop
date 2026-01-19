<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ForumReport;
use App\Models\ForumPost;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ForumModerationController extends Controller
{
    /**
     * Show pending forum posts
     */
    public function pendingPosts(Request $request)
    {
        $query = ForumPost::with(['user', 'assignedModerator'])
            ->pending()
            ->latest();

        // Filter by assigned moderator
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'me') {
                $query->where('assigned_moderator_id', auth()->id());
            } elseif ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_moderator_id');
            }
        }

        $posts = $query->paginate(20);

        return view('forum.moderate.pending-posts', compact('posts'));
    }

    /**
     * Show all posts with moderation status
     */
    public function allPosts(Request $request)
    {
        $query = ForumPost::with(['user', 'assignedModerator', 'moderatedBy'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->paginate(20);

        return view('forum.moderate.all-posts', compact('posts'));
    }

    /**
     * Approve a pending post
     */
    public function approvePost(Request $request, ForumPost $post)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $post->update([
            'status' => 'approved',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
            'moderation_notes' => $request->notes,
        ]);

        AuditLog::log('post_approved', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'approved_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Post approved successfully.');
    }

    /**
     * Reject a pending post
     */
    public function rejectPost(Request $request, ForumPost $post)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $post->update([
            'status' => 'rejected',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
            'moderation_notes' => $request->notes,
        ]);

        AuditLog::log('post_rejected', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'rejected_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Post rejected.');
    }

    public function reports(Request $request)
    {
        $query = ForumReport::with(['user', 'reportable', 'reviewer'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to pending reports
            $query->where('status', 'pending');
        }

        // Filter for urgent reports (last 2 hours)
        if ($request->filled('urgent')) {
            $query->where('created_at', '>=', now()->subHours(2))
                ->where('status', 'pending');
        }

        // Filter for old reports (over 24 hours)
        if ($request->filled('old')) {
            $query->where('created_at', '<=', now()->subHours(24))
                ->where('status', 'pending');
        }

        $reports = $query->paginate(20);

        return view('forum.moderate.reports', compact('reports'));
    }

    public function reviewReport(Request $request, ForumReport $report)
    {
        $request->validate([
            'action' => 'required|in:dismiss,ban_user',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Mark report as reviewed
        $report->markAsReviewed(auth()->id(), $request->notes);

        // Handle ban user action
        if ($request->action === 'ban_user') {
            $reportedUser = null;

            // Determine the reported user based on content type
            if ($report->reportable_type === 'App\Models\ForumPost') {
                $reportedUser = $report->reportable->user;
            } elseif ($report->reportable_type === 'App\Models\ForumComment') {
                $reportedUser = $report->reportable->user;
            }

            if ($reportedUser) {
                $reportedUser->update(['status' => 'banned']);

                // Log the ban action
                AuditLog::log('user_banned', $reportedUser->id, [
                    'reason' => 'Forum violation',
                    'report_id' => $report->id,
                    'notes' => $request->notes,
                    'banned_by' => auth()->id()
                ]);
            }
        }

        // Log the report review action
        AuditLog::log('report_reviewed', $report->user_id, [
            'report_id' => $report->id,
            'action' => $request->action,
            'notes' => $request->notes,
            'reviewed_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Report reviewed successfully.');
    }

    public function banUser(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        // Update user status
        $user->update(['status' => 'banned']);

        // Log the ban action
        AuditLog::log('user_banned', $user->id, [
            'reason' => $request->reason ?? 'Manual ban by moderator',
            'banned_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'User banned successfully.');
    }

    public function unbanUser(User $user)
    {
        // Update user status
        $user->update(['status' => 'active']);

        // Log the unban action
        AuditLog::log('user_unbanned', $user->id, [
            'unbanned_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'User unbanned successfully.');
    }
}
