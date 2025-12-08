@extends('layouts.admin')
@section('page-title', 'Dispute Details')

@section('breadcrumbs')
    <a href="{{ route('admin.disputes.index') }}" class="text-yellow-700 hover:text-yellow-800">Disputes</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">#{{ substr($dispute->uuid, 0, 8) }}</span>
@endsection

@section('page-heading')
    Dispute Details: #{{ substr($dispute->uuid, 0, 8) }}
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Dispute Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $dispute->subject }}</h2>
                    <p class="text-gray-600 mb-4">{{ $dispute->description }}</p>

                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                        <span>Created {{ $dispute->created_at->format('M d, Y g:i A') }}</span>
                        <span>•</span>
                        <span>Type: {{ ucfirst(str_replace('_', ' ', $dispute->type)) }}</span>
                        <span>•</span>
                        <span>Amount: ${{ number_format($dispute->disputed_amount, 2) }}</span>
                    </div>

                    <div class="flex items-center space-x-3">
                        {{-- Status Badge --}}
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @switch($dispute->status)
                            @case('open')
                                bg-yellow-100 text-yellow-800
                                @break
                            @case('under_review')
                                bg-blue-100 text-blue-800
                                @break
                            @case('waiting_vendor')
                                bg-orange-100 text-orange-800
                                @break
                            @case('waiting_buyer')
                                bg-purple-100 text-purple-800
                                @break
                            @case('escalated')
                                bg-red-100 text-red-800
                                @break
                            @case('resolved')
                                bg-emerald-100 text-emerald-800
                                @break
                            @case('closed')
                                bg-gray-100 text-gray-800
                                @break
                            @default
                                bg-gray-100 text-gray-800
                        @endswitch">
                            {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                        </span>

                        {{-- Priority Badge --}}
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                        @switch($dispute->priority)
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
                            {{ ucfirst($dispute->priority) }} Priority
                        </span>
                    </div>
                </div>

                {{-- Admin Actions --}}
                <div class="flex flex-col space-y-2">
                    @if(!$dispute->assignedAdmin)
                        <form action="{{ route('admin.disputes.assign', $dispute) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">
                                Assign to Me
                            </button>
                        </form>
                    @elseif($dispute->assignedAdmin->id === auth()->id())
                        <span class="px-4 py-2 bg-purple-100 text-purple-800 rounded text-sm text-center">
                            Assigned to You
                        </span>
                    @else
                        <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded text-sm text-center">
                            Assigned to {{ $dispute->assignedAdmin->username_pub }}
                        </span>
                    @endif

                    {{-- Moderator Assignment --}}
                    <div class="border-t pt-2 mt-2">
                        <form action="{{ route('admin.disputes.reassign-moderator', $dispute) }}" method="POST">
                            @csrf
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Assigned Moderator
                                </label>
                                <select name="moderator_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    <option value="">None</option>
                                    @foreach($moderators as $moderator)
                                        <option value="{{ $moderator->id }}"
                                                @if($dispute->assignedModerator && $dispute->assignedModerator->id === $moderator->id)
                                                    selected
                                                @endif>
                                            {{ $moderator->username_pub }}
                                            @if($dispute->assignedModerator && $dispute->assignedModerator->id === $moderator->id)
                                                (Current)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    Update Moderator
                                </button>
                            </div>
                        </form>
                    </div>

                    @if($dispute->canBeEscalated())
                        <form action="{{ route('admin.disputes.escalate', $dispute) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                Escalate
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Dispute Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">${{ number_format($dispute->disputed_amount, 2) }}</div>
                    <div class="text-sm text-gray-600">Disputed Amount</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-purple-600">
                        {{ $dispute->messages->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Total Messages</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-blue-600">{{ $dispute->evidence->count() }}</div>
                    <div class="text-sm text-gray-600">Evidence Files</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-green-600">
                        {{ $dispute->created_at->diffInDays(now()) }}
                    </div>
                    <div class="text-sm text-gray-600">Days Old</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Messages and Actions --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Related Order Information --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Order</h3>
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                            @if($dispute->order->listing->media->isNotEmpty())
                                <img src="{{ $dispute->order->listing->media->first()->data_uri }}" 
                                     alt="{{ $dispute->order->listing->title }}"
                                     class="w-full h-full object-cover">
                            @else
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ $dispute->order->listing->title }}</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>Order #{{ substr($dispute->order->uuid, 0, 8) }} • {{ $dispute->order->created_at->format('M d, Y') }}</div>
                                <div>Quantity: {{ $dispute->order->quantity }} • Total: ${{ number_format($dispute->order->usd_price, 2) }}</div>
                                <div>Vendor: {{ $dispute->order->listing->user->username_pub }}</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.orders.show', $dispute->order) }}"
                           class="text-sm text-yellow-700 hover:text-yellow-800 font-medium">
                            View Order
                        </a>
                    </div>
                </div>

                {{-- Messages Section --}}
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="border-b border-gray-200 p-4">
                        <h3 class="text-lg font-semibold text-gray-900">Dispute Messages</h3>
                    </div>

                    <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                        @forelse($messages as $message)
                            <div class="flex space-x-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                    @if($message->user_id === $dispute->initiated_by)
                                        bg-blue-100
                                    @elseif($message->user_id === $dispute->disputed_against)
                                        bg-red-100
                                    @elseif($message->user->hasRole('admin'))
                                        bg-purple-100
                                    @else
                                        bg-gray-100
                                    @endif">
                                    <span class="text-sm font-medium
                                        @if($message->user_id === $dispute->initiated_by)
                                            text-blue-700
                                        @elseif($message->user_id === $dispute->disputed_against)
                                            text-red-700
                                        @elseif($message->user->hasRole('admin'))
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
                                        @if($message->user_id === $dispute->initiated_by)
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">Buyer</span>
                                        @elseif($message->user_id === $dispute->disputed_against)
                                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Vendor</span>
                                        @elseif($message->user->hasRole('admin'))
                                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">Admin</span>
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
                                        @elseif($message->message_type === 'evidence_upload')
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                </svg>
                                                <span class="text-blue-700">{{ $message->message }}</span>
                                            </div>
                                        @else
                                            {{ $message->message }}
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
                        <form action="{{ route('admin.disputes.add-admin-message', $dispute) }}" method="POST">
                            @csrf
                            <div class="space-y-3">
                                <textarea name="message" rows="3" placeholder="Type your admin message..." required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 resize-none"></textarea>
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_internal" value="1" class="mr-2">
                                        <span class="text-sm text-gray-600">Internal message (not visible to users)</span>
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

                {{-- Admin Resolution Actions --}}
                @if($dispute->isOpen())
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resolution Actions</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Resolve Dispute --}}
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Resolve Dispute</h4>
                                <form action="{{ route('admin.disputes.resolve', $dispute) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Resolution</label>
                                        <select name="resolution" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                            <option value="">Select resolution...</option>
                                            <option value="buyer_favor">In favor of buyer</option>
                                            <option value="vendor_favor">In favor of vendor</option>
                                            <option value="partial_refund">Partial refund</option>
                                            <option value="no_action">No action required</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Refund Amount</label>
                                        <input type="number" name="refund_amount" step="0.01" min="0" max="{{ $dispute->disputed_amount }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                               placeholder="0.00">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Resolution Notes</label>
                                        <textarea name="resolution_notes" rows="2" required
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                                  placeholder="Explain the resolution decision..."></textarea>
                                    </div>
                                    <button type="submit"
                                            class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                        Resolve Dispute
                                    </button>
                                </form>
                            </div>

                            {{-- Close Dispute --}}
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Close Dispute</h4>
                                <form action="{{ route('admin.disputes.close', $dispute) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Close Reason</label>
                                        <textarea name="close_reason" rows="3" required
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                                  placeholder="Reason for closing without resolution..."></textarea>
                                    </div>
                                    <button type="submit"
                                            class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                                        Close Dispute
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column - Details --}}
            <div class="space-y-6">

                {{-- Dispute Participants --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Participants</h3>

                    <div class="space-y-4">
                        {{-- Buyer --}}
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-700 font-medium">{{ substr($dispute->initiatedBy->username_pub, 0, 1) }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $dispute->initiatedBy->username_pub }}</div>
                                <div class="text-sm text-blue-600">Buyer (Dispute Initiator)</div>
                                <div class="text-sm text-gray-500">TL{{ $dispute->initiatedBy->trust_level ?? 0 }}</div>
                            </div>
                            <a href="{{ route('admin.users.show', $dispute->initiatedBy) }}"
                               class="text-sm text-yellow-600 hover:text-yellow-800">View</a>
                        </div>

                        {{-- Vendor --}}
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-red-700 font-medium">{{ substr($dispute->disputedAgainst->username_pub, 0, 1) }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $dispute->disputedAgainst->username_pub }}</div>
                                <div class="text-sm text-red-600">Vendor</div>
                                <div class="text-sm text-gray-500">TL{{ $dispute->disputedAgainst->trust_level ?? 0 }}</div>
                            </div>
                            <a href="{{ route('admin.users.show', $dispute->disputedAgainst) }}"
                               class="text-sm text-yellow-600 hover:text-yellow-800">View</a>
                        </div>

                        {{-- Admin (if assigned) --}}
                        @if($dispute->assignedAdmin)
                            <div class="flex items-center space-x-3 pt-2 border-t border-gray-100">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-purple-700 font-medium">{{ substr($dispute->assignedAdmin->username_pub, 0, 1) }}</span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $dispute->assignedAdmin->username_pub }}</div>
                                    <div class="text-sm text-purple-600">Assigned Admin</div>
                                    <div class="text-sm text-gray-500">Assigned {{ $dispute->admin_reviewed_at ? $dispute->admin_reviewed_at->diffForHumans() : 'recently' }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Priority Management --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Priority Management</h3>

                    <form action="{{ route('admin.disputes.update-priority', $dispute) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Priority</label>
                            <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="low" {{ $dispute->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $dispute->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $dispute->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ $dispute->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            Update Priority
                        </button>
                    </form>
                </div>

                {{-- Evidence Section --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Evidence Files</h3>

                    <div class="space-y-3">
                        @forelse($dispute->evidence as $evidence)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        @if($evidence->isImage())
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
                                        <div class="text-sm font-medium text-gray-900">{{ $evidence->file_name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ ucfirst(str_replace('_', ' ', $evidence->evidence_type)) }} •
                                            {{ $evidence->formatted_file_size }} •
                                            {{ $evidence->uploadedBy->username_pub }}
                                        </div>
                                        @if($evidence->is_verified)
                                            <div class="text-xs text-green-600 font-medium">✓ Verified</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.disputes.download-evidence', [$dispute, $evidence]) }}"
                                       class="text-sm text-blue-600 hover:text-blue-800">Download</a>
                                    @if(!$evidence->is_verified)
                                        <form action="{{ route('admin.disputes.verify-evidence', [$dispute, $evidence]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-sm text-green-600 hover:text-green-800">
                                                Verify
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-gray-500">
                                <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <p class="mt-2 text-sm">No evidence uploaded yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Dispute Timeline --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Dispute Timeline</h3>

                    <div class="space-y-4">
                        {{-- Dispute Created --}}
                        <div class="flex items-center space-x-4">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">Dispute Created</div>
                                <div class="text-sm text-gray-500">{{ $dispute->created_at->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>

                        {{-- Vendor Response --}}
                        @if($dispute->vendor_responded_at)
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Vendor Responded</div>
                                    <div class="text-sm text-gray-500">{{ $dispute->vendor_responded_at->format('M d, Y g:i A') }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Admin Review --}}
                        @if($dispute->admin_reviewed_at)
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Admin Review Started</div>
                                    <div class="text-sm text-gray-500">{{ $dispute->admin_reviewed_at->format('M d, Y g:i A') }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Escalation --}}
                        @if($dispute->escalated_at)
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Dispute Escalated</div>
                                    <div class="text-sm text-gray-500">{{ $dispute->escalated_at->format('M d, Y g:i A') }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Resolution --}}
                        @if($dispute->resolved_at)
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Dispute Resolved</div>
                                    <div class="text-sm text-gray-500">{{ $dispute->resolved_at->format('M d, Y g:i A') }}</div>
                                    @if($dispute->resolution)
                                        <div class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $dispute->resolution)) }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Closure --}}
                        @if($dispute->closed_at)
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Dispute Closed</div>
                                    <div class="text-sm text-gray-500">{{ $dispute->closed_at->format('M d, Y g:i A') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
