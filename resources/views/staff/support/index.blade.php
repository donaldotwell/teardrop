@extends('layouts.app')
@section('page-title', 'My Support Queue')

@section('breadcrumbs')
    <span class="text-gray-600">Support Queue</span>
@endsection

@section('page-heading')
    My Support Queue
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Header with Quick Stats --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Support Queue</h1>
                    <p class="text-gray-600">Manage your assigned tickets and pick up new ones</p>
                </div>

                {{-- Quick Actions --}}
                <div class="flex items-center space-x-3">
                    <form action="{{ route('staff.support.bulk-assign-me') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="limit" value="3">
                        <button type="submit"
                                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors text-sm">
                            Grab 3 Tickets
                        </button>
                    </form>
                    <a href="{{ route('staff.support.urgent') }}"
                       class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors text-sm">
                        Urgent Queue
                    </a>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['my_tickets'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">My Tickets</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['unassigned_tickets'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Available</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['my_resolved_today'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Resolved Today</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ round(($stats['my_avg_response_time'] ?? 0) / 60, 1) }}h</div>
                    <div class="text-sm text-gray-600">Avg Response</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['overdue_assigned'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Overdue</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $stats['high_priority_available'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">High Priority</div>
                </div>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('staff.support.index') }}"
                   class="px-4 py-2 rounded {{ !request('assignment') ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    All Available
                </a>
                <a href="{{ route('staff.support.index', ['assignment' => 'mine']) }}"
                   class="px-4 py-2 rounded {{ request('assignment') === 'mine' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    My Tickets ({{ $stats['my_tickets'] ?? 0 }})
                </a>
                <a href="{{ route('staff.support.index', ['assignment' => 'unassigned']) }}"
                   class="px-4 py-2 rounded {{ request('assignment') === 'unassigned' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Unassigned ({{ $stats['unassigned_tickets'] ?? 0 }})
                </a>
                <a href="{{ route('staff.support.index', ['priority' => 'high']) }}"
                   class="px-4 py-2 rounded {{ request('priority') === 'high' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    High Priority
                </a>
                <a href="{{ route('staff.support.index', ['priority' => 'urgent']) }}"
                   class="px-4 py-2 rounded {{ request('priority') === 'urgent' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Urgent
                </a>
            </div>

            {{-- Advanced Filters --}}
            <form method="GET" action="{{ route('staff.support.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    {{-- Search --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Ticket, subject, user..."
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            <option value="">All Statuses</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
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

                    {{-- Submit --}}
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                            Filter
                        </button>
                    </div>
                </div>

                {{-- Preserve assignment filter --}}
                @if(request('assignment'))
                    <input type="hidden" name="assignment" value="{{ request('assignment') }}">
                @endif

                {{-- Clear Filters --}}
                @if(request()->hasAny(['search', 'status', 'category', 'type']))
                    <div class="text-center">
                        <a href="{{ route('staff.support.index', request()->only(['assignment'])) }}"
                           class="text-sm text-gray-600 hover:text-gray-800">
                            Clear filters
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Tickets List --}}
        <div class="space-y-4">
            @forelse($tickets as $ticket)
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:border-amber-300 transition-colors
                    {{ $ticket->priority === 'urgent' ? 'border-red-300 bg-red-50' : '' }}
                    {{ $ticket->priority === 'high' ? 'border-orange-300 bg-orange-50' : '' }}
                    {{ $ticket->created_at->diffInHours(now()) > 48 && $ticket->isOpen() ? 'border-yellow-300 bg-yellow-50' : '' }}">

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

                                {{-- Overdue Indicator --}}
                                @if($ticket->created_at->diffInHours(now()) > 48 && $ticket->isOpen())
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        Overdue
                                    </span>
                                @endif
                            </div>

                            <div class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Ticket:</span>
                                {{ $ticket->ticket_number }}
                                •
                                <span class="font-medium">User:</span>
                                {{ $ticket->user->username_pub }}
                                •
                                <span class="font-medium">Type:</span>
                                {{ $ticket->getTypeDisplayName() }}
                            </div>

                            <div class="text-sm text-gray-700 mb-3">
                                {{ Str::limit($ticket->description, 150) }}
                            </div>

                            {{-- Assignment Status --}}
                            @if($ticket->assignedTo)
                                @if($ticket->assignedTo->id === auth()->id())
                                    <div class="flex items-center space-x-2 text-sm text-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Assigned to you</span>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span>Assigned to {{ $ticket->assignedTo->username_pub }}</span>
                                    </div>
                                @endif
                            @else
                                <div class="flex items-center space-x-2 text-sm text-green-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Available for assignment</span>
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

                            <div class="flex flex-col space-y-2">
                                <a href="{{ route('staff.support.show', $ticket) }}"
                                   class="inline-block px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 transition-colors text-center">
                                    View Details
                                </a>

                                @if(!$ticket->assignedTo)
                                    <form action="{{ route('staff.support.assign-me', $ticket) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="w-full px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition-colors">
                                            Assign to Me
                                        </button>
                                    </form>
                                @elseif($ticket->assignedTo->id === auth()->id() && $ticket->status === 'open')
                                    <form action="{{ route('staff.support.update-status', $ticket) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit"
                                                class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                            Start Working
                                        </button>
                                    </form>
                                @endif
                            </div>
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
                                        by {{ $lastMessage->user->username_pub }}
                                    </div>
                                @endif
                            </div>

                            {{-- Unread Messages Indicator --}}
                            @if($ticket->assignedTo && $ticket->assignedTo->id === auth()->id())
                                @php
                                    $unreadCount = $ticket->getUnreadCountFor(auth()->user());
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $unreadCount }} new message{{ $unreadCount > 1 ? 's' : '' }}
                                    </span>
                                @endif
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Tickets Found</h3>
                    <p class="text-gray-600 mb-6">
                        @if(request()->hasAny(['search', 'status', 'category', 'type']))
                            No tickets match your current filters.
                        @else
                            No tickets available in your queue.
                        @endif
                    </p>

                    <div class="flex items-center justify-center space-x-4">
                        @if(request()->hasAny(['search', 'status', 'category', 'type']))
                            <a href="{{ route('staff.support.index', request()->only(['assignment'])) }}"
                               class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                Clear Filters
                            </a>
                        @endif
                        @if(request('assignment') === 'mine')
                            <a href="{{ route('staff.support.index', ['assignment' => 'unassigned']) }}"
                               class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                                View Available Tickets
                            </a>
                        @endif
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
            <h3 class="text-sm font-medium text-blue-800 mb-2">Support Staff Guide</h3>
            <div class="text-sm text-blue-700 space-y-2">
                <p>• <strong>Priority:</strong> Focus on urgent and high priority tickets first</p>
                <p>• <strong>Response Time:</strong> Aim to respond within 2 hours for urgent, 8 hours for others</p>
                <p>• <strong>Escalation:</strong> Use escalation requests for complex technical or policy issues</p>
                <p>• <strong>Assignment:</strong> Pick up 2-3 tickets at a time to maintain quality</p>
                <p>• <strong>Internal Notes:</strong> Use internal messages to coordinate with other staff</p>
            </div>
        </div>
    </div>
@endsection
