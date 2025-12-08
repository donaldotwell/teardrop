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
    public function topup(Request $request): View
    {
        $user = $request->user();
        $xmrWallet = MoneroRepository::getOrCreateWalletForUser($user);

        // Use primary address for deposits (Monero doesn't need address rotation like Bitcoin)
        $currentAddress = $xmrWallet->addresses()->where('address_index', 0)->first();

        if (!$currentAddress) {
            return back()->with('error', 'Unable to get deposit address. Please contact support.');
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
            \Log::error("Failed to generate Monero QR code for user {$user->id}", ['exception' => $e->getMessage()]);
            $qrCodeDataUri = null;
        }

        // Get current XMR price
        $xmrPrice = MoneroRepository::getCurrentPrice();

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
                'regex:/^[48][0-9AB][1-9A-HJ-NP-Za-km-z]{93,104}$/', // Monero address format
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

            // Create withdrawal transaction record first
            $withdrawal = $xmrWallet->transactions()->create([
                'xmr_address_id' => null,
                'txid' => null, // Will be set after broadcast
                'payment_id' => null,
                'type' => 'withdrawal',
                'amount' => $validated['amount'],
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
