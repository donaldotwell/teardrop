@extends('layouts.moderator')
@section('page-title', 'Audit Logs')

@section('breadcrumbs')
    <span class="text-gray-600">Audit Logs</span>
@endsection

@section('page-heading')
    Audit Logs
@endsection

@section('page-description')
    Track all moderation actions and system activities
@endsection

@section('page-actions')
    <div class="flex items-center space-x-3">
        <a href="{{ route('moderator.audit.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            Refresh
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Action Type Filters --}}
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8">
                <a href="{{ route('moderator.audit.index') }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ !request('action') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    All Actions ({{ $stats['total_logs'] }})
                </a>
                <a href="{{ route('moderator.audit.index', ['action' => 'user_banned']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('action') === 'user_banned' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    User Bans ({{ $stats['user_bans'] }})
                </a>
                <a href="{{ route('moderator.audit.index', ['action' => 'report_reviewed']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('action') === 'report_reviewed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Report Reviews ({{ $stats['report_reviews'] }})
                </a>
                <a href="{{ route('moderator.audit.index', ['action' => 'content']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('action') === 'content' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Content Actions ({{ $stats['content_actions'] }})
                </a>
            </nav>
        </div>

        {{-- Search and Filters --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <input type="hidden" name="action" value="{{ request('action') }}">

                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by moderator, target user, or action details..."
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>

                <select name="moderator" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Moderators</option>
                    @foreach($moderators as $moderator)
                        <option value="{{ $moderator->username_pub }}"
                            {{ request('moderator') === $moderator->username_pub ? 'selected' : '' }}>
                            {{ $moderator->username_pub }}
                        </option>
                    @endforeach
                </select>

                <select name="date_filter" class="px-3 py-2 border border-gray-300 rounded">
                    <option value="">All Time</option>
                    <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('date_filter') === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ request('date_filter') === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>

                @if(request('date_filter') === 'custom')
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="px-3 py-2 border border-gray-300 rounded">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="px-3 py-2 border border-gray-300 rounded">
                @endif

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('moderator.audit.index') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Clear
                </a>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">Today's Actions</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $stats['today_actions'] }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">This Week</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $stats['week_actions'] }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">My Actions</div>
                <div class="text-2xl font-semibold text-blue-600">{{ $stats['my_actions'] }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600">Active Moderators</div>
                <div class="text-2xl font-semibold text-green-600">{{ $stats['active_moderators'] }}</div>
            </div>
        </div>

        {{-- Audit Logs Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Action
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Moderator
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Target
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Details
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date & Time
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            {{-- Action Column --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @php
                                        $actionBadgeColor = match(true) {
                                            str_contains($log->action, 'ban') => 'bg-red-500',
                                            str_contains($log->action, 'unban') => 'bg-green-500',
                                            str_contains($log->action, 'report') => 'bg-yellow-500',
                                            str_contains($log->action, 'content') => 'bg-blue-500',
                                            str_contains($log->action, 'delete') => 'bg-red-600',
                                            default => 'bg-gray-500'
                                        };

                                        $actionIcon = match(true) {
                                            str_contains($log->action, 'ban') => 'B',
                                            str_contains($log->action, 'report') => 'R',
                                            str_contains($log->action, 'content') => 'C',
                                            str_contains($log->action, 'delete') => 'D',
                                            str_contains($log->action, 'user') => 'U',
                                            default => 'A'
                                        };

                                        $actionLabel = ucwords(str_replace('_', ' ', $log->action));
                                    @endphp
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 {{ $actionBadgeColor }}">
                                            <span class="text-white font-bold text-sm">
                                                {{ $actionIcon }}
                                            </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $actionLabel }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $log->action }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Moderator Column --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                            <span class="text-blue-600 font-medium text-xs">
                                                {{ $log->user ? substr($log->user->username_pub, 0, 1) : 'S' }}
                                            </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $log->user->username_pub ?? 'System' }}
                                        </div>
                                        @if($log->ip_address)
                                            <div class="text-xs text-gray-500">
                                                {{ $log->ip_address }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Target Column --}}
                            <td class="px-6 py-4">
                                @if($log->targetUser)
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-2">
                                                <span class="text-gray-600 font-medium text-xs">
                                                    {{ substr($log->targetUser->username_pub, 0, 1) }}
                                                </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="{{ route('profile.show', $log->targetUser->username_pub) }}"
                                                   class="hover:text-blue-600">
                                                    {{ $log->targetUser->username_pub }}
                                                </a>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                ID: {{ $log->targetUser->id }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500 text-sm">No target</span>
                                @endif
                            </td>

                            {{-- Details Column --}}
                            <td class="px-6 py-4">
                                @if($log->details)
                                    <div class="space-y-1">
                                        @foreach($log->details as $key => $value)
                                            @if(is_string($value) && strlen($value) < 100)
                                                <div class="text-sm">
                                                    <span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                    <span class="text-gray-900">{{ $value }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">No details</span>
                                @endif
                            </td>

                            {{-- Date & Time Column --}}
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ $log->created_at->format('M d, Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $log->created_at->format('g:i A') }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No audit logs found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
