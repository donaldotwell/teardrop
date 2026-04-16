<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Load wallets from DB only — no RPC, no creation.
        // Wallets are created lazily on first visit to the topup page.
        $btcWallet = $user->btcWallet;
        $xmrWallet = $user->xmrWallet;

        $balance = $user->getBalance();

        return view('wallets.index', compact('balance', 'btcWallet', 'xmrWallet'));
    }
}
