@extends('layouts.app')
@section('page-title', 'Dispute Details')

@section('breadcrumbs')
    <a href="{{ route('disputes.index') }}" class="text-amber-700 hover:text-amber-900">My Disputes</a>
    <span class="text-amber-400">/</span>
    <span class="text-amber-700">Dispute #{{ substr($dispute->uuid, 0, 8) }}</span>
@endsection

@section('page-heading')
    Dispute #{{ substr($dispute->uuid, 0, 8) }}
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Dispute Header --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $dispute->subject }}</h2>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <span>Created {{ $dispute->created_at->format('M d, Y \a\t h:i A') }}</span>
                        <span>•</span>
                        <span>Type: {{ ucfirst(str_replace('_', ' ', $dispute->type)) }}</span>
                        <span>•</span>
                        <span>Amount: ${{ number_format($dispute->disputed_amount, 2) }}</span>
                    </div>
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
                    @if($dispute->priority !== 'medium')
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                        @switch($dispute->priority)
                            @case('low')
                                bg-gray-100 text-gray-700
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
                    @endif
                </div>
            </div>

            {{-- Description --}}
            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Description</h3>
                <p class="text-sm text-gray-700">{{ $dispute->description }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Messages --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Order Information --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Order</h3>
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                            <x-image-gallery
                                :images="$dispute->order->listing->media"
                                :title="$dispute->order->listing->title"
                                :modal-id="'gallery-dispute-' . $dispute->id"
                            />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ $dispute->order->listing->title }}</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>Order #{{ substr($dispute->order->uuid, 0, 8) }} • {{ $dispute->order->created_at->format('M d, Y') }}</div>
                                <div>Quantity: {{ $dispute->order->quantity }} • Total: ${{ number_format($dispute->order->usd_price, 2) }}</div>
                            </div>
                        </div>
                        <a href="{{ route('orders.show', $dispute->order) }}"
                           class="text-sm text-amber-700 hover:text-amber-800 font-medium">
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
                        @forelse($dispute->publicMessages as $message)
                            <div class="flex space-x-3 {{ $message->user_id === auth()->id() ? 'flex-row-reverse space-x-reverse' : '' }}">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $message->user_id === auth()->id() ? 'bg-amber-100' : 'bg-gray-100' }}">
                                    <span class="text-sm font-medium
                                        {{ $message->user_id === auth()->id() ? 'text-amber-700' : 'text-gray-700' }}">
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
                                        <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="p-3 rounded-lg text-sm
                                        {{ $message->user_id === auth()->id() ? 'bg-amber-50 text-amber-900' : 'bg-gray-50 text-gray-900' }}">
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

                    {{-- Add Message Form --}}
                    @if($dispute->isOpen())
                        <div class="border-t border-gray-200 p-4">
                            <form action="{{ route('disputes.add-message', $dispute) }}" method="POST">
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
                            <p class="text-sm text-gray-600 text-center">This dispute is {{ $dispute->status }} and no longer accepts new messages.</p>
                        </div>
                    @endif
                </div>
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
                            <div>
                                <div class="font-medium text-gray-900">{{ $dispute->initiatedBy->username_pub }}</div>
                                <div class="text-sm text-blue-600">Buyer (Dispute Initiator)</div>
                            </div>
                        </div>

                        {{-- Vendor --}}
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-red-700 font-medium">{{ substr($dispute->disputedAgainst->username_pub, 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $dispute->disputedAgainst->username_pub }}</div>
                                <div class="text-sm text-red-600">Vendor</div>
                            </div>
                        </div>

                        {{-- Admin (if assigned) --}}
                        @if($dispute->assignedAdmin)
                            <div class="flex items-center space-x-3 pt-2 border-t border-gray-100">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-purple-700 font-medium">{{ substr($dispute->assignedAdmin->username_pub, 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $dispute->assignedAdmin->username_pub }}</div>
                                    <div class="text-sm text-purple-600">Assigned Admin</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Evidence Section --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Evidence</h3>

                    {{-- Evidence Upload Form --}}
                    @if($dispute->isOpen())
                        <details class="mb-4">
                            <summary class="cursor-pointer text-sm text-amber-700 hover:text-amber-800 font-medium">
                                Upload Evidence
                            </summary>
                            <div class="mt-3 p-4 bg-gray-50 rounded-lg">
                                <form action="{{ route('disputes.upload-evidence', $dispute) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Evidence Type</label>
                                            <select name="evidence_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                                                <option value="">Select type...</option>
                                                <option value="product_photo">Product Photo</option>
                                                <option value="packaging_photo">Packaging Photo</option>
                                                <option value="shipping_label">Shipping Label</option>
                                                <option value="receipt">Receipt</option>
                                                <option value="communication">Communication</option>
                                                <option value="damage_photo">Damage Photo</option>
                                                <option value="tracking_info">Tracking Info</option>
                                                <option value="other_document">Other Document</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">File (Images Only)</label>
                                            <input type="file" name="evidence_file" required
                                                   accept="image/jpeg,image/png,image/gif"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                                            <p class="text-xs text-gray-500 mt-1">Max 2MB. Supported: JPG, PNG, GIF</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                                            <textarea name="description" rows="2"
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"></textarea>
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="submit"
                                                    class="px-3 py-1.5 bg-amber-600 text-white rounded-md hover:bg-amber-700 text-sm font-medium">
                                                Upload
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </details>
                    @endif

                    {{-- Evidence List --}}
                    <div class="space-y-3">
                        @forelse($dispute->evidence as $evidence)
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                {{-- Evidence Image --}}
                                @if($evidence->isImage())
                                    <div class="bg-gray-50 p-4">
                                        <img src="{{ $evidence->data_uri }}"
                                             alt="{{ $evidence->file_name }}"
                                             class="max-w-full h-auto rounded-lg">
                                    </div>
                                @endif

                                {{-- Evidence Info --}}
                                <div class="p-4 bg-white">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">{{ $evidence->file_name }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="px-2 py-0.5 bg-{{ $evidence->getTypeColor() }}-100 text-{{ $evidence->getTypeColor() }}-700 rounded">
                                                    {{ ucfirst(str_replace('_', ' ', $evidence->evidence_type)) }}
                                                </span>
                                                <span class="ml-2">{{ $evidence->formatted_file_size }}</span>
                                                <span class="ml-2">{{ $evidence->created_at->format('M d, Y') }}</span>
                                            </div>
                                            @if($evidence->description)
                                                <p class="text-sm text-gray-600 mt-2">{{ $evidence->description }}</p>
                                            @endif
                                            @if($evidence->is_verified)
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        Verified by Admin
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 ml-4">
                                            By {{ $evidence->uploadedBy->username_pub }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <p class="text-sm">No evidence uploaded yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Quick Actions --}}
                @if($dispute->isOpen() && auth()->id() === $dispute->initiated_by)
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('orders.show', $dispute->order) }}"
                               class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors text-sm">
                                View Original Order
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
