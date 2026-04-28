@extends('layouts.app')

@section('page-title', 'Messages')
@section('page-heading', 'Messages')

@section('breadcrumbs')
<span>Messages</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

    @if($threads->isEmpty())
    <div class="bg-white border border-gray-200 rounded-xl p-10 text-center text-gray-400 text-sm">
        No conversations yet. Messages are opened automatically when an order is placed.
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl divide-y divide-gray-100 overflow-hidden">
        @foreach($threads as $thread)
        @php
            $isUnread = $thread->unread_count > 0;
            $latest   = $thread->latestMessage;
            $other    = $thread->user_id === auth()->id() ? $thread->receiver : $thread->user;
            $isMine   = $latest && $latest->sender_id === auth()->id();
        @endphp
        <a href="{{ route('messages.show', $thread->uuid) }}"
           class="flex items-center gap-4 px-5 py-4 transition-colors {{ $isUnread ? 'bg-amber-50 hover:bg-amber-100' : 'bg-white hover:bg-gray-50' }}">

            {{-- Avatar initial --}}
            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold
                        {{ $isUnread ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                {{ strtoupper(substr($other->username_pub ?? '?', 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 mb-0.5">
                    <span class="text-sm font-{{ $isUnread ? 'bold' : 'medium' }} text-gray-900 truncate">
                        {{ $other->username_pub ?? 'Unknown' }}
                    </span>
                    <span class="text-xs text-gray-400 flex-shrink-0">
                        {{ $latest ? $latest->created_at->diffForHumans() : '' }}
                    </span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-500 truncate">
                        @if($latest)
                            {{ $isMine ? 'You: ' : '' }}{{ $latest->message }}
                        @endif
                    </p>
                    @if($isUnread)
                    <span class="flex-shrink-0 bg-amber-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 leading-none">
                        {{ $thread->unread_count > 99 ? '99+' : $thread->unread_count }}
                    </span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $threads->links() }}
    </div>
    @endif

</div>
@endsection
