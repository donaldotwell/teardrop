@extends('layouts.moderator')
@section('page-title', 'Dispute Management')

@section('breadcrumbs')
    <span class="text-gray-600">Dispute Management</span>
@endsection

@section('page-heading')
    Dispute Management
@endsection

@section('page-description')
    Manage order disputes, assign cases, and facilitate resolutions
@endsection

@section('page-actions')
    <div class="flex items-center space-x-3">
        <form method="POST" action="{{ route('moderator.disputes.auto-assign') }}" class="inline">
            @csrf
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">
                Auto-Assign
            </button>
        </form>
        <a href="{{ route('moderator.disputes.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            Refresh
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Assignment Status Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8">
                <a href="{{ route('moderator.disputes.index') }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ !request('assignment') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    All Disputes ({{ $stats['total_open'] }})
                </a>
                <a href="{{ route('moderator.disputes.index', ['assignment' => 'mine']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('assignment') === 'mine' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    My Disputes ({{ $stats['my_disputes'] }})
                </a>
                <a href="{{ route('moderator.disputes.index', ['assignment' => 'unassigned']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('assignment') === 'unassigned' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Unassigned ({{ $stats['unassigned_disputes'] }})
                </a>
                <a href="{{ route('moderator.disputes.index', ['assignment' => 'auto_assigned']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('assignment') === 'auto_assigned' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Auto-Assigned
                </a>
                <a href="{{ route('moderator.disputes.index', ['status' => 'escalated']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'escalated' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Escalated ({{ $stats['escalated_disputes'] }})
                </a>
            </nav>
        </div>

        {{-- Search and Filters --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <input type="hidden" name="assignment" value="{{ request('assignment') }}">

                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by dispute #, order #, or usernames..."
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <select name="status" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="waiting_vendor" {{ request('status') === 'waiting_vendor' ? 'selected' : '' }}>Waiting Vendor</option>
                    <option value="waiting_buyer" {{ request('status') === 'waiting_buyer' ? 'selected' : '' }}>Waiting Buyer</option>
                    <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>Escalated</option>
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

                <a href="{{ route('moderator.disputes.index') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Clear
                </a>
            </form>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">My Active Disputes</div>
                <div class="text-2xl font-semibold text-blue-600">{{ $stats['my_disputes'] }}</div>
                <div class="text-xs text-gray-500">Currently assigned to me</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">Unassigned</div>
                <div class="text-2xl font-semibold text-red-600">{{ $stats['unassigned_disputes'] }}</div>
                <div class="text-xs text-gray-500">Awaiting assignment</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">Auto-Assigned Today</div>
                <div class="text-2xl font-semibold text-purple-600">{{ $stats['auto_assigned_today'] }}</div>
                <div class="text-xs text-gray-500">System assignments</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">Avg Resolution Time</div>
                <div class="text-2xl font-semibold text-green-600">{{ $stats['avg_resolution_time'] }}</div>
                <div class="text-xs text-gray-500">Time to resolve</div>
            </div>
        </div>

        {{-- Disputes Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dispute
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Parties
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Assignment
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($disputes as $dispute)
                        <tr class="hover:bg-gray-50">
                            {{-- Dispute Column --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @php
                                        $priorityColor = match($dispute->priority ?? 'medium') {
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
                                            <a href="{{ route('moderator.disputes.show', $dispute) }}"
                                               class="hover:text-blue-600">
                                                {{ $dispute->dispute_number }}
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Order: {{ $dispute->order->uuid ?? 'N/A' }}
                                        </div>
                                        @if($dispute->auto_assigned)
                                            <div class="text-xs text-purple-600">Auto-assigned</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Parties Column --}}
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="flex items-center text-sm">
                                        <span class="text-gray-500 mr-1">Buyer:</span>
                                        <a href="{{ route('profile.show', $dispute->initiatedBy->username_pub) }}"
                                           class="text-blue-600 hover:text-blue-800">
                                            {{ $dispute->initiatedBy->username_pub }}
                                        </a>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <span class="text-gray-500 mr-1">Vendor:</span>
                                        <a href="{{ route('profile.show', $dispute->disputedAgainst->username_pub) }}"
                                           class="text-blue-600 hover:text-blue-800">
                                            {{ $dispute->disputedAgainst->username_pub }}
                                        </a>
                                    </div>
                                </div>
                            </td>

                            {{-- Status Column --}}
                            <td class="px-6 py-4">
                                @php
                                    $statusColor = match($dispute->status) {
                                        'open' => 'bg-blue-100 text-blue-800',
                                        'under_review' => 'bg-yellow-100 text-yellow-800',
                                        'waiting_vendor' => 'bg-orange-100 text-orange-800',
                                        'waiting_buyer' => 'bg-purple-100 text-purple-800',
                                        'escalated' => 'bg-red-100 text-red-800',
                                        'resolved' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <div class="flex items-center">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                            {{ ucwords(str_replace('_', ' ', $dispute->status)) }}
                                        </span>
                                </div>
                                @if($dispute->info_request_deadline && $dispute->info_request_deadline->isFuture())
                                    <div class="text-xs text-gray-500 mt-1">
                                        Deadline: {{ $dispute->info_request_deadline->format('M d, g:i A') }}
                                    </div>
                                @endif
                            </td>

                            {{-- Assignment Column --}}
                            <td class="px-6 py-4">
                                @if($dispute->assignedModerator)
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                                <span class="text-blue-600 font-medium text-xs">
                                                    {{ substr($dispute->assignedModerator->username_pub, 0, 1) }}
                                                </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $dispute->assignedModerator->username_pub }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $dispute->assigned_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500 text-sm">Unassigned</span>
                                @endif
                            </td>

                            {{-- Amount Column --}}
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    ${{ number_format($dispute->disputed_amount, 2) }}
                                </div>
                                @if($dispute->order && $dispute->order->listing)
                                    <div class="text-xs text-gray-500">
                                        {{ Str::limit($dispute->order->listing->title, 30) }}
                                    </div>
                                @endif
                            </td>

                            {{-- Created Column --}}
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ $dispute->created_at->format('M d, Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $dispute->created_at->format('g:i A') }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $dispute->created_at->diffForHumans() }}
                                </div>
                            </td>

                            {{-- Actions Column --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('moderator.disputes.show', $dispute) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        View
                                    </a>

                                    @if(!$dispute->assignedModerator)
                                        <form method="POST" action="{{ route('moderator.disputes.assign', $dispute) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-green-600 hover:text-green-800 text-sm">
                                                Assign Me
                                            </button>
                                        </form>
                                    @elseif($dispute->assignedModerator->id === auth()->id())
                                        <form method="POST" action="{{ route('moderator.disputes.unassign', $dispute) }}" class="inline">
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
                                No disputes found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $disputes->withQueryString()->links() }}
            </div>
        </div>

        {{-- Quick Stats Summary --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Summary</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['my_resolved_today'] }}</div>
                    <div class="text-sm text-gray-600">Resolved by Me</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ $stats['auto_assigned_today'] }}</div>
                    <div class="text-sm text-gray-600">Auto-Assigned</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-orange-600">{{ $stats['pending_info_requests'] }}</div>
                    <div class="text-sm text-gray-600">Pending Info</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['avg_resolution_time'] }}</div>
                    <div class="text-sm text-gray-600">Avg Resolution</div>
                </div>
            </div>
        </div>
    </div>
@endsection
