<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Review;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Vendor is always viewing from vendor perspective
        $isVendor = true;
        $otherParty = $order->user; // The buyer

        $order->load([
            'listing.user',
            'listing.media',
            'listing.originCountry',
            'listing.destinationCountry',
            'user',
            'review',
            'dispute',
            'messages' => function($query) {
                $query->with(['sender', 'receiver'])
                      ->orderBy('created_at', 'desc')
                      ->limit(10);
            }
        ]);

        return view('vendor.orders.show', compact('order', 'otherParty', 'isVendor'));
    }

    /**
     * Mark order as shipped.
     */
    public function shipOrder(Request $request, Order $order)
    {
        $vendor = auth()->user();

        // Verify vendor owns this order's listing
        if ($order->listing->user_id !== $vendor->id) {
            abort(403, 'Only the vendor can mark order as shipped');
        }

        // Check if order is in valid state
        if ($order->status !== 'pending') {
            return redirect()->back()->withErrors([
                'error' => 'This order cannot be marked as shipped. Current status: ' . $order->status
            ]);
        }

        try {
            DB::transaction(function () use ($order) {
                // Update order status to shipped
                $order->update([
                    'status' => 'shipped',
                    'shipped_at' => now(),
                ]);

                // Create notification message to buyer
                \App\Models\UserMessage::create([
                    'sender_id' => $order->listing->user_id,
                    'receiver_id' => $order->user_id,
                    'message' => "Your order #{$order->id} has been marked as shipped by the vendor.",
                    'order_id' => $order->id,
                ]);

                \Log::info("Order #{$order->id} marked as shipped");
            });

            return redirect()->route('vendor.orders.show', $order)->with('success', 'Order marked as shipped successfully!');

        } catch (\Exception $e) {
            \Log::error("Failed to mark order #{$order->id} as shipped", [
                'exception' => $e,
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
            ]);
            return redirect()->back()->withErrors([
                'error' => 'Failed to mark order as shipped. Please try again later.'
            ]);
        }
    }

    /**
     * Cancel an order and refund buyer (minus transaction fees).
     */
    public function cancelOrder(Request $request, Order $order)
    {
        $vendor = auth()->user();

        // Verify vendor owns this order's listing
        if ($order->listing->user_id !== $vendor->id) {
            abort(403, 'Only the vendor can cancel this order');
        }

        // Check if order can be cancelled
        if (!in_array($order->status, ['pending', 'shipped'])) {
            return redirect()->back()->withErrors([
                'error' => 'This order cannot be cancelled. Current status: ' . $order->status
            ]);
        }

        // Validate cancellation reason
        $data = $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($order, $data, $vendor) {
                // Check if order has escrow wallet
                if ($order->escrow_wallet_id) {
                    $escrowWallet = $order->escrowWallet;

                    if (!$escrowWallet) {
                        throw new \Exception("Escrow wallet not found for order #{$order->id}");
                    }

                    // Refund to buyer (EscrowService handles transaction fees automatically)
                    $escrowService = new EscrowService();
                    $refundTxid = $escrowService->refundEscrow($escrowWallet, $order);

                    // Record the refund transaction for the buyer
                    $buyer = $order->user;
                    $wallet = $buyer->wallets()->where('currency', $order->currency)->first();

                    if ($wallet) {
                        // The actual refunded amount (escrow balance minus network fees)
                        $refundedAmount = $escrowWallet->balance;

                        $wallet->transactions()->create([
                            'amount' => $refundedAmount,
                            'type' => 'order_refund',
                            'comment' => "Refund for cancelled order #{$order->id}. Vendor reason: " . substr($data['cancellation_reason'], 0, 100),
                        ]);

                        // Update buyer's wallet balance
                        $wallet->increment('balance', $refundedAmount);
                    }

                    Log::info("Order #{$order->id} refunded to buyer", [
                        'refund_txid' => $refundTxid,
                        'amount' => $refundedAmount ?? 'N/A',
                        'currency' => $order->currency,
                    ]);
                }

                // Update order status
                $order->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => $data['cancellation_reason'],
                ]);

                // Notify buyer
                \App\Models\UserMessage::create([
                    'sender_id' => $vendor->id,
                    'receiver_id' => $order->user_id,
                    'message' => "Your order #{$order->id} has been cancelled by the vendor.\n\nReason: {$data['cancellation_reason']}\n\n" .
                                ($order->escrow_wallet_id ? "Funds have been refunded to your wallet (minus network transaction fees)." : ""),
                    'order_id' => $order->id,
                ]);

                Log::info("Order #{$order->id} cancelled by vendor", [
                    'vendor_id' => $vendor->id,
                    'buyer_id' => $order->user_id,
                    'reason' => $data['cancellation_reason'],
                ]);
            });

            return redirect()->route('vendor.orders.show', $order)
                ->with('success', 'Order cancelled successfully. Buyer has been refunded.');

        } catch (\Exception $e) {
            Log::error("Failed to cancel order #{$order->id}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id,
                'vendor_id' => $vendor->id,
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Failed to cancel order: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send message to buyer about order.
     */
    public function sendOrderMessage(Request $request, Order $order)
    {
        $vendor = auth()->user();

        // Verify vendor owns this order's listing
        if ($order->listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized to send message for this order');
        }

        // Validate message
        $data = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Determine receiver (the buyer)
        $receiverId = $order->user_id;

        try {
            \App\Models\UserMessage::create([
                'sender_id' => $vendor->id,
                'receiver_id' => $receiverId,
                'message' => $data['message'],
                'order_id' => $order->id,
            ]);

            return redirect()->route('vendor.orders.show', $order)->with('success', 'Message sent successfully!');

        } catch (\Exception $e) {
            \Log::error("Failed to send message for order #{$order->id}", [
                'exception' => $e,
                'order_id' => $order->id,
                'user_id' => $vendor->id,
            ]);
            return redirect()->back()->withErrors([
                'error' => 'Failed to send message. Please try again.'
            ]);
        }
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
