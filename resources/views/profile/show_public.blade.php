@extends('layouts.app')
@section('page-title', $user->username_pub . "'s Profile")

@section('breadcrumbs')
    <span class="text-amber-700">Profile</span>
    <span class="text-amber-400">/</span>
    <span class="text-amber-700">{{ $user->username_pub }}</span>
@endsection

@section('page-heading')
    {{ $user->username_pub }}'s Profile
@endsection

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Profile Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                    <span class="text-yellow-700 font-bold text-xl">{{ substr($user->username_pub, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $user->username_pub }}</h2>
                    <p class="text-gray-600">Trust Level {{ $user->trust_level }}</p>
                    <p class="text-sm text-gray-500">Member since {{ $user->created_at->format('M d, Y') }}</p>
                    @if($user->status === 'banned')
                        <span class="inline-block mt-1 text-xs bg-red-600 text-white px-2 py-1 rounded">BANNED</span>
                    @endif
                </div>
            </div>

            {{-- Account Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 pt-6 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->trust_level }}</div>
                    <div class="text-sm text-gray-600">Trust Level</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->vendor_level }}</div>
                    <div class="text-sm text-gray-600">Vendor Level</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->orders()->count() }}</div>
                    <div class="text-sm text-gray-600">Orders</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $forumStats['posts_count'] }}</div>
                    <div class="text-sm text-gray-600">Forum Posts</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $forumStats['comments_count'] }}</div>
                    <div class="text-sm text-gray-600">Comments</div>
                </div>
            </div>
        </div>

        {{-- Recent Forum Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Posts --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Posts</h3>
                @if($forumStats['recent_posts']->count() > 0)
                    <div class="space-y-3">
                        @foreach($forumStats['recent_posts'] as $post)
                            <div class="border-b border-gray-100 pb-3 last:border-b-0">
                                <h4 class="font-medium">
                                    <a href="{{ route('forum.posts.show', $post) }}"
                                       class="text-yellow-700 hover:text-yellow-600">
                                        {{ Str::limit($post->title, 60) }}
                                    </a>
                                </h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $post->created_at->diffForHumans() }}
                                    • {{ $post->views_count }} views
                                    • {{ $post->allComments()->count() }} comments
                                </p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('forum.index', ['author' => $user->username_pub]) }}"
                           class="text-sm text-yellow-700 hover:text-yellow-600">
                            View all posts by {{ $user->username_pub }}
                        </a>
                    </div>
                @else
                    <p class="text-gray-500">No posts yet.</p>
                @endif
            </div>

            {{-- Recent Comments --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Comments</h3>
                @if($forumStats['recent_comments']->count() > 0)
                    <div class="space-y-3">
                        @foreach($forumStats['recent_comments'] as $comment)
                            <div class="border-b border-gray-100 pb-3 last:border-b-0">
                                <p class="text-sm text-gray-700">{{ Str::limit($comment->body, 100) }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    on <a href="{{ route('forum.posts.show', $comment->post) }}"
                                          class="text-yellow-700 hover:text-yellow-600">{{ Str::limit($comment->post->title, 40) }}</a>
                                    • {{ $comment->created_at->diffForHumans() }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No comments yet.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
