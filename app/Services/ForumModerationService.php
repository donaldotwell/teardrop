<?php

namespace App\Services;

use App\Models\User;
use App\Models\ForumReport;
use App\Models\AuditLog;

class ForumModerationService
{
    /**
     * Check if a user should be auto-banned for suspicious reporting behavior
     */
    public function checkForAbusiveReporting(User $user)
    {
        // Check if user is new (< 2 months) and has made many reports
        $isNewUser = $user->created_at->gt(now()->subMonths(2));
        $recentReports = $user->forumReports()->where('created_at', '>', now()->subDays(7))->count();

        // If new user has made more than 10 reports in a week, flag for review
        if ($isNewUser && $recentReports > 10) {
            AuditLog::log('suspicious_reporting_detected', $user->id, [
                'reports_count' => $recentReports,
                'account_age_days' => $user->created_at->diffInDays(now())
            ]);

            // Could implement auto-throttling here
            return true;
        }

        return false;
    }

    /**
     * Auto-ban user for confirmed violations
     */
    public function banUserForViolation(User $user, $reason = 'Forum violation', $reportId = null)
    {
        $user->update(['status' => 'banned']);

        AuditLog::log('user_banned', $user->id, [
            'reason' => $reason,
            'report_id' => $reportId,
            'auto_ban' => true
        ]);
    }

    /**
     * Get moderation statistics
     */
    public function getModerationStats()
    {
        return [
            'pending_reports' => ForumReport::pending()->count(),
            'total_reports_today' => ForumReport::whereDate('created_at', today())->count(),
            'total_bans_this_week' => AuditLog::where('action', 'user_banned')
                ->where('created_at', '>', now()->subWeek())->count(),
        ];
    }
}
