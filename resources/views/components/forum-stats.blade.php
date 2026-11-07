@php
    $forumStats = \Illuminate\Support\Facades\Cache::remember('forum:stats', 60, fn () => [
        'posts'           => \App\Models\ForumPost::count(),
        'comments'        => \App\Models\ForumComment::count(),
        'active_users'    => \App\Models\User::where('status', 'active')->count(),
        'pending_reports' => \App\Models\ForumReport::pending()->count(),
    ]);
@endphp
<div class="bg-white border border-gray-200 rounded-lg p-4">
    <h3 class="font-semibold text-gray-900 mb-3">Forum Statistics</h3>
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <div class="font-medium text-gray-900">{{ $forumStats['posts'] }}</div>
            <div class="text-gray-600">Total Posts</div>
        </div>
        <div>
            <div class="font-medium text-gray-900">{{ $forumStats['comments'] }}</div>
            <div class="text-gray-600">Total Comments</div>
        </div>
        <div>
            <div class="font-medium text-gray-900">{{ $forumStats['active_users'] }}</div>
            <div class="text-gray-600">Active Users</div>
        </div>
        <div>
            <div class="font-medium text-gray-900">{{ $forumStats['pending_reports'] }}</div>
            <div class="text-gray-600">Pending Reports</div>
        </div>
    </div>
</div>
