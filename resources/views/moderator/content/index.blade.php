@extends('layouts.moderator')
@section('page-title', 'Content Review')

@section('breadcrumbs')
    <span class="text-gray-600">Content Review</span>
@endsection

@section('page-heading')
    Content Review
@endsection

@section('page-description')
    Review and moderate forum posts, comments, and reported content
@endsection

@section('page-actions')
    <div class="flex items-center space-x-3">
        <select name="bulk_action" class="px-3 py-2 border border-gray-300 rounded text-sm">
            <option value="">Bulk Actions</option>
            <option value="hide">Hide Selected</option>
            <option value="delete">Delete Selected</option>
        </select>
        <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            Apply
        </button>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Content Type Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8">
                <a href="{{ route('moderator.content.index') }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ !request('type') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    All Content ({{ $stats['total_content'] }})
                </a>
                <a href="{{ route('moderator.content.index', ['type' => 'posts']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('type') === 'posts' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Posts ({{ $stats['posts_count'] }})
                </a>
                <a href="{{ route('moderator.content.index', ['type' => 'comments']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('type') === 'comments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Comments ({{ $stats['comments_count'] }})
                </a>
                <a href="{{ route('moderator.content.index', ['flagged' => 1]) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('flagged') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Flagged ({{ $stats['flagged_count'] }})
                </a>
                <a href="{{ route('moderator.content.index', ['reported' => 1]) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('reported') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Reported ({{ $stats['reported_count'] }})
                </a>
            </nav>
        </div>

        {{-- Search and Filters --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <input type="hidden" name="type" value="{{ request('type') }}">
                <input type="hidden" name="flagged" value="{{ request('flagged') }}">
                <input type="hidden" name="reported" value="{{ request('reported') }}">

                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search content by title, body, or author..."
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <select name="author" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Authors</option>
                    @if(request('author'))
                        <option value="{{ request('author') }}" selected>{{ request('author') }}</option>
                    @endif
                </select>

                <select name="status" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>Hidden</option>
                    <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>

                <select name="date_filter" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Time</option>
                    <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('date_filter') === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ request('date_filter') === 'month' ? 'selected' : '' }}>This Month</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('moderator.content.index') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Clear
                </a>
            </form>
        </div>

        {{-- Content List --}}
        <div class="space-y-4">
            @forelse($content as $item)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" value="{{ $item->id }}" class="rounded border-gray-300">

                            {{-- Content Type Badge --}}
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $item instanceof \App\Models\ForumPost ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ $item instanceof \App\Models\ForumPost ? 'Post' : 'Comment' }}
                            </span>

                            {{-- Status Badge --}}
                            @if(isset($item->status))
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $item->status === 'active' ? 'bg-green-100 text-green-800' :
                                       ($item->status === 'hidden' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif

                            {{-- Report Count --}}
                            @if($item->reports_count > 0)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ $item->reports_count }} {{ Str::plural('Report', $item->reports_count) }}
                                </span>
                            @endif
                        </div>

                        {{-- Actions Dropdown --}}
                        <div class="flex items-center space-x-2">
                            <button class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Content Preview --}}
                    <div class="mb-4">
                        @if($item instanceof \App\Models\ForumPost)
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                <a href="{{ route('forum.posts.show', $item) }}"
                                   class="hover:text-blue-600" target="_blank">
                                    {{ $item->title }}
                                </a>
                            </h3>
                        @endif

                        <div class="text-gray-700">
                            {{ Str::limit($item->body, 300) }}
                        </div>
                    </div>

                    {{-- Content Meta --}}
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-1">
                                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-medium text-xs">
                                        {{ substr($item->user->username_pub, 0, 1) }}
                                    </span>
                                </div>
                                <a href="{{ route('profile.show', $item->user->username_pub) }}"
                                   class="hover:text-blue-600">
                                    {{ $item->user->username_pub }}
                                </a>
                            </div>

                            <span>{{ $item->created_at->format('M d, Y g:i A') }}</span>

                            @if($item instanceof \App\Models\ForumPost)
                                <span>{{ $item->comments_count ?? 0 }} {{ Str::plural('comment', $item->comments_count ?? 0) }}</span>
                            @endif

                            @if(isset($item->updated_at) && $item->updated_at != $item->created_at)
                                <span class="text-yellow-600">Edited {{ $item->updated_at->diffForHumans() }}</span>
                            @endif
                        </div>

                        {{-- Quick Actions --}}
                        <div class="flex items-center space-x-3">
                            <a href="{{ $item instanceof \App\Models\ForumPost ? route('forum.posts.show', $item) : route('forum.posts.show', $item->post) }}"
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800">
                                View
                            </a>

                            @if(!isset($item->status) || $item->status === 'active')
                                <form method="POST" action="{{ route('moderator.content.hide', [$item->getMorphClass(), $item->id]) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-yellow-600 hover:text-yellow-800">
                                        Hide
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('moderator.content.show', [$item->getMorphClass(), $item->id]) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-green-600 hover:text-green-800">
                                        Show
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('moderator.content.delete', [$item->getMorphClass(), $item->id]) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-800">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Reports Summary (if any) --}}
                    @if($item->reports_count > 0)
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                            <h4 class="text-sm font-medium text-red-900 mb-2">Recent Reports:</h4>
                            <div class="space-y-1">
                                @foreach($item->recentReports as $report)
                                    <div class="text-sm text-red-800">
                                        "{{ Str::limit($report->reason, 100) }}"
                                        - by {{ $report->user->username_pub }}
                                        ({{ $report->created_at->diffForHumans() }})
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('moderator.forum.moderate.reports', ['content' => $item->getMorphClass() . ':' . $item->id]) }}"
                               class="text-sm text-red-600 hover:text-red-800 mt-2 inline-block">
                                View All Reports →
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-12">
                    <p class="text-gray-500">No content found matching your criteria.</p>
                </div>
            @endforelse

            {{-- Pagination --}}
            <div class="bg-white px-4 py-3 border border-gray-200 rounded-lg">
                {{ $content->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
