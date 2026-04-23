<?php

namespace App\Http\Controllers;

use App\Models\Fullz;
use App\Models\FullzBase;
use App\Models\FullzPurchase;
use App\Models\User;
use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoshopController extends Controller
{
    /**
     * Browse all available records with filtering.
     */
    public function index(Request $request): View
    {
        // Filter options — only from active bases with available stock
        $activeBases = FullzBase::active()
            ->with('vendor:id,username_pub')
            ->orderBy('name')
            ->get();

        // Distinct states and genders from available records in active bases
        $states = Fullz::select('fullz.state')
            ->join('fullz_bases', 'fullz.base_id', '=', 'fullz_bases.id')
            ->where('fullz.status', 'available')
            ->where('fullz_bases.is_active', true)
            ->whereNotNull('fullz.state')
            ->where('fullz.state', '!=', '')
            ->distinct()
            ->orderBy('fullz.state')
            ->pluck('fullz.state');

        $genders = Fullz::select('fullz.gender')
            ->join('fullz_bases', 'fullz.base_id', '=', 'fullz_bases.id')
            ->where('fullz.status', 'available')
            ->where('fullz_bases.is_active', true)
            ->whereNotNull('fullz.gender')
            ->where('fullz.gender', '!=', '')
            ->distinct()
            ->orderBy('fullz.gender')
            ->pluck('fullz.gender');

        // Build main query — always join bases so we can filter and sort by price
        $query = Fullz::select('fullz.*', 'fullz_bases.price_usd', 'fullz_bases.name as base_name',
                               'fullz_bases.vendor_id as base_vendor_id')
            ->join('fullz_bases', 'fullz.base_id', '=', 'fullz_bases.id')
            ->where('fullz.status', 'available')
            ->where('fullz_bases.is_active', true)
            ->where('fullz_bases.available_count', '>', 0);

        // --- Filters ---
        if ($request->filled('base_id')) {
            $query->where('fullz.base_id', $request->integer('base_id'));
        }

        if ($request->filled('vendor_id')) {
            $query->where('fullz_bases.vendor_id', $request->integer('vendor_id'));
        }

        if ($request->filled('state')) {
            $query->where('fullz.state', $request->input('state'));
        }

        if ($request->filled('gender')) {
            $query->where('fullz.gender', $request->input('gender'));
        }

        if ($request->filled('price_min')) {
            $query->where('fullz_bases.price_usd', '>=', (float) $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('fullz_bases.price_usd', '<=', (float) $request->input('price_max'));
        }

        // --- Sort ---
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->orderBy('fullz_bases.price_usd', 'asc')->orderBy('fullz.id', 'asc'),
            'price_desc' => $query->orderBy('fullz_bases.price_usd', 'desc')->orderBy('fullz.id', 'asc'),
            default      => $query->orderByDesc('fullz.created_at'),
        };

        $records = $query->paginate(50)->withQueryString();

        return view('autoshop.fullz.index', compact(
            'records', 'activeBases', 'states', 'genders', 'sort'
        ));
    }

    /**
     * Show records in a single base (focused view, pre-filters index).
     */
    public function show(FullzBase $base): View
    {
        abort_if(!$base->is_active, 404);

        $records = $base->records()
            ->available()
            ->orderBy('id')
            ->paginate(50);

        return view('autoshop.fullz.show', compact('base', 'records'));
    }

    /**
     * Purchase selected records — cross-base allowed if same vendor.
     */
    public function purchase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fullz_ids'   => 'required|array|min:1|max:100',
            'fullz_ids.*' => 'integer',
            'currency'    => 'required|in:btc,xmr',
        ]);

        $buyer    = $request->user();
        $currency = $validated['currency'];

        // Load records with their base for price lookup
        $records = Fullz::with('base:id,name,price_usd,vendor_id')
            ->whereIn('id', $validated['fullz_ids'])
            ->where('status', 'available')
            ->get();

        if ($records->isEmpty()) {
            return back()->withErrors(['error' => 'None of the selected records are available.']);
        }

        // All records must belong to the same vendor (one payment)
        $vendorIds = $records->pluck('vendor_id')->unique();
        if ($vendorIds->count() > 1) {
            return back()->withErrors([
                'error' => 'All selected records must be from the same vendor. Filter by vendor or base and try again.',
            ]);
        }

        $count     = $records->count();
        $totalUsd  = round($records->sum(fn($r) => (float) $r->base->price_usd), 2);
        $totalCrypto = convert_usd_to_crypto($totalUsd, $currency);

        // Single base_id if all records share one base, else null
        $baseIds = $records->pluck('base_id')->unique();
        $baseFk  = $baseIds->count() === 1 ? $baseIds->first() : null;

        // Wallet balance check
        if ($currency === 'btc') {
            $buyerWallet = $buyer->btcWallet;
            if (!$buyerWallet) {
                return back()->withErrors(['error' => 'You do not have a Bitcoin wallet. Visit the Bitcoin top-up page first.']);
            }
            if ($buyerWallet->getBalance() < $totalCrypto) {
                return back()->withErrors(['error' => "Insufficient BTC balance. Need " . number_format($totalCrypto, 8) . " BTC."]);
            }
        } else {
            $buyerWallet = $buyer->xmrWallet;
            if (!$buyerWallet) {
                return back()->withErrors(['error' => 'You do not have a Monero wallet. Visit the Monero top-up page first.']);
            }
            $bal = $buyerWallet->getBalance();
            if ($bal['unlocked_balance'] < $totalCrypto) {
                return back()->withErrors(['error' => "Insufficient XMR unlocked balance. Need " . number_format($totalCrypto, 12) . " XMR."]);
            }
        }

        try {
            $purchase = DB::transaction(function () use (
                $buyer, $records, $currency, $totalUsd, $totalCrypto, $count, $baseFk, $vendorIds
            ) {
                $vendor = User::findOrFail($vendorIds->first());

                // Re-lock records to prevent double-purchase
                $lockedIds = Fullz::whereIn('id', $records->pluck('id'))
                    ->where('status', 'available')
                    ->lockForUpdate()
                    ->pluck('id');

                if ($lockedIds->count() !== $count) {
                    throw new \Exception('Some records were sold by another buyer just now. Please reselect and try again.');
                }

                // Process payment
                $txid = $currency === 'btc'
                    ? $this->processBtcPayment($buyer, $vendor, $totalCrypto)
                    : $this->processXmrPayment($buyer, $vendor, $totalCrypto);

                // Record purchase
                $purchase = FullzPurchase::create([
                    'buyer_id'     => $buyer->id,
                    'vendor_id'    => $vendor->id,
                    'base_id'      => $baseFk,
                    'currency'     => $currency,
                    'total_usd'    => $totalUsd,
                    'total_crypto' => $totalCrypto,
                    'txid'         => $txid,
                    'record_count' => $count,
                ]);

                // Mark records sold
                Fullz::whereIn('id', $lockedIds->all())->update([
                    'status'      => 'sold',
                    'buyer_id'    => $buyer->id,
                    'purchase_id' => $purchase->id,
                    'sold_at'     => now(),
                ]);

                // Update base available/sold counters
                foreach ($records->groupBy('base_id') as $baseId => $group) {
                    FullzBase::where('id', $baseId)->decrement('available_count', $group->count());
                    FullzBase::where('id', $baseId)->increment('sold_count',      $group->count());
                }

                Log::info('Autoshop purchase completed', [
                    'purchase_id' => $purchase->id,
                    'buyer_id'    => $buyer->id,
                    'vendor_id'   => $vendor->id,
                    'currency'    => $currency,
                    'total_usd'   => $totalUsd,
                    'records'     => $count,
                    'bases'       => $records->pluck('base_id')->unique()->values(),
                ]);

                return $purchase;
            });
        } catch (\Exception $e) {
            Log::error('Autoshop purchase failed', [
                'buyer_id' => $buyer->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('autoshop.fullz.receipt', $purchase)
            ->with('success', "Purchase complete! {$count} record(s) unlocked.");
    }

    /**
     * Show full details of a purchase (buyer only).
     */
    public function receipt(Request $request, FullzPurchase $purchase): View
    {
        if ($purchase->buyer_id !== $request->user()->id) {
            abort(403);
        }

        $purchase->load(['base', 'vendor:id,username_pub', 'records']);

        return view('autoshop.fullz.receipt', compact('purchase'));
    }

    /**
     * List all purchases by this buyer.
     */
    public function myPurchases(Request $request): View
    {
        $purchases = FullzPurchase::where('buyer_id', $request->user()->id)
            ->with('base', 'vendor:id,username_pub')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('autoshop.fullz.my-purchases', compact('purchases'));
    }

    // -------------------------------------------------------------------------

    private function processBtcPayment(User $buyer, User $vendor, float $amount): ?string
    {
        $vendorWallet  = $vendor->btcWallet ?? BitcoinRepository::getOrCreateWalletForUser($vendor);
        $vendorAddress = $vendorWallet->getCurrentAddress() ?? $vendorWallet->generateNewAddress();

        $txid = BitcoinRepository::sendBitcoin($buyer->btcWallet->name, $vendorAddress->address, $amount);

        if (!$txid) {
            throw new \Exception('Bitcoin transfer failed. Please try again or contact support.');
        }

        $buyer->btcWallet->transactions()->create([
            'type'            => 'withdrawal',
            'amount'          => $amount,
            'usd_value'       => convert_crypto_to_usd($amount, 'btc'),
            'status'          => 'pending',
            'confirmations'   => 0,
            'txid'            => $txid,
            'raw_transaction' => ['purpose' => 'autoshop_purchase'],
        ]);

        return $txid;
    }

    private function processXmrPayment(User $buyer, User $vendor, float $amount): ?string
    {
        $vendorWallet  = $vendor->xmrWallet ?? MoneroRepository::getOrCreateWalletForUser($vendor);
        $vendorAddress = $vendorWallet->getCurrentAddress() ?? $vendorWallet->generateNewAddress();

        $repository = new MoneroRepository();
        $result     = $repository->transfer($buyer->xmrWallet, $vendorAddress->address, $amount);
        $txHash     = $result['tx_hash'] ?? null;

        if (!$txHash) {
            throw new \Exception('Monero transfer failed. Please try again or contact support.');
        }

        $buyer->xmrWallet->transactions()->create([
            'type'            => 'withdrawal',
            'amount'          => $amount,
            'usd_value'       => convert_crypto_to_usd($amount, 'xmr'),
            'status'          => 'pending',
            'confirmations'   => 0,
            'txid'            => $txHash,
            'raw_transaction' => ['purpose' => 'autoshop_purchase'],
        ]);

        return $txHash;
    }
}
