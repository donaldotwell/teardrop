<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCartItem;
use App\Models\CartItem;
use App\Models\Listing;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    private const CART_TTL_DAYS = 30;

    // -------------------------------------------------------------------------
    // Add to cart
    // -------------------------------------------------------------------------

    public function store(Request $request, Listing $listing): RedirectResponse
    {
        if ($listing->user_id === $request->user()->id) {
            return back()->withErrors(['error' => 'You cannot add your own listing to your cart.']);
        }

        if (!$listing->is_active || !$listing->isInStock()) {
            return back()->withErrors(['error' => 'This listing is no longer available.']);
        }

        $validated = $request->validate(['quantity' => 'required|integer|min:1|max:100']);
        $quantity  = $validated['quantity'];

        if ($listing->quantity !== null && !$listing->hasAvailableStock($quantity)) {
            return back()->withErrors(['error' => 'Requested quantity exceeds available stock.']);
        }

        $user = $request->user();

        // Restore soft-deleted record for the same user+listing if it exists, otherwise create.
        $existing = CartItem::withTrashed()
            ->where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->first();

        if ($existing) {
            $existing->restore();
            $existing->update(['quantity' => $quantity, 'expires_at' => now()->addDays(self::CART_TTL_DAYS)]);
        } else {
            CartItem::create([
                'user_id'    => $user->id,
                'listing_id' => $listing->id,
                'quantity'   => $quantity,
                'expires_at' => now()->addDays(self::CART_TTL_DAYS),
            ]);
        }

        return back()->with('success', "Added to cart.");
    }

    // -------------------------------------------------------------------------
    // Remove from cart
    // -------------------------------------------------------------------------

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_if($cartItem->user_id !== $request->user()->id, 403);
        $cartItem->delete();
        return back()->with('success', 'Item removed from cart.');
    }

    // -------------------------------------------------------------------------
    // View cart
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $user  = $request->user();

        $items = CartItem::with([
                'listing' => fn($q) => $q->with([
                    'firstMedia',
                    'user:id,username_pub,pgp_pub_key',
                    'product.productCategory',
                ]),
            ])
            ->where('user_id', $user->id)
            ->active()
            ->get();

        // Remove cart items whose listing was deleted or deactivated
        $stale = $items->filter(fn($i) => !$i->listing || !$i->listing->is_active);
        if ($stale->isNotEmpty()) {
            CartItem::whereIn('id', $stale->pluck('id'))->delete();
            $items = $items->diff($stale);
        }

        $totalUsd = $items->sum(
            fn($i) => ($i->listing->price * $i->quantity) + $i->listing->price_shipping
        );

        $totalBtc = $totalUsd > 0 ? convert_usd_to_crypto($totalUsd, 'btc') : 0;
        $totalXmr = $totalUsd > 0 ? convert_usd_to_crypto($totalUsd, 'xmr') : 0;
        $estBtcFee = $totalBtc > 0 ? estimate_btc_transaction_fee($totalBtc) : 0;

        $btcWallet  = $user->btcWallet;
        $xmrWallet  = $user->xmrWallet;
        $btcAddress = $btcWallet?->getCurrentAddress() ?? $btcWallet?->generateNewAddress();
        $xmrAddress = $xmrWallet?->getCurrentAddress() ?? $xmrWallet?->generateNewAddress();

        $vendorGroups = $items->groupBy(fn($i) => $i->listing->user_id);

        return view('cart.index', compact(
            'items', 'vendorGroups', 'totalUsd',
            'totalBtc', 'totalXmr', 'estBtcFee',
            'btcAddress', 'xmrAddress'
        ));
    }

    // -------------------------------------------------------------------------
    // Checkout — dispatches one background job per cart item
    // -------------------------------------------------------------------------

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate(['currency' => 'required|in:btc,xmr']);
        $currency  = $validated['currency'];
        $user      = $request->user();

        $items = CartItem::with([
                'listing' => fn($q) => $q->with(['user', 'product.productCategory']),
            ])
            ->where('user_id', $user->id)
            ->active()
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['error' => 'Your cart is empty.']);
        }

        // Self-purchase guard
        foreach ($items as $item) {
            if ($item->listing->user_id === $user->id) {
                return back()->withErrors(['error' => "You cannot purchase your own listing: \"{$item->listing->title}\"."]);
            }
        }

        // Availability and PGP guard
        foreach ($items as $item) {
            if (!$item->listing || !$item->listing->is_active) {
                return back()->withErrors(['error' => "Listing is no longer available: \"{$item->listing->title}\"."]);
            }
            if (!$item->listing->isInStock() || !$item->listing->hasAvailableStock($item->quantity)) {
                return back()->withErrors(['error' => "Insufficient stock for: \"{$item->listing->title}\"."]);
            }
            if (empty($item->listing->user->pgp_pub_key)) {
                return back()->withErrors(['error' => "Vendor has no PGP key for: \"{$item->listing->title}\"."]);
            }
        }

        // Upfront balance check — fast feedback before queuing
        $totalUsd    = $items->sum(fn($i) => ($i->listing->price * $i->quantity) + $i->listing->price_shipping);
        $totalCrypto = convert_usd_to_crypto($totalUsd, $currency);
        $fee         = $currency === 'btc' ? estimate_btc_transaction_fee($totalCrypto) : 0;
        $totalNeeded = $totalCrypto + $fee;

        $balance   = $user->getBalance();
        $available = $currency === 'xmr'
            ? $balance['xmr']['unlocked_balance']
            : $balance['btc']['balance'];

        if ($totalNeeded > $available) {
            $shortfall = number_format($totalNeeded - $available, $currency === 'btc' ? 8 : 12);
            return back()->withErrors([
                'error' => "Insufficient {$currency} balance. Short by {$shortfall} {$currency}. Top up your wallet and return to cart.",
            ]);
        }

        // Dispatch one job per item — each job handles escrow, notifications, and cart removal.
        $count = $items->count();
        foreach ($items as $item) {
            ProcessCartItem::dispatch(
                $user->id,
                $item->id,
                $item->listing_id,
                $item->quantity,
                $currency,
            );
        }

        // Immediate acknowledgement to buyer.
        NotificationService::send(
            $user->id,
            'order',
            'Orders Queued',
            "{$count} cart order(s) are being processed in the background. You will be notified when each is confirmed.",
            route('orders.index')
        );

        return redirect()->route('orders.index')
            ->with('success', "{$count} order(s) queued — you will be notified as each is confirmed.");
    }
}
