@extends('layouts.moderator')
@section('page-title', 'Ticket Management')

@section('breadcrumbs')
    <span class="text-gray-600">Ticket Management</span>
@endsection

@section('page-heading')
    Support Tickets
@endsection

@section('page-description')
    Manage user support tickets, account issues, and moderation requests
@endsection

@section('page-actions')
    <div class="flex items-center space-x-3">
        <form method="POST" action="{{ route('moderator.tickets.auto-assign') }}" class="inline">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                Auto-Assign
            </button>
        </form>
        <a href="{{ route('moderator.tickets.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            Refresh
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Assignment Status Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8">
                <a href="{{ route('moderator.tickets.index') }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ !request('assignment') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    All Tickets ({{ $stats['my_tickets'] + $stats['unassigned_tickets'] }})
                </a>
                <a href="{{ route('moderator.tickets.index', ['assignment' => 'mine']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('assignment') === 'mine' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    My Tickets ({{ $stats['my_tickets'] }})
                </a>
                <a href="{{ route('moderator.tickets.index', ['assignment' => 'unassigned']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('assignment') === 'unassigned' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Unassigned ({{ $stats['unassigned_tickets'] }})
                </a>
                <a href="{{ route('moderator.tickets.index', ['assignment' => 'team']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('assignment') === 'team' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Team Tickets ({{ $stats['team_tickets'] }})
                </a>
                <a href="{{ route('moderator.tickets.index', ['assignment' => 'escalated']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('assignment') === 'escalated' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Escalated ({{ $stats['escalated_tickets'] }})
                </a>
            </nav>
        </div>

        {{-- Search and Filters --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <input type="hidden" name="assignment" value="{{ request('assignment') }}">

                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by ticket #, subject, or username..."
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <select name="category" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Categories</option>
                    <option value="account_issues" {{ request('category') === 'account_issues' ? 'selected' : '' }}>Account Issues</option>
                    <option value="user_reports" {{ request('category') === 'user_reports' ? 'selected' : '' }}>User Reports</option>
                    <option value="content_moderation" {{ request('category') === 'content_moderation' ? 'selected' : '' }}>Content Moderation</option>
                    <option value="dispute_appeals" {{ request('category') === 'dispute_appeals' ? 'selected' : '' }}>Dispute Appeals</option>
                    <option value="technical_support" {{ request('category') === 'technical_support' ? 'selected' : '' }}>Technical Support</option>
                    <option value="billing" {{ request('category') === 'billing' ? 'selected' : '' }}>Billing</option>
                    <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                </select>

                <select name="status" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="pending_user" {{ request('status') === 'pending_user' ? 'selected' : '' }}>Pending User</option>
                    <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>Escalated</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>

                <select name="priority" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>

                <select name="date_filter" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Time</option>
                    <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('date_filter') === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ request('date_filter') === 'month' ? 'selected' : '' }}>This Month</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('moderator.tickets.index') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Clear
                </a>
            </form>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">My Active Tickets</div>
                <div class="text-2xl font-semibold text-blue-600">{{ $stats['my_tickets'] }}</div>
                <div class="text-xs text-gray-500">Currently assigned to me</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">Unassigned</div>
                <div class="text-2xl font-semibold text-red-600">{{ $stats['unassigned_tickets'] }}</div>
                <div class="text-xs text-gray-500">Awaiting assignment</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">Urgent Tickets</div>
                <div class="text-2xl font-semibold text-orange-600">{{ $stats['urgent_tickets'] }}</div>
                <div class="text-xs text-gray-500">Require immediate attention</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">Avg Response Time</div>
                <div class="text-2xl font-semibold text-green-600">{{ $stats['avg_response_time'] }}</div>
                <div class="text-xs text-gray-500">My average response</div>
            </div>
        </div>

        {{-- Tickets Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ticket
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Assignment
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Last Activity
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50">
                            {{-- Ticket Column --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @php
                                        $priorityColor = match($ticket->priority) {
                                            'urgent' => 'bg-red-500',
                                            'high' => 'bg-orange-500',
                                            'medium' => 'bg-yellow-500',
                                            'low' => 'bg-green-500',
                                            default => 'bg-gray-500'
                                        };
                                    @endphp
                                    <div class="w-3 h-8 {{ $priorityColor }} rounded-l mr-3"></div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('moderator.tickets.show', $ticket) }}"
                                               class="hover:text-blue-600">
                                                {{ $ticket->ticket_number }}
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ Str::limit($ticket->subject, 40) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Priority: {{ ucfirst($ticket->priority) }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- User Column --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                            <span class="text-blue-600 font-medium text-xs">
                                                {{ substr($ticket->user->username_pub, 0, 1) }}
                                            </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('profile.show', $ticket->user->username_pub) }}"
                                               class="hover:text-blue-600">
                                                {{ $ticket->user->username_pub }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Trust: {{ $ticket->user->trust_level ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Category Column --}}
                            <td class="px-6 py-4">
                                @php
                                    $categoryColor = match($ticket->category) {
                                        'account_issues' => 'bg-purple-100 text-purple-800',
                                        'user_reports' => 'bg-red-100 text-red-800',
                                        'content_moderation' => 'bg-orange-100 text-orange-800',
                                        'dispute_appeals' => 'bg-yellow-100 text-yellow-800',
                                        'technical_support' => 'bg-blue-100 text-blue-800',
                                        'billing' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $categoryColor }}">
                                        {{ ucwords(str_replace('_', ' ', $ticket->category)) }}
                                    </span>
                            </td>

                            {{-- Status Column --}}
                            <td class="px-6 py-4">
                                @php
                                    $statusColor = match($ticket->status) {
                                        'open' => 'bg-blue-100 text-blue-800',
                                        'in_progress' => 'bg-yellow-100 text-yellow-800',
                                        'pending_user' => 'bg-purple-100 text-purple-800',
                                        'on_hold' => 'bg-gray-100 text-gray-800',
                                        'escalated' => 'bg-red-100 text-red-800',
                                        'resolved' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                        {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                @if($ticket->created_at < now()->subDays(2) && in_array($ticket->status, ['open', 'in_progress']))
                                    <div class="text-xs text-red-600 mt-1">Overdue</div>
                                @endif
                            </td>

                            {{-- Assignment Column --}}
                            <td class="px-6 py-4">
                                @if($ticket->assignedTo)
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-2">
                                                <span class="text-green-600 font-medium text-xs">
                                                    {{ substr($ticket->assignedTo->username_pub, 0, 1) }}
                                                </span>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-900">
                                                {{ $ticket->assignedTo->username_pub }}
                                            </div>
                                            @if($ticket->assignedTo->id === auth()->id())
                                                <div class="text-xs text-blue-600">You</div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500 text-sm">Unassigned</span>
                                @endif
                            </td>

                            {{-- Last Activity Column --}}
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ $ticket->last_activity_at->format('M d, Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $ticket->last_activity_at->format('g:i A') }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $ticket->last_activity_at->diffForHumans() }}
                                </div>
                            </td>

                            {{-- Actions Column --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('moderator.tickets.show', $ticket) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        View
                                    </a>

                                    @if(!$ticket->assignedTo)
                                        <form method="POST" action="{{ route('moderator.tickets.assign', $ticket) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-green-600 hover:text-green-800 text-sm">
                                                Assign Me
                                            </button>
                                        </form>
                                    @elseif($ticket->assignedTo->id === auth()->id())
                                        <form method="POST" action="{{ route('moderator.tickets.unassign', $ticket) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-orange-600 hover:text-orange-800 text-sm">
                                                Unassign
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No tickets found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $tickets->withQueryString()->links() }}
            </div>
        </div>

        {{-- Performance Summary --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Performance</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['my_resolved_today'] }}</div>
                    <div class="text-sm text-gray-600">Resolved by Me</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['avg_response_time'] }}</div>
                    <div class="text-sm text-gray-600">Avg Response</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-orange-600">{{ $stats['overdue_tickets'] }}</div>
                    <div class="text-sm text-gray-600">Overdue</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ $stats['team_tickets'] }}</div>
                    <div class="text-sm text-gray-600">Team Active</div>
                </div>
            </div>
        </div>
    </div>
@endsection
