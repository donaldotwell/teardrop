<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardBase;
use App\Models\CardPurchase;
use App\Models\User;
use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
    public function index(Request $request): View
    {
        $activeBases = CardBase::active()
            ->with('vendor:id,username_pub')
            ->orderBy('name')
            ->get();

        $states = Card::select('cards.state')
            ->join('card_bases', 'cards.base_id', '=', 'card_bases.id')
            ->where('cards.status', 'available')
            ->where('card_bases.is_active', true)
            ->whereNotNull('cards.state')
            ->where('cards.state', '!=', '')
            ->distinct()
            ->orderBy('cards.state')
            ->pluck('cards.state');

        $countries = Card::select('cards.country')
            ->join('card_bases', 'cards.base_id', '=', 'card_bases.id')
            ->where('cards.status', 'available')
            ->where('card_bases.is_active', true)
            ->whereNotNull('cards.country')
            ->where('cards.country', '!=', '')
            ->distinct()
            ->orderBy('cards.country')
            ->pluck('cards.country');

        $query = Card::select(
                'cards.*',
                'card_bases.name as base_name',
                'card_bases.vendor_id as base_vendor_id',
                'card_bases.discount_pct as base_discount_pct',
                'users.username_pub as vendor_name'
            )
            ->join('card_bases', 'cards.base_id', '=', 'card_bases.id')
            ->join('users', 'card_bases.vendor_id', '=', 'users.id')
            ->where('cards.status', 'available')
            ->where('card_bases.is_active', true)
            ->where('card_bases.available_count', '>', 0);

        if ($request->filled('vendor_id')) {
            $query->where('card_bases.vendor_id', $request->integer('vendor_id'));
        }
        if ($request->filled('base_id')) {
            $query->where('cards.base_id', $request->integer('base_id'));
        }
        if ($request->filled('state')) {
            $query->where('cards.state', $request->input('state'));
        }
        if ($request->filled('country')) {
            $query->where('cards.country', $request->input('country'));
        }
        if ($request->filled('cardholder')) {
            $query->where('cards.cardholder_name', 'like', '%' . $request->input('cardholder') . '%');
        }
        if ($request->filled('bin')) {
            $query->where(DB::raw("LEFT(REGEXP_REPLACE(cards.card_number, '[^0-9]', '', 'g'), 6)"), $request->input('bin'));
        }
        if ($request->filled('email')) {
            if ($request->input('email') === 'yes') {
                $query->whereNotNull('cards.email')->where('cards.email', '!=', '');
            } else {
                $query->where(fn($q) => $q->whereNull('cards.email')->orWhere('cards.email', ''));
            }
        }
        if ($request->filled('price_min')) {
            $query->where('cards.price_usd', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('cards.price_usd', '<=', (float) $request->input('price_max'));
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->orderBy('cards.price_usd', 'asc')->orderBy('cards.id', 'asc'),
            'price_desc' => $query->orderBy('cards.price_usd', 'desc')->orderBy('cards.id', 'asc'),
            default      => $query->orderByDesc('cards.created_at'),
        };

        $records = $query->paginate(50)->withQueryString();

        $vendors = $activeBases
            ->map(fn($b) => (object) ['id' => $b->vendor_id, 'name' => $b->vendor->username_pub])
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('autoshop.cards.index', compact(
            'records', 'activeBases', 'vendors', 'states', 'countries', 'sort'
        ));
    }

    public function show(CardBase $base): View
    {
        abort_if(!$base->is_active, 404);
        abort_if($base->available_count === 0, 404);

        $records = $base->records()
            ->available()
            ->orderBy('id')
            ->paginate(50);

        return view('autoshop.cards.show', compact('base', 'records'));
    }

    public function purchase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'card_ids'   => 'required|array|min:1|max:100',
            'card_ids.*' => 'integer',
            'currency'   => 'required|in:btc,xmr',
        ]);

        $buyer    = $request->user();
        $currency = $validated['currency'];

        $records = Card::with('base:id,name,vendor_id,price_usd,discount_pct')
            ->whereIn('id', $validated['card_ids'])
            ->where('status', 'available')
            ->get();

        if ($records->isEmpty()) {
            return back()->withErrors(['error' => 'None of the selected records are available.']);
        }

        $vendorIds = $records->pluck('vendor_id')->unique();
        if ($vendorIds->count() > 1) {
            return back()->withErrors([
                'error' => 'All selected records must be from the same vendor. Filter by vendor or base and try again.',
            ]);
        }

        if ($vendorIds->first() == $buyer->id) {
            return back()->withErrors(['error' => 'You cannot purchase records you have uploaded.']);
        }

        $count       = $records->count();
        $totalUsd    = round($records->sum(function ($r) {
            $full = (float) ($r->price_usd ?? $r->base?->price_usd ?? 0);
            $pct  = max(0, min(99, (float) ($r->base?->discount_pct ?? 0)));
            return $full * (1 - $pct / 100);
        }), 2);
        $totalCrypto = convert_usd_to_crypto($totalUsd, $currency);

        $baseIds = $records->pluck('base_id')->unique();
        $baseFk  = $baseIds->count() === 1 ? $baseIds->first() : null;

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

                $lockedIds = Card::whereIn('id', $records->pluck('id'))
                    ->where('status', 'available')
                    ->lockForUpdate()
                    ->pluck('id');

                if ($lockedIds->count() !== $count) {
                    throw new \Exception('Some records were sold by another buyer just now. Please reselect and try again.');
                }

                $txid = $currency === 'btc'
                    ? $this->processBtcPayment($buyer, $vendor, $totalCrypto)
                    : $this->processXmrPayment($buyer, $vendor, $totalCrypto);

                $purchase = CardPurchase::create([
                    'buyer_id'     => $buyer->id,
                    'vendor_id'    => $vendor->id,
                    'base_id'      => $baseFk,
                    'currency'     => $currency,
                    'total_usd'    => $totalUsd,
                    'total_crypto' => $totalCrypto,
                    'txid'         => $txid,
                    'record_count' => $count,
                ]);

                Card::whereIn('id', $lockedIds->all())->update([
                    'status'      => 'sold',
                    'buyer_id'    => $buyer->id,
                    'purchase_id' => $purchase->id,
                    'sold_at'     => now(),
                ]);

                foreach ($records->groupBy('base_id') as $baseId => $group) {
                    CardBase::where('id', $baseId)->decrement('available_count', $group->count());
                    CardBase::where('id', $baseId)->increment('sold_count',      $group->count());
                }

                Log::info('Cards autoshop purchase completed', [
                    'purchase_id' => $purchase->id,
                    'buyer_id'    => $buyer->id,
                    'vendor_id'   => $vendor->id,
                    'currency'    => $currency,
                    'total_usd'   => $totalUsd,
                    'records'     => $count,
                ]);

                return $purchase;
            });
        } catch (\Exception $e) {
            Log::error('Cards autoshop purchase failed', [
                'buyer_id' => $buyer->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('autoshop.cards.receipt', $purchase)
            ->with('success', "Purchase complete! {$count} card(s) unlocked.");
    }

    public function receipt(Request $request, CardPurchase $purchase): View
    {
        if ($purchase->buyer_id !== $request->user()->id) {
            abort(403);
        }

        $purchase->load(['base', 'vendor:id,username_pub', 'records']);

        return view('autoshop.cards.receipt', compact('purchase'));
    }

    public function download(Request $request, CardPurchase $purchase): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if ($purchase->buyer_id !== $request->user()->id) {
            abort(403);
        }

        $purchase->load('records');

        $filename = 'cards-purchase-' . $purchase->id . '-' . now()->format('Ymd') . '.csv';

        $headers = ['card_number', 'exp_month', 'exp_year', 'cvv', 'cardholder_name',
                    'address', 'city', 'state', 'zip', 'country', 'email', 'phone'];

        return response()->streamDownload(function () use ($purchase, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($purchase->records as $r) {
                fputcsv($out, [
                    $r->card_number, $r->exp_month, $r->exp_year, $r->cvv, $r->cardholder_name,
                    $r->address, $r->city, $r->state, $r->zip, $r->country, $r->email, $r->phone,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function myPurchases(Request $request): View
    {
        $purchases = CardPurchase::where('buyer_id', $request->user()->id)
            ->with('base', 'vendor:id,username_pub')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('autoshop.cards.my-purchases', compact('purchases'));
    }

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
            'raw_transaction' => ['purpose' => 'autoshop_cards_purchase'],
        ]);

        $vendorWallet->transactions()->create([
            'btc_address_id'  => $vendorAddress->id,
            'type'            => 'deposit',
            'amount'          => $amount,
            'usd_value'       => convert_crypto_to_usd($amount, 'btc'),
            'status'          => 'pending',
            'confirmations'   => 0,
            'txid'            => $txid,
            'raw_transaction' => ['purpose' => 'autoshop_cards_sale'],
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
            'raw_transaction' => ['purpose' => 'autoshop_cards_purchase'],
        ]);

        $vendorWallet->transactions()->create([
            'xmr_address_id'  => $vendorAddress->id,
            'type'             => 'deposit',
            'amount'           => $amount,
            'usd_value'        => convert_crypto_to_usd($amount, 'xmr'),
            'status'           => 'pending',
            'confirmations'    => 0,
            'txid'             => $txHash,
            'raw_transaction'  => ['purpose' => 'autoshop_cards_sale'],
        ]);

        return $txHash;
    }
}
