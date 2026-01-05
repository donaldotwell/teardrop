<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Order;
use App\Models\UserMessage;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Calculate estimated transaction fee (only for BTC)
        $estimatedFee = 0;
        if ($data['currency'] === 'btc') {
            $estimatedFee = estimate_btc_transaction_fee($crypto_value);
        }

        // Total amount needed = transaction amount + fee
        $totalNeeded = $crypto_value + $estimatedFee;

        if ($totalNeeded > $user_balance[$data['currency']]['balance']) {
            $feeDisplay = $estimatedFee > 0 ? " (including ~{$estimatedFee} {$data['currency']} network fee)" : "";
            return redirect()->back()->withErrors([
                'error' => "Insufficient balance for this transaction. Your current balance is {$user_balance[$data['currency']]['balance']} {$data['currency']} which is not enough to cover the transaction of {$crypto_value} {$data['currency']}{$feeDisplay}.",
            ]);
        }

        // Everything checks out—show the confirmation view.
        return view('orders.create', [
            'listing'       => $listing,
            'usd_price'     => $usd_price,
            'crypto_value' => $crypto_value,
            'currency'      => $data['currency'],
            'quantity'      => $data['quantity'],
            'estimated_fee' => $estimatedFee,
            'total_needed'  => $totalNeeded,
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
        // Authorization: Only buyer can complete the order (confirm receipt and release escrow)
        $user = $request->user();
        if ($order->user_id !== $user->id) {
            abort(403, 'Only the buyer can complete the order and release escrow');
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

        // Check for escrow wallet
        if (!$order->escrowWallet) {
            return redirect()->back()->withErrors([
                'error' => 'No escrow wallet found for this order.',
            ]);
        }

        // Check if escrow has been funded
        if (!$order->escrow_funded_at) {
            return redirect()->back()->withErrors([
                'error' => 'Escrow has not been funded yet. Please wait for blockchain confirmations.',
            ]);
        }

        try {
            DB::transaction(function () use ($order) {
                $escrowService = new EscrowService();

                // Release escrow to vendor and admin
                // The releaseEscrow method sends crypto and returns transaction IDs
                // The sync jobs (bitcoin:sync/monero:sync) will automatically update wallet balances
                $txids = $escrowService->releaseEscrow($order->escrowWallet, $order);

                // Update order status to completed
                $order->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'txid' => $txids['seller_txid'], // Store seller transaction
                ]);

                // Notify vendor
                UserMessage::create([
                    'sender_id' => $order->user_id,
                    'receiver_id' => $order->listing->user_id,
                    'message' => "Order #{$order->id} has been completed.\nEscrow released.\nVendor Payment Transaction ID: {$txids['seller_txid']}\nAdmin Fee Transaction ID: {$txids['admin_txid']}",
                    'order_id' => $order->id,
                ]);

                Log::info("Order completed via escrow release", [
                    'order_id' => $order->id,
                    'seller_txid' => $txids['seller_txid'],
                    'admin_txid' => $txids['admin_txid'],
                ]);
            });

            $currencyName = $order->currency === 'btc' ? 'Bitcoin' : 'Monero';
            return redirect()->route('orders.show', $order)->with('success', "Order completed! Escrow released. {$currencyName} sent to vendor. Balances will update automatically after blockchain confirmations.");

        } catch (\Exception $e) {
            Log::error("Failed to complete order #{$order->id}", [
                'exception' => $e->getMessage(),
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
            ]);
            return redirect()->back()->withErrors([
                'error' => 'Failed to complete order. Please contact support if the issue persists.'
            ]);
        }
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

        // Recheck balance including transaction fees
        $user_balance = $user->getBalance();
        $usd_price = $listing->price * $data['quantity'];
        $crypto_value = convert_usd_to_crypto($usd_price, $data['currency']);

        // Calculate estimated transaction fee (only for BTC)
        $estimatedFee = 0;
        if ($data['currency'] === 'btc') {
            $estimatedFee = estimate_btc_transaction_fee($crypto_value);
        }

        // Total amount needed = transaction amount + fee
        $totalNeeded = $crypto_value + $estimatedFee;

        if ($totalNeeded > $user_balance[$data['currency']]['balance']) {
            $feeDisplay = $estimatedFee > 0 ? " + {$estimatedFee} fee" : "";
            return redirect()->back()->withErrors([
                'error' => "Insufficient balance. Needed: {$crypto_value}{$feeDisplay} {$data['currency']}, Available: {$user_balance[$data['currency']]['balance']} {$data['currency']}",
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

        // Create order and escrow wallet in transaction
        try {
            DB::transaction(function () use ($user, $listing, $data, $crypto_value, $usd_price, $encryptedAddress) {
                // 1. Create order with encrypted delivery address
                $order = $user->orders()->create([
                    'listing_id' => $listing->id,
                    'quantity' => $data['quantity'],
                    'currency' => $data['currency'],
                    'crypto_value' => $crypto_value,
                    'usd_price' => $usd_price,
                    'status' => 'pending',
                    'encrypted_delivery_address' => $encryptedAddress,
                ]);

                // 2. Create escrow wallet
                $escrowService = new EscrowService();
                $escrowWallet = $escrowService->createEscrowForOrder($order);

                // 3. Fund escrow from buyer's wallet
                $txid = $escrowService->fundEscrow($escrowWallet, $order);

                if (!$txid) {
                    throw new \Exception("Failed to fund escrow wallet");
                }

                // 4. Update order with escrow info
                $order->update([
                    'escrow_wallet_id' => $escrowWallet->id,
                    'txid' => $txid, // Store initial funding transaction
                ]);

                // 5. Create wallet transaction record for buyer (for balance tracking)
                $wallet = $user->wallets()->where('currency', $data['currency'])->firstOrFail();
                $wallet->transactions()->create([
                    'amount' => -$crypto_value,
                    'type' => 'order_escrow',
                    'comment' => "Sent to escrow for order #{$order->id}",
                ]);

                // 6. Update buyer's wallet balance
                $wallet->decrement('balance', $crypto_value);

                // 7. Create message to vendor
                $messageContent = "New order #{$order->id}:\n";
                $messageContent .= "Quantity: {$data['quantity']}\n";
                $messageContent .= "Amount: {$crypto_value} ".strtoupper($data['currency'])."\n";
                $messageContent .= "Escrow Address: {$escrowWallet->address}\n";
                $messageContent .= "Note: ".(($data['note'] ?? 'No message provided'));

                UserMessage::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $listing->user_id,
                    'message' => $messageContent,
                    'order_id' => $order->id,
                ]);

                Log::info("Order created with escrow", [
                    'order_id' => $order->id,
                    'escrow_wallet' => $escrowWallet->wallet_name,
                    'funding_txid' => $txid,
                ]);
            });

            return redirect()->route('orders.index')->with('success', 'Order placed successfully! Funds sent to escrow.');

        } catch (\Exception $e) {
            Log::error("Failed to create order with escrow", [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'listing_id' => $listing->id,
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Failed to create order. Please try again or contact support.',
            ])->withInput();
        }
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
