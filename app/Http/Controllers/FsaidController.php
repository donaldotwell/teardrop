<?php

namespace App\Http\Controllers;

use App\Models\Fsaid;
use App\Models\FsaidBase;
use App\Models\FsaidPurchase;
use App\Models\User;
use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FsaidController extends Controller
{
    public function index(Request $request): View
    {
        $activeBases = FsaidBase::active()
            ->with('vendor:id,username_pub')
            ->orderBy('name')
            ->get();

        $states = Fsaid::select('fsaid.state')
            ->join('fsaid_bases', 'fsaid.base_id', '=', 'fsaid_bases.id')
            ->where('fsaid.status', 'available')
            ->where('fsaid_bases.is_active', true)
            ->whereNotNull('fsaid.state')
            ->where('fsaid.state', '!=', '')
            ->distinct()
            ->orderBy('fsaid.state')
            ->pluck('fsaid.state');

        $query = Fsaid::select(
                'fsaid.*',
                'fsaid_bases.name as base_name',
                'fsaid_bases.vendor_id as base_vendor_id',
                'users.username_pub as vendor_name'
            )
            ->join('fsaid_bases', 'fsaid.base_id', '=', 'fsaid_bases.id')
            ->join('users', 'fsaid_bases.vendor_id', '=', 'users.id')
            ->where('fsaid.status', 'available')
            ->where('fsaid_bases.is_active', true)
            ->where('fsaid_bases.available_count', '>', 0);

        if ($request->filled('vendor_id')) {
            $query->where('fsaid_bases.vendor_id', $request->integer('vendor_id'));
        }
        if ($request->filled('base_id')) {
            $query->where('fsaid.base_id', $request->integer('base_id'));
        }
        if ($request->filled('state')) {
            $query->where('fsaid.state', $request->input('state'));
        }
        if ($request->filled('city')) {
            $query->where('fsaid.city', 'like', '%' . $request->input('city') . '%');
        }
        if ($request->filled('zip')) {
            $query->where('fsaid.zip', $request->input('zip'));
        }
        if ($request->filled('name')) {
            $name = $request->input('name');
            $query->where(function ($q) use ($name) {
                $q->where('fsaid.first_name', 'like', '%' . $name . '%')
                  ->orWhere('fsaid.last_name', 'like', '%' . $name . '%');
            });
        }
        if ($request->filled('two_fa')) {
            if ($request->input('two_fa') === 'yes') {
                $query->whereNotNull('fsaid.two_fa')->where('fsaid.two_fa', '!=', '');
            } else {
                $query->where(fn($q) => $q->whereNull('fsaid.two_fa')->orWhere('fsaid.two_fa', ''));
            }
        }
        if ($request->filled('level')) {
            $query->where('fsaid.level', $request->input('level'));
        }
        if ($request->filled('enrollment')) {
            $query->where('fsaid.enrollment', $request->input('enrollment'));
        }
        if ($request->filled('price_min')) {
            $query->where('fsaid.price_usd', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('fsaid.price_usd', '<=', (float) $request->input('price_max'));
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->orderBy('fsaid.price_usd', 'asc')->orderBy('fsaid.id', 'asc'),
            'price_desc' => $query->orderBy('fsaid.price_usd', 'desc')->orderBy('fsaid.id', 'asc'),
            default      => $query->orderByDesc('fsaid.created_at'),
        };

        $records = $query->paginate(50)->withQueryString();

        $vendors = $activeBases
            ->map(fn($b) => (object) ['id' => $b->vendor_id, 'name' => $b->vendor->username_pub])
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('autoshop.fsaid.index', compact('records', 'activeBases', 'vendors', 'states', 'sort'));
    }

    public function show(FsaidBase $base): View
    {
        abort_if(!$base->is_active, 404);

        $records = $base->records()
            ->available()
            ->orderBy('id')
            ->paginate(50);

        return view('autoshop.fsaid.show', compact('base', 'records'));
    }

    public function purchase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fsaid_ids'   => 'required|array|min:1|max:100',
            'fsaid_ids.*' => 'integer',
            'currency'    => 'required|in:btc,xmr',
        ]);

        $buyer    = $request->user();
        $currency = $validated['currency'];

        $records = Fsaid::with('base:id,name,vendor_id')
            ->whereIn('id', $validated['fsaid_ids'])
            ->where('status', 'available')
            ->get();

        if ($records->isEmpty()) {
            return back()->withErrors(['error' => 'None of the selected records are available.']);
        }

        $vendorIds = $records->pluck('vendor_id')->unique();
        if ($vendorIds->count() > 1) {
            return back()->withErrors(['error' => 'All selected records must be from the same vendor.']);
        }

        if ($vendorIds->first() == $buyer->id) {
            return back()->withErrors(['error' => 'You cannot purchase records you have uploaded.']);
        }

        $count       = $records->count();
        $totalUsd    = round($records->sum(fn($r) => (float) ($r->price_usd ?? $r->base->price_usd)), 2);
        $totalCrypto = convert_usd_to_crypto($totalUsd, $currency);
        $baseIds     = $records->pluck('base_id')->unique();
        $baseFk      = $baseIds->count() === 1 ? $baseIds->first() : null;

        if ($currency === 'btc') {
            $buyerWallet = $buyer->btcWallet;
            if (!$buyerWallet) {
                return back()->withErrors(['error' => 'You do not have a Bitcoin wallet.']);
            }
            if ($buyerWallet->getBalance() < $totalCrypto) {
                return back()->withErrors(['error' => 'Insufficient BTC balance. Need ' . number_format($totalCrypto, 8) . ' BTC.']);
            }
        } else {
            $buyerWallet = $buyer->xmrWallet;
            if (!$buyerWallet) {
                return back()->withErrors(['error' => 'You do not have a Monero wallet.']);
            }
            $bal = $buyerWallet->getBalance();
            if ($bal['unlocked_balance'] < $totalCrypto) {
                return back()->withErrors(['error' => 'Insufficient XMR balance. Need ' . number_format($totalCrypto, 12) . ' XMR.']);
            }
        }

        try {
            $purchase = DB::transaction(function () use (
                $buyer, $records, $currency, $totalUsd, $totalCrypto, $count, $baseFk, $vendorIds
            ) {
                $vendor = User::findOrFail($vendorIds->first());

                $lockedIds = Fsaid::whereIn('id', $records->pluck('id'))
                    ->where('status', 'available')
                    ->lockForUpdate()
                    ->pluck('id');

                if ($lockedIds->count() !== $count) {
                    throw new \Exception('Some records were sold just now. Please reselect and try again.');
                }

                $txid = $currency === 'btc'
                    ? $this->processBtcPayment($buyer, $vendor, $totalCrypto)
                    : $this->processXmrPayment($buyer, $vendor, $totalCrypto);

                $purchase = FsaidPurchase::create([
                    'buyer_id'     => $buyer->id,
                    'vendor_id'    => $vendor->id,
                    'base_id'      => $baseFk,
                    'currency'     => $currency,
                    'total_usd'    => $totalUsd,
                    'total_crypto' => $totalCrypto,
                    'txid'         => $txid,
                    'record_count' => $count,
                ]);

                Fsaid::whereIn('id', $lockedIds->all())->update([
                    'status'               => 'sold',
                    'platform_buyer_id'    => $buyer->id,
                    'platform_purchase_id' => $purchase->id,
                    'sold_at'              => now(),
                ]);

                foreach ($records->groupBy('base_id') as $baseId => $group) {
                    FsaidBase::where('id', $baseId)->decrement('available_count', $group->count());
                    FsaidBase::where('id', $baseId)->increment('sold_count', $group->count());
                }

                Log::info('FSAID purchase completed', [
                    'purchase_id' => $purchase->id,
                    'buyer_id'    => $buyer->id,
                    'vendor_id'   => $vendor->id,
                    'count'       => $count,
                ]);

                return $purchase;
            });
        } catch (\Exception $e) {
            Log::error('FSAID purchase failed', ['buyer_id' => $buyer->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('autoshop.fsaid.receipt', $purchase)
            ->with('success', "Purchase complete! {$count} record(s) unlocked.");
    }

    public function myPurchases(Request $request): View
    {
        $purchases = FsaidPurchase::where('buyer_id', $request->user()->id)
            ->with('base', 'vendor:id,username_pub')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('autoshop.fsaid.my-purchases', compact('purchases'));
    }

    public function receipt(Request $request, FsaidPurchase $purchase): View
    {
        if ($purchase->buyer_id !== $request->user()->id) {
            abort(403);
        }

        $purchase->load(['base', 'vendor:id,username_pub', 'records']);

        return view('autoshop.fsaid.receipt', compact('purchase'));
    }

    public function download(Request $request, FsaidPurchase $purchase): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if ($purchase->buyer_id !== $request->user()->id) {
            abort(403);
        }

        $purchase->load('records');

        $filename = 'fsaid-purchase-' . $purchase->id . '-' . now()->format('Ymd') . '.csv';

        $headers = [
            'first_name', 'last_name', 'dob', 'ssn',
            'address', 'city', 'state', 'zip',
            'email', 'email_pass',
            'fa_uname', 'fa_pass', 'two_fa', 'backup_code', 'security_qa',
            'level', 'enrollment', 'programs', 'enrollment_details', 'description',
        ];

        return response()->streamDownload(function () use ($purchase, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($purchase->records as $r) {
                fputcsv($out, [
                    $r->first_name, $r->last_name, $r->dob ? explode(' ', trim($r->dob))[0] : '',
                    $r->ssn, $r->address, $r->city, $r->state, $r->zip,
                    $r->email, $r->email_pass,
                    $r->fa_uname, $r->fa_pass, $r->two_fa, $r->backup_code, $r->security_qa,
                    $r->level, $r->enrollment, $r->programs, $r->enrollment_details, $r->description,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
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
            'raw_transaction' => ['purpose' => 'fsaid_purchase'],
        ]);

        $vendorWallet->transactions()->create([
            'btc_address_id'  => $vendorAddress->id,
            'type'            => 'deposit',
            'amount'          => $amount,
            'usd_value'       => convert_crypto_to_usd($amount, 'btc'),
            'status'          => 'pending',
            'confirmations'   => 0,
            'txid'            => $txid,
            'raw_transaction' => ['purpose' => 'fsaid_sale'],
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
            'raw_transaction' => ['purpose' => 'fsaid_purchase'],
        ]);

        $vendorWallet->transactions()->create([
            'xmr_address_id'  => $vendorAddress->id,
            'type'            => 'deposit',
            'amount'          => $amount,
            'usd_value'       => convert_crypto_to_usd($amount, 'xmr'),
            'status'          => 'pending',
            'confirmations'   => 0,
            'txid'            => $txHash,
            'raw_transaction' => ['purpose' => 'fsaid_sale'],
        ]);

        return $txHash;
    }
}
