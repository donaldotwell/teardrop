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
     * Mark order as complete and release escrow to vendor.
     */
    public function completeOrder(Request $request, Order $order)
    {
        $vendor = auth()->user();

        // Verify vendor owns this order's listing
        if ($order->listing->user_id !== $vendor->id) {
            abort(403, 'Only the vendor can complete this order.');
        }

        // Check if order is in valid state
        if ($order->status !== 'pending' && $order->status !== 'shipped') {
            return redirect()->back()->withErrors([
                'error' => 'This order cannot be completed. Current status: ' . $order->status
            ]);
        }

        // Only handle BTC and XMR orders
        if (!in_array($order->currency, ['btc', 'xmr'])) {
            return redirect()->back()->withErrors([
                'error' => 'Only Bitcoin and Monero orders can be completed at this time.'
            ]);
        }

        try {
            DB::transaction(function () use ($order) {
                $buyer = $order->user;
                $seller = $order->listing->user;

                // Calculate service charge (from config)
                $serviceFeePercent = config('fees.order_completion_percentage', 3);

                // Determine decimal precision based on currency
                $decimals = $order->currency === 'btc' ? 8 : 12;

                $serviceFeeAmount = round(($order->crypto_value * $serviceFeePercent) / 100, $decimals);
                $sellerAmount = round($order->crypto_value - $serviceFeeAmount, $decimals);

                // Handle based on currency
                if ($order->currency === 'btc') {
                    $this->completeBitcoinOrder($order, $buyer, $seller, $sellerAmount, $serviceFeeAmount, $serviceFeePercent);
                } elseif ($order->currency === 'xmr') {
                    $this->completeMoneroOrder($order, $buyer, $seller, $sellerAmount, $serviceFeeAmount, $serviceFeePercent);
                }
            });

            $currencyName = $order->currency === 'btc' ? 'Bitcoin' : 'Monero';
            return redirect()->route('vendor.orders.show', $order)->with('success', "Order completed! {$currencyName} sent to your wallet. Transaction will be confirmed by the network.");

        } catch (\Exception $e) {
            \Log::error("Failed to complete order #{$order->id}", [
                'exception' => $e,
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
            ]);
            return redirect()->back()->withErrors([
                'error' => 'Failed to complete order. Please contact support if the issue persists.'
            ]);
        }
    }

    /**
     * Complete a Bitcoin order (vendor side).
     */
    private function completeBitcoinOrder($order, $buyer, $seller, $sellerAmount, $serviceFeeAmount, $serviceFeePercent): void
    {
        // 1. Get seller's current Bitcoin address
        $sellerBtcWallet = $seller->btcWallet;
        if (!$sellerBtcWallet) {
            throw new \Exception("Seller does not have a Bitcoin wallet configured");
        }

        $sellerAddress = $sellerBtcWallet->getCurrentAddress();
        if (!$sellerAddress) {
            // Generate new address for seller if none exists
            $sellerAddress = $sellerBtcWallet->generateNewAddress();
        }

        // 2. Get admin wallet for service charge
        $adminWalletName = config('fees.admin_btc_wallet_name', 'admin');
        $adminBtcWallet = \App\Models\BtcWallet::where('name', $adminWalletName)->first();

        if (!$adminBtcWallet) {
            throw new \Exception("Admin wallet not found: {$adminWalletName}");
        }

        $adminAddress = $adminBtcWallet->getCurrentAddress();
        if (!$adminAddress) {
            $adminAddress = $adminBtcWallet->generateNewAddress();
        }

        // 3. Get buyer's wallet
        $buyerBtcWallet = $buyer->btcWallet;
        if (!$buyerBtcWallet) {
            throw new \Exception("Buyer does not have a Bitcoin wallet configured");
        }

        \Log::info("Sending Bitcoin for order #{$order->id}", [
            'buyer_wallet' => $buyerBtcWallet->name,
            'seller_address' => $sellerAddress->address,
            'seller_amount' => $sellerAmount,
            'service_fee' => $serviceFeeAmount,
            'admin_address' => $adminAddress->address,
            'total_amount' => $order->crypto_value,
        ]);

        // 4. Send Bitcoin to seller (after service fee deduction)
        $sellerTxid = \App\Repositories\BitcoinRepository::sendBitcoin(
            $buyerBtcWallet->name,
            $sellerAddress->address,
            $sellerAmount
        );

        if (!$sellerTxid) {
            throw new \Exception("Failed to send Bitcoin transaction to seller");
        }

        \Log::info("Bitcoin sent to seller for order #{$order->id}", ['txid' => $sellerTxid]);

        // 5. Send service fee to admin
        $adminTxid = \App\Repositories\BitcoinRepository::sendBitcoin(
            $buyerBtcWallet->name,
            $adminAddress->address,
            $serviceFeeAmount
        );

        if (!$adminTxid) {
            throw new \Exception("Failed to send service fee to admin");
        }

        \Log::info("Service fee sent to admin for order #{$order->id}", ['txid' => $adminTxid]);

        // 6. Create withdrawal transaction for seller payment
        \App\Models\BtcTransaction::create([
            'btc_wallet_id' => $buyerBtcWallet->id,
            'btc_address_id' => null,
            'txid' => $sellerTxid,
            'type' => 'withdrawal',
            'amount' => $sellerAmount,
            'fee' => 0,
            'confirmations' => 0,
            'status' => 'pending',
            'raw_transaction' => [
                'order_id' => $order->id,
                'to_address' => $sellerAddress->address,
                'purpose' => 'order_payment',
            ],
        ]);

        // 7. Create withdrawal transaction for service fee
        \App\Models\BtcTransaction::create([
            'btc_wallet_id' => $buyerBtcWallet->id,
            'btc_address_id' => null,
            'txid' => $adminTxid,
            'type' => 'withdrawal',
            'amount' => $serviceFeeAmount,
            'fee' => 0,
            'confirmations' => 0,
            'status' => 'pending',
            'raw_transaction' => [
                'order_id' => $order->id,
                'to_address' => $adminAddress->address,
                'purpose' => 'service_fee',
                'fee_percent' => $serviceFeePercent,
            ],
        ]);

        // 8. Update buyer's balance to reflect both withdrawals
        $buyerBtcWallet->updateBalance();

        // 9. Mark order as completed
        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
            'txid' => $sellerTxid,
        ]);

        // 10. Create notification message
        \App\Models\UserMessage::create([
            'sender_id' => $order->listing->user_id,
            'receiver_id' => $order->user_id,
            'message' => "Order #{$order->id} has been completed.\nPayment: " . number_format($sellerAmount, 8) . " BTC\nService fee ({$serviceFeePercent}%): " . number_format($serviceFeeAmount, 8) . " BTC\nTransaction ID: {$sellerTxid}\n\nThe bitcoin:sync command will detect and credit your wallet once confirmed.",
            'order_id' => $order->id,
        ]);

        \Log::info("Order #{$order->id} completed with blockchain transactions", [
            'seller_txid' => $sellerTxid,
            'admin_txid' => $adminTxid,
            'seller_amount' => $sellerAmount,
            'service_fee' => $serviceFeeAmount,
            'seller_address' => $sellerAddress->address,
            'admin_address' => $adminAddress->address,
        ]);
    }

    /**
     * Complete a Monero order (vendor side).
     */
    private function completeMoneroOrder($order, $buyer, $seller, $sellerAmount, $serviceFeeAmount, $serviceFeePercent): void
    {
        // 1. Get seller's current Monero address
        $sellerXmrWallet = $seller->xmrWallet;
        if (!$sellerXmrWallet) {
            throw new \Exception("Seller does not have a Monero wallet configured");
        }

        $sellerAddress = $sellerXmrWallet->getCurrentAddress();
        if (!$sellerAddress) {
            // Generate new address for seller if none exists
            $sellerAddress = $sellerXmrWallet->generateNewAddress();
        }

        // 2. Get admin wallet for service charge
        $adminWalletName = config('fees.admin_xmr_wallet_name', 'admin');
        $adminXmrWallet = \App\Models\XmrWallet::where('name', $adminWalletName)->first();

        if (!$adminXmrWallet) {
            throw new \Exception("Admin wallet not found: {$adminWalletName}");
        }

        $adminAddress = $adminXmrWallet->getCurrentAddress();
        if (!$adminAddress) {
            $adminAddress = $adminXmrWallet->generateNewAddress();
        }

        // 3. Get buyer's wallet
        $buyerXmrWallet = $buyer->xmrWallet;
        if (!$buyerXmrWallet) {
            throw new \Exception("Buyer does not have a Monero wallet configured");
        }

        \Log::info("Sending Monero for order #{$order->id}", [
            'buyer_wallet' => $buyerXmrWallet->name,
            'seller_address' => $sellerAddress->address,
            'seller_amount' => $sellerAmount,
            'service_fee' => $serviceFeeAmount,
            'admin_address' => $adminAddress->address,
            'total_amount' => $order->crypto_value,
        ]);

        // 4. Send Monero to seller (after service fee deduction)
        $sellerTxid = \App\Repositories\MoneroRepository::transfer(
            $buyerXmrWallet->name,
            $sellerAddress->address,
            $sellerAmount
        );

        if (!$sellerTxid) {
            throw new \Exception("Failed to send Monero transaction to seller");
        }

        \Log::info("Monero sent to seller for order #{$order->id}", ['txid' => $sellerTxid]);

        // 5. Send service fee to admin
        $adminTxid = \App\Repositories\MoneroRepository::transfer(
            $buyerXmrWallet->name,
            $adminAddress->address,
            $serviceFeeAmount
        );

        if (!$adminTxid) {
            throw new \Exception("Failed to send service fee to admin");
        }

        \Log::info("Service fee sent to admin for order #{$order->id}", ['txid' => $adminTxid]);

        // 6. Create withdrawal transaction for seller payment
        \App\Models\XmrTransaction::create([
            'xmr_wallet_id' => $buyerXmrWallet->id,
            'xmr_address_id' => null,
            'txid' => $sellerTxid,
            'type' => 'withdrawal',
            'amount' => $sellerAmount,
            'fee' => 0,
            'confirmations' => 0,
            'unlock_time' => 0,
            'status' => 'pending',
            'raw_transaction' => [
                'order_id' => $order->id,
                'to_address' => $sellerAddress->address,
                'purpose' => 'order_payment',
            ],
        ]);

        // 7. Create withdrawal transaction for service fee
        \App\Models\XmrTransaction::create([
            'xmr_wallet_id' => $buyerXmrWallet->id,
            'xmr_address_id' => null,
            'txid' => $adminTxid,
            'type' => 'withdrawal',
            'amount' => $serviceFeeAmount,
            'fee' => 0,
            'confirmations' => 0,
            'unlock_time' => 0,
            'status' => 'pending',
            'raw_transaction' => [
                'order_id' => $order->id,
                'to_address' => $adminAddress->address,
                'purpose' => 'service_fee',
                'fee_percent' => $serviceFeePercent,
            ],
        ]);

        // 8. Update buyer's balance to reflect both withdrawals
        $buyerXmrWallet->updateBalance();

        // 9. Mark order as completed
        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
            'txid' => $sellerTxid,
        ]);

        // 10. Create notification message
        \App\Models\UserMessage::create([
            'sender_id' => $order->listing->user_id,
            'receiver_id' => $order->user_id,
            'message' => "Order #{$order->id} has been completed.\nPayment: " . number_format($sellerAmount, 12) . " XMR\nService fee ({$serviceFeePercent}%): " . number_format($serviceFeeAmount, 12) . " XMR\nTransaction ID: {$sellerTxid}\n\nThe monero:sync command will detect and credit your wallet once confirmed (10 confirmations required).",
            'order_id' => $order->id,
        ]);

        \Log::info("Order #{$order->id} completed with blockchain transactions", [
            'seller_txid' => $sellerTxid,
            'admin_txid' => $adminTxid,
            'seller_amount' => $sellerAmount,
            'service_fee' => $serviceFeeAmount,
            'seller_address' => $sellerAddress->address,
            'admin_address' => $adminAddress->address,
        ]);
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
