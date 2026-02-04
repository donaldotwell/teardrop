{{-- resources/views/moderator/dashboard.blade.php --}}
@extends('layouts.moderator')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
    <span class="text-gray-600">Dashboard</span>
@endsection

@section('page-heading')
    Moderator Dashboard
@endsection

@section('page-description')
    Content moderation overview and pending actions
@endsection

@section('page-actions')
    <a href="{{ route('moderator.forum.moderate.reports') }}"
       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Review Reports
    </a>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Alert Cards for Urgent Items --}}
        @if($stats['critical_reports'] > 0 || $stats['old_unreviewed_reports'] > 0)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-6 h-6 bg-red-600 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-sm font-bold">!</span>
                    </div>
                    <h3 class="text-lg font-semibold text-red-900">Urgent Attention Required</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($stats['critical_reports'] > 0)
                        <div class="bg-white border border-red-200 rounded p-3">
                            <div class="font-medium text-red-900">{{ $stats['critical_reports'] }} Critical Reports</div>
                            <div class="text-sm text-red-700">Reports received in the last 2 hours</div>
                            <a href="{{ route('moderator.forum.moderate.reports', ['urgent' => 1]) }}"
                               class="text-sm text-red-600 hover:text-red-800 mt-1 inline-block">
                                Review Now →
                            </a>
                        </div>
                    @endif
                    @if($stats['old_unreviewed_reports'] > 0)
                        <div class="bg-white border border-red-200 rounded p-3">
                            <div class="font-medium text-red-900">{{ $stats['old_unreviewed_reports'] }} Old Reports</div>
                            <div class="text-sm text-red-700">Pending for over 24 hours</div>
                            <a href="{{ route('moderator.forum.moderate.reports', ['old' => 1]) }}"
                               class="text-sm text-red-600 hover:text-red-800 mt-1 inline-block">
                                Review Now →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Key Moderation Metrics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Pending Reports</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['pending_reports']) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-red-600 font-bold">R</span>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('moderator.forum.moderate.reports') }}" class="text-sm text-red-600 hover:text-red-800">
                        Review Reports →
                    </a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Active Users</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['active_users']) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 font-bold">U</span>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('moderator.users.index') }}" class="text-sm text-green-600 hover:text-green-800">
                        Manage Users →
                    </a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Flagged Content</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['flagged_content']) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-600 font-bold">C</span>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('moderator.content.index', ['flagged' => 1]) }}" class="text-sm text-yellow-600 hover:text-yellow-800">
                        Review Content →
                    </a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Banned Users</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['banned_users']) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <span class="text-gray-600 font-bold">B</span>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('moderator.users.index', ['status' => 'banned']) }}" class="text-sm text-gray-600 hover:text-gray-800">
                        Manage Users →
                    </a>
                </div>
            </div>
        </div>

        {{-- Dispute Management Metrics --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Dispute Management</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 border border-blue-200 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Open Disputes</div>
                    <div class="text-2xl font-semibold text-blue-600 mb-2">{{ number_format($stats['open_disputes']) }}</div>
                    <a href="{{ route('moderator.disputes.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        View All →
                    </a>
                </div>

                <div class="p-4 border border-orange-200 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Unassigned</div>
                    <div class="text-2xl font-semibold text-orange-600 mb-2">{{ number_format($stats['unassigned_disputes']) }}</div>
                    <a href="{{ route('moderator.disputes.index', ['filter' => 'unassigned']) }}" class="text-sm text-orange-600 hover:text-orange-800">
                        Assign Now →
                    </a>
                </div>

                <div class="p-4 border border-red-200 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">High Priority</div>
                    <div class="text-2xl font-semibold text-red-600 mb-2">{{ number_format($stats['high_priority_disputes']) }}</div>
                    <a href="{{ route('moderator.disputes.index', ['priority' => 'high']) }}" class="text-sm text-red-600 hover:text-red-800">
                        Review Now →
                    </a>
                </div>
            </div>
        </div>

        {{-- Quick Moderation Actions --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('moderator.forum.moderate.reports', ['status' => 'pending']) }}"
                   class="p-4 border border-red-200 rounded-lg hover:border-red-300 hover:bg-red-50">
                    <div class="font-medium text-gray-900 mb-1">Review Pending Reports</div>
                    <div class="text-2xl font-semibold text-red-600 mb-2">{{ $stats['pending_reports'] }}</div>
                    <div class="text-sm text-gray-600">Awaiting moderation</div>
                </a>

                <a href="{{ route('moderator.content.index', ['flagged' => 1]) }}"
                   class="p-4 border border-yellow-200 rounded-lg hover:border-yellow-300 hover:bg-yellow-50">
                    <div class="font-medium text-gray-900 mb-1">Flagged Content</div>
                    <div class="text-2xl font-semibold text-yellow-600 mb-2">{{ $stats['flagged_content'] }}</div>
                    <div class="text-sm text-gray-600">Auto-flagged posts</div>
                </a>

                <a href="{{ route('moderator.users.index', ['suspicious' => 1]) }}"
                   class="p-4 border border-orange-200 rounded-lg hover:border-orange-300 hover:bg-orange-50">
                    <div class="font-medium text-gray-900 mb-1">Suspicious Users</div>
                    <div class="text-2xl font-semibold text-orange-600 mb-2">{{ $stats['suspicious_users'] }}</div>
                    <div class="text-sm text-gray-600">Require review</div>
                </a>
            </div>
        </div>

        {{-- My Personal Statistics --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">My Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Reports Today</div>
                    <div class="text-2xl font-semibold text-blue-600">{{ number_format($my_stats['reports_today']) }}</div>
                </div>

                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Reports This Week</div>
                    <div class="text-2xl font-semibold text-green-600">{{ number_format($my_stats['reports_this_week']) }}</div>
                </div>

                <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">My Assigned Disputes</div>
                    <div class="text-2xl font-semibold text-purple-600">{{ number_format($my_stats['my_assigned_disputes']) }}</div>
                    <a href="{{ route('moderator.disputes.index', ['assigned_to' => 'me']) }}" class="text-xs text-purple-600 hover:text-purple-800 mt-1 inline-block">
                        View →
                    </a>
                </div>

                <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Disputes This Week</div>
                    <div class="text-2xl font-semibold text-orange-600">{{ number_format($my_stats['my_disputes_this_week']) }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Reports --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Reports</h3>
                    <a href="{{ route('moderator.forum.moderate.reports') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        View All →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recent_reports as $report)
                        <div class="flex items-start justify-between py-3 border-b border-gray-100 last:border-b-0">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-medium text-gray-900">
                                        Report #{{ $report->id }}
                                    </span>
                                    <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded">
                                        {{ ucfirst($report->reportable_type === 'App\Models\ForumPost' ? 'Post' : 'Comment') }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 mb-1">
                                    by {{ $report->user->username_pub }} • {{ $report->created_at->diffForHumans() }}
                                </div>
                                <div class="text-sm text-gray-700">
                                    {{ Str::limit($report->reason, 60) }}
                                </div>
                            </div>
                            <a href="{{ route('moderator.forum.moderate.reports') }}#report-{{ $report->id }}"
                               class="text-xs text-blue-600 hover:text-blue-800 ml-3">
                                Review
                            </a>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-6">
                            No recent reports to display.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    <a href="{{ route('moderator.audit.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        View All →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recent_audit_logs as $log)
                        <div class="py-3 border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                                <span class="text-sm text-gray-600">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-900">
                                by {{ $log->user->username_pub ?? 'System' }}
                            </div>
                            @if($log->target_user_id && $log->targetUser)
                                <div class="text-xs text-gray-600 mt-1">
                                    Target: {{ $log->targetUser->username_pub }}
                                </div>
                            @endif
                            @if(isset($log->metadata['notes']) && $log->metadata['notes'])
                                <div class="text-xs text-gray-600 mt-1">
                                    Notes: {{ $log->metadata['notes'] }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-6">
                            No recent activity to display.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
