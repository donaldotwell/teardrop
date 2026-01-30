<?php

namespace App\Http\Controllers;

use App\Models\XmrWallet;
use App\Models\XmrTransaction;
use App\Repositories\MoneroRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class MoneroController extends Controller
{
    /**
     * Show Monero wallet dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $xmrWallet = MoneroRepository::getOrCreateWalletForUser($user);

        // Get recent transactions
        $recentTransactions = $xmrWallet->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $balance = $user->getBalance();

        return view('monero.index', compact(
            'xmrWallet',
            'recentTransactions',
            'balance'
        ));
    }

    /**
     * Show Monero top-up page.
     */
    public function topup(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $xmrWallet = MoneroRepository::getOrCreateWalletForUser($user);

        // Get or automatically generate current receiving address
        $currentAddress = $xmrWallet->getCurrentAddress();

        // If no unused address exists, automatically generate one
        if (!$currentAddress) {
            try {
                $currentAddress = $xmrWallet->generateNewAddress();
            } catch (\Exception $e) {
                // Log error and show user-friendly message
                \Log::error("Failed to generate Monero address for user {$user->id}", ['exception' => $e]);

                return back()->with('error', 'Unable to generate Monero address. Please contact support.');
            }
        }

        // Generate QR code as base64 encoded PNG (matching BitcoinController)
        try {
            $result = \Endroid\QrCode\Builder\Builder::create()
                ->writer(new \Endroid\QrCode\Writer\PngWriter())
                ->data($currentAddress->getQrCodeData())
                ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(250)
                ->margin(10)
                ->build();
            
            // Convert to base64 data URI for inline display
            $qrCodeDataUri = 'data:' . $result->getMimeType() . ';base64,' . base64_encode($result->getString());
        } catch (\Exception $e) {
            \Log::error("Failed to generate Monero QR code for user {$user->id}", [
                'exception' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                ]);
            $qrCodeDataUri = null;
        }

        // Get current XMR price from database
        $xmrRate = \App\Models\ExchangeRate::where('crypto_shortname', 'xmr')->first();
        $xmrPrice = $xmrRate ? $xmrRate->usd_rate : 0;

        return view('monero.topup', compact(
            'xmrWallet',
            'currentAddress',
            'qrCodeDataUri',
            'xmrPrice'
        ));
    }

    /**
     * Process Monero withdrawal.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $user = $request->user();
        $xmrWallet = MoneroRepository::getOrCreateWalletForUser($user);

        // Validate withdrawal request
        $validated = $request->validate([
            'address' => [
                'required',
                'string',
                'regex:/^[489AB][0-9A-Za-z]{94,106}$/', // Monero address format (mainnet and testnet)
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.000000000001', // Minimum XMR amount
                'max:' . $xmrWallet->unlocked_balance,
            ],
            'pin' => 'required|string|size:6',
        ], [
            'address.required' => 'Recipient address is required.',
            'address.regex' => 'Invalid Monero address format.',
            'amount.required' => 'Amount is required.',
            'amount.min' => 'Minimum withdrawal amount is 0.000000000001 XMR.',
            'amount.max' => 'Insufficient unlocked balance. Available: ' . number_format($xmrWallet->unlocked_balance, 12) . ' XMR.',
            'pin.required' => 'Security PIN is required.',
            'pin.size' => 'PIN must be exactly 6 digits.',
        ]);

        // Ensure amount is treated as XMR float, not atomic units
        $validated['amount'] = (float) $validated['amount'];

        // Verify PIN
        if (!Hash::check($validated['pin'], $user->pin)) {
            return back()
                ->withInput($request->except('pin'))
                ->withErrors(['pin' => 'Invalid security PIN.']);
        }

        // Check if user is banned
        if ($user->is_banned) {
            return back()->withErrors(['error' => 'Your account is restricted from making withdrawals.']);
        }

        try {
            DB::beginTransaction();

            // Lock wallet to prevent race conditions
            $xmrWallet = XmrWallet::where('id', $xmrWallet->id)->lockForUpdate()->first();

            // Re-validate balance after lock
            if ($xmrWallet->unlocked_balance < $validated['amount']) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'Insufficient unlocked balance. Another transaction may be in progress.']);
            }

            // Calculate USD value at time of withdrawal
            $usdValue = null;
            try {
                $xmrRate = \App\Models\ExchangeRate::where('crypto_shortname', 'xmr')->first();
                if ($xmrRate) {
                    $usdValue = $validated['amount'] * $xmrRate->usd_rate;
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to calculate USD value for withdrawal: " . $e->getMessage());
            }

            // Create withdrawal transaction record first
            $withdrawal = $xmrWallet->transactions()->create([
                'xmr_address_id' => null,
                'txid' => null, // Will be set after broadcast
                'payment_id' => null,
                'type' => 'withdrawal',
                'amount' => $validated['amount'],
                'usd_value' => $usdValue,
                'fee' => 0, // Will be updated from actual transaction
                'confirmations' => 0,
                'unlock_time' => 0,
                'height' => null,
                'status' => 'pending',
                'raw_transaction' => [
                    'recipient_address' => $validated['address'],
                    'requested_at' => now()->toIso8601String(),
                    'requested_by' => $user->id,
                ],
            ]);

            // Update balance
            $xmrWallet->updateBalance();

            DB::commit();

            // Broadcast to Monero network (outside transaction to avoid long locks)
            $txHash = MoneroRepository::transfer(
                $xmrWallet->name,
                $validated['address'],
                $validated['amount']
            );

            if (!$txHash) {
                // Rollback if broadcast fails
                $withdrawal->delete();
                $xmrWallet->updateBalance();

                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Failed to broadcast transaction. Please try again or contact support.']);
            }

            // Update withdrawal with actual tx hash
            $withdrawal->update(['txid' => $txHash]);

            \Log::info("Monero withdrawal initiated", [
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'address' => $validated['address'],
                'tx_hash' => $txHash,
            ]);

            return redirect()->route('monero.index')
                ->with('success', 'Withdrawal initiated successfully. Transaction hash: ' . $txHash);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error("Monero withdrawal failed", [
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'address' => $validated['address'],
                'exception' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Withdrawal failed. Please try again or contact support.']);
        }
    }

    /**
     * Show transaction details.
     */
    public function transaction(Request $request, XmrTransaction $transaction): View
    {
        $user = $request->user();

        // Authorization check
        if ($transaction->wallet->user_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction');
        }

        return view('monero.transaction', compact('transaction'));
    }
}
