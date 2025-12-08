@extends('layouts.moderator')
@section('page-title', 'Dispute #' . $dispute->dispute_number)

@section('breadcrumbs')
    <a href="{{ route('moderator.disputes.index') }}" class="text-gray-600 hover:text-blue-600">Dispute Management</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600">Dispute #{{ $dispute->dispute_number }}</span>
@endsection

@section('page-heading')
    Dispute #{{ $dispute->dispute_number }}
@endsection

@section('page-description')
    Order: {{ $dispute->order->uuid ?? 'N/A' }} • Amount: ${{ number_format($dispute->disputed_amount, 2) }}
@endsection

@section('page-actions')
    <div class="flex items-center space-x-3">
        @if(!$dispute->assignedModerator)
            <form method="POST" action="{{ route('moderator.disputes.assign', $dispute) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Assign to Me
                </button>
            </form>
        @elseif($dispute->assignedModerator->id === auth()->id())
            <div class="flex items-center space-x-2">
                <form method="POST" action="{{ route('moderator.disputes.unassign', $dispute) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 text-sm">
                        Unassign
                    </button>
                </form>

                <details class="inline-block relative">
                    <summary class="cursor-pointer px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Reassign
                    </summary>
                    <div class="absolute right-0 mt-2 w-64 bg-white border border-gray-300 rounded-lg shadow-lg z-10 p-4">
                        <form method="POST" action="{{ route('moderator.disputes.reassign-moderator', $dispute) }}">
                            @csrf
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Transfer to Moderator
                                </label>
                                <select name="moderator_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    <option value="">Select moderator...</option>
                                    @foreach($moderators as $moderator)
                                        @if($moderator->id !== auth()->id())
                                            <option value="{{ $moderator->id }}">
                                                {{ $moderator->username_pub }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    Transfer Dispute
                                </button>
                            </div>
                        </form>
                    </div>
                </details>
            </div>
        @endif

        @if($dispute->status !== 'escalated' && $dispute->assignedModerator && $dispute->assignedModerator->id === auth()->id())
            <details class="inline-block">
                <summary class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm inline-block">
                    Escalate
                </summary>
                <div class="absolute mt-2 p-5 border w-96 shadow-lg rounded-md bg-white z-50">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Escalate Dispute</h3>
                    <form method="POST" action="{{ route('moderator.disputes.escalate', $dispute) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Escalation Reason</label>
                                <textarea name="escalation_reason" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Explain why this dispute needs admin attention..."></textarea>
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

        <a href="{{ route('moderator.disputes.index') }}"
           class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">
            Back to List
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Dispute Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Left Column - Basic Info --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Dispute Details</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Status:</dt>
                                <dd>
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
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                        {{ ucwords(str_replace('_', ' ', $dispute->status)) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Priority:</dt>
                                <dd>
                                    @php
                                        $priorityColor = match($dispute->priority ?? 'medium') {
                                            'urgent' => 'text-red-600',
                                            'high' => 'text-orange-600',
                                            'medium' => 'text-yellow-600',
                                            'low' => 'text-green-600',
                                            default => 'text-gray-600'
                                        };
                                    @endphp
                                    <span class="font-medium {{ $priorityColor }}">
                                        {{ ucfirst($dispute->priority ?? 'Medium') }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Amount:</dt>
                                <dd class="font-medium text-gray-900">${{ number_format($dispute->disputed_amount, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Created:</dt>
                                <dd class="text-sm text-gray-900">{{ $dispute->created_at->format('M d, Y g:i A') }}</dd>
                            </div>
                            @if($dispute->info_request_deadline)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Info Deadline:</dt>
                                    <dd class="text-sm {{ $dispute->info_request_deadline->isPast() ? 'text-red-600' : 'text-orange-600' }}">
                                        {{ $dispute->info_request_deadline->format('M d, Y g:i A') }}
                                        @if($dispute->info_request_deadline->isPast())
                                            (Overdue)
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Assignment Info --}}
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-2">Assignment</h4>
                        @if($dispute->assignedModerator)
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-medium">
                                        {{ substr($dispute->assignedModerator->username_pub, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $dispute->assignedModerator->username_pub }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Assigned {{ $dispute->assigned_at->diffForHumans() }}
                                        @if($dispute->auto_assigned)
                                            • Auto-assigned
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-gray-500">No moderator assigned</div>
                        @endif
                    </div>
                </div>

                {{-- Right Column - Parties --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Dispute Parties</h3>

                        {{-- Buyer --}}
                        <div class="border border-gray-200 rounded-lg p-4 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Buyer (Dispute Initiator)</span>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Buyer</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-medium text-sm">
                                        {{ substr($dispute->initiatedBy->username_pub, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('profile.show', $dispute->initiatedBy->username_pub) }}"
                                           class="hover:text-blue-600">
                                            {{ $dispute->initiatedBy->username_pub }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ID: {{ $dispute->initiatedBy->id }} •
                                        Trust Level: {{ $dispute->initiatedBy->trust_level ?? 0 }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Vendor --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Vendor</span>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Vendor</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 font-medium text-sm">
                                        {{ substr($dispute->disputedAgainst->username_pub, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('profile.show', $dispute->disputedAgainst->username_pub) }}"
                                           class="hover:text-blue-600">
                                            {{ $dispute->disputedAgainst->username_pub }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ID: {{ $dispute->disputedAgainst->id }} •
                                        Vendor Level: {{ $dispute->disputedAgainst->vendor_level ?? 0 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Details --}}
        @if($dispute->order)
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
                
                {{-- Listing Image and Title --}}
                @if($dispute->order->listing)
                    <div class="flex items-center space-x-4 mb-6 pb-6 border-b border-gray-100">
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if($dispute->order->listing->media->isNotEmpty())
                                <img src="{{ $dispute->order->listing->media->first()->data_uri }}" 
                                     alt="{{ $dispute->order->listing->title }}"
                                     class="w-full h-full object-cover">
                            @else
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 truncate">{{ $dispute->order->listing->title }}</h4>
                            <p class="text-sm text-gray-500 mt-1">Vendor: {{ $dispute->order->listing->user->username_pub ?? 'N/A' }}</p>
                        </div>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Order ID:</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $dispute->order->uuid }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Order Total:</dt>
                                <dd class="text-sm font-medium text-gray-900">${{ number_format($dispute->order->total_amount ?? 0, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Order Status:</dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst($dispute->order->status ?? 'N/A') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        @endif

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
                                    'assigned' => 'bg-green-500',
                                    'escalated' => 'bg-red-500',
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
                <h3 class="text-lg font-semibold text-gray-900">Messages & Communication</h3>
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
                                        'moderator_note' => 'bg-purple-100 text-purple-800',
                                        'system_message' => 'bg-gray-100 text-gray-800',
                                        'assignment_update' => 'bg-green-100 text-green-800',
                                        'info_request' => 'bg-orange-100 text-orange-800',
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
                            <div class="text-sm text-gray-700 bg-gray-50 rounded p-3">
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

        {{-- Moderator Actions --}}
        @if($dispute->assignedModerator && $dispute->assignedModerator->id === auth()->id() && $dispute->status !== 'resolved')
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Moderator Actions</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Add Note --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Add Note</h4>
                        <form method="POST" action="{{ route('moderator.disputes.add-note', $dispute) }}">
                            @csrf
                            <div class="space-y-3">
                                <textarea name="note" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Add your note here..."></textarea>
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_internal" value="1" checked
                                           class="rounded border-gray-300 mr-2">
                                    <label class="text-sm text-gray-600">Internal note (not visible to users)</label>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    Add Note
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Request Information --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Request Information</h4>
                        <form method="POST" action="{{ route('moderator.disputes.request-info', $dispute) }}">
                            @csrf
                            <div class="space-y-3">
                                <select name="target" class="w-full px-3 py-2 border border-gray-300 rounded">
                                    <option value="buyer">From Buyer</option>
                                    <option value="vendor">From Vendor</option>
                                    <option value="both">From Both Parties</option>
                                </select>
                                <textarea name="request_message" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="What information do you need?"></textarea>
                                <input type="datetime-local" name="deadline"
                                       class="w-full px-3 py-2 border border-gray-300 rounded"
                                       min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}">
                                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 text-sm">
                                    Request Info
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
