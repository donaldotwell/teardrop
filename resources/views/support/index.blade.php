@extends('layouts.app')
@section('page-title', 'My Support Tickets')

@section('breadcrumbs')
    <span class="text-gray-600">Support</span>
@endsection

@section('page-heading')
    My Support Tickets
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Header with Create Button --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
                <p class="text-gray-600">Get help with your account, payments, and technical issues</p>
            </div>
            <a href="{{ route('support.create') }}"
               class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 transition-colors">
                Create Ticket
            </a>
        </div>

        {{-- Status Filter Tabs --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('support.index') }}"
                   class="px-4 py-2 rounded {{ !request('status') ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    All Tickets ({{ $statusCounts['all'] }})
                </a>
                <a href="{{ route('support.index', ['status' => 'open']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'open' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Open ({{ $statusCounts['open'] }})
                </a>
                <a href="{{ route('support.index', ['status' => 'in_progress']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    In Progress ({{ $statusCounts['in_progress'] }})
                </a>
                <a href="{{ route('support.index', ['status' => 'resolved']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'resolved' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Resolved ({{ $statusCounts['resolved'] }})
                </a>
                <a href="{{ route('support.index', ['status' => 'closed']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'closed' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Closed ({{ $statusCounts['closed'] }})
                </a>
            </div>
        </div>

        {{-- Search and Filters --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="GET" action="{{ route('support.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Ticket number, subject..."
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    </div>

                    {{-- Category Filter --}}
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category"
                                id="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            <option value="">All Categories</option>
                            <option value="account" {{ request('category') == 'account' ? 'selected' : '' }}>Account</option>
                            <option value="payments" {{ request('category') == 'payments' ? 'selected' : '' }}>Payments</option>
                            <option value="orders" {{ request('category') == 'orders' ? 'selected' : '' }}>Orders</option>
                            <option value="technical" {{ request('category') == 'technical' ? 'selected' : '' }}>Technical</option>
                            <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>General</option>
                        </select>
                    </div>

                    {{-- Type Filter --}}
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type"
                                id="type"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            <option value="">All Types</option>
                            @foreach($ticketTypes as $category => $types)
                                <optgroup label="{{ ucfirst($category) }}">
                                    @foreach($types as $typeValue => $typeLabel)
                                        <option value="{{ $typeValue }}" {{ request('type') == $typeValue ? 'selected' : '' }}>
                                            {{ $typeLabel }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                            Filter
                        </button>
                    </div>
                </div>

                {{-- Clear Filters --}}
                @if(request()->hasAny(['search', 'category', 'type', 'status']))
                    <div class="text-center">
                        <a href="{{ route('support.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800">
                            Clear all filters
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Tickets List --}}
        <div class="space-y-4">
            @forelse($tickets as $ticket)
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:border-amber-300 transition-colors">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $ticket->subject }}
                                </h3>

                                {{-- Status Badge --}}
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                @switch($ticket->status)
                                    @case('open')
                                        bg-yellow-100 text-yellow-800
                                        @break
                                    @case('pending')
                                        bg-orange-100 text-orange-800
                                        @break
                                    @case('in_progress')
                                        bg-blue-100 text-blue-800
                                        @break
                                    @case('on_hold')
                                        bg-purple-100 text-purple-800
                                        @break
                                    @case('resolved')
                                        bg-green-100 text-green-800
                                        @break
                                    @case('closed')
                                        bg-gray-100 text-gray-800
                                        @break
                                    @default
                                        bg-gray-100 text-gray-800
                                @endswitch">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>

                                {{-- Priority Badge --}}
                                @if($ticket->priority !== 'medium')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @switch($ticket->priority)
                                        @case('low')
                                            bg-gray-100 text-gray-700
                                            @break
                                        @case('high')
                                            bg-orange-100 text-orange-700
                                            @break
                                        @case('urgent')
                                            bg-red-100 text-red-700
                                            @break
                                        @default
                                            bg-gray-100 text-gray-700
                                    @endswitch">
                                        {{ ucfirst($ticket->priority) }} Priority
                                    </span>
                                @endif
                            </div>

                            <div class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Ticket:</span>
                                {{ $ticket->ticket_number }}
                                •
                                <span class="font-medium">Type:</span>
                                {{ $ticket->getTypeDisplayName() }}
                                •
                                <span class="font-medium">Category:</span>
                                {{ $ticket->getCategoryDisplayName() }}
                            </div>

                            <div class="text-sm text-gray-700 mb-3">
                                {{ Str::limit($ticket->description, 150) }}
                            </div>

                            {{-- Assigned Staff --}}
                            @if($ticket->assignedTo)
                                <div class="flex items-center space-x-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Assigned to: {{ $ticket->assignedTo->username_pub }}</span>
                                </div>
                            @else
                                <div class="flex items-center space-x-2 text-sm text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Awaiting assignment</span>
                                </div>
                            @endif
                        </div>

                        <div class="text-right">
                            <div class="text-sm text-gray-500 mb-2">
                                {{ $ticket->created_at->format('M d, Y') }}
                            </div>
                            <div class="text-sm text-gray-500 mb-4">
                                {{ $ticket->created_at->diffForHumans() }}
                            </div>
                            <a href="{{ route('support.show', $ticket) }}"
                               class="inline-block px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 transition-colors">
                                View Details
                            </a>
                        </div>
                    </div>

                    {{-- Last Activity --}}
                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                @if($ticket->messages->isNotEmpty())
                                    @php $lastMessage = $ticket->messages->first(); @endphp
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">Last update:</span>
                                        {{ $lastMessage->created_at->diffForHumans() }}
                                        @if($lastMessage->user_id !== auth()->id())
                                            by {{ $lastMessage->user->username_pub }}
                                        @else
                                            by you
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Unread Messages Indicator --}}
                            @php
                                $unreadCount = $ticket->getUnreadCountFor(auth()->user());
                            @endphp
                            @if($unreadCount > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $unreadCount }} new message{{ $unreadCount > 1 ? 's' : '' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Support Tickets Found</h3>
                    <p class="text-gray-600 mb-6">
                        @if(request()->hasAny(['search', 'category', 'type', 'status']))
                            No tickets match your current filters.
                        @else
                            You haven't created any support tickets yet.
                        @endif
                    </p>

                    <div class="flex items-center justify-center space-x-4">
                        @if(request()->hasAny(['search', 'category', 'type', 'status']))
                            <a href="{{ route('support.index') }}"
                               class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                Clear Filters
                            </a>
                        @endif
                        <a href="{{ route('support.create') }}"
                           class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                            Create Your First Ticket
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($tickets->hasPages())
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                {{ $tickets->appends(request()->query())->links() }}
            </div>
        @endif

        {{-- Quick Help Section --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-sm font-medium text-blue-800 mb-2">Getting Support</h3>
            <div class="text-sm text-blue-700 space-y-2">
                <p>• <strong>Account Issues:</strong> For banned/suspended accounts or verification problems</p>
                <p>• <strong>Payment Issues:</strong> For Bitcoin/Monero deposits, withdrawals, or balance problems</p>
                <p>• <strong>Order Issues:</strong> For problems with your orders or listings</p>
                <p>• <strong>Technical Issues:</strong> For bugs, login problems, or website issues</p>
                <p>• <strong>Response Time:</strong> We aim to respond within 24 hours for most issues</p>
            </div>
        </div>
    </div>
@endsection
