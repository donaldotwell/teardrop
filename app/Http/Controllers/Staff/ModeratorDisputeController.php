<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModeratorDisputeController extends Controller
{
    /**
     * Display dispute management dashboard
     */
    public function index(Request $request)
    {
        $query = Dispute::with(['order.listing', 'initiatedBy', 'disputedAgainst', 'assignedModerator', 'messages'])
            ->whereIn('status', ['open', 'under_review', 'waiting_vendor', 'waiting_buyer', 'escalated'])
            ->latest();

        // Apply assignment filter
        if ($request->filled('assignment')) {
            switch ($request->assignment) {
                case 'mine':
                    $query->where('assigned_moderator_id', auth()->id());
                    break;
                case 'unassigned':
                    $query->whereNull('assigned_moderator_id');
                    break;
                case 'auto_assigned':
                    $query->whereNotNull('assigned_moderator_id')
                        ->where('auto_assigned', true);
                    break;
                case 'manual_assigned':
                    $query->whereNotNull('assigned_moderator_id')
                        ->where('auto_assigned', false);
                    break;
            }
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
                $q->where('dispute_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function($orderQuery) use ($search) {
                        $orderQuery->where('uuid', 'like', "%{$search}%");
                    })
                    ->orWhereHas('initiatedBy', function($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'like', "%{$search}%");
                    })
                    ->orWhereHas('disputedAgainst', function($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'like', "%{$search}%");
                    });
            });
        }

        // Apply date filter
        $this->applyDateFilter($query, $request);

        $disputes = $query->paginate(20);

        // Calculate statistics
        $stats = $this->getDisputeStats();

        // Get moderators for assignment filter
        $moderators = $this->getModerators();

        return view('moderator.disputes.index', compact('disputes', 'stats', 'moderators'));
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
            'assignedModerator',
            'assignedAdmin',
            'messages.user',
            'evidence.uploadedBy'
        ]);

        // Get all messages visible to moderators
        $messages = $dispute->messages()
            ->with('user')
            ->where(function($q) {
                $q->where('is_internal', false)
                    ->orWhere('message_type', 'moderator_note');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Get timeline of key events
        $timeline = $this->getDisputeTimeline($dispute);

        // Get moderators for reassignment
        $moderators = User::whereHas('roles', function($q) {
            $q->where('name', 'moderator');
        })->where('status', 'active')
          ->orderBy('username_pub')
          ->get();

        return view('moderator.disputes.show', compact('dispute', 'messages', 'timeline', 'moderators'));
    }

    /**
     * Assign dispute to current moderator
     */
    public function assign(Dispute $dispute)
    {
        $moderator = auth()->user();

        // Check if dispute is already assigned to someone else
        if ($dispute->assignedModerator && $dispute->assignedModerator->id !== $moderator->id) {
            return redirect()->back()
                ->with('error', 'This dispute is already assigned to another moderator.');
        }

        // Check moderator workload
        $currentWorkload = $this->getModeratorWorkload($moderator->id);
        if ($currentWorkload >= config('disputes.max_moderator_workload', 10)) {
            return redirect()->back()
                ->with('error', 'You have reached your maximum dispute assignment limit.');
        }

        $dispute->update([
            'assigned_moderator_id' => $moderator->id,
            'assigned_at' => now(),
            'auto_assigned' => false,
            'status' => 'under_review'
        ]);

        // Add assignment message
        $dispute->messages()->create([
            'user_id' => $moderator->id,
            'message' => "Dispute assigned to moderator: {$moderator->username_pub}",
            'message_type' => 'assignment_update',
            'is_internal' => true,
        ]);

        // Log the action
        AuditLog::log('dispute_assigned', $moderator->id, [
            'dispute_id' => $dispute->id,
            'dispute_number' => $dispute->dispute_number,
            'assignment_type' => 'manual'
        ]);

        return redirect()->back()
            ->with('success', 'Dispute assigned to you successfully.');
    }

    /**
     * Unassign dispute from current moderator
     */
    public function unassign(Dispute $dispute)
    {
        $moderator = auth()->user();

        // Only allow unassigning if assigned to current moderator
        if (!$dispute->assignedModerator || $dispute->assignedModerator->id !== $moderator->id) {
            return redirect()->back()
                ->with('error', 'You can only unassign disputes assigned to you.');
        }

        $dispute->update([
            'assigned_moderator_id' => null,
            'assigned_at' => null,
            'status' => 'open'
        ]);

        // Add unassignment message
        $dispute->messages()->create([
            'user_id' => $moderator->id,
            'message' => "Dispute unassigned by moderator: {$moderator->username_pub}",
            'message_type' => 'assignment_update',
            'is_internal' => true,
        ]);

        // Log the action
        AuditLog::log('dispute_unassigned', $moderator->id, [
            'dispute_id' => $dispute->id,
            'dispute_number' => $dispute->dispute_number
        ]);

        return redirect()->back()
            ->with('success', 'Dispute unassigned successfully.');
    }

    /**
     * Reassign dispute to another moderator
     */
    public function reassignModerator(Request $request, Dispute $dispute)
    {
        $currentModerator = auth()->user();

        // Only allow reassigning if assigned to current moderator
        if (!$dispute->assignedModerator || $dispute->assignedModerator->id !== $currentModerator->id) {
            return redirect()->back()
                ->with('error', 'You can only reassign disputes assigned to you.');
        }

        $validated = $request->validate([
            'moderator_id' => 'required|exists:users,id',
        ]);

        // Don't allow reassigning to self
        if ($validated['moderator_id'] == $currentModerator->id) {
            return redirect()->back()
                ->with('error', 'Dispute is already assigned to you.');
        }

        // Verify the target user is actually a moderator
        $newModerator = User::find($validated['moderator_id']);
        if (!$newModerator->hasRole('moderator')) {
            return redirect()->back()
                ->with('error', 'Selected user is not a moderator.');
        }

        // Check if new moderator is at capacity
        $newModeratorWorkload = $this->getModeratorWorkload($newModerator->id);
        if ($newModeratorWorkload >= config('disputes.max_moderator_workload', 10)) {
            return redirect()->back()
                ->with('error', 'Selected moderator has reached maximum dispute capacity.');
        }

        $dispute->update([
            'assigned_moderator_id' => $newModerator->id,
            'assigned_at' => now(),
            'auto_assigned' => false,
        ]);

        // Add reassignment message
        $dispute->messages()->create([
            'user_id' => $currentModerator->id,
            'message' => "Dispute reassigned from {$currentModerator->username_pub} to {$newModerator->username_pub}",
            'message_type' => 'assignment_update',
            'is_internal' => true,
        ]);

        // Log the action
        AuditLog::log('dispute_moderator_reassigned', $currentModerator->id, [
            'dispute_id' => $dispute->id,
            'dispute_number' => $dispute->dispute_number,
            'from_moderator_id' => $currentModerator->id,
            'to_moderator_id' => $newModerator->id,
        ]);

        return redirect()->route('moderator.disputes.index')
            ->with('success', "Dispute reassigned to {$newModerator->username_pub} successfully.");
    }

    /**
     * Add moderator note to dispute
     */
    public function addNote(Request $request, Dispute $dispute)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
            'is_internal' => 'boolean'
        ]);

        $moderator = auth()->user();

        $dispute->messages()->create([
            'user_id' => $moderator->id,
            'message' => $request->note,
            'message_type' => 'moderator_note',
            'is_internal' => $request->boolean('is_internal', true),
        ]);

        // Log the action
        AuditLog::log('dispute_note_added', $moderator->id, [
            'dispute_id' => $dispute->id,
            'dispute_number' => $dispute->dispute_number,
            'note_type' => $request->boolean('is_internal') ? 'internal' : 'public'
        ]);

        return redirect()->back()
            ->with('success', 'Note added successfully.');
    }

    /**
     * Request information from dispute parties
     */
    public function requestInfo(Request $request, Dispute $dispute)
    {
        $request->validate([
            'target' => 'required|in:buyer,vendor,both',
            'request_message' => 'required|string|max:500',
            'deadline' => 'nullable|date|after:now'
        ]);

        $moderator = auth()->user();
        $deadline = $request->deadline ? \Carbon\Carbon::parse($request->deadline) : now()->addDays(3);

        // Update dispute status based on target
        $newStatus = match($request->target) {
            'buyer' => 'waiting_buyer',
            'vendor' => 'waiting_vendor',
            'both' => 'waiting_both',
        };

        $dispute->update([
            'status' => $newStatus,
            'info_request_deadline' => $deadline
        ]);

        // Add information request message
        $dispute->messages()->create([
            'user_id' => $moderator->id,
            'message' => "Information requested from {$request->target}: {$request->request_message}",
            'message_type' => 'info_request',
            'is_internal' => false,
        ]);

        // Log the action
        AuditLog::log('dispute_info_requested', $moderator->id, [
            'dispute_id' => $dispute->id,
            'dispute_number' => $dispute->dispute_number,
            'target' => $request->target,
            'deadline' => $deadline
        ]);

        return redirect()->back()
            ->with('success', 'Information request sent successfully.');
    }

    /**
     * Escalate dispute to admin
     */
    public function escalate(Request $request, Dispute $dispute)
    {
        $request->validate([
            'escalation_reason' => 'required|string|max:500'
        ]);

        $moderator = auth()->user();

        $dispute->update([
            'status' => 'escalated',
            'escalated_at' => now(),
            'escalation_reason' => $request->escalation_reason
        ]);

        // Add escalation message
        $dispute->messages()->create([
            'user_id' => $moderator->id,
            'message' => "Dispute escalated to admin. Reason: {$request->escalation_reason}",
            'message_type' => 'escalation',
            'is_internal' => true,
        ]);

        // Log the action
        AuditLog::log('dispute_escalated', $moderator->id, [
            'dispute_id' => $dispute->id,
            'dispute_number' => $dispute->dispute_number,
            'reason' => $request->escalation_reason
        ]);

        return redirect()->back()
            ->with('success', 'Dispute escalated to admin successfully.');
    }

    /**
     * Auto-assign disputes to available moderators
     */
    public function autoAssign()
    {
        $availableModerators = $this->getAvailableModerators();

        if ($availableModerators->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No moderators available for auto-assignment.');
        }

        $unassignedDisputes = Dispute::whereNull('assigned_moderator_id')
            ->whereIn('status', ['open', 'under_review'])
            ->where('created_at', '>', now()->subHours(24)) // Only recent disputes
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        $assignedCount = 0;
        foreach ($unassignedDisputes as $dispute) {
            // Round-robin assignment
            $moderator = $availableModerators[$assignedCount % $availableModerators->count()];

            $dispute->update([
                'assigned_moderator_id' => $moderator->id,
                'assigned_at' => now(),
                'auto_assigned' => true,
                'status' => 'under_review'
            ]);

            // Add assignment message
            $dispute->messages()->create([
                'user_id' => auth()->id(),
                'message' => "Dispute auto-assigned to moderator: {$moderator->username_pub}",
                'message_type' => 'assignment_update',
                'is_internal' => true,
            ]);

            $assignedCount++;
        }

        // Log the action
        AuditLog::log('disputes_auto_assigned', auth()->id(), [
            'assigned_count' => $assignedCount,
            'moderators' => $availableModerators->pluck('username_pub')->toArray()
        ]);

        return redirect()->back()
            ->with('success', "{$assignedCount} disputes auto-assigned successfully.");
    }

    /**
     * Get dispute statistics
     */
    private function getDisputeStats()
    {
        $myId = auth()->id();

        return [
            'my_disputes' => Dispute::where('assigned_moderator_id', $myId)
                ->whereIn('status', ['under_review', 'waiting_vendor', 'waiting_buyer'])
                ->count(),
            'unassigned_disputes' => Dispute::whereNull('assigned_moderator_id')
                ->whereIn('status', ['open'])
                ->count(),
            'total_open' => Dispute::whereIn('status', ['open', 'under_review', 'waiting_vendor', 'waiting_buyer'])
                ->count(),
            'escalated_disputes' => Dispute::where('status', 'escalated')->count(),
            'auto_assigned_today' => Dispute::where('auto_assigned', true)
                ->whereDate('assigned_at', today())
                ->count(),
            'my_resolved_today' => Dispute::where('assigned_moderator_id', $myId)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', today())
                ->count(),
            'avg_resolution_time' => $this->getAverageResolutionTime(),
            'pending_info_requests' => Dispute::whereIn('status', ['waiting_vendor', 'waiting_buyer', 'waiting_both'])
                ->count()
        ];
    }

    /**
     * Get available moderators for assignment
     */
    private function getAvailableModerators()
    {
        return User::whereHas('roles', function($q) {
            $q->whereIn('name', ['moderator', 'admin']);
        })
            ->where('status', 'active')
            ->get()
            ->filter(function($moderator) {
                return $this->getModeratorWorkload($moderator->id) < config('disputes.max_moderator_workload', 10);
            });
    }

    /**
     * Get moderator workload count
     */
    private function getModeratorWorkload($moderatorId)
    {
        return Dispute::where('assigned_moderator_id', $moderatorId)
            ->whereIn('status', ['under_review', 'waiting_vendor', 'waiting_buyer'])
            ->count();
    }

    /**
     * Get list of moderators
     */
    private function getModerators()
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
     * Get dispute timeline
     */
    private function getDisputeTimeline(Dispute $dispute)
    {
        $timeline = collect();

        // Add creation event
        $timeline->push([
            'type' => 'created',
            'title' => 'Dispute Created',
            'description' => "Dispute initiated by {$dispute->initiatedBy->username_pub}",
            'timestamp' => $dispute->created_at,
            'user' => $dispute->initiatedBy
        ]);

        // Add assignment events
        if ($dispute->assigned_at) {
            $timeline->push([
                'type' => 'assigned',
                'title' => 'Assigned to Moderator',
                'description' => "Assigned to {$dispute->assignedModerator->username_pub}",
                'timestamp' => $dispute->assigned_at,
                'user' => $dispute->assignedModerator
            ]);
        }

        // Add escalation events
        if ($dispute->escalated_at) {
            $timeline->push([
                'type' => 'escalated',
                'title' => 'Escalated to Admin',
                'description' => $dispute->escalation_reason ?? 'Escalated for admin review',
                'timestamp' => $dispute->escalated_at,
                'user' => $dispute->assignedModerator
            ]);
        }

        return $timeline->sortBy('timestamp');
    }

    /**
     * Get average resolution time
     */
    private function getAverageResolutionTime()
    {
        $disputes = Dispute::where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->select('created_at', 'resolved_at')
            ->get();

        if ($disputes->isEmpty()) return 'N/A';

        $avgMinutes = $disputes->avg(function ($dispute) {
            return $dispute->created_at->diffInMinutes($dispute->resolved_at);
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
