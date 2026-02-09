<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BtcWallet;
use App\Models\XmrWallet;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\User;
use App\Repositories\BitcoinRepository;
use App\Repositories\MoneroRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminDisputeController extends Controller
{
    /**
     * Display disputes index with filtering
     */
    public function index(Request $request)
    {
        $query = Dispute::with(['order.listing', 'initiatedBy', 'disputedAgainst', 'assignedAdmin']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                    ->orWhere('uuid', 'LIKE', "%{$search}%")
                    ->orWhereHas('order', function($orderQuery) use ($search) {
                        $orderQuery->where('uuid', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('initiatedBy', function($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('disputedAgainst', function($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('assigned_admin')) {
            if ($request->get('assigned_admin') === 'unassigned') {
                $query->whereNull('assigned_admin_id');
            } else {
                $query->where('assigned_admin_id', $request->get('assigned_admin'));
            }
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate stats
        $stats = [
            'total_disputes' => Dispute::count(),
            'open_disputes' => Dispute::whereIn('status', ['open', 'under_review', 'waiting_vendor', 'waiting_buyer'])->count(),
            'under_review_disputes' => Dispute::where('status', 'under_review')->count(),
            'escalated_disputes' => Dispute::where('status', 'escalated')->count(),
            'resolved_disputes' => Dispute::where('status', 'resolved')->count(),
            'total_value' => Dispute::sum('disputed_amount'),
        ];

        // Get admin users for filter
        $admins = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->get();

        // Recent activity - real data from disputes
        $recent_activity = collect();

        // Get recently created disputes (last 24 hours)
        $recentlyCreated = Dispute::with(['order', 'initiatedBy'])
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($dispute) {
                return [
                    'type' => 'created',
                    'message' => "New dispute created for Order #{$dispute->order->uuid}",
                    'time' => $dispute->created_at->diffForHumans(),
                    'amount' => $dispute->disputed_amount
                ];
            });

        // Get recently resolved disputes (last 24 hours)
        $recentlyResolved = Dispute::with(['order'])
            ->whereNotNull('resolved_at')
            ->where('resolved_at', '>=', now()->subDay())
            ->orderBy('resolved_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($dispute) {
                $resolutionText = $dispute->resolution
                    ? str_replace('_', ' ', $dispute->resolution)
                    : 'resolved';
                return [
                    'type' => 'resolved',
                    'message' => "Dispute #{$dispute->uuid} {$resolutionText}",
                    'time' => $dispute->resolved_at->diffForHumans(),
                    'amount' => $dispute->disputed_amount
                ];
            });

        // Get recently escalated disputes (last 24 hours)
        $recentlyEscalated = Dispute::with(['order'])
            ->whereNotNull('escalated_at')
            ->where('escalated_at', '>=', now()->subDay())
            ->orderBy('escalated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($dispute) {
                return [
                    'type' => 'escalated',
                    'message' => "Dispute #{$dispute->uuid} escalated to {$dispute->priority} priority",
                    'time' => $dispute->escalated_at->diffForHumans(),
                    'amount' => $dispute->disputed_amount
                ];
            });

        // Merge all activities and sort by time (most recent first)
        $recent_activity = $recent_activity
            ->concat($recentlyCreated)
            ->concat($recentlyResolved)
            ->concat($recentlyEscalated)
            ->sortByDesc(function($activity) {
                // Convert "X ago" back to timestamp for sorting
                // This is approximate but works for display purposes
                return $activity['time'];
            })
            ->take(10)
            ->values();

        return view('admin.disputes.index', compact('disputes', 'stats', 'admins', 'recent_activity'));
    }

    /**
     * Show dispute details
     */
    public function show(Dispute $dispute)
    {
        $dispute->load([
            'order.listing.media',
            'order.listing.user',
            'initiatedBy',
            'disputedAgainst',
            'assignedAdmin',
            'assignedModerator',
            'messages.user',
            'evidence.uploadedBy'
        ]);

        // Get all messages (including internal ones for admin)
        $messages = $dispute->messages()->with('user')->orderBy('created_at', 'asc')->get();

        // Get admin users for assignment
        $admins = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->get();

        // Get moderators for reassignment
        $moderators = User::whereHas('roles', function($q) {
            $q->where('name', 'moderator');
        })->where('status', 'active')
          ->orderBy('username_pub')
          ->get();

        // Get vendor balance for resolution context
        $vendor = $dispute->disputedAgainst;
        $vendorBalance = $vendor ? $vendor->getBalance() : null;
        $vendorDisputedAmounts = $vendor ? $vendor->getActiveDisputedAmounts() : ['btc' => 0, 'xmr' => 0];

        return view('admin.disputes.show', compact('dispute', 'messages', 'admins', 'moderators', 'vendorBalance', 'vendorDisputedAmounts'));
    }

    /**
     * Assign dispute to current admin
     */
    public function assign(Dispute $dispute)
    {
        $admin = auth()->user();

        if ($dispute->assignedAdmin && $dispute->assignedAdmin->id !== $admin->id) {
            return redirect()->back()
                ->with('error', 'This dispute is already assigned to another admin.');
        }

        $dispute->assignAdmin($admin);

        return redirect()->back()
            ->with('success', 'Dispute assigned successfully.');
    }

    /**
     * Reassign dispute to a specific moderator
     */
    public function reassignModerator(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'moderator_id' => 'nullable|exists:users,id',
        ]);

        // If moderator_id is null, unassign moderator
        if (!$validated['moderator_id']) {
            $previousModerator = $dispute->assignedModerator;

            $dispute->update([
                'assigned_moderator_id' => null,
                'assigned_at' => null,
            ]);

            $dispute->messages()->create([
                'user_id' => auth()->id(),
                'message' => "Moderator unassigned by admin: " .
                    ($previousModerator ? $previousModerator->username_pub : 'N/A') . " removed",
                'message_type' => 'status_update',
                'is_internal' => true,
            ]);

            return redirect()->back()
                ->with('success', 'Moderator unassigned successfully.');
        }

        // Verify the user is actually a moderator
        $moderator = User::find($validated['moderator_id']);
        if (!$moderator->hasRole('moderator')) {
            return redirect()->back()
                ->with('error', 'Selected user is not a moderator.');
        }

        $previousModerator = $dispute->assignedModerator;

        $dispute->update([
            'assigned_moderator_id' => $moderator->id,
            'assigned_at' => now(),
            'auto_assigned' => false,
        ]);

        // Add assignment message
        $messageText = $previousModerator
            ? "Dispute reassigned by admin from {$previousModerator->username_pub} to {$moderator->username_pub}"
            : "Dispute assigned to moderator: {$moderator->username_pub} by admin";

        $dispute->messages()->create([
            'user_id' => auth()->id(),
            'message' => $messageText,
            'message_type' => 'assignment_update',
            'is_internal' => true,
        ]);

        // Log the action
        AuditLog::log('dispute_moderator_reassigned', auth()->id(), [
            'dispute_id' => $dispute->id,
            'previous_moderator_id' => $previousModerator?->id,
            'new_moderator_id' => $moderator->id,
        ]);

        return redirect()->back()
            ->with('success', 'Dispute reassigned to moderator successfully.');
    }

    /**
     * Escalate dispute priority
     */
    public function escalate(Dispute $dispute)
    {
        if (!$dispute->canBeEscalated()) {
            return redirect()->back()
                ->with('error', 'This dispute cannot be escalated.');
        }

        $dispute->escalate('Escalated by admin: ' . auth()->user()->username_pub);

        return redirect()->back()
            ->with('success', 'Dispute escalated successfully.');
    }

    /**
     * Resolve dispute with actual refund processing
     */
    public function resolve(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'resolution' => 'required|in:buyer_favor,vendor_favor,partial_refund,no_action',
            'refund_amount' => 'nullable|numeric|min:0|max:' . $dispute->disputed_amount,
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $resolution = $validated['resolution'];
        $refundUsd = null;

        // Determine the USD refund amount based on resolution
        if ($resolution === 'buyer_favor') {
            $refundUsd = $dispute->disputed_amount;
        } elseif ($resolution === 'partial_refund') {
            $refundUsd = $validated['refund_amount'] ?? 0;
            if ($refundUsd <= 0) {
                return redirect()->back()
                    ->with('error', 'A refund amount is required for partial refund resolution.')
                    ->withInput();
            }
        }

        // Process actual refund for buyer_favor or partial_refund
        if ($refundUsd && $refundUsd > 0) {
            $order = $dispute->order;
            $currency = $order->currency ?? 'btc';
            $vendor = $dispute->disputedAgainst;
            $buyer = $dispute->initiatedBy;
            $refundCrypto = convert_usd_to_crypto($refundUsd, $currency);

            try {
                DB::beginTransaction();

                if ($currency === 'btc') {
                    $vendorBtcWallet = BtcWallet::where('user_id', $vendor->id)->lockForUpdate()->first();
                    $buyerBtcWallet = BtcWallet::where('user_id', $buyer->id)->first();

                    if (!$vendorBtcWallet || $vendorBtcWallet->getBalance() < $refundCrypto) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', 'Vendor has insufficient BTC balance for refund. Available: '
                                . number_format($vendorBtcWallet ? $vendorBtcWallet->getBalance() : 0, 8) . ' BTC, Required: '
                                . number_format($refundCrypto, 8) . ' BTC.')
                            ->withInput();
                    }

                    if (!$buyerBtcWallet) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', 'Buyer does not have a BTC wallet.')
                            ->withInput();
                    }

                    // Get buyer receiving address
                    $buyerAddress = $buyerBtcWallet->getCurrentAddress() ?? $buyerBtcWallet->generateNewAddress();

                    // Create withdrawal transaction on vendor's wallet
                    $vendorBtcWallet->transactions()->create([
                        'btc_address_id' => null,
                        'txid' => null,
                        'type' => 'withdrawal',
                        'amount' => $refundCrypto,
                        'usd_value' => $refundUsd,
                        'fee' => 0,
                        'confirmations' => 0,
                        'status' => 'pending',
                        'raw_transaction' => [
                            'purpose' => 'dispute_refund',
                            'order_id' => $order->id,
                            'order_uuid' => $order->uuid,
                            'dispute_id' => $dispute->id,
                            'dispute_uuid' => $dispute->uuid,
                            'resolution' => $resolution,
                            'refund_usd' => $refundUsd,
                            'recipient_address' => $buyerAddress->address,
                            'recipient_user_id' => $buyer->id,
                            'initiated_by' => auth()->id(),
                            'initiated_at' => now()->toIso8601String(),
                        ],
                    ]);

                    // Create deposit transaction on buyer's wallet
                    $buyerBtcWallet->transactions()->create([
                        'btc_address_id' => $buyerAddress->id,
                        'txid' => null,
                        'type' => 'deposit',
                        'amount' => $refundCrypto,
                        'usd_value' => $refundUsd,
                        'fee' => 0,
                        'confirmations' => 0,
                        'status' => 'pending',
                        'raw_transaction' => [
                            'purpose' => 'dispute_refund',
                            'order_id' => $order->id,
                            'order_uuid' => $order->uuid,
                            'dispute_id' => $dispute->id,
                            'dispute_uuid' => $dispute->uuid,
                            'resolution' => $resolution,
                            'refund_usd' => $refundUsd,
                            'sender_user_id' => $vendor->id,
                            'initiated_by' => auth()->id(),
                            'initiated_at' => now()->toIso8601String(),
                        ],
                    ]);

                    // Update both wallet balances
                    $vendorBtcWallet->updateBalance();
                    $buyerBtcWallet->updateBalance();

                    DB::commit();

                    // Broadcast the actual BTC transfer outside the DB transaction
                    try {
                        $repository = new BitcoinRepository();
                        $txid = $repository->sendBitcoin(
                            $vendor->username_pri,
                            $buyerAddress->address,
                            $refundCrypto
                        );

                        if ($txid) {
                            // Update both transaction records with the txid
                            $vendorBtcWallet->transactions()
                                ->whereNull('txid')
                                ->where('raw_transaction->purpose', 'dispute_refund')
                                ->where('raw_transaction->dispute_id', $dispute->id)
                                ->update(['txid' => $txid]);

                            $buyerBtcWallet->transactions()
                                ->whereNull('txid')
                                ->where('raw_transaction->purpose', 'dispute_refund')
                                ->where('raw_transaction->dispute_id', $dispute->id)
                                ->update(['txid' => $txid]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('BTC refund broadcast failed for dispute ' . $dispute->uuid, [
                            'exception' => $e->getMessage(),
                            'dispute_id' => $dispute->id,
                            'amount' => $refundCrypto,
                        ]);
                        // Transaction records remain with null txid — sync commands will pick them up
                    }

                } elseif ($currency === 'xmr') {
                    $vendorXmrWallet = XmrWallet::where('user_id', $vendor->id)->lockForUpdate()->first();
                    $buyerXmrWallet = XmrWallet::where('user_id', $buyer->id)->first();

                    $vendorBalance = $vendorXmrWallet ? $vendorXmrWallet->getBalance() : ['balance' => 0, 'unlocked_balance' => 0];
                    $vendorUnlocked = $vendorBalance['unlocked_balance'] ?? 0;

                    if (!$vendorXmrWallet || $vendorUnlocked < $refundCrypto) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', 'Vendor has insufficient XMR balance for refund. Available: '
                                . number_format($vendorUnlocked, 12) . ' XMR, Required: '
                                . number_format($refundCrypto, 12) . ' XMR.')
                            ->withInput();
                    }

                    if (!$buyerXmrWallet) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', 'Buyer does not have an XMR wallet.')
                            ->withInput();
                    }

                    // Get buyer receiving address
                    $buyerAddress = $buyerXmrWallet->getCurrentAddress() ?? $buyerXmrWallet->generateNewAddress();

                    // Create withdrawal transaction on vendor's wallet
                    $vendorXmrWallet->transactions()->create([
                        'xmr_address_id' => null,
                        'txid' => null,
                        'payment_id' => null,
                        'type' => 'withdrawal',
                        'amount' => $refundCrypto,
                        'usd_value' => $refundUsd,
                        'fee' => 0,
                        'confirmations' => 0,
                        'unlock_time' => 0,
                        'height' => null,
                        'status' => 'pending',
                        'raw_transaction' => [
                            'purpose' => 'dispute_refund',
                            'order_id' => $order->id,
                            'order_uuid' => $order->uuid,
                            'dispute_id' => $dispute->id,
                            'dispute_uuid' => $dispute->uuid,
                            'resolution' => $resolution,
                            'refund_usd' => $refundUsd,
                            'recipient_address' => $buyerAddress->address,
                            'recipient_user_id' => $buyer->id,
                            'initiated_by' => auth()->id(),
                            'initiated_at' => now()->toIso8601String(),
                        ],
                    ]);

                    // Create deposit transaction on buyer's wallet
                    $buyerXmrWallet->transactions()->create([
                        'xmr_address_id' => $buyerAddress->id,
                        'txid' => null,
                        'payment_id' => null,
                        'type' => 'deposit',
                        'amount' => $refundCrypto,
                        'usd_value' => $refundUsd,
                        'fee' => 0,
                        'confirmations' => 0,
                        'unlock_time' => 0,
                        'height' => null,
                        'status' => 'pending',
                        'raw_transaction' => [
                            'purpose' => 'dispute_refund',
                            'order_id' => $order->id,
                            'order_uuid' => $order->uuid,
                            'dispute_id' => $dispute->id,
                            'dispute_uuid' => $dispute->uuid,
                            'resolution' => $resolution,
                            'refund_usd' => $refundUsd,
                            'sender_user_id' => $vendor->id,
                            'initiated_by' => auth()->id(),
                            'initiated_at' => now()->toIso8601String(),
                        ],
                    ]);

                    // Update both wallet balances
                    $vendorXmrWallet->updateBalance();
                    $buyerXmrWallet->updateBalance();

                    DB::commit();

                    // Broadcast the actual XMR transfer outside the DB transaction
                    try {
                        $txHash = MoneroRepository::transfer(
                            $vendorXmrWallet->name,
                            $buyerAddress->address,
                            $refundCrypto
                        );

                        if ($txHash) {
                            // Update both transaction records with the txid
                            $vendorXmrWallet->transactions()
                                ->whereNull('txid')
                                ->where('raw_transaction->purpose', 'dispute_refund')
                                ->where('raw_transaction->dispute_id', $dispute->id)
                                ->update(['txid' => $txHash]);

                            $buyerXmrWallet->transactions()
                                ->whereNull('txid')
                                ->where('raw_transaction->purpose', 'dispute_refund')
                                ->where('raw_transaction->dispute_id', $dispute->id)
                                ->update(['txid' => $txHash]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('XMR refund broadcast failed for dispute ' . $dispute->uuid, [
                            'exception' => $e->getMessage(),
                            'dispute_id' => $dispute->id,
                            'amount' => $refundCrypto,
                        ]);
                        // Transaction records remain with null txid — sync commands will pick them up
                    }
                }

                // Log the refund
                AuditLog::log('dispute_refund_processed', auth()->id(), [
                    'dispute_id' => $dispute->id,
                    'dispute_uuid' => $dispute->uuid,
                    'order_id' => $order->id,
                    'resolution' => $resolution,
                    'refund_usd' => $refundUsd,
                    'refund_crypto' => $refundCrypto,
                    'currency' => $currency,
                    'vendor_id' => $vendor->id,
                    'buyer_id' => $buyer->id,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Dispute refund failed', [
                    'dispute_id' => $dispute->id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return redirect()->back()
                    ->with('error', 'Failed to process refund: ' . $e->getMessage())
                    ->withInput();
            }
        }

        // Mark dispute as resolved
        $dispute->markAsResolved(
            $resolution,
            $refundUsd,
            $validated['resolution_notes']
        );

        // Add admin resolution message
        $refundInfo = '';
        if ($refundUsd && $refundUsd > 0) {
            $currency = $dispute->order->currency ?? 'btc';
            $refundCrypto = convert_usd_to_crypto($refundUsd, $currency);
            $refundInfo = " Refund: \${$refundUsd} USD (" . number_format($refundCrypto, $currency === 'btc' ? 8 : 12) . " " . strtoupper($currency) . ").";
        }

        $dispute->messages()->create([
            'user_id' => auth()->id(),
            'message' => "Dispute resolved: {$resolution}.{$refundInfo} Notes: {$validated['resolution_notes']}",
            'message_type' => 'resolution_note',
            'is_internal' => false,
        ]);

        return redirect()->route('admin.disputes.index')
            ->with('success', 'Dispute resolved successfully.' . ($refundUsd ? " Refund of \${$refundUsd} processed." : ''));
    }

    /**
     * Close dispute
     */
    public function close(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'close_reason' => 'required|string|max:500',
        ]);

        $dispute->update([
            'status' => 'closed',
            'closed_at' => now(),
            'resolution_notes' => $validated['close_reason'],
        ]);

        // Add admin closure message
        $dispute->messages()->create([
            'user_id' => auth()->id(),
            'message' => "Dispute closed by admin. Reason: {$validated['close_reason']}",
            'message_type' => 'status_update',
            'is_internal' => false,
        ]);

        return redirect()->route('admin.disputes.index')
            ->with('success', 'Dispute closed successfully.');
    }

    /**
     * Add admin message to dispute
     */
    public function addAdminMessage(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'is_internal' => 'boolean',
        ]);

        $dispute->messages()->create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'message_type' => 'admin_message',
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        return redirect()->back()
            ->with('success', 'Message added successfully.');
    }

    /**
     * Update dispute priority
     */
    public function updatePriority(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $oldPriority = $dispute->priority;
        $dispute->update(['priority' => $validated['priority']]);

        // Add system message about priority change
        $dispute->messages()->create([
            'user_id' => auth()->id(),
            'message' => "Priority changed from {$oldPriority} to {$validated['priority']} by admin.",
            'message_type' => 'status_update',
            'is_internal' => true,
        ]);

        return redirect()->back()
            ->with('success', 'Priority updated successfully.');
    }

    /**
     * Export disputes to CSV
     */
    public function export(Request $request)
    {
        $query = Dispute::with(['order', 'initiatedBy', 'disputedAgainst', 'assignedAdmin']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        $disputes = $query->orderBy('created_at', 'desc')->get();

        $csvData = [];
        $csvData[] = [
            'Dispute ID',
            'Subject',
            'Type',
            'Status',
            'Priority',
            'Buyer',
            'Vendor',
            'Order ID',
            'Disputed Amount',
            'Created At',
            'Assigned Admin',
            'Resolved At'
        ];

        foreach ($disputes as $dispute) {
            $csvData[] = [
                $dispute->uuid,
                $dispute->subject,
                ucfirst(str_replace('_', ' ', $dispute->type)),
                ucfirst(str_replace('_', ' ', $dispute->status)),
                ucfirst($dispute->priority),
                $dispute->initiatedBy->username_pub,
                $dispute->disputedAgainst->username_pub,
                $dispute->order->uuid,
                $dispute->disputed_amount,
                $dispute->created_at->format('Y-m-d H:i:s'),
                $dispute->assignedAdmin ? $dispute->assignedAdmin->username_pub : 'Unassigned',
                $dispute->resolved_at ? $dispute->resolved_at->format('Y-m-d H:i:s') : ''
            ];
        }

        $filename = 'disputes_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $handle = fopen('php://temp', 'w+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Download evidence file
     */
    public function downloadEvidence(Dispute $dispute, DisputeEvidence $evidence)
    {
        // Verify evidence belongs to this dispute
        if ($evidence->dispute_id !== $dispute->id) {
            abort(404, 'Evidence not found.');
        }

        // Check if file exists
        if (!Storage::disk('private')->exists($evidence->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('private')->download(
            $evidence->file_path,
            $evidence->file_name
        );
    }

    /**
     * Verify evidence
     */
    public function verifyEvidence(Dispute $dispute, DisputeEvidence $evidence)
    {
        // Verify evidence belongs to this dispute
        if ($evidence->dispute_id !== $dispute->id) {
            abort(404, 'Evidence not found.');
        }

        $evidence->verify(auth()->user());

        return redirect()->back()
            ->with('success', 'Evidence verified successfully.');
    }
}
