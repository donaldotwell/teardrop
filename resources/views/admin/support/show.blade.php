@extends('layouts.admin')
@section('page-title', 'Support Ticket Details')

@section('breadcrumbs')
    <a href="{{ route('admin.support.index') }}" class="text-yellow-700 hover:text-yellow-800">Support Tickets</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">{{ $supportTicket->ticket_number }}</span>
@endsection

@section('page-heading')
    Support Ticket: {{ $supportTicket->ticket_number }}
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Ticket Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $supportTicket->subject }}</h2>
                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                        <span>Created {{ $supportTicket->created_at->format('M d, Y g:i A') }}</span>
                        <span>•</span>
                        <span>Type: {{ $supportTicket->getTypeDisplayName() }}</span>
                        <span>•</span>
                        <span>Category: {{ $supportTicket->getCategoryDisplayName() }}</span>
                    </div>

                    <div class="flex items-center space-x-3">
                        {{-- Status Badge --}}
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @switch($supportTicket->status)
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
                            {{ ucfirst(str_replace('_', ' ', $supportTicket->status)) }}
                        </span>

                        {{-- Priority Badge --}}
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                        @switch($supportTicket->priority)
                            @case('low')
                                bg-gray-100 text-gray-700
                                @break
                            @case('medium')
                                bg-blue-100 text-blue-700
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
                            {{ ucfirst($supportTicket->priority) }} Priority
                        </span>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="flex flex-col space-y-2">
                    @if(!$supportTicket->assignedTo)
                        <form action="{{ route('admin.support.assign', $supportTicket) }}" method="POST">
                            @csrf
                            <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                            <button type="submit"
                                    class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">
                                Assign to Me
                            </button>
                        </form>
                    @elseif($supportTicket->assignedTo->id === auth()->id())
                        <span class="px-4 py-2 bg-purple-100 text-purple-800 rounded text-sm text-center">
                            Assigned to You
                        </span>
                    @else
                        <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded text-sm text-center">
                            Assigned to {{ $supportTicket->assignedTo->username_pub }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            <div class="border-t border-gray-200 pt-4 mb-4">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Description</h3>
                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $supportTicket->description }}</div>
            </div>

            {{-- Ticket Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">{{ $supportTicket->messages->count() }}</div>
                    <div class="text-sm text-gray-600">Total Messages</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-purple-600">{{ $supportTicket->attachments->count() }}</div>
                    <div class="text-sm text-gray-600">Attachments</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-blue-600">
                        {{ $supportTicket->getResponseTime() ? round($supportTicket->getResponseTime() / 60, 1) . 'h' : 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-600">Response Time</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-green-600">
                        {{ $supportTicket->created_at->diffInDays(now()) }}
                    </div>
                    <div class="text-sm text-gray-600">Days Old</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Messages and Actions --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- User Information --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">User Information</h3>
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                            <span class="text-yellow-700 font-bold text-xl">{{ substr($supportTicket->user->username_pub, 0, 1) }}</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ $supportTicket->user->username_pub }}</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>User ID: {{ $supportTicket->user->id }}</div>
                                <div>Trust Level: TL{{ $supportTicket->user->trust_level ?? 0 }}</div>
                                <div>Member since: {{ $supportTicket->user->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.users.show', $supportTicket->user) }}"
                           class="text-sm text-yellow-700 hover:text-yellow-800 font-medium">
                            View Profile
                        </a>
                    </div>
                </div>

                {{-- Messages Section --}}
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="border-b border-gray-200 p-4">
                        <h3 class="text-lg font-semibold text-gray-900">Messages</h3>
                    </div>

                    <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                        @forelse($messages as $message)
                            <div class="flex space-x-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                    @if($message->user_id === $supportTicket->user_id)
                                        bg-blue-100
                                    @elseif($message->user->hasRole('admin'))
                                        bg-red-100
                                    @elseif($message->user->hasRole('support'))
                                        bg-purple-100
                                    @else
                                        bg-gray-100
                                    @endif">
                                    <span class="text-sm font-medium
                                        @if($message->user_id === $supportTicket->user_id)
                                            text-blue-700
                                        @elseif($message->user->hasRole('admin'))
                                            text-red-700
                                        @elseif($message->user->hasRole('support'))
                                            text-purple-700
                                        @else
                                            text-gray-700
                                        @endif">
                                        {{ substr($message->user->username_pub, 0, 1) }}
                                    </span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-sm font-medium text-gray-900">{{ $message->user->username_pub }}</span>
                                        @if($message->user_id === $supportTicket->user_id)
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">User</span>
                                        @elseif($message->user->hasRole('admin'))
                                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Admin</span>
                                        @elseif($message->user->hasRole('support'))
                                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">Support</span>
                                        @endif
                                        @if($message->is_internal)
                                            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">Internal</span>
                                        @endif
                                        <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="p-3 rounded-lg text-sm bg-gray-50 text-gray-900">
                                        @if($message->message_type === 'system_message')
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="italic text-gray-600">{{ $message->message }}</span>
                                            </div>
                                        @else
                                            <div class="whitespace-pre-line">{{ $message->message }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <p class="mt-2">No messages yet</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Add Admin Message Form --}}
                    <div class="border-t border-gray-200 p-4">
                        <form action="{{ route('admin.support.add-message', $supportTicket) }}" method="POST">
                            @csrf
                            <div class="space-y-3">
                                <textarea name="message" rows="3" placeholder="Type your response..." required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 resize-none"></textarea>
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_internal" value="1" class="mr-2">
                                        <span class="text-sm text-gray-600">Internal note (not visible to user)</span>
                                    </label>
                                    <button type="submit"
                                            class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors text-sm font-medium">
                                        Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Admin Action Forms --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Actions</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Resolve Ticket --}}
                        @if($supportTicket->isOpen())
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Resolve Ticket</h4>
                                <form action="{{ route('admin.support.resolve', $supportTicket) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Resolution Notes</label>
                                        <textarea name="resolution_notes" rows="3" required
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                                  placeholder="Explain how the issue was resolved..."></textarea>
                                    </div>
                                    <button type="submit"
                                            class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                        Resolve Ticket
                                    </button>
                                </form>
                            </div>

                            {{-- Close Ticket --}}
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Close Ticket</h4>
                                <form action="{{ route('admin.support.close', $supportTicket) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Close Reason</label>
                                        <textarea name="close_reason" rows="3" required
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                                  placeholder="Reason for closing without resolution..."></textarea>
                                    </div>
                                    <button type="submit"
                                            class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                                        Close Ticket
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column - Management --}}
            <div class="space-y-6">

                {{-- Assignment Management --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Assignment</h3>

                    @if($supportTicket->assignedTo)
                        <div class="mb-4">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-purple-700 font-medium">{{ substr($supportTicket->assignedTo->username_pub, 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $supportTicket->assignedTo->username_pub }}</div>
                                    <div class="text-sm text-gray-500">Current assignee</div>
                                </div>
                            </div>

                            {{-- Reassign Form --}}
                            <form action="{{ route('admin.support.reassign', $supportTicket) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reassign To</label>
                                    <select name="reassign_to" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                        @foreach($staffMembers as $staff)
                                            @if($staff->id !== $supportTicket->assigned_to)
                                                <option value="{{ $staff->id }}">{{ $staff->username_pub }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason (Optional)</label>
                                    <textarea name="reassign_reason" rows="2"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                              placeholder="Why reassigning..."></textarea>
                                </div>
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                    Reassign Ticket
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('admin.support.assign', $supportTicket) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                                <select name="assigned_to" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    <option value="">Select staff member...</option>
                                    @foreach($staffMembers as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->username_pub }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm">
                                Assign Ticket
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Status Management --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Management</h3>

                    <form action="{{ route('admin.support.update-status', $supportTicket) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="open" {{ $supportTicket->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="pending" {{ $supportTicket->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $supportTicket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="on_hold" {{ $supportTicket->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="resolved" {{ $supportTicket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $supportTicket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason (Optional)</label>
                            <textarea name="status_reason" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                      placeholder="Reason for status change..."></textarea>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            Update Status
                        </button>
                    </form>
                </div>

                {{-- Priority Management --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Priority Management</h3>

                    <form action="{{ route('admin.support.update-priority', $supportTicket) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="low" {{ $supportTicket->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $supportTicket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $supportTicket->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ $supportTicket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason (Optional)</label>
                            <textarea name="priority_reason" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                      placeholder="Reason for priority change..."></textarea>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 text-sm">
                            Update Priority
                        </button>
                    </form>
                </div>

                {{-- Attachments Section --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Attachments</h3>

                    <div class="space-y-3">
                        @forelse($supportTicket->attachments as $attachment)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        @if($attachment->isImage())
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a1 1 0 011.828 0L16 16m-2-2l1.586-1.586a1 1 0 011.828 0L20 15m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $attachment->file_name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $attachment->formatted_file_size }} •
                                            {{ $attachment->uploadedBy->username_pub }} •
                                            {{ $attachment->created_at->format('M d, Y') }}
                                        </div>
                                        @if($attachment->description)
                                            <div class="text-xs text-gray-600">{{ $attachment->description }}</div>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('admin.support.download-attachment', [$supportTicket, $attachment]) }}"
                                   class="text-sm text-blue-600 hover:text-blue-800">Download</a>
                            </div>
                        @empty
                            <div class="text-center py-4 text-gray-500">
                                <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <p class="mt-2 text-sm">No attachments</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Ticket Information --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ticket Information</h3>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-gray-900">Ticket Number</dt>
                            <dd class="text-gray-600">{{ $supportTicket->ticket_number }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">Created</dt>
                            <dd class="text-gray-600">{{ $supportTicket->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        @if($supportTicket->first_response_at)
                            <div>
                                <dt class="font-medium text-gray-900">First Response</dt>
                                <dd class="text-gray-600">{{ $supportTicket->first_response_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        @endif
                        @if($supportTicket->resolved_at)
                            <div>
                                <dt class="font-medium text-gray-900">Resolved</dt>
                                <dd class="text-gray-600">{{ $supportTicket->resolved_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        @endif
                        @if($supportTicket->closed_at)
                            <div>
                                <dt class="font-medium text-gray-900">Closed</dt>
                                <dd class="text-gray-600">{{ $supportTicket->closed_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="font-medium text-gray-900">Last Activity</dt>
                            <dd class="text-gray-600">{{ $supportTicket->last_activity_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Resolution Notes --}}
                @if($supportTicket->resolution_notes)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-green-800 mb-3">Resolution Notes</h3>
                        <div class="text-sm text-green-700 whitespace-pre-line">{{ $supportTicket->resolution_notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
