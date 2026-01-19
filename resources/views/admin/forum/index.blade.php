@extends('layouts.admin')
@section('page-title', 'Forum Management')

@section('breadcrumbs')
    <span class="text-gray-600">Forum Posts</span>
@endsection

@section('page-heading')
    Forum Posts Management
@endsection

@section('page-description')
    Manage forum posts, moderation, and assignments
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Total Posts</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $posts->total() }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Pending Review</div>
                <div class="text-2xl font-semibold text-yellow-600">
                    {{ \App\Models\ForumPost::pending()->count() }}
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Approved</div>
                <div class="text-2xl font-semibold text-green-600">
                    {{ \App\Models\ForumPost::approved()->count() }}
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Rejected</div>
                <div class="text-2xl font-semibold text-red-600">
                    {{ \App\Models\ForumPost::rejected()->count() }}
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="GET" action="{{ route('admin.forum.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Status Filter --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    {{-- Moderator Filter --}}
                    <div>
                        <label for="moderator" class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                        <select name="moderator"
                                id="moderator"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Moderators</option>
                            <option value="unassigned" {{ request('moderator') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            @foreach($moderators as $mod)
                                <option value="{{ $mod->id }}" {{ request('moderator') == $mod->id ? 'selected' : '' }}>
                                    {{ $mod->username_pub }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-end">
                        <button type="submit"
                                class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors">
                            Filter
                        </button>
                        <a href="{{ route('admin.forum.index') }}"
                           class="ml-2 px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Posts Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Post
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Author
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Assigned To
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($posts as $post)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        @if($post->is_pinned)
                                            <span class="text-yellow-600" title="Pinned">📌</span>
                                        @endif
                                        @if($post->is_locked)
                                            <span class="text-gray-600" title="Locked">🔒</span>
                                        @endif
                                        <div>
                                            <a href="{{ route('forum.posts.show', $post) }}"
                                               target="_blank"
                                               class="text-sm font-medium text-yellow-700 hover:text-yellow-800 hover:underline">
                                                {{ $post->title }}
                                            </a>
                                            <div class="text-xs text-gray-500">
                                                {{ $post->views_count }} views • {{ $post->allComments()->count() }} comments
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $post->user->username_pub }}</div>
                                    <div class="text-xs text-gray-500">TL{{ $post->user->trust_level }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($post->status === 'pending')
                                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">
                                            Pending
                                        </span>
                                    @elseif($post->status === 'approved')
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                            Approved
                                        </span>
                                    @elseif($post->status === 'rejected')
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($post->assignedModerator)
                                        <div class="text-sm text-gray-900">{{ $post->assignedModerator->username_pub }}</div>
                                    @else
                                        <span class="text-sm text-gray-400">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $post->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $post->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <details class="relative inline-block text-left">
                                        <summary class="cursor-pointer inline-flex items-center px-3 py-1 border border-gray-300 rounded text-gray-700 bg-white hover:bg-gray-50">
                                            Actions
                                        </summary>
                                        <div class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                                            {{-- Reassign Moderator --}}
                                            <form method="POST" action="{{ route('admin.forum.reassign-moderator', $post) }}" class="p-2 border-b border-gray-100">
                                                @csrf
                                                <label class="block text-xs text-gray-700 mb-1">Reassign to:</label>
                                                <select name="moderator_id"
                                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded mb-2">
                                                    @foreach($moderators as $mod)
                                                        <option value="{{ $mod->id }}">{{ $mod->username_pub }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit"
                                                        class="w-full px-2 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700">
                                                    Reassign
                                                </button>
                                            </form>

                                            {{-- Approve/Reject --}}
                                            @if($post->status === 'pending')
                                                <form method="POST" action="{{ route('admin.forum.approve', $post) }}" class="p-2 border-b border-gray-100">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.forum.reject', $post) }}" class="p-2 border-b border-gray-100">
                                                    @csrf
                                                    <input type="text"
                                                           name="notes"
                                                           placeholder="Rejection reason"
                                                           required
                                                           class="w-full px-2 py-1 text-xs border border-gray-300 rounded mb-2">
                                                    <button type="submit"
                                                            class="w-full px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                                        Reject
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Pin/Unpin --}}
                                            @if($post->is_pinned)
                                                <form method="POST" action="{{ route('admin.forum.unpin', $post) }}" class="p-2 border-b border-gray-100">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 rounded">
                                                        Unpin Post
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.forum.pin', $post) }}" class="p-2 border-b border-gray-100">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 rounded">
                                                        Pin Post
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Lock/Unlock --}}
                                            @if($post->is_locked)
                                                <form method="POST" action="{{ route('admin.forum.unlock', $post) }}" class="p-2 border-b border-gray-100">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 rounded">
                                                        Unlock Post
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.forum.lock', $post) }}" class="p-2 border-b border-gray-100">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 rounded">
                                                        Lock Post
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('admin.forum.destroy', $post) }}" class="p-2">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('Are you sure you want to delete this post?')"
                                                        class="w-full px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                                    Delete Post
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No forum posts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($posts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $posts->appends(request()->except('page'))->links() }}
                </div>
            @endif
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
