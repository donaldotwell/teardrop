@extends('layouts.moderator')
@section('page-title', 'Moderate Reports')

@section('breadcrumbs')
    <span class="text-gray-600">Moderation</span>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Reports</span>
@endsection

@section('page-heading')
    Content Reports
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Filter Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8">
                <a href="{{ route('moderator.forum.moderate.reports') }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ !request('status') || request('status') === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Pending ({{ \App\Models\ForumReport::pending()->count() }})
                </a>
                <a href="{{ route('moderator.forum.moderate.reports', ['status' => 'reviewed']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'reviewed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Reviewed
                </a>
                <a href="{{ route('moderator.forum.moderate.reports', ['status' => 'dismissed']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'dismissed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Dismissed
                </a>
            </nav>
        </div>

        {{-- Reports List --}}
        <div class="space-y-4">
            @forelse($reports as $report)
                <div class="bg-white border border-gray-200 rounded-lg p-6" id="report-{{ $report->id }}">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">
                                Report #{{ $report->id }}
                                <span class="ml-2 text-xs px-2 py-1 rounded
                                    {{ $report->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                       ($report->status === 'reviewed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </h3>
                            <p class="text-sm text-gray-600">
                                Reported by
                                <a href="{{ route('profile.show', $report->user->username_pub) }}"
                                   class="text-blue-700 hover:text-blue-600">{{ $report->user->username_pub }}</a>
                                • {{ $report->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    {{-- Reported Content --}}
                    <div class="bg-gray-50 border border-gray-200 rounded p-4 mb-4">
                        <h4 class="font-medium text-gray-900 mb-2">Reported Content:</h4>
                        @if($report->reportable_type === 'App\Models\ForumPost')
                            <div class="space-y-2">
                                <p class="font-medium">Post: {{ $report->reportable->title }}</p>
                                <p class="text-sm text-gray-700">{{ Str::limit($report->reportable->body, 300) }}</p>
                                <p class="text-xs text-gray-500">
                                    by <a href="{{ route('profile.show', $report->reportable->user->username_pub) }}"
                                          class="text-blue-700 hover:text-blue-600">{{ $report->reportable->user->username_pub }}</a>
                                    • {{ $report->reportable->created_at->diffForHumans() }}
                                </p>
                            </div>
                        @elseif($report->reportable_type === 'App\Models\ForumComment')
                            <div class="space-y-2">
                                <p class="font-medium">Comment</p>
                                <p class="text-sm text-gray-700">{{ Str::limit($report->reportable->body, 300) }}</p>
                                <p class="text-xs text-gray-500">
                                    by <a href="{{ route('profile.show', $report->reportable->user->username_pub) }}"
                                          class="text-blue-700 hover:text-blue-600">{{ $report->reportable->user->username_pub }}</a>
                                    • {{ $report->reportable->created_at->diffForHumans() }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Report Reason --}}
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900 mb-1">Report Reason:</h4>
                        <p class="text-sm text-gray-700">{{ $report->reason }}</p>
                    </div>

                    {{-- Actions for Pending Reports --}}
                    @if($report->status === 'pending')
                        <form method="POST" action="{{ route('moderator.forum.moderate.reports.review', $report) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Review Notes (Optional):</label>
                                <textarea name="notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="Add any notes about your review decision..."></textarea>
                            </div>

                            <div class="flex items-center space-x-3">
                                <button type="submit" name="action" value="dismiss"
                                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                    Dismiss Report
                                </button>
                                <button type="submit" name="action" value="ban_user"
                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    Ban User
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Show review details for reviewed reports --}}
                        <div class="bg-blue-50 border border-blue-200 rounded p-4">
                            <h4 class="font-medium text-blue-900 mb-2">Review Details:</h4>
                            <p class="text-sm text-blue-800">
                                Reviewed by
                                <a href="{{ route('profile.show', $report->reviewer->username_pub) }}"
                                   class="text-blue-700 hover:text-blue-600">{{ $report->reviewer->username_pub }}</a>
                                on {{ $report->reviewed_at->format('M d, Y g:i A') }}
                            </p>
                            @if($report->review_notes)
                                <p class="text-sm text-blue-700 mt-2">Notes: {{ $report->review_notes }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-12">
                    <p class="text-gray-500">No reports to review.</p>
                </div>
            @endforelse

            {{-- Pagination --}}
            {{ $reports->links() }}
        </div>
    </div>
@endsection
