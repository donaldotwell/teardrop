<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ForumReport;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ForumModerationController extends Controller
{

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
