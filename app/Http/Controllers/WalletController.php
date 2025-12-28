<?php

namespace App\Http\Controllers;

use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Ensure user has Bitcoin wallet
        try {
            $btcWallet = BitcoinRepository::getOrCreateWalletForUser($user);

            // Update Bitcoin balance from RPC when user visits wallet page
            if ($btcWallet) {
                try {
                    $btcWallet->updateBalance();
                } catch (\Exception $e) {
                    \Log::warning("Failed to update Bitcoin balance for user {$user->id}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to initialize Bitcoin wallet for user {$user->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Create a placeholder wallet object to prevent view errors
            $btcWallet = null;

            // Optionally flash an error message
            session()->flash('warning', 'Bitcoin wallet is temporarily unavailable. Please contact support if this persists.');
        }

        // Ensure user has Monero wallet
        try {
            $xmrWallet = MoneroRepository::getOrCreateWalletForUser($user);

            // Update Monero balance from RPC when user visits wallet page
            if ($xmrWallet) {
                try {
                    $xmrWallet->updateBalance();
                } catch (\Exception $e) {
                    \Log::warning("Failed to update Monero balance for user {$user->id}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to initialize Monero wallet for user {$user->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Create a placeholder wallet object to prevent view errors
            $xmrWallet = null;

            // Optionally flash an error message
            session()->flash('warning', 'Monero wallet is temporarily unavailable. Please contact support if this persists.');
        }

        // Get balance (now without slow RPC calls since we just updated above)
        $balance = $user->getBalance();

        return view('wallets.index', compact('balance', 'btcWallet', 'xmrWallet'));
    }
}
