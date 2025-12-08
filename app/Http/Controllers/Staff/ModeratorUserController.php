<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModeratorUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->withCount([
                'forumReports as reports_against_count' => function($q) {
                    $q->whereHas('reportable', function($subQuery) {
                        $subQuery->where('user_id', '=', DB::raw('users.id'));
                    });
                }
            ]);

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username_pub', 'like', "%{$search}%")
                    ->orWhere('username_pri', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        // Apply trust level filter
        if ($request->filled('trust_level')) {
            $query->where('trust_level', $request->trust_level);
        }

        // Apply vendor level filter
        if ($request->filled('vendor_level')) {
            $query->where('vendor_level', $request->vendor_level);
        }

        // Apply suspicious users filter
        if ($request->filled('suspicious')) {
            $twoMonthsAgo = now()->subMonths(2);
            $query->where('created_at', '>', $twoMonthsAgo)
                ->whereHas('forumReports', function($q) {
                    $q->where('created_at', '>', now()->subWeek());
                }, '>=', 3);
        }

        // Default ordering
        $users = $query->latest()->paginate(20);

        // Calculate statistics for filter tabs
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'banned_users' => User::where('status', 'banned')->count(),
            'suspicious_users' => $this->getSuspiciousUsersCount(),
        ];

        return view('moderator.users.index', compact('users', 'stats'));
    }

    /**
     * Get count of suspicious users for stats
     */
    private function getSuspiciousUsersCount()
    {
        $twoMonthsAgo = now()->subMonths(2);

        return User::where('created_at', '>', $twoMonthsAgo)
            ->whereHas('forumReports', function($query) {
                $query->where('created_at', '>', now()->subWeek());
            }, '>=', 3)
            ->count();
    }
}
