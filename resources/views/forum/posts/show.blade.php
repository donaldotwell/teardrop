@extends('layouts.app')
@section('page-title', $post->title)

@section('breadcrumbs')
    <a href="{{ route('forum.index') }}" class="text-yellow-700 hover:text-yellow-600">Forum</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600">{{ Str::limit($post->title, 30) }}</span>
@endsection

@section('page-heading')
    {{ $post->title }}
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Original Post --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            {{-- Post Header --}}
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-700 font-bold">{{ substr($post->user->username_pub, 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="font-semibold">
                            <a href="{{ route('profile.show', $post->user->username_pub) }}"
                               class="text-yellow-700 hover:text-yellow-600">
                                {{ $post->user->username_pub }}
                            </a>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $post->created_at->format('M d, Y g:i A') }}
                            @if($post->created_at != $post->updated_at)
                                • edited {{ $post->updated_at->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Post Actions --}}
                <div class="flex items-center space-x-2">
                    @if($post->is_pinned)
                        <span class="text-xs bg-yellow-600 text-white px-2 py-1 rounded">PINNED</span>
                    @endif
                    @if($post->is_locked)
                        <span class="text-xs bg-gray-600 text-white px-2 py-1 rounded">LOCKED</span>
                    @endif

                    @can('update', $post)
                        <a href="{{ route('forum.posts.edit', $post) }}"
                           class="text-sm text-gray-600 hover:text-gray-800">Edit</a>
                    @endcan

                    @can('delete', $post)
                        <form action="{{ route('forum.posts.destroy', $post) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-sm text-red-600 hover:text-red-800">
                                Delete
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            {{-- Post Content --}}
            <div class="prose max-w-none">
                <div class="whitespace-pre-wrap text-gray-900">{{ $post->body }}</div>
            </div>

            {{-- Post Stats --}}
            <div class="flex items-center space-x-4 mt-4 pt-4 border-t border-gray-100 text-sm text-gray-500">
                <span>{{ $post->views_count }} views</span>
                <span>{{ $post->allComments()->count() }} comments</span>
            </div>
        </div>

        {{-- Comments Section --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                Comments ({{ $post->allComments()->count() }})
            </h3>

            {{-- Add Comment Form --}}
            @if(!$post->is_locked)
                <form action="{{ route('forum.comments.store', $post) }}" method="POST" class="mb-6">
                    @csrf
                    <div class="space-y-3">
                        <textarea name="body"
                                  rows="4"
                                  class="block w-full px-3 py-2 border @error('body') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                  placeholder="Write a comment..."
                                  required
                                  maxlength="5000">{{ old('body') }}</textarea>
                        @error('body')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <button type="submit"
                                class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                            Add Comment
                        </button>
                    </div>
                </form>
            @else
                <div class="bg-gray-100 border border-gray-200 rounded p-4 mb-6">
                    <p class="text-gray-600">This post is locked. No new comments can be added.</p>
                </div>
            @endif

            {{-- Comments List --}}
            <div class="space-y-4">
                @foreach($post->comments as $comment)
                    @include('forum.partials.comment', ['comment' => $comment, 'post' => $post, 'level' => 0])
                @endforeach
            </div>

            {{-- Report Post --}}
            @if(auth()->id() !== $post->user_id)
                <details class="mt-6 pt-6 border-t border-gray-200">
                    <summary class="cursor-pointer text-sm text-red-600 hover:text-red-800 font-medium">
                        Report this post
                    </summary>
                    <div class="mt-3 bg-white rounded-lg p-4 border border-gray-200">
                        <form action="{{ route('forum.posts.report', $post) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                        Reason for reporting
                                    </label>
                                    <textarea name="reason"
                                              id="reason"
                                              rows="4"
                                              class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500"
                                              placeholder="Please explain why you are reporting this post..."
                                              required
                                              maxlength="1000"></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit"
                                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                        Submit Report
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </details>
            @endif
        </div>
    </div>
@endsection

