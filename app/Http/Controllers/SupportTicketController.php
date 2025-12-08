<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    /**
     * Display user's support tickets
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Build query for user's tickets
        $query = SupportTicket::where('user_id', $user->id)
            ->with(['assignedTo', 'messages' => function($q) {
                $q->latest()->limit(1);
            }]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                    ->orWhere('ticket_number', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('last_activity_at', 'desc')->paginate(10);

        // Calculate status counts for filter tabs
        $statusCounts = [
            'all' => $user->supportTickets()->count(),
            'open' => $user->supportTickets()->where('status', 'open')->count(),
            'in_progress' => $user->supportTickets()->where('status', 'in_progress')->count(),
            'resolved' => $user->supportTickets()->where('status', 'resolved')->count(),
            'closed' => $user->supportTickets()->where('status', 'closed')->count(),
        ];

        // Get ticket types for filtering
        $ticketTypes = SupportTicket::getTicketTypes();

        return view('support.index', compact('tickets', 'statusCounts', 'ticketTypes'));
    }

    /**
     * Show create ticket form
     */
    public function create(Request $request)
    {
        $ticketTypes = SupportTicket::getTicketTypes();

        // Pre-select category and type if provided
        $selectedCategory = $request->get('category');
        $selectedType = $request->get('type');

        return view('support.create', compact('ticketTypes', 'selectedCategory', 'selectedType'));
    }

    /**
     * Store new support ticket
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Rate limiting - prevent spam
        $recentTickets = $user->supportTickets()
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentTickets >= 3) {
            return redirect()->back()
                ->with('error', 'You can only create 3 tickets per hour. Please wait before creating another ticket.');
        }

        $validated = $request->validate([
            'category' => ['required', Rule::in(['account', 'payments', 'orders', 'technical', 'general'])],
            'type' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $category = $request->get('category');
                    $ticketTypes = SupportTicket::getTicketTypes();

                    if (!$category || !isset($ticketTypes[$category]) || !array_key_exists($value, $ticketTypes[$category])) {
                        $fail('The selected type is invalid for the chosen category.');
                    }
                }
            ],
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'metadata' => 'nullable|array',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Determine priority based on ticket type if not specified
        if (!isset($validated['priority'])) {
            $validated['priority'] = $this->getDefaultPriority($validated['type']);
        }

        // Auto-assign to staff member with lowest workload
        $assignedStaff = $this->assignToAvailableStaff();

        // Create the ticket
        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'category' => $validated['category'],
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'metadata' => $validated['metadata'] ?? null,
            'status' => 'open',
            'assigned_to' => $assignedStaff?->id,
        ]);

        // Add initial system message
        $ticket->messages()->create([
            'user_id' => $user->id,
            'message' => "Support ticket created: {$ticket->subject}",
            'message_type' => 'system_message',
            'is_internal' => false,
        ]);

        // Add auto-assignment message if staff was assigned
        if ($assignedStaff) {
            $ticket->messages()->create([
                'user_id' => $user->id,
                'message' => "Automatically assigned to staff: {$assignedStaff->username_pub}",
                'message_type' => 'assignment_update',
                'is_internal' => true,
            ]);
        }

        // Handle file attachment if provided
        if ($request->hasFile('attachment')) {
            $this->handleFileUpload($request->file('attachment'), $ticket, $user);
        }

        return redirect()->route('support.show', $ticket)
            ->with('success', 'Support ticket created successfully. Our support team will respond as soon as possible.');
    }

    /**
     * Show specific support ticket
     */
    public function show(SupportTicket $supportTicket)
    {
        $user = auth()->user();

        // Check if user can view this ticket
        if (!$supportTicket->canUserParticipate($user)) {
            abort(403, 'You cannot view this support ticket.');
        }

        // Load relationships
        $supportTicket->load([
            'assignedTo',
            'publicMessages.user',
            'attachments.uploadedBy'
        ]);

        // Mark unread messages as read for this user
        $supportTicket->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_internal', false)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('support.show', compact('supportTicket'));
    }

    /**
     * Add message to support ticket
     */
    public function addMessage(Request $request, SupportTicket $supportTicket)
    {
        $user = auth()->user();

        // Check if user can participate
        if (!$supportTicket->canUserParticipate($user)) {
            abort(403, 'You cannot participate in this support ticket.');
        }

        // Check if ticket is still open
        if ($supportTicket->isClosed()) {
            return redirect()->back()
                ->with('error', 'Cannot add messages to a closed support ticket.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $supportTicket->messages()->create([
            'user_id' => $user->id,
            'message' => $validated['message'],
            'message_type' => 'user_message',
            'is_internal' => false,
        ]);

        // Update ticket status if it was pending
        if ($supportTicket->status === 'pending') {
            $supportTicket->updateStatus('open', $user);
        }

        return redirect()->back()
            ->with('success', 'Message added successfully.');
    }

    /**
     * Close support ticket (user can close their own tickets)
     */
    public function closeTicket(Request $request, SupportTicket $supportTicket)
    {
        $user = auth()->user();

        // Only ticket owner can close their own ticket
        if ($supportTicket->user_id !== $user->id) {
            abort(403, 'You can only close your own support tickets.');
        }

        // Cannot close already closed tickets
        if ($supportTicket->isClosed()) {
            return redirect()->back()
                ->with('error', 'This ticket is already closed.');
        }

        $validated = $request->validate([
            'close_reason' => 'nullable|string|max:500',
        ]);

        $supportTicket->markAsClosed($validated['close_reason'] ?? 'Closed by user');

        // Add system message
        $supportTicket->messages()->create([
            'user_id' => $user->id,
            'message' => "Ticket closed by user. Reason: " . ($validated['close_reason'] ?? 'No reason provided'),
            'message_type' => 'system_message',
            'is_internal' => false,
        ]);

        return redirect()->route('support.index')
            ->with('success', 'Support ticket closed successfully.');
    }

    /**
     * Upload attachment to support ticket
     */
    public function uploadAttachment(Request $request, SupportTicket $supportTicket)
    {
        $user = auth()->user();

        // Check if user can participate
        if (!$supportTicket->canUserParticipate($user)) {
            abort(403, 'You cannot upload attachments to this support ticket.');
        }

        // Check if ticket is still open
        if ($supportTicket->isClosed()) {
            return redirect()->back()
                ->with('error', 'Cannot upload attachments to a closed support ticket.');
        }

        $validated = $request->validate([
            'attachment' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string|max:500',
        ]);

        $attachment = $this->handleFileUpload(
            $validated['attachment'],
            $supportTicket,
            $user,
            $validated['description'] ?? null
        );

        // Add message about attachment upload
        $supportTicket->messages()->create([
            'user_id' => $user->id,
            'message' => "Attachment uploaded: {$attachment->file_name}" .
                ($attachment->description ? " - {$attachment->description}" : ""),
            'message_type' => 'system_message',
            'is_internal' => false,
        ]);

        return redirect()->back()
            ->with('success', 'Attachment uploaded successfully.');
    }

    /**
     * Download attachment (display inline as data URI)
     */
    public function downloadAttachment(SupportTicket $supportTicket, SupportTicketAttachment $attachment)
    {
        $user = auth()->user();

        // Check if user can access this ticket
        if (!$supportTicket->canUserParticipate($user)) {
            abort(403, 'You cannot access this attachment.');
        }

        // Verify attachment belongs to this ticket
        if ($attachment->support_ticket_id !== $supportTicket->id) {
            abort(404, 'Attachment not found.');
        }

        // Return image inline
        return response(
            base64_decode($attachment->content),
            200,
            [
                'Content-Type' => $attachment->type,
                'Content-Disposition' => 'inline; filename="' . $attachment->file_name . '"',
            ]
        );
    }

    /**
     * Mark support ticket messages as read
     */
    public function markMessagesRead(SupportTicket $supportTicket)
    {
        $user = auth()->user();

        // Check if user can access this ticket
        if (!$supportTicket->canUserParticipate($user)) {
            abort(403, 'You cannot access this support ticket.');
        }

        // Mark unread messages as read
        $supportTicket->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_internal', false)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->route('support.show', $supportTicket)
            ->with('success', 'Messages marked as read.');
    }

    /**
     * Handle file upload for attachments
     */
    private function handleFileUpload($file, SupportTicket $ticket, $user, $description = null): SupportTicketAttachment
    {
        // Encode image as base64
        $imageContent = base64_encode(file_get_contents($file->getRealPath()));
        $mimeType = $file->getMimeType();

        // Create attachment record
        return $ticket->attachments()->create([
            'uploaded_by' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'content' => $imageContent,
            'type' => $mimeType,
            'description' => $description,
        ]);
    }

    /**
     * Get default priority based on ticket type
     */
    private function getDefaultPriority(string $ticketType): string
    {
        $highPriorityTypes = [
            'account_banned',
            'account_suspended',
            'btc_withdrawal_issue',
            'xmr_withdrawal_issue',
            'balance_discrepancy',
            'escrow_issue',
        ];

        $urgentTypes = [
            'login_issues', // If they can't access their account
        ];

        if (in_array($ticketType, $urgentTypes)) {
            return 'urgent';
        } elseif (in_array($ticketType, $highPriorityTypes)) {
            return 'high';
        } else {
            return 'medium';
        }
    }

    /**
     * Get user's unread ticket count (for notifications)
     */
    public function getUnreadCount()
    {
        $user = auth()->user();

        $unreadCount = SupportTicket::where('user_id', $user->id)
            ->whereHas('messages', function($q) use ($user) {
                $q->where('user_id', '!=', $user->id)
                    ->where('is_internal', false)
                    ->where('is_read', false);
            })
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    /**
     * Get ticket statistics for user dashboard
     */
    public function getUserStats()
    {
        $user = auth()->user();

        $stats = [
            'total_tickets' => $user->supportTickets()->count(),
            'open_tickets' => $user->supportTickets()->open()->count(),
            'resolved_tickets' => $user->supportTickets()->where('status', 'resolved')->count(),
            'average_response_time' => $user->supportTickets()
                ->whereNotNull('first_response_at')
                ->get()
                ->avg(function($ticket) {
                    return $ticket->getResponseTime();
                }),
            'recent_tickets' => $user->supportTickets()
                ->with(['assignedTo'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Reopen a closed ticket
     */
    public function reopenTicket(Request $request, SupportTicket $supportTicket)
    {
        $user = auth()->user();

        // Only ticket owner can reopen their own ticket
        if ($supportTicket->user_id !== $user->id) {
            abort(403, 'You can only reopen your own support tickets.');
        }

        // Can only reopen closed or resolved tickets
        if (!$supportTicket->isClosed()) {
            return redirect()->back()
                ->with('error', 'This ticket is not closed and cannot be reopened.');
        }

        $validated = $request->validate([
            'reopen_reason' => 'required|string|max:500',
        ]);

        // Reopen the ticket
        $supportTicket->update([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);

        // Add message about reopening
        $supportTicket->messages()->create([
            'user_id' => $user->id,
            'message' => "Ticket reopened by user. Reason: {$validated['reopen_reason']}",
            'message_type' => 'system_message',
            'is_internal' => false,
        ]);

        return redirect()->back()
            ->with('success', 'Support ticket reopened successfully.');
    }

    /**
     * Assign ticket to staff member with lowest workload
     */
    private function assignToAvailableStaff()
    {
        // Get all active support staff
        $staffMembers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'support']);
        })
            ->where('status', 'active')
            ->get();

        if ($staffMembers->isEmpty()) {
            return null;
        }

        $maxWorkload = config('tickets.max_staff_workload', 15);

        // Calculate current workload for each staff member
        $staffWorkloads = $staffMembers->map(function($staff) {
            $workload = SupportTicket::where('assigned_to', $staff->id)
                ->whereIn('status', ['open', 'pending', 'in_progress', 'on_hold'])
                ->count();

            return [
                'staff' => $staff,
                'workload' => $workload,
            ];
        });

        // Filter out staff at capacity
        $availableStaff = $staffWorkloads->filter(function($item) use ($maxWorkload) {
            return $item['workload'] < $maxWorkload;
        });

        // If all staff are at capacity, return null
        if ($availableStaff->isEmpty()) {
            return null;
        }

        // Sort by workload (ascending) and get the one with lowest workload
        $leastBusy = $availableStaff->sortBy('workload')->first();

        return $leastBusy['staff'];
    }
}
