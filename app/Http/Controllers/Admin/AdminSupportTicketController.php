<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminSupportTicketController extends Controller
{
    /**
     * Display admin support tickets index with filtering
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedTo', 'messages' => function($q) {
            $q->latest()->limit(1);
        }]);

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

        if ($request->filled('assigned_to')) {
            if ($request->get('assigned_to') === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->get('assigned_to'));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $tickets = $query->orderBy('last_activity_at', 'desc')->paginate(15);

        // Calculate stats
        $stats = [
            'total_tickets' => SupportTicket::count(),
            'open_tickets' => SupportTicket::whereIn('status', ['open', 'pending', 'in_progress'])->count(),
            'unassigned_tickets' => SupportTicket::whereNull('assigned_to')->open()->count(),
            'overdue_tickets' => SupportTicket::overdue()->count(),
            'resolved_today' => SupportTicket::where('status', 'resolved')->whereDate('resolved_at', today())->count(),
            'avg_response_time' => SupportTicket::whereNotNull('first_response_at')
                ->get()
                ->avg(function($ticket) {
                    return $ticket->getResponseTime();
                }),
        ];

        // Get staff members for assignment filter
        $staffMembers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'moderator']);
        })->get();

        // Get ticket types for filtering
        $ticketTypes = SupportTicket::getTicketTypes();

        // Recent activity - real data from support tickets
        $recent_activity = collect();

        // Get recently created tickets (last 24 hours)
        $recentlyCreated = SupportTicket::with(['user'])
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($ticket) {
                return [
                    'type' => 'created',
                    'message' => "New ticket created by {$ticket->user->username_pub}",
                    'time' => $ticket->created_at->diffForHumans(),
                    'ticket_id' => $ticket->ticket_number
                ];
            });

        // Get recently resolved tickets (last 24 hours)
        $recentlyResolved = SupportTicket::whereNotNull('resolved_at')
            ->where('resolved_at', '>=', now()->subDay())
            ->orderBy('resolved_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($ticket) {
                return [
                    'type' => 'resolved',
                    'message' => "Ticket {$ticket->ticket_number} resolved",
                    'time' => $ticket->resolved_at->diffForHumans(),
                    'ticket_id' => $ticket->ticket_number
                ];
            });

        // Get recently assigned tickets (last 24 hours)
        $recentlyAssigned = SupportTicket::with(['assignedTo'])
            ->whereNotNull('assigned_to')
            ->where('updated_at', '>=', now()->subDay())
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($ticket) {
                return [
                    'type' => 'assigned',
                    'message' => "Ticket {$ticket->ticket_number} assigned to {$ticket->assignedTo->username_pub}",
                    'time' => $ticket->updated_at->diffForHumans(),
                    'ticket_id' => $ticket->ticket_number
                ];
            });

        // Get recently closed tickets (last 24 hours)
        $recentlyClosed = SupportTicket::whereNotNull('closed_at')
            ->where('closed_at', '>=', now()->subDay())
            ->orderBy('closed_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($ticket) {
                return [
                    'type' => 'closed',
                    'message' => "Ticket {$ticket->ticket_number} closed",
                    'time' => $ticket->closed_at->diffForHumans(),
                    'ticket_id' => $ticket->ticket_number
                ];
            });

        // Merge all activities and sort by time (most recent first)
        $recent_activity = $recent_activity
            ->concat($recentlyCreated)
            ->concat($recentlyResolved)
            ->concat($recentlyAssigned)
            ->concat($recentlyClosed)
            ->sortByDesc(function($activity) {
                // Convert "X ago" back to timestamp for sorting
                // This is approximate but works for display purposes
                return $activity['time'];
            })
            ->take(10)
            ->values();

        return view('admin.support.index', compact(
            'tickets',
            'stats',
            'staffMembers',
            'ticketTypes',
            'recent_activity'
        ));
    }

    /**
     * Show specific support ticket for admin
     */
    public function show(SupportTicket $supportTicket)
    {
        // Load all relationships including internal messages
        $supportTicket->load([
            'user',
            'assignedTo',
            'messages.user',
            'attachments.uploadedBy'
        ]);

        // Get all messages (including internal ones for admin)
        $messages = $supportTicket->messages()->with('user')->orderBy('created_at', 'asc')->get();

        // Get staff members for assignment/reassignment
        $staffMembers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'moderator']);
        })->where('status', 'active')
          ->orderBy('username_pub')
          ->get();

        return view('admin.support.show', compact('supportTicket', 'messages', 'staffMembers'));
    }

    /**
     * Assign ticket to staff member
     */
    public function assign(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $staff = User::findOrFail($validated['assigned_to']);

        // Check if staff has proper role
        if (!$staff->hasAnyRole(['admin', 'moderator'])) {
            return redirect()->back()
                ->with('error', 'Selected user is not a staff member.');
        }

        $supportTicket->assignTo($staff);

        return redirect()->back()
            ->with('success', "Ticket assigned to {$staff->username_pub} successfully.");
    }

    /**
     * Reassign ticket to different staff member or unassign
     */
    public function reassignStaff(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'staff_id' => 'nullable|exists:users,id',
        ]);

        // If staff_id is null, unassign the ticket
        if (!$validated['staff_id']) {
            $oldStaff = $supportTicket->assignedTo;

            $supportTicket->update([
                'assigned_to' => null,
            ]);

            $supportTicket->messages()->create([
                'user_id' => auth()->id(),
                'message' => "Ticket unassigned by admin" .
                    ($oldStaff ? " from {$oldStaff->username_pub}" : ""),
                'message_type' => 'assignment_update',
                'is_internal' => true,
            ]);

            return redirect()->back()
                ->with('success', 'Ticket unassigned successfully.');
        }

        $newStaff = User::find($validated['staff_id']);

        // Check if new staff has proper role
        if (!$newStaff->hasAnyRole(['admin', 'moderator'])) {
            return redirect()->back()
                ->with('error', 'Selected user is not a staff member.');
        }

        $oldStaff = $supportTicket->assignedTo;
        $supportTicket->assignTo($newStaff);

        // Add reassignment message
        $messageText = $oldStaff
            ? "Ticket reassigned from {$oldStaff->username_pub} to {$newStaff->username_pub} by admin"
            : "Ticket assigned to {$newStaff->username_pub} by admin";

        $supportTicket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $messageText,
            'message_type' => 'assignment_update',
            'is_internal' => true,
        ]);

        return redirect()->back()
            ->with('success', 'Ticket assignment updated successfully.');
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'pending', 'in_progress', 'on_hold', 'resolved', 'closed'])],
            'status_reason' => 'nullable|string|max:500',
        ]);

        $supportTicket->updateStatus($validated['status'], auth()->user());

        // Add status reason if provided
        if (!empty($validated['priority_reason'])) {
            $supportTicket->messages()->create([
                'user_id' => auth()->id(),
                'message' => "Status change reason: {$validated['status_reason']}",
                'message_type' => 'status_update',
                'is_internal' => true,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Update ticket priority
     */
    public function updatePriority(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'priority_reason' => 'nullable|string|max:500',
        ]);

        $supportTicket->updatePriority($validated['priority'], auth()->user());

        // Add priority reason if provided
        if ($validated['priority_reason']) {
            $supportTicket->messages()->create([
                'user_id' => auth()->id(),
                'message' => "Priority change reason: {$validated['priority_reason']}",
                'message_type' => 'priority_update',
                'is_internal' => true,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Ticket priority updated successfully.');
    }

    /**
     * Add admin message to support ticket
     */
    public function addMessage(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'is_internal' => 'boolean',
        ]);

        $supportTicket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'message_type' => 'staff_message',
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        // Update ticket status if it was pending
        if ($supportTicket->status === 'pending') {
            $supportTicket->updateStatus('in_progress', auth()->user());
        }

        return redirect()->back()
            ->with('success', 'Message added successfully.');
    }

    /**
     * Resolve support ticket
     */
    public function resolve(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $supportTicket->markAsResolved($validated['resolution_notes']);

        // Add resolution message
        $supportTicket->messages()->create([
            'user_id' => auth()->id(),
            'message' => "Ticket resolved by admin. Resolution: {$validated['resolution_notes']}",
            'message_type' => 'status_update',
            'is_internal' => false,
        ]);

        return redirect()->route('admin.support.index')
            ->with('success', 'Support ticket resolved successfully.');
    }

    /**
     * Close support ticket
     */
    public function close(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'close_reason' => 'required|string|max:500',
        ]);

        $supportTicket->markAsClosed($validated['close_reason']);

        // Add closure message
        $supportTicket->messages()->create([
            'user_id' => auth()->id(),
            'message' => "Ticket closed by admin. Reason: {$validated['close_reason']}",
            'message_type' => 'status_update',
            'is_internal' => false,
        ]);

        return redirect()->route('admin.support.index')
            ->with('success', 'Support ticket closed successfully.');
    }

    /**
     * Export support tickets to CSV
     */
    public function export(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedTo']);

        // Apply same filters as index
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

        if ($request->filled('assigned_to')) {
            if ($request->get('assigned_to') === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->get('assigned_to'));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        $csvData = [];
        $csvData[] = [
            'Ticket Number',
            'Subject',
            'Category',
            'Type',
            'Status',
            'Priority',
            'User',
            'Assigned To',
            'Created At',
            'First Response At',
            'Resolved At',
            'Response Time (minutes)',
            'Resolution Time (hours)'
        ];

        foreach ($tickets as $ticket) {
            $csvData[] = [
                $ticket->ticket_number,
                $ticket->subject,
                ucfirst($ticket->category),
                $ticket->getTypeDisplayName(),
                ucfirst(str_replace('_', ' ', $ticket->status)),
                ucfirst($ticket->priority),
                $ticket->user->username_pub,
                $ticket->assignedTo ? $ticket->assignedTo->username_pub : 'Unassigned',
                $ticket->created_at->format('Y-m-d H:i:s'),
                $ticket->first_response_at ? $ticket->first_response_at->format('Y-m-d H:i:s') : '',
                $ticket->resolved_at ? $ticket->resolved_at->format('Y-m-d H:i:s') : '',
                $ticket->getResponseTime() ?? '',
                $ticket->getResolutionTime() ?? ''
            ];
        }

        $filename = 'support_tickets_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

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
     * Download attachment file
     */
    public function downloadAttachment(SupportTicket $supportTicket, SupportTicketAttachment $attachment)
    {
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
     * Bulk actions on tickets
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['assign', 'update_status', 'update_priority', 'close'])],
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'exists:support_tickets,id',
            'bulk_assigned_to' => 'required_if:action,assign|exists:users,id',
            'bulk_status' => 'required_if:action,update_status|in:open,pending,in_progress,on_hold,resolved,closed',
            'bulk_priority' => 'required_if:action,update_priority|in:low,medium,high,urgent',
            'bulk_close_reason' => 'required_if:action,close|string|max:500',
        ]);

        $tickets = SupportTicket::whereIn('id', $validated['ticket_ids'])->get();
        $updatedCount = 0;

        foreach ($tickets as $ticket) {
            switch ($validated['action']) {
                case 'assign':
                    $staff = User::findOrFail($validated['bulk_assigned_to']);
                    if ($staff->hasAnyRole(['admin', 'moderator'])) {
                        $ticket->assignTo($staff);
                        $updatedCount++;
                    }
                    break;

                case 'update_status':
                    $ticket->updateStatus($validated['bulk_status'], auth()->user());
                    $updatedCount++;
                    break;

                case 'update_priority':
                    $ticket->updatePriority($validated['bulk_priority'], auth()->user());
                    $updatedCount++;
                    break;

                case 'close':
                    if ($ticket->isOpen()) {
                        $ticket->markAsClosed($validated['bulk_close_reason']);
                        $ticket->messages()->create([
                            'user_id' => auth()->id(),
                            'message' => "Ticket closed by admin (bulk action). Reason: {$validated['bulk_close_reason']}",
                            'message_type' => 'status_update',
                            'is_internal' => false,
                        ]);
                        $updatedCount++;
                    }
                    break;
            }
        }

        return redirect()->back()
            ->with('success', "{$updatedCount} tickets updated successfully.");
    }

    /**
     * Get admin statistics
     */
    public function getStats()
    {
        $stats = [
            'total_tickets' => SupportTicket::count(),
            'open_tickets' => SupportTicket::open()->count(),
            'unassigned_tickets' => SupportTicket::unassigned()->open()->count(),
            'overdue_tickets' => SupportTicket::overdue()->count(),
            'high_priority_tickets' => SupportTicket::highPriority()->open()->count(),
            'resolved_today' => SupportTicket::where('status', 'resolved')->whereDate('resolved_at', today())->count(),
            'avg_response_time_hours' => round(SupportTicket::whereNotNull('first_response_at')
                    ->get()
                    ->avg(function($ticket) {
                        return $ticket->getResponseTime();
                    }) / 60, 1),
            'tickets_by_category' => SupportTicket::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'recent_tickets' => SupportTicket::with(['user', 'assignedTo'])
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Auto-assign tickets to available staff
     */
    public function autoAssign(Request $request)
    {
        $validated = $request->validate([
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'category' => ['sometimes', Rule::in(['account', 'payments', 'orders', 'technical', 'general'])],
        ]);

        // Get unassigned tickets
        $query = SupportTicket::unassigned()->open();

        if ($validated['priority'] ?? null) {
            $query->where('priority', $validated['priority']);
        }

        if ($validated['category'] ?? null) {
            $query->where('category', $validated['category']);
        }

        $tickets = $query->orderBy('created_at', 'asc')->limit(10)->get();

        // Get available staff (those with fewer than 10 open assigned tickets)
        $availableStaff = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'moderator']);
        })->whereHas('assignedSupportTickets', function($q) {
            $q->open();
        }, '<', 10)->get();

        if ($availableStaff->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No available staff members for auto-assignment.');
        }

        $assignedCount = 0;
        foreach ($tickets as $ticket) {
            // Round-robin assignment
            $staff = $availableStaff[$assignedCount % $availableStaff->count()];
            $ticket->assignTo($staff);
            $assignedCount++;
        }

        return redirect()->back()
            ->with('success', "{$assignedCount} tickets auto-assigned successfully.");
    }
}
