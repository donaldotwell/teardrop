<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class ModeratorAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'targetUser'])->latest('created_at');

        // Apply action filter
        if ($request->filled('action')) {
            if ($request->action === 'content') {
                // Group content-related actions
                $query->where(function($q) {
                    $q->where('action', 'like', '%content%')
                        ->orWhere('action', 'like', '%post%')
                        ->orWhere('action', 'like', '%comment%');
                });
            } else {
                $query->where('action', 'like', "%{$request->action}%");
            }
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('username_pub', 'like', "%{$search}%");
                })
                    ->orWhereHas('targetUser', function($targetQuery) use ($search) {
                        $targetQuery->where('username_pub', 'like', "%{$search}%");
                    })
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            });
        }

        // Apply moderator filter
        if ($request->filled('moderator')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username_pub', $request->moderator);
            });
        }

        // Apply date filter
        $this->applyDateFilter($query, $request);

        // Get paginated logs
        $logs = $query->paginate(50);

        // Get statistics for dashboard cards and filters
        $stats = $this->getAuditStats();

        // Get list of moderators for filter dropdown
        $moderators = $this->getModerators();

        return view('moderator.audit.index', compact('logs', 'stats', 'moderators'));
    }

    /**
     * Apply date filter to query
     */
    private function applyDateFilter($query, Request $request)
    {
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->where('created_at', '>=', now()->startOfWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->startOfMonth());
                    break;
                case 'custom':
                    if ($request->filled('date_from')) {
                        $query->whereDate('created_at', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $query->whereDate('created_at', '<=', $request->date_to);
                    }
                    break;
            }
        }
    }

    /**
     * Get audit statistics
     */
    private function getAuditStats()
    {
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $myId = auth()->id();

        return [
            'total_logs' => AuditLog::count(),
            'user_bans' => AuditLog::where('action', 'user_banned')->count(),
            'report_reviews' => AuditLog::where('action', 'report_reviewed')->count(),
            'content_actions' => AuditLog::where(function($q) {
                $q->where('action', 'like', '%content%')
                    ->orWhere('action', 'like', '%post%')
                    ->orWhere('action', 'like', '%comment%');
            })->count(),
            'today_actions' => AuditLog::where('created_at', '>=', $today)->count(),
            'week_actions' => AuditLog::where('created_at', '>=', $weekStart)->count(),
            'my_actions' => AuditLog::where('user_id', $myId)->count(),
            'active_moderators' => $this->getActiveModeratorsCount(),
        ];
    }

    /**
     * Get list of moderators who have performed actions
     */
    private function getModerators()
    {
        return User::whereHas('auditLogs')
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'moderator']);
            })
            ->orderBy('username_pub')
            ->get(['id', 'username_pub']);
    }

    /**
     * Get count of moderators who have been active in the last 30 days
     */
    private function getActiveModeratorsCount()
    {
        $thirtyDaysAgo = now()->subDays(30);

        return User::whereHas('auditLogs', function($q) use ($thirtyDaysAgo) {
            $q->where('created_at', '>=', $thirtyDaysAgo);
        })
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'moderator']);
            })
            ->distinct()
            ->count();
    }

    /**
     * Export audit logs (optional feature)
     */
    public function export(Request $request)
    {
        // This would implement CSV/PDF export functionality
        // For now, just return the same filtered data as JSON
        $query = AuditLog::with(['user', 'targetUser'])->latest('created_at');

        // Apply same filters as index method
        if ($request->filled('action')) {
            if ($request->action === 'content') {
                $query->where(function($q) {
                    $q->where('action', 'like', '%content%')
                        ->orWhere('action', 'like', '%post%')
                        ->orWhere('action', 'like', '%comment%');
                });
            } else {
                $query->where('action', 'like', "%{$request->action}%");
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('username_pub', 'like', "%{$search}%");
                })
                    ->orWhereHas('targetUser', function($targetQuery) use ($search) {
                        $targetQuery->where('username_pub', 'like', "%{$search}%");
                    })
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $this->applyDateFilter($query, $request);

        $logs = $query->limit(1000)->get(); // Limit for performance

        return response()->json($logs->map(function($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'moderator' => $log->user->username_pub ?? 'System',
                'target' => $log->targetUser->username_pub ?? null,
                'details' => $log->details,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->toISOString(),
            ];
        }));
    }
}
