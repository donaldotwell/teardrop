@extends('layouts.admin')
@section('page-title', 'Support Tickets Management')

@section('breadcrumbs')
    <span class="text-gray-600">Support Tickets</span>
@endsection

@section('page-heading')
    Support Tickets Management
@endsection

@section('page-description')
    Monitor and manage all user support tickets and requests
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Total Tickets</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $stats['total_tickets'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Open</div>
                <div class="text-2xl font-semibold text-yellow-600">{{ $stats['open_tickets'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Unassigned</div>
                <div class="text-2xl font-semibold text-red-600">{{ $stats['unassigned_tickets'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Overdue</div>
                <div class="text-2xl font-semibold text-orange-600">{{ $stats['overdue_tickets'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Resolved Today</div>
                <div class="text-2xl font-semibold text-green-600">{{ $stats['resolved_today'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Avg Response</div>
                <div class="text-2xl font-semibold text-blue-600">{{ round(($stats['avg_response_time'] ?? 0) / 60, 1) }}h</div>
            </div>
        </div>

        {{-- Filters and Search --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="GET" action="{{ route('admin.support.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    {{-- Search --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Ticket, subject, user..."
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Statuses</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    {{-- Priority Filter --}}
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority"
                                id="priority"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Priorities</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    {{-- Category Filter --}}
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category"
                                id="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Categories</option>
                            <option value="account" {{ request('category') == 'account' ? 'selected' : '' }}>Account</option>
                            <option value="payments" {{ request('category') == 'payments' ? 'selected' : '' }}>Payments</option>
                            <option value="orders" {{ request('category') == 'orders' ? 'selected' : '' }}>Orders</option>
                            <option value="technical" {{ request('category') == 'technical' ? 'selected' : '' }}>Technical</option>
                            <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>General</option>
                        </select>
                    </div>

                    {{-- Assigned To Filter --}}
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                        <select name="assigned_to"
                                id="assigned_to"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Staff</option>
                            <option value="unassigned" {{ request('assigned_to') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            @foreach($staffMembers ?? [] as $staff)
                                <option value="{{ $staff->id }}" {{ request('assigned_to') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->username_pub }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Range --}}
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date"
                                   name="date_from"
                                   id="date_from"
                                   value="{{ request('date_from') }}"
                                   class="w-full px-2 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 text-xs">
                            <input type="date"
                                   name="date_to"
                                   id="date_to"
                                   value="{{ request('date_to') }}"
                                   class="w-full px-2 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 text-xs">
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Filter Tickets
                    </button>
                    <a href="{{ route('admin.support.index') }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                        Clear Filters
                    </a>
                    <a href="{{ route('admin.support.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Export CSV
                    </a>
                </div>
            </form>
        </div>

        {{-- Support Tickets Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    Support Tickets ({{ $tickets->total() }} total)
                </h3>

                {{-- Quick Actions --}}
                <div class="flex items-center space-x-2">
                    <form action="{{ route('admin.support.auto-assign') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                            Auto-Assign
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50 {{ $ticket->priority === 'urgent' ? 'bg-red-50' : ($ticket->priority === 'high' ? 'bg-orange-50' : '') }}">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $ticket->ticket_number }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $ticket->getTypeDisplayName() }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-yellow-700 font-medium text-sm">{{ substr($ticket->user->username_pub, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $ticket->user->username_pub }}</div>
                                        <div class="text-sm text-gray-500">ID: {{ $ticket->user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                                    {{ $ticket->subject }}
                                </div>
                                @if($ticket->messages->isNotEmpty())
                                    @php $lastMessage = $ticket->messages->first(); @endphp
                                    <div class="text-sm text-gray-500">
                                        Last: {{ $lastMessage->created_at->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $ticket->getCategoryDisplayName() }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
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
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium
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
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($ticket->assignedTo)
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-2">
                                            <span class="text-purple-700 font-medium text-xs">{{ substr($ticket->assignedTo->username_pub, 0, 1) }}</span>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">{{ $ticket->assignedTo->username_pub }}</div>
                                    </div>
                                @else
                                    <span class="text-sm text-red-600 font-medium">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $ticket->created_at->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $ticket->created_at->format('g:i A') }}</div>
                                @if($ticket->created_at->diffInHours(now()) > 48 && $ticket->isOpen())
                                    <div class="text-xs text-red-600 font-medium">Overdue</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.support.show', $ticket) }}"
                                       class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                        View
                                    </a>

                                    @if(!$ticket->assignedTo)
                                        <form action="{{ route('admin.support.assign', $ticket) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded hover:bg-purple-200">
                                                Assign Me
                                            </button>
                                        </form>
                                    @endif

                                    @if($ticket->isOpen())
                                        <form action="{{ route('admin.support.update-status', $ticket) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">
                                                Start
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                No support tickets found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $tickets->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Support Activity</h3>

            <div class="space-y-3">
                @forelse($recent_activity ?? [] as $activity)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 rounded-full
                                {{ $activity['type'] === 'created' ? 'bg-yellow-500' : '' }}
                                {{ $activity['type'] === 'resolved' ? 'bg-green-500' : '' }}
                                {{ $activity['type'] === 'assigned' ? 'bg-blue-500' : '' }}">
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</div>
                                <div class="text-sm text-gray-500">{{ $activity['time'] }}</div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $activity['ticket_id'] }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-4">
                        No recent activity to display.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
