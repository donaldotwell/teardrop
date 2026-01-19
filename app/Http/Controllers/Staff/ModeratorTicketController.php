<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModeratorTicketController extends Controller
{
    /**
     * Display ticket management dashboard for moderators
     */
    public function index(Request $request)
    {
        $moderator = auth()->user();

        $query = SupportTicket::with(['user', 'assignedTo', 'messages' => function($q) {
            $q->latest()->limit(1);
        }]);

        // Moderators can see tickets assigned to them + unassigned tickets + moderator-specific categories
        $query->where(function($q) use ($moderator) {
            $q->where('assigned_to', $moderator->id)
                ->orWhereNull('assigned_to')
                ->orWhereIn('category', ['account_issues', 'user_reports', 'content_moderation', 'dispute_appeals']);
        });

        // Apply assignment filter
        if ($request->filled('assignment')) {
            switch ($request->assignment) {
                case 'mine':
                    $query->where('assigned_to', $moderator->id);
                    break;
                case 'unassigned':
                    $query->whereNull('assigned_to');
                    break;
                case 'team':
                    $query->whereHas('assignedTo', function($q) {
                        $q->whereHas('roles', function($roleQuery) {
                            $roleQuery->whereIn('name', ['moderator', 'admin']);
                        });
                    });
                    break;
                case 'escalated':
                    $query->where('priority', 'urgent')
                        ->orWhere('status', 'escalated');
                    break;
            }
        }

        // Apply category filter (moderator-relevant categories)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'like', "%{$search}%");
                    });
            });
        }

        // Apply date filter
        $this->applyDateFilter($query, $request);

        $tickets = $query->orderBy('priority', 'desc')
            ->orderBy('last_activity_at', 'desc')
            ->paginate(20);

        // Calculate statistics
        $stats = $this->getTicketStats($moderator);

        // Get team members for assignment
        $teamMembers = $this->getTeamMembers();

        return view('moderator.tickets.index', compact('tickets', 'stats', 'teamMembers'));
    }

    /**
     * Show ticket details
     */
    public function show(SupportTicket $supportTicket)
    {
        $moderator = auth()->user();

        // Check if moderator can view this ticket
        if (!$this->canModeratorAccessTicket($supportTicket, $moderator)) {
            abort(403, 'You cannot access this ticket.');
        }

        $supportTicket->load([
            'user',
            'assignedTo',
            'messages.user',
            'attachments.uploadedBy'
        ]);

        // Get all messages (including internal ones for moderators)
        $messages = $supportTicket->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get related user reports if this is a user-related ticket
        $relatedReports = $this->getRelatedUserReports($supportTicket);

        // Get ticket timeline
        $timeline = $this->getTicketTimeline($supportTicket);

        // Get team members for escalation dropdown
        $teamMembers = $this->getTeamMembers();

        return view('moderator.tickets.show', compact('supportTicket', 'messages', 'relatedReports', 'timeline', 'teamMembers'));
    }

    /**
     * Assign ticket to current moderator
     */
    public function assign(SupportTicket $supportTicket)
    {
        $moderator = auth()->user();

        // Check if ticket is already assigned
        if ($supportTicket->assignedTo && $supportTicket->assignedTo->id !== $moderator->id) {
            return redirect()->back()
                ->with('error', 'This ticket is already assigned to another team member.');
        }

        // Check moderator workload
        $currentWorkload = $this->getModeratorWorkload($moderator->id);
        if ($currentWorkload >= config('tickets.max_moderator_workload', 15)) {
            return redirect()->back()
                ->with('error', 'You have reached your maximum ticket assignment limit.');
        }

        $supportTicket->assignTo($moderator);

        // Add assignment message
        $supportTicket->messages()->create([
            'user_id' => $moderator->id,
            'message' => "Ticket assigned to moderator: {$moderator->username_pub}",
            'message_type' => 'assignment_update',
            'is_internal' => true,
        ]);

        // Log the action
        AuditLog::log('ticket_assigned', $moderator->id, [
            'ticket_id' => $supportTicket->id,
            'ticket_number' => $supportTicket->ticket_number,
            'assignment_type' => 'self_assigned'
        ]);

        return redirect()->back()
            ->with('success', 'Ticket assigned to you successfully.');
    }

    /**
     * Unassign ticket from current moderator
     */
    public function unassign(SupportTicket $supportTicket)
    {
        $moderator = auth()->user();

        // Only allow unassigning if assigned to current moderator
        if (!$supportTicket->assignedTo || $supportTicket->assignedTo->id !== $moderator->id) {
            return redirect()->back()
                ->with('error', 'You can only unassign tickets assigned to you.');
        }

        $supportTicket->update([
            'assigned_to' => null,
            'status' => $supportTicket->status === 'in_progress' ? 'open' : $supportTicket->status
        ]);

        // Add unassignment message
        $supportTicket->messages()->create([
            'user_id' => $moderator->id,
            'message' => "Ticket unassigned by moderator: {$moderator->username_pub}",
            'message_type' => 'assignment_update',
            'is_internal' => true,
        ]);

        // Log the action
        AuditLog::log('ticket_unassigned', $moderator->id, [
            'ticket_id' => $supportTicket->id,
            'ticket_number' => $supportTicket->ticket_number
        ]);

        return redirect()->back()
            ->with('success', 'Ticket unassigned successfully.');
    }

    /**
     * Add moderator response to ticket
     */
    public function addResponse(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'is_internal' => 'boolean',
            'status_change' => 'nullable|in:open,in_progress,on_hold,pending_user,resolved'
        ]);

        $moderator = auth()->user();

        // Check if moderator can respond to this ticket
        if (!$this->canModeratorAccessTicket($supportTicket, $moderator)) {
            abort(403, 'You cannot respond to this ticket.');
        }

        // Add the response message
        $supportTicket->messages()->create([
            'user_id' => $moderator->id,
            'message' => $request->message,
            'message_type' => 'staff_message',
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        // Update status if requested
        if ($request->filled('status_change')) {
            $supportTicket->updateStatus($request->status_change, $moderator);
        } elseif ($supportTicket->status === 'open') {
            // Auto-change to in_progress if responding to open ticket
            $supportTicket->updateStatus('in_progress', $moderator);
        }

        // Auto-assign if not assigned
        if (!$supportTicket->assignedTo) {
            $supportTicket->assignTo($moderator);
        }

        // Log the action
        AuditLog::log('ticket_response_added', $moderator->id, [
            'ticket_id' => $supportTicket->id,
            'ticket_number' => $supportTicket->ticket_number,
            'response_type' => $request->boolean('is_internal') ? 'internal' : 'public'
        ]);

        return redirect()->back()
            ->with('success', 'Response added successfully.');
    }

    /**
     * Escalate ticket to admin
     */
    public function escalate(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'escalation_reason' => 'required|string|max:500',
            'escalate_to' => 'nullable|exists:users,id'
        ]);

        $moderator = auth()->user();

        // Update ticket priority and add escalation flag
        $supportTicket->update([
            'priority' => 'urgent',
            'status' => 'escalated',
            'metadata' => array_merge($supportTicket->metadata ?? [], [
                'escalated_by' => $moderator->id,
                'escalated_at' => now()->toISOString(),
                'escalation_reason' => $request->escalation_reason
            ])
        ]);

        // Reassign to admin if specified
        if ($request->filled('escalate_to')) {
            $admin = User::findOrFail($request->escalate_to);
            $supportTicket->assignTo($admin);
        }

        // Add escalation message
        $supportTicket->messages()->create([
            'user_id' => $moderator->id,
            'message' => "Ticket escalated to admin. Reason: {$request->escalation_reason}",
            'message_type' => 'escalation',
            'is_internal' => true,
        ]);

        // Log the action
        AuditLog::log('ticket_escalated', $moderator->id, [
            'ticket_id' => $supportTicket->id,
            'ticket_number' => $supportTicket->ticket_number,
            'reason' => $request->escalation_reason,
            'escalated_to' => $request->escalate_to
        ]);

        return redirect()->back()
            ->with('success', 'Ticket escalated to admin successfully.');
    }

    /**
     * Resolve ticket with moderator solution
     */
    public function resolve(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'resolution_notes' => 'required|string|max:1000',
            'follow_up_required' => 'boolean'
        ]);

        $moderator = auth()->user();

        // Check if moderator can resolve this ticket
        if (!$supportTicket->assignedTo || $supportTicket->assignedTo->id !== $moderator->id) {
            return redirect()->back()
                ->with('error', 'You can only resolve tickets assigned to you.');
        }

        $supportTicket->markAsResolved($request->resolution_notes);

        // Add follow-up flag if needed
        if ($request->boolean('follow_up_required')) {
            $supportTicket->update([
                'metadata' => array_merge($supportTicket->metadata ?? [], [
                    'follow_up_required' => true,
                    'follow_up_date' => now()->addDays(3)->toISOString()
                ])
            ]);
        }

        // Add resolution message
        $supportTicket->messages()->create([
            'user_id' => $moderator->id,
            'message' => "Ticket resolved by moderator. Resolution: {$request->resolution_notes}",
            'message_type' => 'status_update',
            'is_internal' => false,
        ]);

        // Log the action
        AuditLog::log('ticket_resolved', $moderator->id, [
            'ticket_id' => $supportTicket->id,
            'ticket_number' => $supportTicket->ticket_number,
            'resolution_notes' => $request->resolution_notes,
            'follow_up_required' => $request->boolean('follow_up_required')
        ]);

        return redirect()->route('moderator.tickets.index')
            ->with('success', 'Ticket resolved successfully.');
    }

    /**
     * Auto-assign tickets to available moderators
     */
    public function autoAssign()
    {
        $availableModerators = $this->getAvailableModerators();

        if ($availableModerators->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No moderators available for auto-assignment.');
        }

        // Get unassigned tickets in moderator categories
        $unassignedTickets = SupportTicket::whereNull('assigned_to')
            ->whereIn('category', ['account_issues', 'user_reports', 'content_moderation', 'dispute_appeals'])
            ->whereIn('status', ['open', 'pending'])
            ->where('created_at', '>', now()->subHours(48)) // Only recent tickets
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();

        $assignedCount = 0;
        foreach ($unassignedTickets as $ticket) {
            // Round-robin assignment
            $moderator = $availableModerators[$assignedCount % $availableModerators->count()];

            $ticket->assignTo($moderator);

            // Add assignment message
            $ticket->messages()->create([
                'user_id' => auth()->id(),
                'message' => "Ticket auto-assigned to moderator: {$moderator->username_pub}",
                'message_type' => 'assignment_update',
                'is_internal' => true,
            ]);

            $assignedCount++;
        }

        // Log the action
        AuditLog::log('tickets_auto_assigned', auth()->id(), [
            'assigned_count' => $assignedCount,
            'moderators' => $availableModerators->pluck('username_pub')->toArray()
        ]);

        return redirect()->back()
            ->with('success', "{$assignedCount} tickets auto-assigned successfully.");
    }

    /**
     * Get ticket statistics for moderator dashboard
     */
    private function getTicketStats($moderator)
    {
        return [
            'my_tickets' => SupportTicket::where('assigned_to', $moderator->id)
                ->whereIn('status', ['open', 'in_progress', 'pending_user'])
                ->count(),
            'unassigned_tickets' => SupportTicket::whereNull('assigned_to')
                ->whereIn('category', ['account_issues', 'user_reports', 'content_moderation', 'dispute_appeals'])
                ->whereIn('status', ['open', 'pending'])
                ->count(),
            'urgent_tickets' => SupportTicket::where('priority', 'urgent')
                ->whereIn('status', ['open', 'in_progress', 'escalated'])
                ->count(),
            'my_resolved_today' => SupportTicket::where('assigned_to', $moderator->id)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', today())
                ->count(),
            'team_tickets' => SupportTicket::whereHas('assignedTo', function($q) {
                $q->whereHas('roles', function($roleQuery) {
                    $roleQuery->whereIn('name', ['moderator', 'admin']);
                });
            })
                ->whereIn('status', ['open', 'in_progress', 'pending_user'])
                ->count(),
            'overdue_tickets' => SupportTicket::where('assigned_to', $moderator->id)
                ->where('created_at', '<', now()->subDays(2))
                ->whereIn('status', ['open', 'in_progress'])
                ->count(),
            'avg_response_time' => $this->getAverageResponseTime($moderator->id),
            'escalated_tickets' => SupportTicket::where('status', 'escalated')->count()
        ];
    }

    /**
     * Check if moderator can access ticket
     */
    private function canModeratorAccessTicket($ticket, $moderator)
    {
        // Moderators can access:
        // 1. Tickets assigned to them
        // 2. Unassigned tickets in their categories
        // 3. Tickets in moderator-relevant categories
        return $ticket->assigned_to === $moderator->id ||
            !$ticket->assigned_to ||
            in_array($ticket->category, ['account_issues', 'user_reports', 'content_moderation', 'dispute_appeals']) ||
            $moderator->hasRole('admin');
    }

    /**
     * Get available moderators for assignment
     */
    private function getAvailableModerators()
    {
        return User::whereHas('roles', function($q) {
            $q->whereIn('name', ['moderator']);
        })
            ->where('status', 'active')
            ->get()
            ->filter(function($moderator) {
                return $this->getModeratorWorkload($moderator->id) < config('tickets.max_moderator_workload', 15);
            });
    }

    /**
     * Get moderator workload count
     */
    private function getModeratorWorkload($moderatorId)
    {
        return SupportTicket::where('assigned_to', $moderatorId)
            ->whereIn('status', ['open', 'in_progress', 'pending_user'])
            ->count();
    }

    /**
     * Get team members for assignment
     */
    private function getTeamMembers()
    {
        return User::whereHas('roles', function($q) {
            $q->whereIn('name', ['moderator', 'admin']);
        })
            ->orderBy('username_pub')
            ->get(['id', 'username_pub']);
    }

    /**
     * Apply date filter to query
     */
    private function applyDateFilter($query, Request $request)
    {
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->where('created_at', '>=', now()->startOfWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->startOfMonth());
                    break;
            }
        }
    }

    /**
     * Get related user reports for context
     */
    private function getRelatedUserReports($ticket)
    {
        if (!$ticket->user) return collect();

        // Get recent forum reports involving this user
        return \App\Models\ForumReport::where(function($q) use ($ticket) {
            $q->where('user_id', $ticket->user->id)
                ->orWhereHas('reportable', function($subQuery) use ($ticket) {
                    $subQuery->where('user_id', $ticket->user->id);
                });
        })
            ->with(['reportable', 'user'])
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * Get ticket timeline
     */
    private function getTicketTimeline($ticket)
    {
        $timeline = collect();

        // Add creation event
        $timeline->push([
            'type' => 'created',
            'title' => 'Ticket Created',
            'description' => "Created by {$ticket->user->username_pub}",
            'timestamp' => $ticket->created_at,
            'user' => $ticket->user
        ]);

        // Add assignment events from messages
        $assignmentMessages = $ticket->messages()
            ->where('message_type', 'assignment_update')
            ->with('user')
            ->get();

        foreach ($assignmentMessages as $msg) {
            $timeline->push([
                'type' => 'assignment',
                'title' => 'Assignment Update',
                'description' => $msg->message,
                'timestamp' => $msg->created_at,
                'user' => $msg->user
            ]);
        }

        // Add status changes
        if ($ticket->resolved_at) {
            $timeline->push([
                'type' => 'resolved',
                'title' => 'Ticket Resolved',
                'description' => 'Ticket marked as resolved',
                'timestamp' => $ticket->resolved_at,
                'user' => $ticket->assignedTo
            ]);
        }

        return $timeline->sortBy('timestamp');
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime($moderatorId)
    {
        $tickets = SupportTicket::where('assigned_to', $moderatorId)
            ->whereNotNull('first_response_at')
            ->select('created_at', 'first_response_at')
            ->get();

        if ($tickets->isEmpty()) return 'N/A';

        $avgMinutes = $tickets->avg(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->first_response_at);
        });

        if ($avgMinutes < 60) {
            return round($avgMinutes) . ' min';
        } elseif ($avgMinutes < 1440) {
            return round($avgMinutes / 60, 1) . ' hrs';
        } else {
            return round($avgMinutes / 1440, 1) . ' days';
        }
    }
}
