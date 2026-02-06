<?php

namespace App\Http\Controllers;

use App\Models\BtcWallet;
use App\Models\BtcAddress;
use App\Models\BtcTransaction;
use App\Repositories\BitcoinRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class BitcoinController extends Controller
{
    /**
     * Show Bitcoin wallet dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $btcWallet = BitcoinRepository::getOrCreateWalletForUser($user);

        // Get recent transactions (without loading addresses for privacy)
        $recentTransactions = $btcWallet->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Calculate fresh stats from transactions (not stale fields)
        $walletStats = [
            'balance' => $btcWallet->getBalance(),
            'total_received' => $btcWallet->getTotalReceived(),
            'total_sent' => $btcWallet->getTotalSent(),
        ];

        return view('bitcoin.index', compact(
            'btcWallet',
            'recentTransactions',
            'walletStats'
        ));
    }

    /**
     * Show Bitcoin top-up page.
     */
    public function topup(Request $request)
    {
        $user = $request->user();
        $btcWallet = BitcoinRepository::getOrCreateWalletForUser($user);

        // Get or automatically generate current receiving address
        $currentAddress = $btcWallet->getCurrentAddress();

        // If no unused address exists, automatically generate one
        if (!$currentAddress) {
            try {
                $currentAddress = $btcWallet->generateNewAddress();
            } catch (\Exception $e) {
                // Log error and show user-friendly message
                \Log::error("Failed to generate Bitcoin address for user {$user->id}", ['exception' => $e]);

                return back()->with('error', 'Unable to generate Bitcoin address. Please contact support.');
            }
        }

        // Generate QR code as base64 encoded PNG
        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($currentAddress->getQrCodeData())
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(250)
                ->margin(10)
                ->build();

            // Convert to base64 data URI for inline display
            $qrCodeDataUri = 'data:' . $result->getMimeType() . ';base64,' . base64_encode($result->getString());
        } catch (\Exception $e) {
            \Log::error("Failed to generate QR code for user {$user->id}", ['exception' => $e]);
            $qrCodeDataUri = null;
        }

        return view('bitcoin.topup', compact(
            'btcWallet',
            'currentAddress',
            'qrCodeDataUri'
        ));
    }



    /**
     * Show withdrawal form and process withdrawal.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $user = $request->user();
        $btcWallet = BitcoinRepository::getOrCreateWalletForUser($user);

        // Validate withdrawal request
        $validated = $request->validate([
            'address' => [
                'required',
                'string',
                'regex:/^(bc1|bcrt1|tb1|[13])[a-zA-HJ-NP-Z0-9]{25,62}$/', // Bitcoin address format (mainnet, regtest, testnet)
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.00001',
                'max:' . $btcWallet->balance,
            ],
            'pin' => 'required|string|size:6',
        ], [
            'address.required' => 'Recipient address is required.',
            'address.regex' => 'Invalid Bitcoin address format.',
            'amount.required' => 'Amount is required.',
            'amount.min' => 'Minimum withdrawal amount is 0.00001 BTC.',
            'amount.max' => 'Insufficient balance. Available: ' . number_format($btcWallet->balance, 8) . ' BTC.',
            'pin.required' => 'Security PIN is required.',
            'pin.size' => 'PIN must be exactly 6 digits.',
        ]);

        // Verify PIN
        if (!Hash::check($validated['pin'], $user->pin)) {
            return back()
                ->withInput($request->except('pin'))
                ->withErrors(['pin' => 'Invalid security PIN.']);
        }

        // Additional security: Check if user is banned
        if ($user->is_banned) {
            return back()->withErrors(['error' => 'Your account is restricted from making withdrawals.']);
        }

        // Check minimum balance after withdrawal (keep dust amount)
        $remainingBalance = $btcWallet->balance - $validated['amount'];
        if ($remainingBalance > 0 && $remainingBalance < 0.00001) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'Withdrawal would leave insufficient balance. Please withdraw full amount or leave at least 0.00001 BTC.']);
        }

        try {
            // Use database transaction with pessimistic locking to prevent race conditions
            DB::beginTransaction();

            // Lock the wallet row to prevent concurrent withdrawals
            $btcWallet = BtcWallet::where('id', $btcWallet->id)->lockForUpdate()->first();

            // Re-validate balance after acquiring lock
            if ($btcWallet->balance < $validated['amount']) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'Insufficient balance. Another withdrawal may be in progress.']);
            }

            // Calculate USD value at time of withdrawal using helper function
            $usdValue = convert_crypto_to_usd($validated['amount'], 'btc');

            // Create withdrawal transaction record FIRST (prevents double-spend)
            $withdrawal = $btcWallet->transactions()->create([
                'btc_address_id' => null, // Withdrawal has no receiving address in our system
                'txid' => null, // Will be set after blockchain broadcast
                'type' => 'withdrawal',
                'amount' => $validated['amount'],
                'usd_value' => $usdValue,
                'fee' => 0, // Will be updated from actual transaction
                'confirmations' => 0,
                'status' => 'pending',
                'raw_transaction' => [
                    'recipient_address' => $validated['address'],
                    'requested_at' => now()->toIso8601String(),
                    'requested_by' => $user->id,
                ],
            ]);

            // Update balance to reflect pending withdrawal
            $btcWallet->updateBalance();

            DB::commit();

            DB::commit();

            // Broadcast to Bitcoin network (outside transaction to avoid long locks)
            $repository = new BitcoinRepository();
            $txid = $repository->sendBitcoin(
                $user->username_pri,
                $validated['address'],
                $validated['amount']
            );

            if (!$txid) {
                // Rollback if broadcast fails - delete the withdrawal record
                $withdrawal->delete();
                $btcWallet->updateBalance();

                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Failed to broadcast transaction. Please try again or contact support.']);
            }

            // Update withdrawal with actual txid
            $withdrawal->update(['txid' => $txid]);

            \Log::info("Bitcoin withdrawal initiated", [
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'address' => $validated['address'],
                'txid' => $txid,
            ]);

            return redirect()->route('bitcoin.index')
                ->with('success', 'Withdrawal initiated successfully.');

        } catch (\Exception $e) {
            \Log::error("Bitcoin withdrawal failed", [
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'address' => $validated['address'],
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Withdrawal failed. Please try again or contact support.']);
        }
    }
}
