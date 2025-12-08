<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\ForumReport;
use Illuminate\Http\Request;

class ModeratorContentController extends Controller
{
    public function index(Request $request)
    {
        $content = collect();

        // Determine what content to show based on filters
        if ($request->filled('type')) {
            if ($request->type === 'posts') {
                $content = $this->getFilteredPosts($request);
            } elseif ($request->type === 'comments') {
                $content = $this->getFilteredComments($request);
            }
        } elseif ($request->filled('flagged')) {
            $content = $this->getFlaggedContent($request);
        } elseif ($request->filled('reported')) {
            $content = $this->getReportedContent($request);
        } else {
            // Show mixed content (posts and comments)
            $posts = $this->getFilteredPosts($request);
            $comments = $this->getFilteredComments($request);
            $content = $posts->merge($comments)->sortByDesc('created_at');
        }

        // Convert to paginated collection if not already
        if (!$content instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $content = $this->paginateCollection($content, 20);
        }

        // Calculate statistics for filter tabs
        $stats = [
            'total_content' => $this->getTotalContentCount(),
            'posts_count' => ForumPost::count(),
            'comments_count' => ForumComment::count(),
            'flagged_count' => $this->getFlaggedContentCount(),
            'reported_count' => $this->getReportedContentCount(),
        ];

        return view('moderator.content.index', compact('content', 'stats'));
    }

    /**
     * Get filtered forum posts
     */
    private function getFilteredPosts(Request $request)
    {
        $query = ForumPost::with(['user', 'reports'])
            ->withCount(['reports', 'comments']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'like', "%{$search}%");
                    });
            });
        }

        // Apply author filter
        if ($request->filled('author')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username_pub', $request->author);
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date filter
        $this->applyDateFilter($query, $request);

        return $query->latest()->paginate(20);
    }

    /**
     * Get filtered forum comments
     */
    private function getFilteredComments(Request $request)
    {
        $query = ForumComment::with(['user', 'post', 'reports'])
            ->withCount('reports');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('body', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'like', "%{$search}%");
                    });
            });
        }

        // Apply author filter
        if ($request->filled('author')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username_pub', $request->author);
            });
        }

        // Apply date filter
        $this->applyDateFilter($query, $request);

        return $query->latest()->paginate(20);
    }

    /**
     * Get content that has been flagged by automated systems
     */
    private function getFlaggedContent(Request $request)
    {
        // This would depend on your flagging system
        // For now, get content with multiple reports
        $posts = ForumPost::with(['user', 'reports'])
            ->withCount('reports')
            ->has('reports', '>=', 2)
            ->latest()
            ->get();

        $comments = ForumComment::with(['user', 'post', 'reports'])
            ->withCount('reports')
            ->has('reports', '>=', 2)
            ->latest()
            ->get();

        return $posts->merge($comments)->sortByDesc('created_at');
    }

    /**
     * Get content that has active reports
     */
    private function getReportedContent(Request $request)
    {
        $posts = ForumPost::with(['user', 'reports' => function($q) {
            $q->where('status', 'pending')->latest()->limit(3);
        }])
            ->withCount(['reports' => function($q) {
                $q->where('status', 'pending');
            }])
            ->whereHas('reports', function($q) {
                $q->where('status', 'pending');
            })
            ->latest()
            ->get();

        $comments = ForumComment::with(['user', 'post', 'reports' => function($q) {
            $q->where('status', 'pending')->latest()->limit(3);
        }])
            ->withCount(['reports' => function($q) {
                $q->where('status', 'pending');
            }])
            ->whereHas('reports', function($q) {
                $q->where('status', 'pending');
            })
            ->latest()
            ->get();

        $content = $posts->merge($comments)->sortByDesc('created_at');

        // Add a recentReports accessor for the view
        $content->each(function($item) {
            $item->recentReports = $item->reports;
        });

        return $content;
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
     * Get total content count
     */
    private function getTotalContentCount()
    {
        return ForumPost::count() + ForumComment::count();
    }

    /**
     * Get flagged content count
     */
    private function getFlaggedContentCount()
    {
        $posts = ForumPost::has('reports', '>=', 2)->count();
        $comments = ForumComment::has('reports', '>=', 2)->count();
        return $posts + $comments;
    }

    /**
     * Get reported content count
     */
    private function getReportedContentCount()
    {
        return ForumReport::where('status', 'pending')->count();
    }

    /**
     * Paginate a collection
     */
    private function paginateCollection($collection, $perPage)
    {
        $currentPage = request()->get('page', 1);
        $items = $collection->slice(($currentPage - 1) * $perPage, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Hide content (would need corresponding routes)
     */
    public function hide($type, $id)
    {
        $model = $type === 'App\\Models\\ForumPost' ? ForumPost::findOrFail($id) : ForumComment::findOrFail($id);
        $model->update(['status' => 'hidden']);

        AuditLog::log('content_hidden', null, [
            'content_type' => $type,
            'content_id' => $id,
            'moderator_id' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Content hidden successfully.');
    }

    /**
     * Show content (would need corresponding routes)
     */
    public function show($type, $id)
    {
        $model = $type === 'App\\Models\\ForumPost' ? ForumPost::findOrFail($id) : ForumComment::findOrFail($id);
        $model->update(['status' => 'active']);

        AuditLog::log('content_shown', null, [
            'content_type' => $type,
            'content_id' => $id,
            'moderator_id' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Content shown successfully.');
    }

    /**
     * Delete content (would need corresponding routes)
     */
    public function delete($type, $id)
    {
        $model = $type === 'App\\Models\\ForumPost' ? ForumPost::findOrFail($id) : ForumComment::findOrFail($id);

        AuditLog::log('content_deleted', null, [
            'content_type' => $type,
            'content_id' => $id,
            'content_title' => $model instanceof ForumPost ? $model->title : 'Comment',
            'moderator_id' => auth()->id()
        ]);

        $model->delete();

        return redirect()->back()->with('success', 'Content deleted successfully.');
    }
}
