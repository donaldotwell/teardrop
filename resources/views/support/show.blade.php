@extends('layouts.app')
@section('page-title', 'Support Ticket Details')

@section('breadcrumbs')
    <a href="{{ route('support.index') }}" class="text-amber-700 hover:text-amber-600">Support</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600">{{ $supportTicket->ticket_number }}</span>
@endsection

@section('page-heading')
    Support Ticket: {{ $supportTicket->ticket_number }}
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Ticket Header --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $supportTicket->subject }}</h2>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <span>Created {{ $supportTicket->created_at->format('M d, Y \a\t h:i A') }}</span>
                        <span>•</span>
                        <span>Type: {{ $supportTicket->getTypeDisplayName() }}</span>
                        <span>•</span>
                        <span>Category: {{ $supportTicket->getCategoryDisplayName() }}</span>
                    </div>
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

            {{-- Description --}}
            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Description</h3>
                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $supportTicket->description }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Messages --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Messages Section --}}
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="border-b border-gray-200 p-4">
                        <h3 class="text-lg font-semibold text-gray-900">Messages</h3>
                    </div>

                    <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                        @forelse($supportTicket->publicMessages as $message)
                            <div class="flex space-x-3 {{ $message->user_id === auth()->id() ? 'flex-row-reverse space-x-reverse' : '' }}">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                    @if($message->user_id === auth()->id())
                                        bg-amber-100
                                    @elseif($message->user->hasAnyRole(['admin', 'support']))
                                        bg-purple-100
                                    @else
                                        bg-gray-100
                                    @endif">
                                    <span class="text-sm font-medium
                                        @if($message->user_id === auth()->id())
                                            text-amber-700
                                        @elseif($message->user->hasAnyRole(['admin', 'support']))
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
                                        @if($message->user_id === auth()->id())
                                            <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">You</span>
                                        @elseif($message->user->hasRole('admin'))
                                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">Admin</span>
                                        @elseif($message->user->hasRole('support'))
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">Support</span>
                                        @endif
                                        <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="p-3 rounded-lg text-sm
                                        @if($message->user_id === auth()->id())
                                            bg-amber-50 text-amber-900
                                        @else
                                            bg-gray-50 text-gray-900
                                        @endif">
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

                    {{-- Add Message Form --}}
                    @if($supportTicket->isOpen())
                        <div class="border-t border-gray-200 p-4">
                            <form action="{{ route('support.add-message', $supportTicket) }}" method="POST">
                                @csrf
                                <div class="space-y-3">
                                    <textarea name="message" rows="3" placeholder="Type your message..." required
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 resize-none"></textarea>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                                class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 transition-colors text-sm font-medium">
                                            Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="border-t border-gray-200 p-4 bg-gray-50">
                            <p class="text-sm text-gray-600 text-center">
                                This ticket is {{ $supportTicket->status }} and no longer accepts new messages.
                                @if($supportTicket->status === 'resolved')
                                    <a href="#reopen-form" class="text-amber-600 hover:text-amber-800 ml-2">Reopen ticket?</a>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column - Details --}}
            <div class="space-y-6">

                {{-- Ticket Information --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ticket Information</h3>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-gray-900">Ticket Number</dt>
                            <dd class="text-gray-600">{{ $supportTicket->ticket_number }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">Status</dt>
                            <dd class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $supportTicket->status)) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">Priority</dt>
                            <dd class="text-gray-600">{{ ucfirst($supportTicket->priority) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">Created</dt>
                            <dd class="text-gray-600">{{ $supportTicket->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        @if($supportTicket->assignedTo)
                            <div>
                                <dt class="font-medium text-gray-900">Assigned To</dt>
                                <dd class="text-gray-600">{{ $supportTicket->assignedTo->username_pub }}</dd>
                            </div>
                        @endif
                        @if($supportTicket->first_response_at)
                            <div>
                                <dt class="font-medium text-gray-900">First Response</dt>
                                <dd class="text-gray-600">{{ $supportTicket->first_response_at->diffForHumans() }}</dd>
                            </div>
                        @endif
                        @if($supportTicket->resolved_at)
                            <div>
                                <dt class="font-medium text-gray-900">Resolved</dt>
                                <dd class="text-gray-600">{{ $supportTicket->resolved_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Attachments Section --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Attachments</h3>
                        @if($supportTicket->isOpen())
                            <a href="#upload-form" class="text-sm text-amber-700 hover:text-amber-800 font-medium">
                                Upload File
                            </a>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @forelse($supportTicket->attachments as $attachment)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="mb-2">
                                    <div class="text-sm font-medium text-gray-900">{{ $attachment->file_name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $attachment->created_at->format('M d, Y') }}
                                        @if($attachment->description)
                                            • {{ $attachment->description }}
                                        @endif
                                    </div>
                                </div>
                                @if($attachment->isImage())
                                    <a href="{{ route('support.download-attachment', [$supportTicket, $attachment]) }}" target="_blank">
                                        <img src="{{ $attachment->data_uri }}" 
                                             alt="{{ $attachment->file_name }}"
                                             class="w-full max-w-xs rounded-lg border border-gray-200 hover:opacity-90 transition-opacity">
                                    </a>
                                @endif
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

                    {{-- Upload Form --}}
                    @if($supportTicket->isOpen())
                        <div id="upload-form" class="mt-4 pt-4 border-t border-gray-200">
                            <form action="{{ route('support.upload-attachment', $supportTicket) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                                    <input type="file" name="attachment" required
                                           accept=".jpg,.jpeg,.png,.gif"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                                    <p class="text-xs text-gray-500 mt-1">Max 2MB. Supported: JPG, PNG, GIF</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                                    <textarea name="description" rows="2"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"></textarea>
                                </div>
                                <button type="submit"
                                        class="w-full px-3 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 text-sm font-medium">
                                    Upload File
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>

                    <div class="space-y-3">
                        {{-- Close Ticket --}}
                        @if($supportTicket->isOpen())
                            <form action="{{ route('support.close', $supportTicket) }}" method="POST">
                                @csrf
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Close Reason (Optional)</label>
                                        <textarea name="close_reason" rows="2"
                                                  placeholder="Why are you closing this ticket?"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"></textarea>
                                    </div>
                                    <button type="submit"
                                            class="w-full px-3 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm font-medium">
                                        Close Ticket
                                    </button>
                                </div>
                            </form>
                        @endif

                        {{-- Reopen Ticket --}}
                        @if($supportTicket->status === 'resolved')
                            <div id="reopen-form" class="pt-3 border-t border-gray-200">
                                <form action="{{ route('support.reopen', $supportTicket) }}" method="POST">
                                    @csrf
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Reopen Reason</label>
                                            <textarea name="reopen_reason" rows="2" required
                                                      placeholder="Why do you need to reopen this ticket?"
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"></textarea>
                                        </div>
                                        <button type="submit"
                                                class="w-full px-3 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 text-sm font-medium">
                                            Reopen Ticket
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        {{-- Back to Tickets --}}
                        <a href="{{ route('support.index') }}"
                           class="block w-full text-center px-3 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">
                            Back to All Tickets
                        </a>
                    </div>
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
