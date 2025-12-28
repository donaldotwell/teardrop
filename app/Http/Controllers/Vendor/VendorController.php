<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Show the vendor dashboard.
     */
    public function dashboard()
    {
        $vendor = auth()->user();

        // Get vendor statistics
        $stats = [
            'total_listings' => Listing::where('user_id', $vendor->id)->count(),
            'active_listings' => Listing::where('user_id', $vendor->id)->where('is_active', true)->count(),
            'total_orders' => Order::whereHas('listing', function ($q) use ($vendor) {
                $q->where('user_id', $vendor->id);
            })->count(),
            'pending_orders' => Order::whereHas('listing', function ($q) use ($vendor) {
                $q->where('user_id', $vendor->id);
            })->where('status', 'pending')->count(),
            'total_sales' => Order::whereHas('listing', function ($q) use ($vendor) {
                $q->where('user_id', $vendor->id);
            })->where('status', 'completed')->sum('usd_price'),
            'avg_rating' => Review::whereHas('listing', function ($q) use ($vendor) {
                $q->where('user_id', $vendor->id);
            })->selectRaw('AVG((rating_stealth + rating_quality + rating_delivery) / 3) as avg_rating')
            ->value('avg_rating') ?? 0,
        ];

        // Recent orders
        $recentOrders = Order::with(['listing', 'user'])
            ->whereHas('listing', function ($q) use ($vendor) {
                $q->where('user_id', $vendor->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top listings by views
        $topListings = Listing::where('user_id', $vendor->id)
            ->where('is_active', true)
            ->orderBy('views', 'desc')
            ->limit(5)
            ->get();

        return view('vendor.dashboard', compact('stats', 'recentOrders', 'topListings'));
    }

    /**
     * Show vendor profile/stats.
     */
    public function profile()
    {
        $vendor = auth()->user();

        return view('vendor.profile', compact('vendor'));
    }

    /**
     * Show vendor sales history.
     */
    public function sales()
    {
        $vendor = auth()->user();

        $sales = Order::with(['listing', 'user'])
            ->whereHas('listing', function ($q) use ($vendor) {
                $q->where('user_id', $vendor->id);
            })
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(20);

        $totalRevenue = $sales->sum('usd_price');

        return view('vendor.sales', compact('sales', 'totalRevenue'));
    }

    /**
     * Show vendor analytics.
     */
    public function analytics()
    {
        $vendor = auth()->user();

        // Monthly sales data
        $monthlySales = Order::whereHas('listing', function ($q) use ($vendor) {
            $q->where('user_id', $vendor->id);
        })
        ->where('status', 'completed')
        ->select(DB::raw('DATE_FORMAT(completed_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'), DB::raw('SUM(usd_price) as revenue'))
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(12)
        ->get();

        return view('vendor.analytics', compact('monthlySales'));
    }

    /**
     * Show vendor orders.
     */
    public function orders()
    {
        $vendor = auth()->user();

        $orders = Order::with(['listing', 'user'])
            ->whereHas('listing', function ($q) use ($vendor) {
                $q->where('user_id', $vendor->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('vendor.orders.index', compact('orders'));
    }

    /**
     * Show single order details.
     */
    public function showOrder(Order $order)
    {
        $vendor = auth()->user();

        // Verify vendor owns this order's listing
        if ($order->listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['listing', 'user', 'review']);

        return view('vendor.orders.show', compact('order'));
    }

    /**
     * Mark order as shipped.
     */
    public function shipOrder(Request $request, Order $order)
    {
        $vendor = auth()->user();

        // Verify vendor owns this order's listing
        if ($order->listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->update([
            'status' => 'shipped',
        ]);

        return redirect()->route('vendor.orders.show', $order)
            ->with('success', 'Order marked as shipped.');
    }

    /**
     * Send message to buyer about order.
     */
    public function sendOrderMessage(Request $request, Order $order)
    {
        $vendor = auth()->user();

        // Verify vendor owns this order's listing
        if ($order->listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this order.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        // Implementation depends on your messaging system
        // This is a placeholder

        return redirect()->route('vendor.orders.show', $order)
            ->with('success', 'Message sent to buyer.');
    }

    /**
     * Show vendor reviews.
     */
    public function reviews()
    {
        $vendor = auth()->user();

        $reviews = Review::with(['user', 'listing'])
            ->whereHas('listing', function ($q) use ($vendor) {
                $q->where('user_id', $vendor->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('vendor.reviews', compact('reviews'));
    }
}
