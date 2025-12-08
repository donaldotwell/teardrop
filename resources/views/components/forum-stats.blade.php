<div class="bg-white border border-gray-200 rounded-lg p-4">
    <h3 class="font-semibold text-gray-900 mb-3">Forum Statistics</h3>
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <div class="font-medium text-gray-900">{{ \App\Models\ForumPost::count() }}</div>
            <div class="text-gray-600">Total Posts</div>
        </div>
        <div>
            <div class="font-medium text-gray-900">{{ \App\Models\ForumComment::count() }}</div>
            <div class="text-gray-600">Total Comments</div>
        </div>
        <div>
            <div class="font-medium text-gray-900">{{ \App\Models\User::where('status', 'active')->count() }}</div>
            <div class="text-gray-600">Active Users</div>
        </div>
        <div>
            <div class="font-medium text-gray-900">{{ \App\Models\ForumReport::pending()->count() }}</div>
            <div class="text-gray-600">Pending Reports</div>
        </div>
    </div>
</div>
