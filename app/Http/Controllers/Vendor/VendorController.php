<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Review;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            'total_disputes' => $vendor->allDisputes()->count(),
            'open_disputes' => $vendor->allDisputes()->open()->count(),
            'resolved_disputes' => $vendor->allDisputes()->where('status', 'resolved')->count(),
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
                    'message' => "Your order #{$order->uuid} has been marked as shipped by the vendor.",
                    'order_id' => $order->id,
                ]);

                \Log::info("Order #{$order->uuid} marked as shipped");
            });

            return redirect()->route('vendor.orders.show', $order)->with('success', 'Order marked as shipped successfully!');

        } catch (\Exception $e) {
            \Log::error("Failed to mark order #{$order->uuid} as shipped", [
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
                $buyer = $order->user;
                $refundMessage = '';

                // Handle early finalized orders (funds already sent to vendor)
                if ($order->is_early_finalized) {
                    // For early finalized orders, vendor must manually refund from their own wallet
                    // because funds were already released to vendor at purchase time
                    $vendorWallet = $vendor->wallets()->where('currency', $order->currency)->first();
                    $buyerWallet = $buyer->wallets()->where('currency', $order->currency)->first();

                    if (!$vendorWallet || !$buyerWallet) {
                        throw new \Exception("Vendor or buyer wallet not found for currency: {$order->currency}");
                    }

                    // Check if vendor has sufficient balance to refund
                    if ($vendorWallet->balance < $order->crypto_value) {
                        throw new \Exception("Insufficient vendor balance to refund order. Vendor balance: {$vendorWallet->balance} {$order->currency}, Order amount: {$order->crypto_value} {$order->currency}");
                    }

                    // Deduct from vendor
                    $vendorWallet->decrement('balance', $order->crypto_value);
                    $vendorWallet->transactions()->create([
                        'amount' => -$order->crypto_value,
                        'type' => 'order_refund',
                        'comment' => "Refund to buyer for cancelled order #{$order->uuid}",
                    ]);

                    // Add to buyer
                    $buyerWallet->increment('balance', $order->crypto_value);
                    $buyerWallet->transactions()->create([
                        'amount' => $order->crypto_value,
                        'type' => 'order_refund',
                        'comment' => "Refund from vendor for cancelled order #{$order->uuid}",
                    ]);

                    $refundMessage = "Full refund of {$order->crypto_value} {$order->currency} transferred from vendor to buyer wallet.";

                    Log::info("Early finalized order #{$order->uuid} refunded from vendor to buyer", [
                        'amount' => $order->crypto_value,
                        'currency' => $order->currency,
                        'vendor_id' => $vendor->id,
                        'buyer_id' => $buyer->id,
                    ]);

                } elseif ($order->escrow_wallet_id) {
                    // Handle standard escrow orders
                    $escrowWallet = $order->escrowWallet;

                    if (!$escrowWallet) {
                        throw new \Exception("Escrow wallet not found for order #{$order->uuid}");
                    }

                    // Check if escrow has been funded
                    if (!$order->escrow_funded_at || $escrowWallet->balance <= 0) {
                        throw new \Exception("Escrow has not been funded yet or balance is zero. Cannot process refund.");
                    }

                    // Store balance before refund (this is the amount that will be refunded minus network fees)
                    $escrowBalance = $escrowWallet->balance;

                    // Refund to buyer (EscrowService handles network transaction fees automatically)
                    $escrowService = new EscrowService();
                    $refundTxid = $escrowService->refundEscrow($escrowWallet, $order);

                    // Note: The sync jobs (SyncBitcoinWallets/SyncMoneroWallets) will automatically
                    // update the buyer's wallet balance when the refund transaction confirms.
                    // We don't manually increment the balance here to avoid double-counting.

                    $refundMessage = "Refund of approximately {$escrowBalance} {$order->currency} sent from escrow (minus network fees). Payment confirmed.";

                    Log::info("Order #{$order->uuid} refunded from escrow to buyer", [
                        'refund_txid' => $refundTxid,
                        'escrow_balance' => $escrowBalance,
                        'currency' => $order->currency,
                    ]);

                } else {
                    // Order has no escrow and wasn't early finalized - shouldn't happen
                    throw new \Exception("Order has no escrow wallet and was not early finalized. Cannot process refund.");
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
                    'message' => "Your order #{$order->uuid} has been cancelled by the vendor.\n\nReason: {$data['cancellation_reason']}\n\n{$refundMessage}",
                    'order_id' => $order->id,
                ]);

                Log::info("Order #{$order->uuid} cancelled by vendor", [
                    'vendor_id' => $vendor->id,
                    'buyer_id' => $order->user_id,
                    'reason' => $data['cancellation_reason'],
                    'was_early_finalized' => $order->is_early_finalized,
                ]);
            });

            return redirect()->route('vendor.orders.show', $order)
                ->with('success', 'Order cancelled successfully. Buyer has been refunded.');

        } catch (\Exception $e) {
            Log::error("Failed to cancel order #{$order->uuid}", [
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
            \Log::error("Failed to send message for order #{$order->uuid}", [
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

    /**
     * Display vendor's disputes (disputes against vendor)
     */
    public function disputes(Request $request)
    {
        $vendor = auth()->user();

        // Get disputes where vendor is the disputed party
        $query = $vendor->allDisputes()
            ->with(['order.listing', 'initiatedBy', 'disputedAgainst', 'assignedAdmin', 'assignedModerator']);

        // Filter by status if requested
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by type if requested
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get counts for filter buttons
        $statusCounts = [
            'all' => $vendor->allDisputes()->count(),
            'open' => $vendor->allDisputes()->open()->count(),
            'resolved' => $vendor->allDisputes()->where('status', 'resolved')->count(),
            'closed' => $vendor->allDisputes()->where('status', 'closed')->count(),
        ];

        return view('disputes.index', compact('disputes', 'statusCounts'));
    }

    /**
     * Show specific dispute details
     */
    public function showDispute(Dispute $dispute)
    {
        $vendor = auth()->user();

        // Verify vendor can access this dispute
        if (!$dispute->canUserParticipate($vendor)) {
            abort(403, 'You do not have access to this dispute.');
        }

        $dispute->load([
            'order.listing.media',
            'initiatedBy',
            'disputedAgainst',
            'assignedAdmin',
            'assignedModerator',
            'evidence.uploadedBy'
        ]);

        // Get messages (filter internal if user is not admin)
        $messages = $dispute->messages()
            ->where('is_internal', false)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read for this user
        $dispute->messages()
            ->where('user_id', '!=', $vendor->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('disputes.show', compact('dispute', 'messages'));
    }

    /**
     * Add message to dispute
     */
    public function addDisputeMessage(Request $request, Dispute $dispute)
    {
        $vendor = auth()->user();

        // Verify vendor can access this dispute
        if (!$dispute->canUserParticipate($vendor)) {
            abort(403, 'You do not have access to this dispute.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $dispute->messages()->create([
            'user_id' => $vendor->id,
            'message' => $validated['message'],
            'message_type' => 'user_message',
            'is_internal' => false,
        ]);

        return redirect()->back()
            ->with('success', 'Message added successfully.');
    }

    /**
     * Upload evidence for dispute
     */
    public function uploadDisputeEvidence(Request $request, Dispute $dispute)
    {
        $vendor = auth()->user();

        // Verify vendor can access this dispute
        if (!$dispute->canUserParticipate($vendor)) {
            abort(403, 'You do not have access to this dispute.');
        }

        $validated = $request->validate([
            'evidence.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'description' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('dispute-evidence', 'private');

                DisputeEvidence::create([
                    'dispute_id' => $dispute->id,
                    'uploaded_by' => $vendor->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'description' => $validated['description'] ?? null,
                ]);

                // Add system message about evidence upload
                $dispute->messages()->create([
                    'user_id' => $vendor->id,
                    'message' => "Evidence uploaded: {$file->getClientOriginalName()}",
                    'message_type' => 'evidence_upload',
                    'is_internal' => false,
                ]);
            }
        }

        return redirect()->back()
            ->with('success', 'Evidence uploaded successfully.');
    }

    /**
     * Download dispute evidence
     */
    public function downloadDisputeEvidence(Dispute $dispute, DisputeEvidence $evidence)
    {
        $vendor = auth()->user();

        // Verify vendor can access this dispute
        if (!$dispute->canUserParticipate($vendor)) {
            abort(403, 'You do not have access to this dispute.');
        }

        // Verify evidence belongs to this dispute
        if ($evidence->dispute_id !== $dispute->id) {
            abort(404, 'Evidence not found.');
        }

        // Check if file exists
        if (!Storage::disk('private')->exists($evidence->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('private')->download(
            $evidence->file_path,
            $evidence->file_name
        );
    }
}
