@extends('layouts.app')

@section('page-title', 'Notifications')
@section('page-heading', 'Notifications')

@section('breadcrumbs')
<span>Notifications</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center justify-end mb-4">
        @if($notifications->total() > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit"
                    class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition-colors">
                Mark All Read
            </button>
        </form>
        @endif
    </div>

    @if($notifications->isEmpty())
    <div class="bg-white border border-gray-200 rounded-xl p-10 text-center text-gray-400 text-sm">
        No notifications yet.
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl divide-y divide-gray-100 overflow-hidden">
        @foreach($notifications as $n)
        <div class="{{ $n->isRead() ? 'bg-white' : 'bg-amber-50' }} px-5 py-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold
                            @if($n->type === 'order') bg-blue-100 text-blue-700
                            @elseif($n->type === 'dispute') bg-red-100 text-red-700
                            @elseif($n->type === 'support') bg-purple-100 text-purple-700
                            @elseif($n->type === 'forum') bg-green-100 text-green-700
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ ucfirst($n->type) }}
                        </span>
                        @unless($n->isRead())
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500"></span>
                        @endunless
                    </div>
                    <p class="font-semibold text-sm text-gray-900">{{ $n->title }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $n->body }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                </div>
                <form method="POST" action="{{ route('notifications.read', $n) }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1 text-xs font-medium border rounded transition-colors
                                   {{ $n->isRead()
                                       ? 'border-gray-200 text-gray-400 hover:border-gray-300'
                                       : 'border-amber-400 text-amber-700 hover:bg-amber-100' }}">
                        {{ $n->url ? 'Open' : 'Read' }}
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection
