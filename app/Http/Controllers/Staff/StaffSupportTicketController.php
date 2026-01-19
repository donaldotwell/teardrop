<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffSupportTicketController extends Controller
{
    /**
     * Display support tickets for staff (assigned + unassigned)
     */
    public function index(Request $request)
    {
        $staff = auth()->user();

        // Staff can see their assigned tickets + unassigned tickets
        $query = SupportTicket::with(['user', 'assignedTo', 'messages' => function($q) {
            $q->latest()->limit(1);
        }])
            ->where(function($q) use ($staff) {
                $q->where('assigned_to', $staff->id)
                    ->orWhereNull('assigned_to');
            });

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                    ->orWhere('ticket_number', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
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

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('assignment')) {
            if ($request->get('assignment') === 'mine') {
                $query->where('assigned_to', $staff->id);
            } elseif ($request->get('assignment') === 'unassigned') {
                $query->whereNull('assigned_to');
            }
        }

        $tickets = $query->orderBy('last_activity_at', 'desc')->paginate(15);

        // Calculate stats for staff
        $stats = [
            'my_tickets' => SupportTicket::where('assigned_to', $staff->id)->open()->count(),
            'unassigned_tickets' => SupportTicket::whereNull('assigned_to')->open()->count(),
            'my_resolved_today' => SupportTicket::where('assigned_to', $staff->id)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', today())
                ->count(),
            'my_avg_response_time' => SupportTicket::where('assigned_to', $staff->id)
                ->whereNotNull('first_response_at')
                ->get()
                ->avg(function($ticket) {
                    return $ticket->getResponseTime();
                }),
            'overdue_assigned' => SupportTicket::where('assigned_to', $staff->id)->overdue()->count(),
            'high_priority_available' => SupportTicket::whereNull('assigned_to')
                ->highPriority()
                ->open()
                ->count(),
        ];

        // Get ticket types for filtering
        $ticketTypes = SupportTicket::getTicketTypes();

        return view('staff.support.index', compact('tickets', 'stats', 'ticketTypes'));
    }

    /**
     * Show specific support ticket for staff
     */
    public function show(SupportTicket $supportTicket)
    {
        $staff = auth()->user();

        // Check if staff can view this ticket (assigned to them or unassigned)
        if ($supportTicket->assigned_to && $supportTicket->assigned_to !== $staff->id) {
            abort(403, 'You can only view tickets assigned to you or unassigned tickets.');
        }

        // Load relationships including internal messages for staff
        $supportTicket->load([
            'user',
            'assignedTo',
            'messages.user',
            'attachments.uploadedBy'
        ]);

        // Get all messages (including internal ones for staff)
        $messages = $supportTicket->messages()->with('user')->orderBy('created_at', 'asc')->get();

        // Get other staff members for reassignment
        $staffMembers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'moderator']);
        })->where('status', 'active')
          ->orderBy('username_pub')
          ->get();

        return view('staff.support.show', compact('supportTicket', 'messages', 'staffMembers'));
    }

    /**
     * Self-assign ticket to current staff member
     */
    public function assignMe(SupportTicket $supportTicket)
    {
        $staff = auth()->user();

        // Can only self-assign unassigned tickets
        if ($supportTicket->assigned_to) {
            return redirect()->back()
                ->with('error', 'This ticket is already assigned to someone else.');
        }

        $supportTicket->assignTo($staff);

        return redirect()->back()
            ->with('success', 'Ticket assigned to you successfully.');
    }

    /**
     * Reassign ticket to another staff member
     */
    public function reassignStaff(Request $request, SupportTicket $supportTicket)
    {
        $currentStaff = auth()->user();

        // Only allow reassigning if assigned to current staff
        if (!$supportTicket->assigned_to || $supportTicket->assigned_to !== $currentStaff->id) {
            return redirect()->back()
                ->with('error', 'You can only reassign tickets assigned to you.');
        }

        $validated = $request->validate([
            'staff_id' => 'required|exists:users,id',
        ]);

        // Don't allow reassigning to self
        if ($validated['staff_id'] == $currentStaff->id) {
            return redirect()->back()
                ->with('error', 'Ticket is already assigned to you.');
        }

        // Verify the target user is actually a staff member
        $newStaff = User::find($validated['staff_id']);
        if (!$newStaff->hasAnyRole(['admin', 'moderator'])) {
            return redirect()->back()
                ->with('error', 'Selected user is not a staff member.');
        }

        // Check if new staff is at capacity
        $newStaffWorkload = $this->getStaffWorkload($newStaff->id);
        if ($newStaffWorkload >= config('tickets.max_staff_workload', 15)) {
            return redirect()->back()
                ->with('error', 'Selected staff member has reached maximum ticket capacity.');
        }

        $supportTicket->assignTo($newStaff);

        // Add reassignment message
        $supportTicket->messages()->create([
            'user_id' => $currentStaff->id,
            'message' => "Ticket reassigned from {$currentStaff->username_pub} to {$newStaff->username_pub}",
            'message_type' => 'assignment_update',
            'is_internal' => true,
        ]);

        return redirect()->route('staff.support.index')
            ->with('success', "Ticket reassigned to {$newStaff->username_pub} successfully.");
    }

    /**
     * Update ticket status (limited scope for staff)
     */
    public function updateStatus(Request $request, SupportTicket $supportTicket)
    {
        $staff = auth()->user();

        // Check if staff can modify this ticket
        if ($supportTicket->assigned_to !== $staff->id) {
            abort(403, 'You can only modify tickets assigned to you.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'pending', 'in_progress', 'on_hold', 'resolved'])],
            'status_reason' => 'nullable|string|max:500',
        ]);

        // Staff cannot close tickets, only resolve them
        if ($validated['status'] === 'closed') {
            return redirect()->back()
                ->with('error', 'Staff members cannot close tickets. Please resolve the ticket instead.');
        }

        $supportTicket->updateStatus($validated['status'], $staff);

        // Add status reason if provided
        if (!empty($validated['status_reason'])) {
            $supportTicket->messages()->create([
                'user_id' => $staff->id,
                'message' => "Status change reason: {$validated['status_reason']}",
                'message_type' => 'status_update',
                'is_internal' => true,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Update ticket priority (limited scope for staff)
     */
    public function updatePriority(Request $request, SupportTicket $supportTicket)
    {
        $staff = auth()->user();

        // Check if staff can modify this ticket
        if ($supportTicket->assigned_to !== $staff->id) {
            abort(403, 'You can only modify tickets assigned to you.');
        }

        $validated = $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])], // Staff cannot set urgent
            'priority_reason' => 'nullable|string|max:500',
        ]);

        $supportTicket->updatePriority($validated['priority'], $staff);

        // Add priority reason if provided
        if (!empty($validated['priority_reason'])) {
            $supportTicket->messages()->create([
                'user_id' => $staff->id,
                'message' => "Priority change reason: {$validated['priority_reason']}",
                'message_type' => 'priority_update',
                'is_internal' => true,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Ticket priority updated successfully.');
    }

    /**
     * Add staff message to support ticket
     */
    public function addMessage(Request $request, SupportTicket $supportTicket)
    {
        $staff = auth()->user();

        // Check if staff can participate in this ticket
        if ($supportTicket->assigned_to !== $staff->id) {
            abort(403, 'You can only respond to tickets assigned to you.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'is_internal' => 'boolean',
        ]);

        $supportTicket->messages()->create([
            'user_id' => $staff->id,
            'message' => $validated['message'],
            'message_type' => 'staff_message',
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        // Update ticket status if it was pending
        if ($supportTicket->status === 'pending') {
            $supportTicket->updateStatus('in_progress', $staff);
        }

        return redirect()->back()
            ->with('success', 'Message added successfully.');
    }

    /**
     * Resolve support ticket (staff can resolve but not close)
     */
    public function resolve(Request $request, SupportTicket $supportTicket)
    {
        $staff = auth()->user();

        // Check if staff can resolve this ticket
        if ($supportTicket->assigned_to !== $staff->id) {
            abort(403, 'You can only resolve tickets assigned to you.');
        }

        $validated = $request->validate([
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $supportTicket->markAsResolved($validated['resolution_notes']);

        // Add resolution message
        $supportTicket->messages()->create([
            'user_id' => $staff->id,
            'message' => "Ticket resolved by staff. Resolution: {$validated['resolution_notes']}",
            'message_type' => 'status_update',
            'is_internal' => false,
        ]);

        return redirect()->route('staff.support.index')
            ->with('success', 'Support ticket resolved successfully.');
    }

    /**
     * Download attachment file
     */
    public function downloadAttachment(SupportTicket $supportTicket, SupportTicketAttachment $attachment)
    {
        $staff = auth()->user();

        // Check if staff can access this ticket
        if ($supportTicket->assigned_to !== $staff->id && $supportTicket->assigned_to !== null) {
            abort(403, 'You can only access attachments from tickets assigned to you.');
        }

        // Verify attachment belongs to this ticket
        if ($attachment->support_ticket_id !== $supportTicket->id) {
            abort(404, 'Attachment not found.');
        }

        // Check if file exists
        if (!Storage::disk('private')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('private')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    /**
     * Get staff dashboard statistics
     */
    public function getDashboardStats()
    {
        $staff = auth()->user();

        $stats = [
            'my_open_tickets' => SupportTicket::where('assigned_to', $staff->id)->open()->count(),
            'my_resolved_tickets' => SupportTicket::where('assigned_to', $staff->id)->where('status', 'resolved')->count(),
            'unassigned_tickets' => SupportTicket::whereNull('assigned_to')->open()->count(),
            'my_avg_response_time_hours' => round(SupportTicket::where('assigned_to', $staff->id)
                    ->whereNotNull('first_response_at')
                    ->get()
                    ->avg(function($ticket) {
                        return $ticket->getResponseTime();
                    }) / 60, 1),
            'my_tickets_today' => SupportTicket::where('assigned_to', $staff->id)
                ->whereDate('created_at', today())
                ->count(),
            'my_resolved_today' => SupportTicket::where('assigned_to', $staff->id)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', today())
                ->count(),
            'high_priority_available' => SupportTicket::whereNull('assigned_to')
                ->whereIn('priority', ['high', 'urgent'])
                ->open()
                ->count(),
            'overdue_assigned' => SupportTicket::where('assigned_to', $staff->id)
                ->overdue()
                ->count(),
            'recent_assigned_tickets' => SupportTicket::where('assigned_to', $staff->id)
                ->with(['user'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get tickets available for assignment
     */
    public function getAvailableTickets(Request $request)
    {
        $query = SupportTicket::whereNull('assigned_to')
            ->open()
            ->with(['user']);

        // Filter by priority if requested
        if ($request->filled('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        // Filter by category if requested
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        $tickets = $query->orderBy('created_at', 'asc')->paginate(10);

        return view('staff.support.available', compact('tickets'));
    }

    /**
     * Staff workload summary
     */
    public function getWorkloadSummary()
    {
        $staff = auth()->user();

        $workload = [
            'assigned_tickets' => SupportTicket::where('assigned_to', $staff->id)->open()->get(),
            'tickets_by_priority' => SupportTicket::where('assigned_to', $staff->id)
                ->open()
                ->selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
            'tickets_by_status' => SupportTicket::where('assigned_to', $staff->id)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'avg_resolution_time_hours' => round(SupportTicket::where('assigned_to', $staff->id)
                ->where('status', 'resolved')
                ->get()
                ->avg(function($ticket) {
                    return $ticket->getResolutionTime();
                }), 1),
            'performance_this_week' => [
                'resolved' => SupportTicket::where('assigned_to', $staff->id)
                    ->where('status', 'resolved')
                    ->whereBetween('resolved_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'avg_response_time' => round(SupportTicket::where('assigned_to', $staff->id)
                        ->whereNotNull('first_response_at')
                        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                        ->get()
                        ->avg(function($ticket) {
                            return $ticket->getResponseTime();
                        }) / 60, 1),
            ],
        ];

        return response()->json($workload);
    }

    /**
     * Mark ticket as needing escalation (request admin attention)
     */
    public function requestEscalation(Request $request, SupportTicket $supportTicket)
    {
        $staff = auth()->user();

        // Check if staff can escalate this ticket
        if ($supportTicket->assigned_to !== $staff->id) {
            abort(403, 'You can only escalate tickets assigned to you.');
        }

        $validated = $request->validate([
            'escalation_reason' => 'required|string|max:500',
        ]);

        // Add internal message requesting escalation
        $supportTicket->messages()->create([
            'user_id' => $staff->id,
            'message' => "ESCALATION REQUESTED: {$validated['escalation_reason']}",
            'message_type' => 'status_update',
            'is_internal' => true,
        ]);

        // Update priority to high if not already urgent
        if (!in_array($supportTicket->priority, ['high', 'urgent'])) {
            $supportTicket->updatePriority('high', $staff);
        }

        return redirect()->back()
            ->with('success', 'Escalation request submitted. An admin will review this ticket.');
    }

    /**
     * Get tickets that need immediate attention
     */
    public function getUrgentTickets()
    {
        $staff = auth()->user();

        $urgentTickets = SupportTicket::where('assigned_to', $staff->id)
            ->where(function($q) {
                $q->where('priority', 'urgent')
                    ->orWhere(function($subQuery) {
                        $subQuery->where('priority', 'high')
                            ->where('created_at', '<=', now()->subHours(24));
                    })
                    ->orWhere('created_at', '<=', now()->subHours(48)); // Overdue
            })
            ->open()
            ->with(['user'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('staff.support.urgent', compact('urgentTickets'));
    }

    /**
     * Bulk assign available tickets to current staff
     */
    public function bulkAssignToMe(Request $request)
    {
        $staff = auth()->user();

        $validated = $request->validate([
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'category' => ['sometimes', Rule::in(['account', 'payments', 'orders', 'technical', 'general'])],
            'limit' => 'sometimes|integer|min:1|max:5', // Limit how many tickets staff can grab at once
        ]);

        // Get available tickets
        $query = SupportTicket::whereNull('assigned_to')->open();

        if ($validated['priority'] ?? null) {
            $query->where('priority', $validated['priority']);
        }

        if ($validated['category'] ?? null) {
            $query->where('category', $validated['category']);
        }

        $limit = $validated['limit'] ?? 3; // Default to 3 tickets
        $tickets = $query->orderBy('created_at', 'asc')->limit($limit)->get();

        $assignedCount = 0;
        foreach ($tickets as $ticket) {
            $ticket->assignTo($staff);
            $assignedCount++;
        }

        return redirect()->back()
            ->with('success', "{$assignedCount} tickets assigned to you successfully.");
    }

    /**
     * Get staff member workload count
     */
    private function getStaffWorkload($staffId)
    {
        return SupportTicket::where('assigned_to', $staffId)
            ->whereIn('status', ['open', 'pending', 'in_progress', 'on_hold'])
            ->count();
    }
}
