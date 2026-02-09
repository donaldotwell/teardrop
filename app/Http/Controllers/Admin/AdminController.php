<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\ListingMedia;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Order;
use App\Models\Listing;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_disputes' => Dispute::count(),
            'total_tickets' => SupportTicket::count(),
            'total_orders' => Order::count(),
            'total_listings' => Listing::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('usd_price'),
        ];

        // Get recent orders with relationships
        $recent_orders = Order::with(['user', 'listing'])
            ->latest()
            ->limit(5)
            ->get();

        // Get recent users
        $recent_users = User::latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_orders', 'recent_users'));
    }

    /**
     * Display reports page
     */
    public function reports()
    {
        // Weekly revenue data
        $weeklyRevenue = Order::where('status', 'completed')
            ->where('updated_at', '>=', now()->subWeeks(4))
            ->selectRaw('EXTRACT(WEEK FROM updated_at) as week, SUM(usd_price) as revenue')
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        // Monthly user growth
        $monthlyUsers = User::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top vendors by revenue
        $topVendors = User::withSum([
            'venderOrders as revenue' => function ($query) {
                $query->where('status', 'completed');
            }
        ], 'usd_price')
            ->whereHas('venderOrders', function ($query) {
                $query->where('status', 'completed');
            })
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->filter(fn($user) => $user->revenue > 0)
            ->values();

        return view('admin.reports', compact(
            'weeklyRevenue',
            'monthlyUsers',
            'topVendors'
        ));
    }

    /**
     * Display financial report page
     */
    public function financialReport()
    {
        // Revenue by currency
        $btcRevenue = Order::where('status', 'completed')
            ->where('currency', 'btc')
            ->sum('crypto_value');

        $xmrRevenue = Order::where('status', 'completed')
            ->where('currency', 'xmr')
            ->sum('crypto_value');

        $totalUsdRevenue = Order::where('status', 'completed')
            ->sum('usd_price');

        // Monthly revenue breakdown
        $monthlyRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('EXTRACT(YEAR FROM created_at)::int as year, EXTRACT(MONTH FROM created_at)::int as month, SUM(usd_price) as revenue, COUNT(*) as order_count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Escrow stats
        $activeEscrow = \App\Models\EscrowWallet::where('status', 'active')->sum('balance');
        $releasedEscrow = \App\Models\EscrowWallet::where('status', 'released')->count();

        // Fee collection stats
        $vendorConversions = \App\Models\WalletTransaction::where('comment', 'like', '%vendor_conversion%')
            ->count();

        return view('admin.reports.financial', compact(
            'btcRevenue',
            'xmrRevenue',
            'totalUsdRevenue',
            'monthlyRevenue',
            'activeEscrow',
            'releasedEscrow',
            'vendorConversions'
        ));
    }

    /**
     * Display users report page
     */
    public function usersReport()
    {
        // User growth over time
        $userGrowth = User::where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('EXTRACT(YEAR FROM created_at)::int as year, EXTRACT(MONTH FROM created_at)::int as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // User role breakdown
        $roleBreakdown = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->selectRaw('roles.name, COUNT(*) as count')
            ->groupBy('roles.name')
            ->get();

        // Active vs inactive users
        $userStatus = [
            'active' => User::where('status', 'active')->count(),
            'banned' => User::where('status', 'banned')->count(),
            'inactive' => User::where('last_seen_at', '<', now()->subDays(30))->count(),
            'total' => User::count(),
        ];

        // Top buyers by order count
        $topBuyers = User::withCount(['orders' => function ($q) {
            $q->where('status', 'completed');
        }])
            ->having('orders_count', '>', 0)
            ->orderByDesc('orders_count')
            ->limit(20)
            ->get();

        // Vendor stats
        $vendorCount = User::whereHas('roles', fn($q) => $q->where('name', 'vendor'))->count();

        return view('admin.reports.users', compact(
            'userGrowth',
            'roleBreakdown',
            'userStatus',
            'topBuyers',
            'vendorCount'
        ));
    }

    /**
     * Export report data as CSV
     */
    public function exportReport(string $type)
    {
        $filename = "report_{$type}_" . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($type) {
            $file = fopen('php://output', 'w');

            switch ($type) {
                case 'orders':
                    fputcsv($file, ['ID', 'UUID', 'Buyer', 'Listing', 'Currency', 'USD Price', 'Crypto Value', 'Status', 'Created At']);
                    Order::with(['user', 'listing'])->chunk(500, function ($orders) use ($file) {
                        foreach ($orders as $order) {
                            fputcsv($file, [
                                $order->id,
                                $order->uuid,
                                $order->user->username_pub ?? 'N/A',
                                $order->listing->title ?? 'N/A',
                                $order->currency,
                                $order->usd_price,
                                $order->crypto_value,
                                $order->status,
                                $order->created_at->toDateTimeString(),
                            ]);
                        }
                    });
                    break;

                case 'users':
                    fputcsv($file, ['ID', 'Username', 'Status', 'Vendor Level', 'Orders Count', 'Last Seen', 'Created At']);
                    User::withCount('orders')->chunk(500, function ($users) use ($file) {
                        foreach ($users as $user) {
                            fputcsv($file, [
                                $user->id,
                                $user->username_pub,
                                $user->status,
                                $user->vendor_level ?? 0,
                                $user->orders_count,
                                $user->last_seen_at?->toDateTimeString() ?? 'Never',
                                $user->created_at->toDateTimeString(),
                            ]);
                        }
                    });
                    break;

                case 'financial':
                    fputcsv($file, ['Month', 'Year', 'Revenue (USD)', 'Order Count']);
                    $data = Order::where('status', 'completed')
                        ->selectRaw('EXTRACT(YEAR FROM created_at)::int as year, EXTRACT(MONTH FROM created_at)::int as month, SUM(usd_price) as revenue, COUNT(*) as order_count')
                        ->groupBy('year', 'month')
                        ->orderBy('year')
                        ->orderBy('month')
                        ->get();
                    foreach ($data as $row) {
                        fputcsv($file, [$row->month, $row->year, number_format($row->revenue, 2), $row->order_count]);
                    }
                    break;

                default:
                    fputcsv($file, ['Error']);
                    fputcsv($file, ['Unknown report type: ' . $type]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display settings page
     */
    public function settings()
    {
        // Get current system settings
        $settings = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'maintenance_mode' => app()->isDownForMaintenance(),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'site_maintenance' => 'boolean',
            'new_registrations' => 'boolean',
            'featured_listings_limit' => 'integer|min:1|max:100',
            'max_images_per_listing' => 'integer|min:1|max:10',
            'session_lifetime' => 'integer|min:30|max:1440',
            'max_login_attempts' => 'integer|min:3|max:10',
            'email_order_notifications' => 'boolean',
            'admin_alerts' => 'boolean',
        ]);

        // Handle settings updates here
        // This would typically update a settings table or config files

        return redirect()->back()
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        try {
            // Clear various cache types
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            return redirect()->back()
                ->with('success', 'Application cache cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Optimize application cache
     */
    public function optimizeCache()
    {
        try {
            // Cache configuration, routes, and views for better performance
            \Illuminate\Support\Facades\Artisan::call('config:cache');
            \Illuminate\Support\Facades\Artisan::call('route:cache');
            \Illuminate\Support\Facades\Artisan::call('view:cache');

            return redirect()->back()
                ->with('success', 'Application optimized successfully. Configuration, routes, and views cached.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to optimize application: ' . $e->getMessage());
        }
    }

    /**
     * Restart queue workers
     */
    public function restartQueue()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('queue:restart');

            return redirect()->back()
                ->with('success', 'Queue workers restarted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to restart queue workers: ' . $e->getMessage());
        }
    }

    /**
     * Clean up old database data
     */
    public function cleanupDatabase()
    {
        try {
            $cleaned = 0;

            // Clean up old soft-deleted records (older than 30 days)
            $cleaned += User::onlyTrashed()
                ->where('deleted_at', '<', now()->subDays(30))
                ->forceDelete();

            $cleaned += Listing::onlyTrashed()
                ->where('deleted_at', '<', now()->subDays(30))
                ->forceDelete();

            // Clean up old cancelled orders (older than 90 days)
            $cleaned += Order::where('status', 'cancelled')
                ->where('cancelled_at', '<', now()->subDays(90))
                ->delete();

            // Clean up old wallet transactions (older than 1 year)
            $cleaned += WalletTransaction::where('created_at', '<', now()->subYear())
                ->whereNotNull('completed_at')
                ->delete();

            return redirect()->back()
                ->with('success', "Database cleanup completed. Removed {$cleaned} old records.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to cleanup database: ' . $e->getMessage());
        }
    }

    /**
     * Create database backup
     */
    public function backupDatabase()
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups/' . $filename);

            // Create backups directory if it doesn't exist
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            // Create mysqldump command
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($path)
            );

            // Execute backup command
            $output = null;
            $resultCode = null;
            exec($command, $output, $resultCode);

            if ($resultCode === 0 && file_exists($path)) {
                $size = human_filesize(filesize($path));
                return redirect()->back()
                    ->with('success', "Database backup created successfully: {$filename} ({$size})");
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to create database backup. Make sure mysqldump is available.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to backup database: ' . $e->getMessage());
        }
    }

    /**
     * Enable maintenance mode
     */
    public function enableMaintenance()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('down', [
                '--message' => 'Site is temporarily under maintenance. Please check back soon.',
                '--retry' => 60,
            ]);

            return redirect()->back()
                ->with('warning', 'Maintenance mode enabled. The site is now unavailable to users.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to enable maintenance mode: ' . $e->getMessage());
        }
    }

    /**
     * Purge old data (DANGEROUS - permanent deletion)
     */
    public function purgeOldData()
    {
        try {
            $purged = 0;

            // Purge old orders (older than 2 years)
            $purged += Order::where('created_at', '<', now()->subYears(2))->delete();

            // Purge old inactive users (inactive for more than 2 years, no orders)
            $purged += User::where('last_seen', '<', now()->subYears(2))
                ->where('status', 'inactive')
                ->whereDoesntHave('orders')
                ->delete();

            // Purge old wallet transactions (older than 2 years)
            $purged += WalletTransaction::where('created_at', '<', now()->subYears(2))->delete();

            // Purge old listing media files (where listing no longer exists)
            $mediaToDelete = ListingMedia::whereDoesntHave('listing')->get();
            foreach ($mediaToDelete as $media) {
                // Delete file from storage
                if (\Storage::exists($media->path)) {
                    \Storage::delete($media->path);
                }
                $media->delete();
                $purged++;
            }

            return redirect()->back()
                ->with('success', "Data purge completed. Permanently removed {$purged} old records.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to purge old data: ' . $e->getMessage());
        }
    }
}
