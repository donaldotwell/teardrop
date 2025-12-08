<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumReport;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ModeratorController extends Controller
{

    public function dashboard()
    {
        // Get basic statistics
        $stats = $this->getModeratorStats();

        // Get my personal moderation statistics
        $my_stats = $this->getMyModerationStats();

        // Get recent data for dashboard widgets
        $recent_reports = $this->getRecentReports();
        $recent_audit_logs = $this->getRecentAuditLogs();

        return view('moderator.dashboard', compact(
            'stats',
            'my_stats',
            'recent_reports',
            'recent_audit_logs'
        ));
    }

    /**
     * Get key moderation statistics
     */
    private function getModeratorStats()
    {
        $now = now();
        $twoHoursAgo = now()->subHours(2);

        return [
            // Critical alerts
            'critical_reports' => ForumReport::pending()
                ->where('created_at', '>', $twoHoursAgo)
                ->count(),

            'old_unreviewed_reports' => ForumReport::pending()
                ->where('created_at', '<', now()->subDay())
                ->count(),

            // Basic counts
            'pending_reports' => ForumReport::pending()->count(),
            'active_users' => $this->getActiveUsersCount(),
            'flagged_content' => $this->getFlaggedContentCount(),
            'banned_users' => User::where('status', 'banned')->count(),
            'suspicious_users' => $this->getSuspiciousUsersCount(),

            // Health metrics
            'total_posts' => ForumPost::count(),
            'total_comments' => ForumComment::count(),
            'report_rate' => $this->calculateReportRate(),
            'avg_response_time' => $this->getAverageResponseTime(),
        ];
    }

    /**
     * Get my personal moderation statistics
     */
    private function getMyModerationStats()
    {
        $userId = auth()->id();
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();

        return [
            'reports_today' => ForumReport::where('reviewed_by', $userId)
                ->where('reviewed_at', '>=', $today)
                ->count(),

            'reports_this_week' => ForumReport::where('reviewed_by', $userId)
                ->where('reviewed_at', '>=', $weekStart)
                ->count(),

            'users_banned_today' => AuditLog::where('user_id', $userId)
                ->where('action', 'user_banned')
                ->where('created_at', '>=', $today)
                ->count(),

            'avg_review_time' => $this->getMyAverageReviewTime(),
        ];
    }

    /**
     * Get recent reports for dashboard display
     */
    private function getRecentReports()
    {
        return ForumReport::with(['user', 'reportable'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * Get recent audit logs for activity feed
     */
    private function getRecentAuditLogs()
    {
        return AuditLog::with(['user', 'targetUser'])
            ->whereIn('action', ['user_banned', 'user_unbanned', 'report_reviewed', 'content_removed'])
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Calculate the number of active users (posted in last 30 days)
     */
    private function getActiveUsersCount()
    {
        $thirtyDaysAgo = now()->subDays(30);

        return DB::table('users')
            ->join('forum_posts', 'users.id', '=', 'forum_posts.user_id')
            ->where('forum_posts.created_at', '>=', $thirtyDaysAgo)
            ->distinct('users.id')
            ->count();
    }

    /**
     * Get count of flagged content requiring review
     */
    private function getFlaggedContentCount()
    {
        // This would depend on your flagging system
        // For now, return posts with multiple reports
        return ForumPost::whereHas('reports', function($query) {
            $query->where('status', 'pending');
        }, '>=', 2)->count();
    }

    /**
     * Get count of suspicious users
     */
    private function getSuspiciousUsersCount()
    {
        // Users with multiple reports against their content
        return User::whereHas('forumPosts.reports', function($query) {
            $query->where('status', 'pending');
        }, '>=', 3)->count();
    }

    /**
     * Calculate report rate as percentage
     */
    private function calculateReportRate()
    {
        $totalPosts = ForumPost::count();
        $totalReports = ForumReport::count();

        if ($totalPosts == 0) return 0;

        return ($totalReports / $totalPosts) * 100;
    }

    /**
     * Get average response time to reports
     */
    private function getAverageResponseTime()
    {
        $avgMinutes = ForumReport::whereNotNull('reviewed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, reviewed_at)) as avg_time')
            ->value('avg_time');

        if (!$avgMinutes) return 'N/A';

        if ($avgMinutes < 60) {
            return round($avgMinutes) . 'min';
        } else {
            return round($avgMinutes / 60, 1) . 'h';
        }
    }

    /**
     * Get my personal average review time
     */
    private function getMyAverageReviewTime()
    {
        $avgMinutes = ForumReport::where('reviewed_by', auth()->id())
            ->whereNotNull('reviewed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, reviewed_at)) as avg_time')
            ->value('avg_time');

        if (!$avgMinutes) return 'N/A';

        if ($avgMinutes < 60) {
            return round($avgMinutes) . 'min';
        } else {
            return round($avgMinutes / 60, 1) . 'h';
        }
    }
}
