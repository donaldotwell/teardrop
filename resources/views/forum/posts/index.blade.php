@extends('layouts.app')
@section('page-title', 'Forum')

@section('breadcrumbs')
    <span class="text-amber-700">Forum</span>
@endsection

@section('page-heading')
    Forum
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Search and Create Post --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <form method="GET" class="flex-1 max-w-md">
                <div class="flex space-x-2">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search posts..."
                           class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500">
                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Search
                    </button>
                </div>
                @if(request('search'))
                    <a href="{{ route('forum.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mt-1 inline-block">
                        Clear search
                    </a>
                @endif
            </form>

            <a href="{{ route('forum.posts.create') }}"
               class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                Create Post
            </a>
        </div>

        {{-- Posts List --}}
        <div class="space-y-4">
            @forelse($posts as $post)
                <div class="border border-gray-200 rounded-lg p-4 hover:border-yellow-300 transition-colors
                           {{ $post->is_pinned ? 'bg-yellow-50 border-yellow-200' : 'bg-white' }}">

                    {{-- Post Header --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                @if($post->is_pinned)
                                    <span class="text-xs bg-yellow-600 text-white px-2 py-1 rounded">PINNED</span>
                                @endif
                                @if($post->is_locked)
                                    <span class="text-xs bg-gray-600 text-white px-2 py-1 rounded">LOCKED</span>
                                @endif
                            </div>

                            <h3 class="text-lg font-semibold">
                                <a href="{{ route('forum.posts.show', $post) }}"
                                   class="text-gray-900 hover:text-yellow-700">
                                    {{ $post->title }}
                                </a>
                            </h3>

                            <div class="text-sm text-gray-600 mt-1">
                                by <a href="{{ route('profile.show_public_view', $post->user->username_pub) }}"
                                      class="text-yellow-700 hover:text-yellow-600">{{ $post->user->username_pub }}</a>
                                • {{ $post->created_at->diffForHumans() }}
                                • {{ $post->views_count }} views
                                • {{ $post->allComments()->count() }} comments
                            </div>
                        </div>
                    </div>

                    {{-- Post Preview --}}
                    <div class="text-gray-700 line-clamp-3">
                        {{ Str::limit(strip_tags($post->body), 200) }}
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <p class="text-gray-500 mb-4">No posts found.</p>
                    <a href="{{ route('forum.posts.create') }}"
                       class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Create the first post
                    </a>
                </div>
            @endforelse

            {{-- Pagination --}}
            {{ $posts->links() }}
        </div>
    </div>
@endsection
