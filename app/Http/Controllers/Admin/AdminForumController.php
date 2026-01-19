<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminForumController extends Controller
{
    /**
     * Show all forum posts with moderation status
     */
    public function index(Request $request)
    {
        $query = ForumPost::with(['user', 'assignedModerator', 'moderatedBy'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by assigned moderator
        if ($request->filled('moderator')) {
            if ($request->moderator === 'unassigned') {
                $query->whereNull('assigned_moderator_id');
            } else {
                $query->where('assigned_moderator_id', $request->moderator);
            }
        }

        $posts = $query->paginate(20);

        // Get all moderators for the filter dropdown
        $moderators = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'moderator']);
        })->get();

        return view('admin.forum.index', compact('posts', 'moderators'));
    }

    /**
     * Reassign post to another moderator
     */
    public function reassignModerator(Request $request, ForumPost $post)
    {
        $request->validate([
            'moderator_id' => 'required|exists:users,id',
        ]);

        // Verify the selected user is a moderator or admin
        $moderator = User::findOrFail($request->moderator_id);
        if (!$moderator->hasAnyRole(['admin', 'moderator'])) {
            return redirect()->back()->withErrors([
                'error' => 'Selected user is not a moderator or admin.'
            ]);
        }

        $oldModeratorId = $post->assigned_moderator_id;

        $post->update([
            'assigned_moderator_id' => $request->moderator_id,
        ]);

        AuditLog::log('post_reassigned', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'from_moderator_id' => $oldModeratorId,
            'to_moderator_id' => $request->moderator_id,
            'reassigned_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Post reassigned successfully.');
    }

    /**
     * Approve a post (admin override)
     */
    public function approve(Request $request, ForumPost $post)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $post->update([
            'status' => 'approved',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
            'moderation_notes' => $request->notes,
        ]);

        AuditLog::log('post_approved_by_admin', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'approved_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Post approved successfully.');
    }

    /**
     * Reject a post (admin override)
     */
    public function reject(Request $request, ForumPost $post)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $post->update([
            'status' => 'rejected',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
            'moderation_notes' => $request->notes,
        ]);

        AuditLog::log('post_rejected_by_admin', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'rejected_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Post rejected.');
    }

    /**
     * Pin a post
     */
    public function pin(ForumPost $post)
    {
        $post->update(['is_pinned' => true]);

        AuditLog::log('post_pinned', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'pinned_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Post pinned successfully.');
    }

    /**
     * Unpin a post
     */
    public function unpin(ForumPost $post)
    {
        $post->update(['is_pinned' => false]);

        AuditLog::log('post_unpinned', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'unpinned_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Post unpinned successfully.');
    }

    /**
     * Lock a post
     */
    public function lock(ForumPost $post)
    {
        $post->update(['is_locked' => true]);

        AuditLog::log('post_locked', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'locked_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Post locked successfully.');
    }

    /**
     * Unlock a post
     */
    public function unlock(ForumPost $post)
    {
        $post->update(['is_locked' => false]);

        AuditLog::log('post_unlocked', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'unlocked_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Post unlocked successfully.');
    }

    /**
     * Delete a post
     */
    public function destroy(ForumPost $post)
    {
        AuditLog::log('post_deleted_by_admin', $post->user_id, [
            'post_id' => $post->id,
            'title' => $post->title,
            'deleted_by' => auth()->id(),
        ]);

        $post->delete();

        return redirect()->route('admin.forum.index')->with('success', 'Post deleted successfully.');
    }
}
