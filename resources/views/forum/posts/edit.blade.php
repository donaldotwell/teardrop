@extends('layouts.app')
@section('page-title', 'Edit Post')

@section('breadcrumbs')
    <a href="{{ route('forum.index') }}" class="text-amber-700 hover:text-amber-900">Forum</a>
    <span class="text-amber-400">/</span>
    <a href="{{ route('forum.posts.show', $post) }}" class="text-amber-700 hover:text-amber-900">{{ Str::limit($post->title, 30) }}</a>
    <span class="text-amber-400">/</span>
    <span class="text-amber-700">Edit</span>
@endsection

@section('page-heading')
    Edit Post
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('forum.posts.update', $post) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-1">
                <label for="title" class="block text-sm font-medium text-gray-700">Post Title</label>
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title', $post->title) }}"
                       class="block w-full px-3 py-2 border @error('title') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                       required
                       maxlength="255">
                @error('title')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="body" class="block text-sm font-medium text-gray-700">Post Content</label>
                <textarea name="body"
                          id="body"
                          rows="12"
                          class="block w-full px-3 py-2 border @error('body') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                          required
                          maxlength="10000">{{ old('body', $post->body) }}</textarea>
                <p class="text-xs text-gray-500">Maximum 10,000 characters. Links and URLs are not allowed.</p>
                @error('body')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('forum.posts.show', $post) }}"
                   class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                    Update Post
                </button>
            </div>
        </form>
    </div>
@endsection
