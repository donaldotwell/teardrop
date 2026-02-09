<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\DisputeEvidence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisputeController extends Controller
{
    /**
     * Display user's disputes
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get user's disputes (both initiated and disputed against)
        $query = Dispute::where(function($q) use ($user) {
            $q->where('initiated_by', $user->id)
                ->orWhere('disputed_against', $user->id);
        })->with(['order.listing', 'initiatedBy', 'disputedAgainst', 'assignedAdmin']);

        // Filter by status if requested
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by type if requested
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get counts for filter buttons
        $statusCounts = [
            'all' => $user->allDisputes()->count(),
            'open' => $user->allDisputes()->open()->count(),
            'resolved' => $user->allDisputes()->where('status', 'resolved')->count(),
            'closed' => $user->allDisputes()->where('status', 'closed')->count(),
        ];

        return view('disputes.index', compact('disputes', 'statusCounts'));
    }

    /**
     * Show create dispute form
     */
    public function create(Order $order)
    {
        $user = auth()->user();

        // Verify user owns this order
        if ($order->user_id !== $user->id) {
            abort(403, 'You can only create disputes for your own orders.');
        }

        // Check if order can have a dispute
        if (!$order->canCreateDispute()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'This order cannot have a dispute created.');
        }

        $disputeTypes = [
            'item_not_received' => 'Item Not Received',
            'item_not_as_described' => 'Item Not As Described',
            'damaged_item' => 'Damaged Item',
            'wrong_item' => 'Wrong Item Sent',
            'quality_issue' => 'Quality Issue',
            'shipping_issue' => 'Shipping Problem',
            'vendor_unresponsive' => 'Vendor Not Responding',
            'refund_request' => 'Refund Request',
            'other' => 'Other Issue',
        ];

        return view('disputes.create', compact('order', 'disputeTypes'));
    }

    /**
     * Store new dispute
     */
    public function store(Request $request, Order $order)
    {
        $user = auth()->user();

        // Verify user owns this order
        if ($order->user_id !== $user->id) {
            abort(403, 'You can only create disputes for your own orders.');
        }

        // Check if order can have a dispute
        if (!$order->canCreateDispute()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'This order cannot have a dispute created.');
        }

        // Additional validation for early finalized orders
        if ($order->is_early_finalized) {
            if ($order->isDisputeWindowExpired()) {
                return redirect()->route('orders.show', $order)->withErrors([
                    'error' => 'The dispute window for this order has expired. Disputes can no longer be filed.'
                ]);
            }

            // Show time remaining warning if expiring soon
            $finalizationService = new \App\Services\FinalizationService();
            if ($finalizationService->isDisputeWindowExpiringSoon($order, 60)) {
                $timeRemaining = $finalizationService->getDisputeWindowTimeRemaining($order);
                session()->flash('warning', "Warning: Dispute window expires soon ({$timeRemaining}). Please file your dispute promptly.");
            }
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in([
                'item_not_received', 'item_not_as_described', 'damaged_item',
                'wrong_item', 'quality_issue', 'shipping_issue',
                'vendor_unresponsive', 'refund_request', 'other'
            ])],
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'disputed_amount' => 'required|numeric|min:0|max:' . $order->usd_price,
            'buyer_evidence' => 'nullable|string|max:1000',
        ]);

        // Auto-assign to moderator with lowest workload
        $assignedModerator = $this->assignToAvailableModerator();

        // Create the dispute
        $dispute = Dispute::create([
            'order_id' => $order->id,
            'initiated_by' => $user->id,
            'disputed_against' => $order->listing->user_id, // Vendor
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'disputed_amount' => $validated['disputed_amount'],
            'buyer_evidence' => $validated['buyer_evidence'],
            'status' => 'open',
            'priority' => 'medium',
            'assigned_moderator_id' => $assignedModerator?->id,
            'assigned_at' => $assignedModerator ? now() : null,
            'auto_assigned' => $assignedModerator ? true : false,
        ]);

        // Add initial system message
        $dispute->messages()->create([
            'user_id' => $user->id,
            'message' => "Dispute created: {$dispute->subject}",
            'message_type' => 'system_message',
            'is_internal' => false,
        ]);

        // Add auto-assignment message if moderator was assigned
        if ($assignedModerator) {
            $dispute->messages()->create([
                'user_id' => $user->id,
                'message' => "Automatically assigned to moderator: {$assignedModerator->username_pub}",
                'message_type' => 'status_update',
                'is_internal' => true,
            ]);
        }

        // If this is an escrow order, hold the funds
        if ($order->listing->payment_method === 'escrow') {
            // Add system message about escrow hold
            $dispute->messages()->create([
                'user_id' => $user->id,
                'message' => "Escrow funds have been placed on hold pending dispute resolution.",
                'message_type' => 'system_message',
                'is_internal' => false,
            ]);
        }

        return redirect()->route('disputes.show', $dispute)
            ->with('success', 'Dispute created successfully. An admin will review your case shortly.');
    }

    /**
     * Show specific dispute
     */
    public function show(Dispute $dispute)
    {
        $user = auth()->user();

        // Check if user can view this dispute
        if (!$dispute->canUserParticipate($user)) {
            abort(403, 'You cannot view this dispute.');
        }

        // Load relationships
        $dispute->load([
            'order.listing.media',
            'initiatedBy',
            'disputedAgainst',
            'assignedAdmin',
            'publicMessages.user',
            'evidence.uploadedBy'
        ]);

        // Mark unread messages as read for this user
        $dispute->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_internal', false)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        // Get other party in dispute
        $otherParty = $dispute->getOtherParty($user);

        return view('disputes.show', compact('dispute', 'otherParty'));
    }

    /**
     * Add message to dispute
     */
    public function addMessage(Request $request, Dispute $dispute)
    {
        $user = auth()->user();

        // Check if user can participate
        if (!$dispute->canUserParticipate($user)) {
            abort(403, 'You cannot participate in this dispute.');
        }

        // Check if dispute is still open
        if (!$dispute->isOpen()) {
            return redirect()->back()
                ->with('error', 'Cannot add messages to a closed dispute.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $dispute->messages()->create([
            'user_id' => $user->id,
            'message' => $validated['message'],
            'message_type' => 'user_message',
            'is_internal' => false,
        ]);

        // Update dispute status based on who sent the message
        if ($user->id === $dispute->initiated_by && $dispute->status === 'waiting_buyer') {
            $dispute->update(['status' => 'under_review']);
        } elseif ($user->id === $dispute->disputed_against && $dispute->status === 'waiting_vendor') {
            $dispute->update([
                'status' => 'under_review',
                'vendor_responded_at' => now()
            ]);
        }

        return redirect()->back()
            ->with('success', 'Message added successfully.');
    }

    /**
     * Upload evidence to dispute
     */
    public function uploadEvidence(Request $request, Dispute $dispute)
    {
        $user = auth()->user();

        // Check if user can participate
        if (!$dispute->canUserParticipate($user)) {
            abort(403, 'You cannot upload evidence to this dispute.');
        }

        // Check if dispute is still open
        if (!$dispute->isOpen()) {
            return redirect()->back()
                ->with('error', 'Cannot upload evidence to a closed dispute.');
        }

        $validated = $request->validate([
            'evidence_file' => 'required|file|max:2048|mimes:jpg,jpeg,png,gif',
            'evidence_type' => ['required', Rule::in([
                'product_photo', 'packaging_photo', 'shipping_label', 'receipt',
                'communication', 'damage_photo', 'tracking_info', 'other_document'
            ])],
            'description' => 'nullable|string|max:500',
        ]);

        // Get file and encode as base64
        $file = $request->file('evidence_file');
        $fileContent = base64_encode(file_get_contents($file->getRealPath()));
        $mimeType = $file->getMimeType();

        // Create evidence record
        $evidence = $dispute->evidence()->create([
            'uploaded_by' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'content' => $fileContent,
            'type' => $mimeType,
            'file_type' => str_starts_with($mimeType, 'image/') ? 'image' : 'document',
            'file_size' => $file->getSize(),
            'description' => $validated['description'],
            'evidence_type' => $validated['evidence_type'],
        ]);

        // Add message about evidence upload
        $dispute->messages()->create([
            'user_id' => $user->id,
            'message' => "Evidence uploaded: {$evidence->file_name}" .
                ($evidence->description ? " - {$evidence->description}" : ""),
            'message_type' => 'evidence_upload',
            'is_internal' => false,
        ]);

        return redirect()->back()
            ->with('success', 'Evidence uploaded successfully.');
    }

    /**
     * Mark dispute messages as read
     */
    public function markMessagesRead(Dispute $dispute)
    {
        $user = auth()->user();

        // Check if user can access this dispute
        if (!$dispute->canUserParticipate($user)) {
            abort(403, 'You cannot access this dispute.');
        }

        // Mark unread messages as read
        $dispute->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_internal', false)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->route('disputes.show', $dispute)
            ->with('success', 'Messages marked as read.');
    }

    /**
     * Download dispute evidence as base64 encoded image response.
     */
    public function downloadEvidence(Dispute $dispute, DisputeEvidence $evidence)
    {
        $user = auth()->user();

        // Check if user can access this dispute
        if (!$dispute->canUserParticipate($user)) {
            abort(403, 'You cannot access this dispute.');
        }

        // Verify evidence belongs to this dispute
        if ($evidence->dispute_id !== $dispute->id) {
            abort(404, 'Evidence not found for this dispute.');
        }

        // Return the base64 decoded content as a downloadable response
        $content = base64_decode($evidence->content);
        $mimeType = $evidence->type;
        $fileName = $evidence->file_name;

        return response($content, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->header('Content-Length', strlen($content));
    }

    /**
     * Assign dispute to moderator with lowest workload
     */
    private function assignToAvailableModerator()
    {
        // Get all active moderators
        $moderators = User::whereHas('roles', function($q) {
            $q->where('name', 'moderator');
        })
            ->where('status', 'active')
            ->get();

        if ($moderators->isEmpty()) {
            return null;
        }

        $maxWorkload = config('disputes.max_moderator_workload', 10);

        // Calculate current workload for each moderator
        $moderatorWorkloads = $moderators->map(function($moderator) {
            $workload = Dispute::where('assigned_moderator_id', $moderator->id)
                ->whereIn('status', ['open', 'under_review', 'waiting_vendor', 'waiting_buyer'])
                ->count();

            return [
                'moderator' => $moderator,
                'workload' => $workload,
            ];
        });

        // Filter out moderators at capacity
        $availableModerators = $moderatorWorkloads->filter(function($item) use ($maxWorkload) {
            return $item['workload'] < $maxWorkload;
        });

        // If all moderators are at capacity, return null
        if ($availableModerators->isEmpty()) {
            return null;
        }

        // Sort by workload (ascending) and get the one with lowest workload
        $leastBusy = $availableModerators->sortBy('workload')->first();

        return $leastBusy['moderator'];
    }
}
