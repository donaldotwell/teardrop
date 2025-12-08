@extends('layouts.app')

@section('page-title', "Messages with {$thread->receiver->username_pub}")

@section('breadcrumbs')
    <div class="flex items-center space-x-2 text-sm">
        <a href="{{ route('messages.index') }}" class="text-yellow-700 hover:text-yellow-600 font-medium transition-colors">
            Messages
        </a>
        <span class="text-gray-500 font-medium">{{ $thread->receiver->username_pub }}</span>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-4xl mx-auto">
        <!-- Conversation Header -->
        <div class="border-b border-gray-100 pb-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                <span class="border-l-4 border-yellow-500 pl-3">Conversation with {{ $thread->receiver->username_pub }}</span>
            </h1>
        </div>

        <!-- Message Form -->
        <form action="{{ route('messages.store', $thread) }}" method="POST" class="mb-8">
            @csrf

            <div class="space-y-4">
            <textarea name="message" id="message" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                      placeholder="Write your message..." required></textarea>
                <!-- Error Messages -->
                @error('message')
                <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-white hover:bg-yellow-700 focus:ring-2 focus:ring-yellow-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send Message
                    </button>
                </div>
            </div>
        </form>

        <!-- Messages List -->
        <div class="space-y-6">
            @forelse ($messages as $message)
                <div class="@if($message->sender_id === auth()->id()) text-right @endif">
                    <div class="@if($message->sender_id === auth()->id()) bg-yellow-50 @else bg-white @endif inline-block p-4 rounded-lg border border-gray-100 shadow-sm max-w-3xl w-full">
                        <div class="flex items-center justify-between mb-2 text-sm">
                        <span class="font-medium text-yellow-700">
                            @if($message->sender_id === auth()->id())
                                You to {{ $thread->receiver->username_pub }}
                            @else
                                {{ $thread->user->username_pub }} to You
                            @endif
                        </span>
                            <span class="text-gray-500">
                            {{ \Carbon\Carbon::parse($message->created_at)->format('M j, Y H:i') }}
                        </span>
                        </div>
                        <p class="text-gray-800 whitespace-pre-wrap">{{ $message->message }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="mt-4 text-gray-500">No messages in this conversation yet.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($messages->hasPages())
            <div class="mt-8 border-t border-gray-100 pt-6">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
@endsection
