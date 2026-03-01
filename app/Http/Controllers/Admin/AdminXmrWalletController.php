<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\XmrWallet;
use App\Models\XmrTransaction;
use App\Models\AuditLog;
use App\Repositories\MoneroRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminXmrWalletController extends Controller
{
    /**
     * List all XMR wallets in the system.
     */
    public function index(Request $request)
    {
        $query = XmrWallet::with('user');

        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'user') {
                $query->whereNotNull('user_id');
            } elseif ($request->type === 'escrow') {
                $query->whereNull('user_id');
            }
        }

        // Search by wallet name or user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('username_pub', 'like', "%{$search}%");
                  });
            });
        }

        $wallets = $query->orderByDesc('updated_at')->paginate(25)->withQueryString();

        // Summary stats
        $totalBalance = XmrWallet::sum('balance');
        $totalUnlocked = XmrWallet::sum('unlocked_balance');
        $totalWallets = XmrWallet::count();
        $userWallets = XmrWallet::whereNotNull('user_id')->count();
        $escrowWallets = XmrWallet::whereNull('user_id')->count();

        // Check RPC status
        $rpcAvailable = false;
        try {
            $repo = new MoneroRepository();
            $rpcAvailable = $repo->isRpcAvailable();
        } catch (\Exception $e) {
            // RPC not available
        }

        return view('admin.wallets.xmr.index', compact(
            'wallets', 'totalBalance', 'totalUnlocked', 'totalWallets',
            'userWallets', 'escrowWallets', 'rpcAvailable'
        ));
    }

    /**
     * Show a single XMR wallet with transactions.
     */
    public function show(Request $request, XmrWallet $xmrWallet)
    {
        $xmrWallet->load(['user', 'addresses']);

        $transactions = XmrTransaction::where('xmr_wallet_id', $xmrWallet->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        // Optionally refresh balance from RPC
        $rpcBalance = null;
        if ($request->has('refresh')) {
            try {
                $repo = new MoneroRepository();
                $rpcBalance = $repo->getWalletBalance($xmrWallet);
                $xmrWallet->update([
                    'balance' => $rpcBalance['balance'],
                    'unlocked_balance' => $rpcBalance['unlocked_balance'],
                ]);
                $xmrWallet->refresh();
            } catch (\Exception $e) {
                session()->flash('warning', 'Failed to refresh RPC balance: ' . $e->getMessage());
            }
        }

        return view('admin.wallets.xmr.show', compact('xmrWallet', 'transactions', 'rpcBalance'));
    }

    /**
     * Show the transfer form for an XMR wallet.
     */
    public function transferForm(XmrWallet $xmrWallet)
    {
        $admin = auth()->user();

        if (empty($admin->pgp_pub_key)) {
            return redirect()->route('admin.wallets.xmr.show', $xmrWallet)
                ->withErrors(['error' => 'You must have a verified PGP key to make transfers. Set up your PGP key in your profile first.']);
        }

        $xmrWallet->load('user');

        return view('admin.wallets.xmr.transfer', compact('xmrWallet'));
    }

    /**
     * Initiate PGP challenge for an XMR transfer.
     */
    public function transferInitiate(Request $request, XmrWallet $xmrWallet)
    {
        $admin = auth()->user();

        if (empty($admin->pgp_pub_key)) {
            return redirect()->route('admin.wallets.xmr.show', $xmrWallet)
                ->withErrors(['error' => 'PGP key required for transfers.']);
        }

        $validated = $request->validate([
            'address' => 'required|string|min:95|max:106',
            'amount' => 'required|numeric|min:0.000000000001|max:' . $xmrWallet->unlocked_balance,
        ]);

        if ($xmrWallet->unlocked_balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient unlocked balance.'])->withInput();
        }

        // Generate PGP challenge
        $verificationCode = Str::upper(Str::random(12));

        $challengeMessage = strtoupper(config('app.name')) . " ADMIN XMR TRANSFER AUTHORIZATION\n\n"
            . "Admin: {$admin->username_pub}\n"
            . "From Wallet: {$xmrWallet->name}\n"
            . "To Address: {$validated['address']}\n"
            . "Amount: {$validated['amount']} XMR\n"
            . "Verification Code: {$verificationCode}\n"
            . "Timestamp: " . now()->toDateTimeString() . "\n\n"
            . "Decrypt this message and submit the verification code to authorize this transfer.\n"
            . "This code expires in 10 minutes.";

        try {
            $encryptedMessage = $this->encryptWithPgp($challengeMessage, trim($admin->pgp_pub_key));
        } catch (\Exception $e) {
            Log::error('Admin XMR transfer PGP encryption failed', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to generate PGP challenge. Check your PGP key.'])->withInput();
        }

        // Store challenge in session
        $request->session()->put('admin_xmr_transfer', [
            'wallet_id' => $xmrWallet->id,
            'address' => $validated['address'],
            'amount' => $validated['amount'],
            'code' => $verificationCode,
            'encrypted_message' => $encryptedMessage,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'admin_id' => $admin->id,
        ]);

        return redirect()->route('admin.wallets.xmr.transfer-verify', $xmrWallet);
    }

    /**
     * Show the PGP verification form for an XMR transfer.
     */
    public function transferVerifyForm(Request $request, XmrWallet $xmrWallet)
    {
        $challenge = $request->session()->get('admin_xmr_transfer');

        if (!$challenge || $challenge['wallet_id'] !== $xmrWallet->id) {
            return redirect()->route('admin.wallets.xmr.transfer', $xmrWallet)
                ->withErrors(['error' => 'No active transfer challenge. Please start again.']);
        }

        if ($challenge['expires_at'] < now()->timestamp) {
            $request->session()->forget('admin_xmr_transfer');
            return redirect()->route('admin.wallets.xmr.transfer', $xmrWallet)
                ->withErrors(['error' => 'Transfer challenge expired. Please start again.']);
        }

        $xmrWallet->load('user');

        return view('admin.wallets.xmr.verify', [
            'xmrWallet' => $xmrWallet,
            'encryptedMessage' => $challenge['encrypted_message'],
            'address' => $challenge['address'],
            'amount' => $challenge['amount'],
            'attemptsRemaining' => 5 - $challenge['attempts'],
        ]);
    }

    /**
     * Verify PGP code and execute the XMR transfer.
     */
    public function transferExecute(Request $request, XmrWallet $xmrWallet)
    {
        $challenge = $request->session()->get('admin_xmr_transfer');

        if (!$challenge || $challenge['wallet_id'] !== $xmrWallet->id) {
            return redirect()->route('admin.wallets.xmr.transfer', $xmrWallet)
                ->withErrors(['error' => 'No active transfer challenge.']);
        }

        if ($challenge['expires_at'] < now()->timestamp) {
            $request->session()->forget('admin_xmr_transfer');
            return redirect()->route('admin.wallets.xmr.transfer', $xmrWallet)
                ->withErrors(['error' => 'Transfer challenge expired.']);
        }

        if ($challenge['attempts'] >= 5) {
            $request->session()->forget('admin_xmr_transfer');
            return redirect()->route('admin.wallets.xmr.transfer', $xmrWallet)
                ->withErrors(['error' => 'Maximum verification attempts exceeded.']);
        }

        $validated = $request->validate([
            'verification_code' => 'required|string',
        ]);

        $submittedCode = strtoupper(trim($validated['verification_code']));

        if ($submittedCode !== $challenge['code']) {
            $challenge['attempts']++;
            $request->session()->put('admin_xmr_transfer', $challenge);
            $attemptsLeft = 5 - $challenge['attempts'];

            if ($attemptsLeft <= 0) {
                $request->session()->forget('admin_xmr_transfer');
                return redirect()->route('admin.wallets.xmr.transfer', $xmrWallet)
                    ->withErrors(['error' => 'Maximum verification attempts exceeded.']);
            }

            return back()->withErrors([
                'verification_code' => "Incorrect code. {$attemptsLeft} attempt(s) remaining.",
            ]);
        }

        // PGP verified — execute transfer
        $request->session()->forget('admin_xmr_transfer');

        try {
            $repo = new MoneroRepository();

            if (!$repo->isRpcAvailable()) {
                throw new \Exception('Monero RPC is not available. Please try again later.');
            }

            $result = $repo->transfer($xmrWallet, $challenge['address'], $challenge['amount']);

            // Log the transfer
            AuditLog::log('admin_xmr_transfer', $xmrWallet->id, [
                'admin_id' => auth()->id(),
                'wallet_name' => $xmrWallet->name,
                'to_address' => $challenge['address'],
                'amount' => $challenge['amount'],
                'txid' => $result['tx_hash'],
                'fee' => $result['fee'],
            ]);

            Log::info('Admin XMR transfer executed', [
                'admin_id' => auth()->id(),
                'wallet_id' => $xmrWallet->id,
                'to' => $challenge['address'],
                'amount' => $challenge['amount'],
                'tx_hash' => $result['tx_hash'],
                'fee' => $result['fee'],
            ]);

            return redirect()->route('admin.wallets.xmr.show', $xmrWallet)
                ->with('success', "Transfer sent. TX: {$result['tx_hash']} (Fee: {$result['fee']} XMR)");

        } catch (\Exception $e) {
            Log::error('Admin XMR transfer failed', [
                'admin_id' => auth()->id(),
                'wallet_id' => $xmrWallet->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.wallets.xmr.show', $xmrWallet)
                ->withErrors(['error' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Encrypt a message with a PGP public key.
     */
    private function encryptWithPgp(string $message, string $publicKey): string
    {
        if (!extension_loaded('gnupg')) {
            throw new \Exception('PGP encryption requires the php-gnupg extension.');
        }

        $tempGpgHome = '/tmp/teardrop_gpg_' . uniqid('', true);

        try {
            if (!mkdir($tempGpgHome, 0700, true)) {
                throw new \Exception('Failed to create temporary GPG directory');
            }

            putenv("GNUPGHOME={$tempGpgHome}");

            $publicKey = trim(str_replace(["\r\n", "\r"], "\n", $publicKey));

            $gpg = new \gnupg();
            $gpg->seterrormode(\gnupg::ERROR_EXCEPTION);
            $gpg->setarmor(1);

            $importResult = $gpg->import($publicKey);
            if (!$importResult) {
                throw new \Exception('Key import failed.');
            }

            $fingerprint = $importResult['fingerprint'] ?? null;
            if (empty($fingerprint)) {
                $allKeys = $gpg->keyinfo("");
                if (!empty($allKeys)) {
                    $lastKey = end($allKeys);
                    $fingerprint = $lastKey['subkeys'][0]['fingerprint'] ?? null;
                }
            }

            if (empty($fingerprint)) {
                throw new \Exception('Failed to extract fingerprint.');
            }

            $gpg->addencryptkey($fingerprint);
            $encrypted = $gpg->encrypt($message);

            if (!$encrypted) {
                throw new \Exception('Encryption failed.');
            }

            return $encrypted;

        } finally {
            if (is_dir($tempGpgHome)) {
                array_map('unlink', glob("{$tempGpgHome}/*"));
                @rmdir($tempGpgHome);
            }
            putenv("GNUPGHOME");
        }
    }
}
