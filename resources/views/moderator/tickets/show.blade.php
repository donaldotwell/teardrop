@extends('layouts.moderator')
@section('page-title', 'Ticket #' . $supportTicket->ticket_number)

@section('breadcrumbs')
    <a href="{{ route('moderator.tickets.index') }}" class="text-gray-600 hover:text-blue-600">Ticket Management</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600">Ticket #{{ $supportTicket->ticket_number }}</span>
@endsection

@section('page-heading')
    Ticket #{{ $supportTicket->ticket_number }}
@endsection

@section('page-description')
    {{ $supportTicket->subject }} • {{ ucfirst($supportTicket->category) }}
@endsection

@section('page-actions')
    <div class="flex items-center space-x-3">
        @if(!$supportTicket->assignedTo)
            <form method="POST" action="{{ route('moderator.tickets.assign', $supportTicket) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Assign to Me
                </button>
            </form>
        @elseif($supportTicket->assignedTo->id === auth()->id())
            <form method="POST" action="{{ route('moderator.tickets.unassign', $supportTicket) }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 text-sm">
                    Unassign
                </button>
            </form>
        @endif

        @if($supportTicket->assignedTo && $supportTicket->assignedTo->id === auth()->id() && $supportTicket->status !== 'escalated')
            <details class="inline-block">
                <summary class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm inline-block">
                    Escalate
                </summary>
                <div class="absolute mt-2 p-5 border w-96 shadow-lg rounded-md bg-white z-50">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Escalate Ticket</h3>
                    <form method="POST" action="{{ route('moderator.tickets.escalate', $supportTicket) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Escalation Reason</label>
                                <textarea name="escalation_reason" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Explain why this ticket needs admin attention..."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Escalate to Admin (Optional)</label>
                                <select name="escalate_to" class="w-full px-3 py-2 border border-gray-300 rounded">
                                    <option value="">Auto-assign to available admin</option>
                                    @if($teamMembers ?? null)
                                        @foreach($teamMembers->filter(function($member) { return $member->hasRole('admin'); }) as $admin)
                                            <option value="{{ $admin->id }}">{{ $admin->username_pub }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    Escalate to Admin
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </details>
        @endif

        <a href="{{ route('moderator.tickets.index') }}"
           class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">
            Back to List
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Ticket Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Left Column - Ticket Details --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Ticket Details</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Status:</dt>
                                <dd>
                                    @php
                                        $statusColor = match($supportTicket->status) {
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
                                        {{ ucwords(str_replace('_', ' ', $supportTicket->status)) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Priority:</dt>
                                <dd>
                                    @php
                                        $priorityColor = match($supportTicket->priority) {
                                            'urgent' => 'text-red-600',
                                            'high' => 'text-orange-600',
                                            'medium' => 'text-yellow-600',
                                            'low' => 'text-green-600',
                                            default => 'text-gray-600'
                                        };
                                    @endphp
                                    <span class="font-medium {{ $priorityColor }}">
                                        {{ ucfirst($supportTicket->priority) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Category:</dt>
                                <dd class="text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $supportTicket->category)) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Created:</dt>
                                <dd class="text-sm text-gray-900">{{ $supportTicket->created_at->format('M d, Y g:i A') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Last Activity:</dt>
                                <dd class="text-sm text-gray-900">{{ $supportTicket->last_activity_at->diffForHumans() }}</dd>
                            </div>
                            @if($supportTicket->first_response_at)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">First Response:</dt>
                                    <dd class="text-sm text-gray-900">{{ $supportTicket->first_response_at->diffForHumans() }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Assignment Info --}}
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-2">Assignment</h4>
                        @if($supportTicket->assignedTo)
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 font-medium">
                                        {{ substr($supportTicket->assignedTo->username_pub, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $supportTicket->assignedTo->username_pub }}
                                        @if($supportTicket->assignedTo->id === auth()->id())
                                            <span class="text-blue-600">(You)</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Assigned {{ $supportTicket->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-gray-500">No one assigned</div>
                        @endif
                    </div>
                </div>

                {{-- Right Column - User Info --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">User Information</h3>

                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-medium">
                                        {{ substr($supportTicket->user->username_pub, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('profile.show', $supportTicket->user->username_pub) }}"
                                           class="hover:text-blue-600">
                                            {{ $supportTicket->user->username_pub }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ID: {{ $supportTicket->user->id }} •
                                        Trust Level: {{ $supportTicket->user->trust_level ?? 0 }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-sm text-gray-600">
                                <div>Joined: {{ $supportTicket->user->created_at->format('M d, Y') }}</div>
                                @if($supportTicket->user->vendor_level)
                                    <div>Vendor Level: {{ $supportTicket->user->vendor_level }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Related Reports --}}
                    @if($relatedReports->isNotEmpty())
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-2">Related Reports</h4>
                            <div class="space-y-2">
                                @foreach($relatedReports as $report)
                                    <div class="text-sm border border-gray-200 rounded p-2">
                                        <div class="font-medium text-gray-900">
                                            Report #{{ $report->id }}
                                        </div>
                                        <div class="text-gray-600">
                                            {{ Str::limit($report->reason, 50) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $report->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Original Request --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Original Request</h3>
            <div class="bg-gray-50 border border-gray-200 rounded p-4">
                <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $supportTicket->description }}</div>
            </div>
        </div>

        {{-- Timeline --}}
        @if($timeline->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                <div class="space-y-4">
                    @foreach($timeline as $event)
                        <div class="flex items-start space-x-3">
                            @php
                                $eventColor = match($event['type']) {
                                    'created' => 'bg-blue-500',
                                    'assignment' => 'bg-green-500',
                                    'resolved' => 'bg-purple-500',
                                    default => 'bg-gray-500'
                                };
                            @endphp
                            <div class="w-3 h-3 {{ $eventColor }} rounded-full mt-1"></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ $event['title'] }}</div>
                                <div class="text-sm text-gray-600">{{ $event['description'] }}</div>
                                <div class="text-xs text-gray-500">{{ $event['timestamp']->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Messages --}}
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Communication History</h3>
            </div>

            <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
                @forelse($messages as $message)
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <span class="text-gray-600 font-medium text-xs">
                                {{ $message->user ? substr($message->user->username_pub, 0, 1) : 'S' }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $message->user->username_pub ?? 'System' }}
                                </span>
                                @php
                                    $messageTypeColor = match($message->message_type) {
                                        'user_message' => 'bg-blue-100 text-blue-800',
                                        'staff_message' => 'bg-purple-100 text-purple-800',
                                        'system_message' => 'bg-gray-100 text-gray-800',
                                        'assignment_update' => 'bg-green-100 text-green-800',
                                        'status_update' => 'bg-yellow-100 text-yellow-800',
                                        'escalation' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="text-xs px-2 py-1 rounded-full {{ $messageTypeColor }}">
                                    {{ ucwords(str_replace('_', ' ', $message->message_type)) }}
                                </span>
                                @if($message->is_internal)
                                    <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800">Internal</span>
                                @endif
                                <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-sm text-gray-700 bg-gray-50 rounded p-3 whitespace-pre-wrap">
                                {{ $message->message }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-8">
                        No messages yet.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Moderator Response Form --}}
        @if($supportTicket->assignedTo && $supportTicket->assignedTo->id === auth()->id() && !in_array($supportTicket->status, ['resolved', 'closed']))
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Response</h3>

                <form method="POST" action="{{ route('moderator.tickets.add-response', $supportTicket) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Response Message</label>
                            <textarea name="message" rows="4" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Type your response here..."></textarea>
                        </div>

                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_internal" value="1"
                                       class="rounded border-gray-300 mr-2">
                                <label class="text-sm text-gray-600">Internal note (not visible to user)</label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600">Change status to:</label>
                                <select name="status_change" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                    <option value="">Keep current status</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="pending_user">Pending User Response</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Send Response
                            </button>

                            @if($supportTicket->status !== 'resolved')
                                <details class="inline-block">
                                    <summary class="cursor-pointer px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 inline-block">
                                        Mark as Resolved
                                    </summary>
                                    <div class="absolute mt-2 p-5 border w-96 shadow-lg rounded-md bg-white z-50">
                                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resolve Ticket</h3>
                                        <form method="POST" action="{{ route('moderator.tickets.resolve', $supportTicket) }}">
                                            @csrf
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Resolution Notes</label>
                                                    <textarea name="resolution_notes" rows="4" required
                                                              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                              placeholder="Describe how this ticket was resolved..."></textarea>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" name="follow_up_required" value="1"
                                                           class="rounded border-gray-300 mr-2">
                                                    <label class="text-sm text-gray-600">Schedule follow-up in 3 days</label>
                                                </div>
                                                <div class="flex justify-end">
                                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                                        Mark as Resolved
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </details>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        @endif

        {{-- Quick Actions for Unassigned Tickets --}}
        @if(!$supportTicket->assignedTo)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">This ticket is unassigned</h4>
                        <p class="text-sm text-yellow-700">Assign yourself to respond to this ticket.</p>
                    </div>
                    <form method="POST" action="{{ route('moderator.tickets.assign', $supportTicket) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">
                            Assign to Me
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
