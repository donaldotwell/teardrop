<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Order;
use App\Models\UserMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Build base query for user's orders (as buyer OR vendor)
        $query = Order::query()
            ->with(['listing.user', 'user', 'dispute'])
            ->where(function($q) use ($user) {
                // Orders where user is the buyer
                $q->where('user_id', $user->id)
                    // OR orders where user is the vendor (owns the listing)
                    ->orWhereHas('listing', function($listingQuery) use ($user) {
                        $listingQuery->where('user_id', $user->id);
                    });
            });

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $orders = $query->latest()->paginate(10);

        // Calculate status counts for filter tabs
        $statusCounts = [
            'all' => Order::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('listing', function($listingQuery) use ($user) {
                        $listingQuery->where('user_id', $user->id);
                    });
            })->count(),
            'pending' => Order::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('listing', function($listingQuery) use ($user) {
                        $listingQuery->where('user_id', $user->id);
                    });
            })->where('status', 'pending')->count(),
            'shipped' => Order::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('listing', function($listingQuery) use ($user) {
                        $listingQuery->where('user_id', $user->id);
                    });
            })->where('status', 'shipped')->count(),
            'completed' => Order::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('listing', function($listingQuery) use ($user) {
                        $listingQuery->where('user_id', $user->id);
                    });
            })->where('status', 'completed')->count(),
            'cancelled' => Order::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('listing', function($listingQuery) use ($user) {
                        $listingQuery->where('user_id', $user->id);
                    });
            })->where('status', 'cancelled')->count(),
        ];

        return view('orders.index', compact('orders', 'statusCounts'));
    }

    public function create(Request $request, Listing $listing)
    {
        // Check if vendor has PGP public key configured
        if (empty($listing->user->pgp_pub_key)) {
            return redirect()->back()->withErrors([
                'error' => 'This vendor has not configured a PGP public key. Orders cannot be placed until the vendor adds their PGP key for secure address encryption.',
            ]);
        }

        // Validate the currency parameter (must be either 'btc' or 'xmr')
        $data = $request->validate([
            'currency' => 'required|in:btc,xmr',
            'quantity' => 'required|numeric|min:1',
        ]);

        $user = $request->user();

        $user_balance = $user->getBalance();

        $usd_price = $listing->price * $data['quantity'];

        $crypto_value = convert_usd_to_crypto($usd_price, $data['currency']);

        if ($crypto_value > $user_balance[$data['currency']]['balance']) {
            return redirect()->back()->withErrors([
                'error' => "Insufficient balance for this transaction. Your current balance is {$user_balance[$data['currency']]['balance']} {$data['currency']} which is not enough to cover the transaction of {$crypto_value} {$data['currency']}.",
            ]);
        }

        // Everything checks out—show the confirmation view.
        return view('orders.create', [
            'listing'       => $listing,
            'usd_price'     => $usd_price,
            'crypto_value' => $crypto_value,
            'currency'      => $data['currency'],
            'quantity'      => $data['quantity'],
        ]);
    }

    /**
     * View a single order.
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(Request $request, Order $order)
    {
        // Authorization check
        if ($order->user_id !== $request->user()->id && $order->listing->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to order');
        }

        // Determine who is viewing and who is the other party
        $isVendor = $order->listing->user_id === $request->user()->id;
        $otherParty = $isVendor ? $order->user : $order->listing->user;

        // Load order relationships including recent messages
        $order = $order->load([
            'listing.user',
            'listing.media',
            'user',
            'review',
            'messages' => function($query) {
                $query->with(['sender', 'receiver'])
                      ->orderBy('created_at', 'desc')
                      ->limit(10);
            }
        ]);

        return view('orders.show', compact('order', 'otherParty', 'isVendor'));
    }

    /**
     * Mark order as complete and release escrow to seller.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, Order $order) : \Illuminate\Http\RedirectResponse
    {
        // Authorization: Only buyer or vendor can mark as complete
        $user = $request->user();
        if ($order->user_id !== $user->id && $order->listing->user_id !== $user->id) {
            abort(403, 'Only the buyer or vendor can complete the order');
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
            return redirect()->route('orders.show', $order)->with('success', "Order completed! {$currencyName} sent to seller. Transaction will be confirmed by the network.");

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
     * Complete a Bitcoin order.
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
        UserMessage::create([
            'sender_id' => $order->user_id,
            'receiver_id' => $order->listing->user_id,
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
     * Complete a Monero order.
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
        UserMessage::create([
            'sender_id' => $order->user_id,
            'receiver_id' => $order->listing->user_id,
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
     * Mark order as shipped (vendor only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ship(Request $request, Order $order) : \Illuminate\Http\RedirectResponse
    {
        // Authorization: Only vendor can mark as shipped
        if ($order->listing->user_id !== $request->user()->id) {
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
                UserMessage::create([
                    'sender_id' => $order->listing->user_id,
                    'receiver_id' => $order->user_id,
                    'message' => "Your order #{$order->id} has been marked as shipped by the vendor.",
                    'order_id' => $order->id,
                ]);

                \Log::info("Order #{$order->id} marked as shipped");
            });

            return redirect()->route('orders.show', $order)->with('success', 'Order marked as shipped successfully!');

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
     * Persist the order to the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Listing  $listing
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Listing $listing) : \Illuminate\Http\RedirectResponse
    {
        // Validate request
        $data = $request->validate([
            'currency' => 'required|in:btc,xmr',
            'quantity' => 'required|numeric|min:1',
            'delivery_address' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:1000',
        ]);

        // Verify vendor has PGP public key
        if (empty($listing->user->pgp_pub_key)) {
            return redirect()->back()->withErrors([
                'error' => 'Vendor does not have a PGP public key configured. Cannot place order.',
            ]);
        }

        $user = $request->user();

        // Recheck balance
        $user_balance = $user->getBalance();
        $usd_price = $listing->price * $data['quantity'];
        $crypto_value = convert_usd_to_crypto($usd_price, $data['currency']);

        if ($crypto_value > $user_balance[$data['currency']]['balance']) {
            return redirect()->back()->withErrors([
                'error' => "Insufficient balance. Needed: {$crypto_value} {$data['currency']}, Available: {$user_balance[$data['currency']]['balance']} {$data['currency']}",
            ]);
        }

        // Encrypt delivery address with vendor's PGP public key (if provided)
        $encryptedAddress = null;
        if (!empty($data['delivery_address'])) {
            try {
                $encryptedAddress = $this->encryptWithPGP($data['delivery_address'], $listing->user->pgp_pub_key);
            } catch (\Exception $e) {
                \Log::error('PGP encryption failed for order', [
                    'listing_id' => $listing->id,
                    'vendor_id' => $listing->user_id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->back()->withErrors([
                    'error' => 'Failed to encrypt delivery address. The vendor\'s PGP key may be invalid.',
                ]);
            }
        }

        // Create order and deduct balance in transaction
        DB::transaction(function () use ($user, $listing, $data, $crypto_value, $usd_price, $encryptedAddress) {
            // Deduct balance from main wallet (escrow for order)
            $wallet = $user->wallets()->where('currency', $data['currency'])->firstOrFail();

            // Create wallet transaction for order escrow
            $wallet->transactions()->create([
                'amount' => -$crypto_value,
                'type' => 'order_escrow',
                'comment' => "Escrowed for order #{$listing->id}",
            ]);

            // Update wallet balance
            $wallet->decrement('balance', $crypto_value);

            // Create order with encrypted delivery address
            $order = $user->orders()->create([
                'listing_id' => $listing->id,
                'quantity' => $data['quantity'],
                'currency' => $data['currency'],
                'crypto_value' => $crypto_value,
                'usd_price' => $usd_price,
                'status' => 'pending',
                'encrypted_delivery_address' => $encryptedAddress,
            ]);

            // Create message to vendor
            $messageContent = "New order #{$order->id}:\n";
            $messageContent .= "Quantity: {$data['quantity']}\n";
            $messageContent .= "Amount: {$crypto_value} ".strtoupper($data['currency'])."\n";
            $messageContent .= "Note: ".(($data['note'] ?? 'No message provided'));

            UserMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $listing->user_id,
                'message' => $messageContent,
                'order_id' => $order->id,
            ]);
        });

        return redirect()->route('orders.index')->with('success', 'Order placed successfully!');
    }

    /**
     * Encrypt data with PGP public key.
     *
     * @param string $data
     * @param string $publicKey
     * @return string
     * @throws \Exception
     */
    private function encryptWithPGP(string $data, string $publicKey): string
    {
        // Check if gnupg extension is loaded
        if (!extension_loaded('gnupg')) {
            throw new \Exception('GnuPG extension is not loaded');
        }

        // Initialize gnupg
        $gpg = gnupg_init();

        if (!$gpg) {
            throw new \Exception('Failed to initialize GnuPG');
        }

        // Import the public key and get the fingerprint
        $importResult = gnupg_import($gpg, $publicKey);

        if (!$importResult || !isset($importResult['fingerprint'])) {
            throw new \Exception('Failed to import PGP public key');
        }

        // Add the key for encryption using the fingerprint
        $addKeyResult = gnupg_addencryptkey($gpg, $importResult['fingerprint']);

        if (!$addKeyResult) {
            throw new \Exception('Failed to add encryption key');
        }

        // Encrypt the data
        $encrypted = gnupg_encrypt($gpg, $data);

        if ($encrypted === false) {
            throw new \Exception('Failed to encrypt data with PGP key');
        }

        return $encrypted;
    }

    /**
     * Send a message related to an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendMessage(Request $request, Order $order) : \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Authorization check - only buyer or seller can send messages
        if ($order->user_id !== $user->id && $order->listing->user_id !== $user->id) {
            abort(403, 'Unauthorized to send message for this order');
        }

        // Validate message
        $data = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Determine receiver (the other party)
        $receiverId = ($order->user_id === $user->id)
            ? $order->listing->user_id  // Buyer sending to vendor
            : $order->user_id;           // Vendor sending to buyer

        try {
            UserMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'message' => $data['message'],
                'order_id' => $order->id,
            ]);

            return redirect()->route('orders.show', $order)->with('success', 'Message sent successfully!');

        } catch (\Exception $e) {
            \Log::error("Failed to send message for order #{$order->id}", [
                'exception' => $e,
                'order_id' => $order->id,
                'user_id' => $user->id,
            ]);
            return redirect()->back()->withErrors([
                'error' => 'Failed to send message. Please try again.'
            ]);
        }
    }
}
