<?php

namespace App\Http\Controllers;

use App\Models\BtcWallet;
use App\Models\BtcTransaction;
use App\Models\User;
use App\Repositories\BitcoinRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Display the vendor's public profile.
     *
     * @param User $user
     * @return \Illuminate\Contracts\View\View
     */
    public function show(User $user)
    {
        // Verify the user is a vendor
        if (!$user->hasRole('vendor')) {
            abort(404, 'Vendor not found');
        }

        // Load vendor data with relationships
        $user->load([
            'listings' => function($query) {
                $query->where('is_active', true)
                      ->with('media')
                      ->latest()
                      ->take(12);
            },
            'receivedReviews' => function($query) {
                $query->with('user')
                      ->latest()
                      ->take(10);
            }
        ]);

        // Get rating statistics
        $ratingBreakdown = $user->getRatingBreakdown();
        $totalReviews = $user->getTotalReviews();
        
        // Get vendor statistics
        $activeListingsCount = $user->listings()->where('is_active', true)->count();
        $totalListingsCount = $user->listings()->count();
        
        // Get total views from all listings
        $totalViews = $user->listings()->sum('views');
        
        // Get completed orders count (orders where this vendor's listings were purchased and completed)
        $completedOrders = \App\Models\Order::whereHas('listing', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'completed')->count();
        
        // Get dispute statistics by status
        $disputeStats = [
            'total' => \App\Models\Dispute::whereHas('order.listing', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
            'open' => \App\Models\Dispute::whereHas('order.listing', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'open')->count(),
            'under_review' => \App\Models\Dispute::whereHas('order.listing', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'under_review')->count(),
            'waiting_vendor' => \App\Models\Dispute::whereHas('order.listing', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'waiting_vendor')->count(),
            'waiting_buyer' => \App\Models\Dispute::whereHas('order.listing', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'waiting_buyer')->count(),
            'escalated' => \App\Models\Dispute::whereHas('order.listing', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'escalated')->count(),
            'resolved' => \App\Models\Dispute::whereHas('order.listing', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'resolved')->count(),
            'closed' => \App\Models\Dispute::whereHas('order.listing', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'closed')->count(),
        ];
        
        // Active disputes (not resolved or closed)
        $disputedOrders = $disputeStats['open'] + $disputeStats['under_review'] + 
                         $disputeStats['waiting_vendor'] + $disputeStats['waiting_buyer'] + 
                         $disputeStats['escalated'];

        return view('vendors.show', compact(
            'user',
            'ratingBreakdown',
            'totalReviews',
            'activeListingsCount',
            'totalListingsCount',
            'totalViews',
            'completedOrders',
            'disputedOrders',
            'disputeStats'
        ));
    }

    public function showConvertForm(Request $request)
    {
        // TODO: policy to restrict access to this page if is already a vendor
        return view('vendors.create', [
            'balance' => $request->user()->getBalance()
        ]);
    }

    public function convert(Request $request)
    {
        // TODO: policy to restrict access to this page if is already a vendor
        $user = $request->user();

        $request->validate([
            'currency' => 'required|in:btc,xmr',
            'terms' => 'required|accepted'
        ]);

        // Get conversion fee from config
        $feeUsd = config('fees.vendor_conversion_usd', 1000);
        $requiredAmount = convert_usd_to_crypto($feeUsd, $request->currency);
        $decimals = $request->currency === 'btc' ? 8 : 12;

        if ($request->currency === 'btc') {
            return $this->convertWithBitcoin($user, $requiredAmount, $decimals);
        } elseif ($request->currency === 'xmr') {
            return $this->convertWithMonero($user, $requiredAmount, $decimals);
        }

        return back()->withErrors([
            'currency' => 'Invalid currency selected.'
        ]);
    }

    /**
     * Process vendor conversion with Bitcoin.
     */
    private function convertWithBitcoin($user, $requiredAmountBtc, $decimals)
    {
        // Get user's BTC wallet
        $userBtcWallet = $user->btcWallet;
        if (!$userBtcWallet) {
            return back()->withErrors([
                'currency' => 'You do not have a Bitcoin wallet configured.'
            ]);
        }

        // Check balance
        if ($userBtcWallet->balance < $requiredAmountBtc) {
            return back()->withErrors([
                'currency' => "Insufficient balance. Required: " . number_format($requiredAmountBtc, $decimals) 
                    . " BTC, Available: " . number_format($userBtcWallet->balance, $decimals) . " BTC"
            ]);
        }

        // Get admin wallet
        $adminWalletName = config('fees.admin_btc_wallet_name', 'admin');
        $adminBtcWallet = BtcWallet::where('name', $adminWalletName)->first();
        
        if (!$adminBtcWallet) {
            \Log::error("Admin BTC wallet not found: {$adminWalletName}");
            return back()->withErrors([
                'error' => 'System configuration error. Please contact support.'
            ]);
        }

        // Get admin's current address (or generate new one)
        $adminAddress = $adminBtcWallet->getCurrentAddress();
        if (!$adminAddress) {
            $adminAddress = $adminBtcWallet->generateNewAddress();
        }

        try {
            DB::beginTransaction();
            
            // Lock user's wallet to prevent race conditions
            $userBtcWallet = BtcWallet::where('id', $userBtcWallet->id)->lockForUpdate()->first();
            
            // Re-validate balance after lock
            if ($userBtcWallet->balance < $requiredAmountBtc) {
                DB::rollBack();
                return back()->withErrors([
                    'currency' => 'Insufficient balance. Another transaction may be in progress.'
                ]);
            }

            // Send Bitcoin to admin wallet
            $txid = BitcoinRepository::sendBitcoin(
                $userBtcWallet->name,
                $adminAddress->address,
                $requiredAmountBtc
            );

            if (!$txid) {
                DB::rollBack();
                return back()->withErrors([
                    'error' => 'Failed to process Bitcoin transaction. Please try again or contact support.'
                ]);
            }

            // Create withdrawal transaction for user
            BtcTransaction::create([
                'btc_wallet_id' => $userBtcWallet->id,
                'btc_address_id' => null,
                'txid' => $txid,
                'type' => 'withdrawal',
                'amount' => $requiredAmountBtc,
                'fee' => 0,
                'confirmations' => 0,
                'status' => 'pending',
                'raw_transaction' => [
                    'purpose' => 'vendor_conversion',
                    'to_address' => $adminAddress->address,
                    'admin_wallet_id' => $adminBtcWallet->id,
                ],
            ]);

            // Update user's balance
            $userBtcWallet->updateBalance();

            // Upgrade to vendor
            $user->update([
                'vendor_level' => 1,
                'vendor_since' => now()
            ]);

            // Assign vendor role
            $user->assignRoleByName('vendor');

            DB::commit();

            \Log::info("User {$user->id} converted to vendor with BTC", [
                'amount_btc' => $requiredAmountBtc,
                'txid' => $txid,
                'admin_address' => $adminAddress->address,
            ]);

            return redirect()->route('home')
                ->with('success', 'Vendor account activated successfully! Transaction is being confirmed.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Vendor conversion failed for user {$user->id}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors([
                'error' => 'Failed to process vendor conversion. Please contact support if the issue persists.'
            ]);
        }
    }

    /**
     * Process vendor conversion with Monero.
     */
    private function convertWithMonero($user, $requiredAmountXmr, $decimals)
    {
        // Get user's XMR wallet
        $userXmrWallet = $user->xmrWallet;
        if (!$userXmrWallet) {
            return back()->withErrors([
                'currency' => 'You do not have a Monero wallet configured.'
            ]);
        }

        // Check balance
        if ($userXmrWallet->balance < $requiredAmountXmr) {
            return back()->withErrors([
                'currency' => "Insufficient balance. Required: " . number_format($requiredAmountXmr, $decimals) 
                    . " XMR, Available: " . number_format($userXmrWallet->balance, $decimals) . " XMR"
            ]);
        }

        // Get admin wallet
        $adminWalletName = config('fees.admin_xmr_wallet_name', 'admin');
        $adminXmrWallet = \App\Models\XmrWallet::where('name', $adminWalletName)->first();
        
        if (!$adminXmrWallet) {
            \Log::error("Admin XMR wallet not found: {$adminWalletName}");
            return back()->withErrors([
                'error' => 'System configuration error. Please contact support.'
            ]);
        }

        // Get admin's current address (or generate new one)
        $adminAddress = $adminXmrWallet->getCurrentAddress();
        if (!$adminAddress) {
            $adminAddress = $adminXmrWallet->generateNewAddress();
        }

        try {
            DB::beginTransaction();
            
            // Lock user's wallet to prevent race conditions
            $userXmrWallet = \App\Models\XmrWallet::where('id', $userXmrWallet->id)->lockForUpdate()->first();
            
            // Re-validate balance after lock
            if ($userXmrWallet->balance < $requiredAmountXmr) {
                DB::rollBack();
                return back()->withErrors([
                    'currency' => 'Insufficient balance. Another transaction may be in progress.'
                ]);
            }

            // Send Monero to admin wallet
            $txid = \App\Repositories\MoneroRepository::transfer(
                $userXmrWallet->name,
                $adminAddress->address,
                $requiredAmountXmr
            );

            if (!$txid) {
                DB::rollBack();
                return back()->withErrors([
                    'error' => 'Failed to process Monero transaction. Please try again or contact support.'
                ]);
            }

            // Create withdrawal transaction for user
            \App\Models\XmrTransaction::create([
                'xmr_wallet_id' => $userXmrWallet->id,
                'xmr_address_id' => null,
                'txid' => $txid,
                'type' => 'withdrawal',
                'amount' => $requiredAmountXmr,
                'fee' => 0,
                'confirmations' => 0,
                'unlock_time' => 0,
                'status' => 'pending',
                'raw_transaction' => [
                    'purpose' => 'vendor_conversion',
                    'to_address' => $adminAddress->address,
                    'admin_wallet_id' => $adminXmrWallet->id,
                ],
            ]);

            // Update user's balance
            $userXmrWallet->updateBalance();

            // Upgrade to vendor
            $user->update([
                'vendor_level' => 1,
                'vendor_since' => now()
            ]);

            // Assign vendor role
            $user->assignRoleByName('vendor');

            DB::commit();

            \Log::info("User {$user->id} converted to vendor with XMR", [
                'amount_xmr' => $requiredAmountXmr,
                'txid' => $txid,
                'admin_address' => $adminAddress->address,
            ]);

            return redirect()->route('home')
                ->with('success', 'Vendor account activated successfully! Transaction is being confirmed.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Vendor conversion failed for user {$user->id}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors([
                'error' => 'Failed to process vendor conversion. Please contact support if the issue persists.'
            ]);
        }
    }
}
