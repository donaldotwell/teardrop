<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class AdminOrdersController extends Controller
{
    /**
     * Display orders management page
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'listing.user']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->get('currency'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate stats
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_value' => Order::where('status', 'completed')->sum('usd_price'),
        ];

        // Recent activity
        $recent_activity = Order::with(['user', 'listing'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => $order->status,
                    'message' => "Order #{$order->uuid} {$order->status} by {$order->user->username_pub}",
                    'time' => $order->updated_at->diffForHumans(),
                    'amount' => $order->usd_price,
                ];
            });

        return view('admin.orders.index', compact('orders', 'stats', 'recent_activity'));
    }

    /**
     * Show order details
     */
    public function show(Order $order)
    {
        $order->load(['user', 'listing.user', 'messages', 'finalizationWindow']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Mark order as completed
     */
    public function complete(Order $order)
    {
        if ($order->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending orders can be completed.');
        }

        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Order marked as completed.');
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order)
    {
        if ($order->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending orders can be cancelled.');
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Order has been cancelled.');
    }

    /**
     * Export orders to CSV
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'listing.user']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->get('currency'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Order ID',
                'Customer',
                'Listing Title',
                'Vendor',
                'Quantity',
                'USD Price',
                'Crypto Value',
                'Currency',
                'Status',
                'Created At',
                'Completed At',
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->uuid,
                    $order->user->username_pub,
                    $order->listing->title,
                    $order->listing->user->username_pub,
                    $order->quantity,
                    $order->usd_price,
                    $order->crypto_value,
                    strtoupper($order->currency),
                    $order->status,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display reports page
     */
    public function reports()
    {
        // Weekly revenue data for the last 8 weeks
        $weeklyRevenue = Order::where('status', 'completed')
            ->where('updated_at', '>=', now()->subWeeks(8))
            ->selectRaw("TO_CHAR(updated_at, 'IYYY-IW') as week_year, EXTRACT(WEEK FROM updated_at)::int as week, SUM(usd_price) as revenue, COUNT(*) as order_count")
            ->groupBy('week_year', 'week')
            ->orderBy('week_year')
            ->orderBy('week')
            ->get()
            ->map(function($item) {
                return (object) [
                    'week' => $item->week,
                    'revenue' => $item->revenue,
                    'order_count' => $item->order_count
                ];
            });

        // Monthly user growth for the last 12 months
        $monthlyUsers = User::where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('EXTRACT(YEAR FROM created_at)::int as year, EXTRACT(MONTH FROM created_at)::int as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return (object) [
                    'month' => $item->month,
                    'year' => $item->year,
                    'count' => $item->count
                ];
            });

        // Top vendors by revenue (vendors with completed orders)
        $topVendors = User::select('users.*')
            ->selectRaw('SUM(orders.usd_price) as revenue, COUNT(orders.id) as orders_count')
            ->join('listings', 'users.id', '=', 'listings.user_id')
            ->join('orders', 'listings.id', '=', 'orders.listing_id')
            ->where('orders.status', 'completed')
            ->where('users.vendor_level', '>', 0)
            ->groupBy('users.id')
            ->havingRaw('SUM(orders.usd_price) > 0')
            ->orderByRaw('SUM(orders.usd_price) DESC')
            ->limit(10)
            ->get();

        // Category performance - listings and revenue by category
        $categoryPerformance = ProductCategory::select('product_categories.*')
            ->selectRaw('COUNT(DISTINCT listings.id) as listings_count,
                        COUNT(DISTINCT orders.id) as orders_count,
                        COALESCE(SUM(orders.usd_price), 0) as total_revenue')
            ->leftJoin('products', 'product_categories.id', '=', 'products.product_category_id')
            ->leftJoin('listings', 'products.id', '=', 'listings.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('listings.id', '=', 'orders.listing_id')
                    ->where('orders.status', '=', 'completed');
            })
            ->groupBy('product_categories.id')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Daily statistics for the last 30 days
        $dailyStats = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $dailyStats[] = (object) [
                'date' => $date->format('Y-m-d'),
                'new_users' => User::whereDate('created_at', $date)->count(),
                'new_orders' => Order::whereDate('created_at', $date)->count(),
                'completed_orders' => Order::where('status', 'completed')
                    ->whereDate('updated_at', $date)->count(),
                'revenue' => Order::where('status', 'completed')
                    ->whereDate('updated_at', $date)->sum('usd_price'),
                'new_listings' => Listing::whereDate('created_at', $date)->count(),
            ];
        }

        // Payment method statistics
        $paymentMethods = Order::where('status', 'completed')
            ->join('listings', 'orders.listing_id', '=', 'listings.id')
            ->selectRaw('listings.payment_method, COUNT(*) as count, SUM(orders.usd_price) as revenue')
            ->groupBy('listings.payment_method')
            ->get();

        // Currency usage statistics
        $currencyStats = Order::where('status', 'completed')
            ->selectRaw('currency, COUNT(*) as count, SUM(usd_price) as revenue, SUM(crypto_value) as total_crypto')
            ->groupBy('currency')
            ->get();

        // Geographic data - top countries by orders
        $topOriginCountries = Country::select('countries.*')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count, SUM(orders.usd_price) as revenue')
            ->join('listings', 'countries.id', '=', 'listings.origin_country_id')
            ->join('orders', function($join) {
                $join->on('listings.id', '=', 'orders.listing_id')
                    ->where('orders.status', '=', 'completed');
            })
            ->groupBy('countries.id')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        $topDestinationCountries = Country::select('countries.*')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count, SUM(orders.usd_price) as revenue')
            ->join('listings', 'countries.id', '=', 'listings.destination_country_id')
            ->join('orders', function($join) {
                $join->on('listings.id', '=', 'orders.listing_id')
                    ->where('orders.status', '=', 'completed');
            })
            ->groupBy('countries.id')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        // User engagement statistics
        $userEngagement = (object) [
            'total_users' => User::count(),
            'active_last_7_days' => User::where('last_seen', '>=', now()->subDays(7))->count(),
            'active_last_30_days' => User::where('last_seen', '>=', now()->subDays(30))->count(),
            'users_with_orders' => User::whereHas('orders')->count(),
            'repeat_customers' => User::whereRaw('(SELECT COUNT(*) FROM orders WHERE orders.user_id = users.id) > 1')->count(),
        ];

        return view('admin.reports', compact(
            'weeklyRevenue',
            'monthlyUsers',
            'topVendors',
            'categoryPerformance',
            'dailyStats',
            'paymentMethods',
            'currencyStats',
            'topOriginCountries',
            'topDestinationCountries',
            'userEngagement'
        ));
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
        ]);

        // Handle settings updates here
        // This would typically update a settings table or config files

        return redirect()->back()
            ->with('success', 'Settings updated successfully.');
    }
}
