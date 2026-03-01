<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BtcWallet;
use App\Models\BtcTransaction;
use App\Models\AuditLog;
use App\Repositories\BitcoinRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminBtcWalletController extends Controller
{
    /**
     * List all BTC wallets in the system.
     */
    public function index(Request $request)
    {
        $query = BtcWallet::with('user');

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
        $totalBalance = BtcWallet::sum('balance');
        $totalWallets = BtcWallet::count();
        $userWallets = BtcWallet::whereNotNull('user_id')->count();
        $escrowWallets = BtcWallet::whereNull('user_id')->count();

        return view('admin.wallets.btc.index', compact(
            'wallets', 'totalBalance', 'totalWallets', 'userWallets', 'escrowWallets'
        ));
    }

    /**
     * Show a single BTC wallet with transactions.
     */
    public function show(BtcWallet $btcWallet)
    {
        $btcWallet->load(['user', 'addresses']);

        $transactions = BtcTransaction::where('btc_wallet_id', $btcWallet->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.wallets.btc.show', compact('btcWallet', 'transactions'));
    }

    /**
     * Show the transfer form for a BTC wallet.
     */
    public function transferForm(BtcWallet $btcWallet)
    {
        $admin = auth()->user();

        if (empty($admin->pgp_pub_key)) {
            return redirect()->route('admin.wallets.btc.show', $btcWallet)
                ->withErrors(['error' => 'You must have a verified PGP key to make transfers. Set up your PGP key in your profile first.']);
        }

        $btcWallet->load('user');

        return view('admin.wallets.btc.transfer', compact('btcWallet'));
    }

    /**
     * Initiate PGP challenge for a BTC transfer.
     */
    public function transferInitiate(Request $request, BtcWallet $btcWallet)
    {
        $admin = auth()->user();

        if (empty($admin->pgp_pub_key)) {
            return redirect()->route('admin.wallets.btc.show', $btcWallet)
                ->withErrors(['error' => 'PGP key required for transfers.']);
        }

        $validated = $request->validate([
            'address' => 'required|string|min:26|max:62',
            'amount' => 'required|numeric|min:0.00000001|max:' . $btcWallet->balance,
        ]);

        // Check balance
        if ($btcWallet->balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient wallet balance.'])->withInput();
        }

        // Generate PGP challenge
        $verificationCode = Str::upper(Str::random(12));

        $challengeMessage = strtoupper(config('app.name')) . " ADMIN BTC TRANSFER AUTHORIZATION\n\n"
            . "Admin: {$admin->username_pub}\n"
            . "From Wallet: {$btcWallet->name}\n"
            . "To Address: {$validated['address']}\n"
            . "Amount: {$validated['amount']} BTC\n"
            . "Verification Code: {$verificationCode}\n"
            . "Timestamp: " . now()->toDateTimeString() . "\n\n"
            . "Decrypt this message and submit the verification code to authorize this transfer.\n"
            . "This code expires in 10 minutes.";

        try {
            $encryptedMessage = $this->encryptWithPgp($challengeMessage, trim($admin->pgp_pub_key));
        } catch (\Exception $e) {
            Log::error('Admin BTC transfer PGP encryption failed', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to generate PGP challenge. Check your PGP key.'])->withInput();
        }

        // Store challenge in session
        $request->session()->put('admin_btc_transfer', [
            'wallet_id' => $btcWallet->id,
            'address' => $validated['address'],
            'amount' => $validated['amount'],
            'code' => $verificationCode,
            'encrypted_message' => $encryptedMessage,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'admin_id' => $admin->id,
        ]);

        return redirect()->route('admin.wallets.btc.transfer-verify', $btcWallet);
    }

    /**
     * Show the PGP verification form for a BTC transfer.
     */
    public function transferVerifyForm(Request $request, BtcWallet $btcWallet)
    {
        $challenge = $request->session()->get('admin_btc_transfer');

        if (!$challenge || $challenge['wallet_id'] !== $btcWallet->id) {
            return redirect()->route('admin.wallets.btc.transfer', $btcWallet)
                ->withErrors(['error' => 'No active transfer challenge. Please start again.']);
        }

        if ($challenge['expires_at'] < now()->timestamp) {
            $request->session()->forget('admin_btc_transfer');
            return redirect()->route('admin.wallets.btc.transfer', $btcWallet)
                ->withErrors(['error' => 'Transfer challenge expired. Please start again.']);
        }

        $btcWallet->load('user');

        return view('admin.wallets.btc.verify', [
            'btcWallet' => $btcWallet,
            'encryptedMessage' => $challenge['encrypted_message'],
            'address' => $challenge['address'],
            'amount' => $challenge['amount'],
            'attemptsRemaining' => 5 - $challenge['attempts'],
        ]);
    }

    /**
     * Verify PGP code and execute the BTC transfer.
     */
    public function transferExecute(Request $request, BtcWallet $btcWallet)
    {
        $challenge = $request->session()->get('admin_btc_transfer');

        if (!$challenge || $challenge['wallet_id'] !== $btcWallet->id) {
            return redirect()->route('admin.wallets.btc.transfer', $btcWallet)
                ->withErrors(['error' => 'No active transfer challenge.']);
        }

        if ($challenge['expires_at'] < now()->timestamp) {
            $request->session()->forget('admin_btc_transfer');
            return redirect()->route('admin.wallets.btc.transfer', $btcWallet)
                ->withErrors(['error' => 'Transfer challenge expired.']);
        }

        if ($challenge['attempts'] >= 5) {
            $request->session()->forget('admin_btc_transfer');
            return redirect()->route('admin.wallets.btc.transfer', $btcWallet)
                ->withErrors(['error' => 'Maximum verification attempts exceeded.']);
        }

        $validated = $request->validate([
            'verification_code' => 'required|string',
        ]);

        $submittedCode = strtoupper(trim($validated['verification_code']));

        if ($submittedCode !== $challenge['code']) {
            $challenge['attempts']++;
            $request->session()->put('admin_btc_transfer', $challenge);
            $attemptsLeft = 5 - $challenge['attempts'];

            if ($attemptsLeft <= 0) {
                $request->session()->forget('admin_btc_transfer');
                return redirect()->route('admin.wallets.btc.transfer', $btcWallet)
                    ->withErrors(['error' => 'Maximum verification attempts exceeded.']);
            }

            return back()->withErrors([
                'verification_code' => "Incorrect code. {$attemptsLeft} attempt(s) remaining.",
            ]);
        }

        // PGP verified — execute transfer
        $request->session()->forget('admin_btc_transfer');

        try {
            $txid = BitcoinRepository::sendBitcoin(
                $btcWallet,
                $challenge['address'],
                $challenge['amount']
            );

            // Log the transfer
            AuditLog::log('admin_btc_transfer', $btcWallet->id, [
                'admin_id' => auth()->id(),
                'wallet_name' => $btcWallet->name,
                'to_address' => $challenge['address'],
                'amount' => $challenge['amount'],
                'txid' => $txid,
            ]);

            Log::info('Admin BTC transfer executed', [
                'admin_id' => auth()->id(),
                'wallet_id' => $btcWallet->id,
                'to' => $challenge['address'],
                'amount' => $challenge['amount'],
                'txid' => $txid,
            ]);

            return redirect()->route('admin.wallets.btc.show', $btcWallet)
                ->with('success', "Transfer sent. TXID: {$txid}");

        } catch (\Exception $e) {
            Log::error('Admin BTC transfer failed', [
                'admin_id' => auth()->id(),
                'wallet_id' => $btcWallet->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.wallets.btc.show', $btcWallet)
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
            // Cleanup temp GPG home
            if (is_dir($tempGpgHome)) {
                array_map('unlink', glob("{$tempGpgHome}/*"));
                @rmdir($tempGpgHome);
            }
            putenv("GNUPGHOME");
        }
    }
}
