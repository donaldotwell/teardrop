<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BtcWallet;
use App\Models\Country;
use App\Models\ExchangeRate;
use App\Models\Listing;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Wallet;
use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorListingController extends Controller
{
    /**
     * Display vendor's listings.
     */
    public function index()
    {
        $vendor = auth()->user();

        $listings = Listing::where('user_id', $vendor->id)
            ->with(['product.productCategory', 'originCountry', 'destinationCountry', 'media'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('vendor.listings.index', compact('listings'));
    }

    /**
     * Show form for creating a new listing.
     */
    public function create()
    {
        $countries = Country::all();
        $productCategories = ProductCategory::with('products')->get();

        return view('vendor.listings.create', compact('countries', 'productCategories'));
    }

    /**
     * Store a newly created listing.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:140',
            'short_description' => 'required|string|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'price_shipping' => 'required|numeric|min:0',
            'end_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:1',
            'origin_country_id' => 'required|exists:countries,id',
            'destination_country_id' => 'required|exists:countries,id',
            'tags' => 'nullable|string',
            'images' => 'required|array|min:1|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'return_policy' => 'required|string',
            'product_category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'shipping_method' => 'required|in:shipping,pickup,delivery',
            'payment_method' => 'required|in:escrow,direct',
        ]);

        // Ensure product belongs to selected category
        $product = Product::where('id', $data['product_id'])
            ->where('product_category_id', $data['product_category_id'])
            ->firstOrFail();

        $listing = Listing::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'price' => $data['price'],
            'price_shipping' => $data['price_shipping'],
            'shipping_method' => $data['shipping_method'],
            'payment_method' => $data['payment_method'],
            'end_date' => $data['end_date'],
            'quantity' => $data['quantity'],
            'origin_country_id' => $data['origin_country_id'],
            'destination_country_id' => $data['destination_country_id'],
            'tags' => !empty($data['tags']) ? array_map('trim', explode(',', $data['tags'])) : null,
            'return_policy' => $data['return_policy'],
            'is_active' => $request->boolean('is_active'),
        ]);

        // Store the images as base64 encoded content
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imageContent = base64_encode(file_get_contents($image->getRealPath()));
                $mimeType = $image->getMimeType();

                $listing->media()->create([
                    'content' => $imageContent,
                    'type' => $mimeType,
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('vendor.listings.index')
            ->with('success', 'Listing created successfully.');
    }

    /**
     * Show form for editing a listing.
     */
    public function edit(Listing $listing)
    {
        $vendor = auth()->user();

        // Verify ownership
        if ($listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this listing.');
        }

        $countries = Country::all();
        $productCategories = ProductCategory::with('products')->get();

        return view('vendor.listings.edit', compact('listing', 'countries', 'productCategories'));
    }

    /**
     * Update a listing.
     */
    public function update(Request $request, Listing $listing)
    {
        $vendor = auth()->user();

        // Verify ownership
        if ($listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this listing.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:140',
            'short_description' => 'required|string|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'price_shipping' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:1',
            'is_active' => 'boolean',
        ]);

        $listing->update($data);

        return redirect()->route('vendor.listings.index')
            ->with('success', 'Listing updated successfully.');
    }

    /**
     * Delete a listing.
     */
    public function destroy(Listing $listing)
    {
        $vendor = auth()->user();

        // Verify ownership
        if ($listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this listing.');
        }

        $listing->delete();

        return redirect()->route('vendor.listings.index')
            ->with('success', 'Listing deleted successfully.');
    }

    /**
     * Toggle listing active status.
     */
    public function toggleStatus(Listing $listing)
    {
        $vendor = auth()->user();

        // Verify ownership
        if ($listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this listing.');
        }

        $listing->update(['is_active' => !$listing->is_active]);

        return redirect()->back()
            ->with('success', 'Listing status updated.');
    }

    /**
     * Show form for featuring a listing (requires payment).
     */
    public function showFeatureForm(Listing $listing)
    {
        $vendor = auth()->user();

        // Verify ownership
        if ($listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this listing.');
        }

        // Get feature fee from config (in USD)
        $feeUsd = 100; // $100 USD to feature a listing

        // Get exchange rates
        $btcRate = ExchangeRate::where('crypto_shortname', 'btc')->first();
        $xmrRate = ExchangeRate::where('crypto_shortname', 'xmr')->first();

        // Calculate crypto amounts
        $btcAmount = $btcRate ? number_format($feeUsd / $btcRate->usd_rate, 8) : 'N/A';
        $xmrAmount = $xmrRate ? number_format($feeUsd / $xmrRate->usd_rate, 8) : 'N/A';

        // Get vendor's wallet balances
        $btcWallet = $vendor->btcWallet;
        $xmrWallet = Wallet::where('user_id', $vendor->id)
            ->where('currency', 'xmr')
            ->first();

        return view('vendor.listings.feature', compact(
            'listing',
            'feeUsd',
            'btcAmount',
            'xmrAmount',
            'btcWallet',
            'xmrWallet'
        ));
    }

    /**
     * Process feature listing payment.
     */
    public function featureListing(Request $request, Listing $listing)
    {
        $vendor = auth()->user();

        // Verify ownership
        if ($listing->user_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this listing.');
        }

        // Check if already featured
        if ($listing->is_featured) {
            return redirect()->back()
                ->with('error', 'This listing is already featured.');
        }

        $validated = $request->validate([
            'currency' => 'required|in:btc,xmr',
        ]);

        $currency = $validated['currency'];
        $feeUsd = 100; // $100 USD feature fee

        try {
            DB::beginTransaction();

            if ($currency === 'btc') {
                $this->processFeatureBitcoinPayment($listing, $vendor, $feeUsd);
            } else {
                $this->processFeatureMoneroPayment($listing, $vendor, $feeUsd);
            }

            DB::commit();

            return redirect()->route('vendor.listings.index')
                ->with('success', 'Feature request submitted! Listing will be featured once payment is confirmed.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Process Bitcoin payment for featuring listing.
     */
    private function processFeatureBitcoinPayment(Listing $listing, $vendor, $feeUsd)
    {
        // Get exchange rate
        $btcRate = ExchangeRate::where('crypto_shortname', 'btc')->firstOrFail();
        $requiredAmountBtc = $feeUsd / $btcRate->usd_rate;

        // Get vendor's Bitcoin wallet
        $vendorBtcWallet = $vendor->btcWallet;
        if (!$vendorBtcWallet) {
            throw new \Exception('Bitcoin wallet not found.');
        }

        // Lock wallet to prevent race conditions
        $vendorBtcWallet = BtcWallet::where('id', $vendorBtcWallet->id)->lockForUpdate()->first();

        // Check balance
        if ($vendorBtcWallet->balance < $requiredAmountBtc) {
            throw new \Exception('Insufficient Bitcoin balance. Required: ' . number_format($requiredAmountBtc, 8) . ' BTC');
        }

        // Get admin wallet
        $adminWalletName = config('fees.admin_btc_wallet_name', 'admin');
        $adminBtcWallet = BtcWallet::where('name', $adminWalletName)->firstOrFail();
        $adminAddress = $adminBtcWallet->getCurrentAddress();

        if (!$adminAddress) {
            $adminAddress = $adminBtcWallet->generateNewAddress();
        }

        // Send Bitcoin to admin wallet
        $txid = BitcoinRepository::sendBitcoin(
            $vendorBtcWallet->name,
            $adminAddress->address,
            $requiredAmountBtc
        );

        if (!$txid) {
            throw new \Exception('Failed to send Bitcoin transaction.');
        }

        // Create transaction record
        \App\Models\BtcTransaction::create([
            'btc_wallet_id' => $vendorBtcWallet->id,
            'btc_address_id' => null,
            'txid' => $txid,
            'type' => 'withdrawal',
            'amount' => $requiredAmountBtc,
            'fee' => 0,
            'confirmations' => 0,
            'status' => 'pending',
            'raw_transaction' => [
                'listing_id' => $listing->id,
                'purpose' => 'feature_listing',
                'fee_usd' => $feeUsd,
                'to_address' => $adminAddress->address,
            ],
        ]);

        // Mark listing as featured (pending admin confirmation)
        $listing->update([
            'is_featured' => true,
        ]);
    }

    /**
     * Process Monero payment for featuring listing.
     */
    private function processFeatureMoneroPayment(Listing $listing, $vendor, $feeUsd)
    {
        // Get exchange rate
        $xmrRate = ExchangeRate::where('crypto_shortname', 'xmr')->firstOrFail();
        $requiredAmountXmr = $feeUsd / $xmrRate->usd_rate;

        // Get vendor's Monero wallet
        $vendorXmrWallet = Wallet::where('user_id', $vendor->id)
            ->where('currency', 'xmr')
            ->lockForUpdate()
            ->firstOrFail();

        // Check balance
        if ($vendorXmrWallet->balance < $requiredAmountXmr) {
            throw new \Exception('Insufficient Monero balance. Required: ' . number_format($requiredAmountXmr, 8) . ' XMR');
        }

        // Get admin Monero wallet
        $adminXmrWallet = Wallet::where('user_id', function ($query) {
            $query->select('id')
                ->from('users')
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'admin');
                })
                ->limit(1);
        })
        ->where('currency', 'xmr')
        ->firstOrFail();

        // Get admin wallet address
        $adminAddress = $adminXmrWallet->address;

        // Send Monero to admin wallet
        $txid = MoneroRepository::transfer(
            $vendor->id,
            $adminAddress,
            $requiredAmountXmr,
            'Feature listing payment'
        );

        if (!$txid) {
            throw new \Exception('Failed to send Monero transaction.');
        }

        // Create wallet transaction record
        \App\Models\WalletTransaction::create([
            'wallet_id' => $vendorXmrWallet->id,
            'type' => 'withdrawal',
            'amount' => $requiredAmountXmr,
            'balance_after' => $vendorXmrWallet->balance - $requiredAmountXmr,
            'description' => 'Feature listing payment',
            'metadata' => [
                'listing_id' => $listing->id,
                'purpose' => 'feature_listing',
                'fee_usd' => $feeUsd,
                'txid' => $txid,
            ],
        ]);

        // Update wallet balance
        $vendorXmrWallet->decrement('balance', $requiredAmountXmr);

        // Mark listing as featured
        $listing->update([
            'is_featured' => true,
        ]);
    }
}
