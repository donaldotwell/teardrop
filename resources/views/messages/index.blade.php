@extends('layouts.app')

@section('page-title', 'Messages')

@section('breadcrumbs')
    <span class="text-amber-700">Messages</span>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-900">
                <span class="border-l-4 border-amber-500 pl-3">Your Messages</span>
            </h1>
            <a href="#" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Message
            </a>
        </div>

        <div class="space-y-6">
            @forelse ($threads as $thread)
                <div class="border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <a href="{{ route('messages.show', $thread->uuid) }}" class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium text-gray-900">
                                        @if($thread->latestMessage->sender_id === auth()->id())
                                            You → {{ $thread->latestMessage->receiver->username_pub }}
                                        @else
                                            {{ $thread->latestMessage->sender->username_pub }} → You
                                        @endif
                                    </span>
                                    @if($thread->latestMessage->order_id)
                                        <span class="px-2 py-1 text-xs bg-amber-100 text-amber-700 rounded-full">Order #{{ $thread->latestMessage->order_id }}</span>
                                    @endif
                                </div>
                                <span class="text-sm text-gray-500">{{ $thread->latestMessage->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-600 whitespace-pre-wrap">{{ $thread->latestMessage->message }}</p>
                        </div>
                    </a>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="mt-4 text-gray-500">No messages found.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
