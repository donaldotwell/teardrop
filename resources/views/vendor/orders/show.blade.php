@extends('layouts.vendor')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('vendor.orders.index') }}"
           class="text-amber-600 hover:text-purple-800 text-sm font-medium">
            ← Back to Orders
        </a>
    </div>

    <!-- Order Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->uuid }}</h1>
                <p class="text-sm text-gray-600 mt-1">Created {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @if($order->status === 'pending')
                    <span class="bg-yellow-100 text-yellow-800 text-sm px-3 py-1 rounded-full">Pending</span>
                @elseif($order->status === 'confirmed')
                    <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">Confirmed</span>
                @elseif($order->status === 'shipped')
                    <span class="bg-purple-100 text-purple-800 text-sm px-3 py-1 rounded-full">Shipped</span>
                @elseif($order->status === 'completed')
                    <span class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">Completed</span>
                @elseif($order->status === 'cancelled')
                    <span class="bg-red-100 text-red-800 text-sm px-3 py-1 rounded-full">Cancelled</span>
                @elseif($order->status === 'disputed')
                    <span class="bg-orange-100 text-orange-800 text-sm px-3 py-1 rounded-full">Disputed</span>
                @else
                    <span class="bg-gray-100 text-gray-800 text-sm px-3 py-1 rounded-full">{{ ucfirst($order->status) }}</span>
                @endif

                {{-- Dispute Status Badge --}}
                @if($order->dispute)
                    <span class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-full
                    @switch($order->dispute->status)
                        @case('open')
                            bg-yellow-100 text-yellow-800
                            @break
                        @case('under_review')
                            bg-blue-100 text-blue-800
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
                        Dispute: {{ ucfirst(str_replace('_', ' ', $order->dispute->status)) }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-24 h-24 shrink-0">
                            <x-image-gallery
                                :images="$order->listing->media"
                                :title="$order->listing->title"
                                :modal-id="'listing-gallery-vendor-order-' . $order->id"
                            />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">
                                <a href="{{ route('listings.show', $order->listing) }}" 
                                   target="_blank"
                                   class="text-purple-700 hover:text-purple-900 hover:underline">
                                    {{ $order->listing->title }}
                                </a>
                            </h3>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $order->listing->short_description }}</p>
                            <div class="mt-3 flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-600">Quantity: <span class="font-medium text-gray-900">{{ $order->quantity }}</span></div>
                                    <div class="text-sm text-gray-600">Unit Price: <span class="font-medium text-gray-900">${{ number_format($order->listing->price, 2) }}</span></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-purple-700">${{ number_format($order->usd_price, 2) }}</div>
                                    <div class="text-xs text-gray-500 uppercase">{{ $order->currency }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Encrypted Delivery Address (Vendor Only) --}}
            @if($order->encrypted_delivery_address)
            <div class="bg-white rounded-xl shadow-sm border border-purple-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Encrypted Delivery Address</h2>
                    <p class="text-xs text-gray-500 mt-1">This address is encrypted with your PGP public key. Decrypt it using your private key.</p>
                </div>
                <div class="p-6">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
                        <pre class="text-xs font-mono text-gray-800 whitespace-pre-wrap break-all">{{ $order->encrypted_delivery_address }}</pre>
                    </div>
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-xs text-blue-800">
                            <strong>How to decrypt:</strong> Use your PGP private key to decrypt this message.
                            You can use GPG command line or any PGP-compatible tool to decrypt the address.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Communication Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Communication with
                        <a href="{{ route('profile.show_public_view', $otherParty->username_pub) }}" class="text-amber-600 hover:text-purple-700 hover:underline">
                            {{ $otherParty->username_pub }}
                        </a>
                    </h2>
                </div>

                <div class="p-6">
                    {{-- Recent Messages --}}
                    @if($order->messages->isNotEmpty())
                        <div class="space-y-3 mb-6 max-h-96 overflow-y-auto bg-gray-50 rounded-lg p-4">
                            @foreach($order->messages as $message)
                                <div class="p-3 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-purple-100 ml-8' : 'bg-white mr-8 border border-gray-200' }}">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ $message->sender_id === auth()->id() ? 'You' : $otherParty->username_pub }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $message->created_at ? $message->created_at->diffForHumans() : '' }}</span>
                                    </div>
                                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ $message->message }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mb-6 p-4 rounded-lg bg-gray-50">
                            <p class="text-sm text-gray-500">No messages yet. Start a conversation below.</p>
                        </div>
                    @endif

                    {{-- Send Message Form --}}
                    <form action="{{ route('vendor.orders.message', $order) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                Send a message to buyer
                            </label>
                            <textarea
                                name="message"
                                id="message"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                                placeholder="Type your message here..."
                                required
                            ></textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button
                            type="submit"
                            class="w-full px-6 py-3 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                        >
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            {{-- Dispute Information Card (if dispute exists) --}}
            @if($order->dispute)
                <div class="bg-white rounded-xl shadow-sm border border-yellow-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Dispute Information</h2>
                    </div>

                    <div class="p-6">
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center">
                                <span class="w-20 font-medium text-gray-600">Subject:</span>
                                <span class="text-gray-900">{{ $order->dispute->subject }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-20 font-medium text-gray-600">Type:</span>
                                <span class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->dispute->type)) }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-20 font-medium text-gray-600">Status:</span>
                                <span class="font-medium
                                @switch($order->dispute->status)
                                    @case('open') text-yellow-700 @break
                                    @case('under_review') text-blue-700 @break
                                    @case('resolved') text-emerald-700 @break
                                    @case('closed') text-gray-700 @break
                                    @default text-gray-700
                                @endswitch">
                                    {{ ucfirst(str_replace('_', ' ', $order->dispute->status)) }}
                                </span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-20 font-medium text-gray-600">Created:</span>
                                <span class="text-gray-900">{{ $order->dispute->created_at ? $order->dispute->created_at->format('M d, Y') : 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <a href="{{ route('disputes.show', $order->dispute) }}"
                               class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                View Dispute Details
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Order Notes -->
            @if($order->notes)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Order Notes</h2>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-700">{{ $order->notes }}</p>
                </div>
            </div>
            @endif

            <!-- Review -->
            @if($order->review)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Customer Review</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-700">{{ number_format(($order->review->rating_stealth + $order->review->rating_quality + $order->review->rating_delivery) / 3, 1) }}</div>
                            <div class="text-xs text-gray-500">Overall</div>
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-600 w-20">Stealth:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $order->review->rating_stealth }}/5</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-600 w-20">Quality:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $order->review->rating_quality }}/5</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-600 w-20">Delivery:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $order->review->rating_delivery }}/5</span>
                            </div>
                        </div>
                    </div>
                    @if($order->review->comment)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-sm text-gray-700">{{ $order->review->comment }}</p>
                        </div>
                    @endif
                    <div class="mt-3 text-xs text-gray-500">
                        Reviewed {{ $order->review->created_at ? $order->review->created_at->format('F d, Y') : 'N/A' }}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Order Actions Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Order Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    {{-- Mark as Shipped (Vendor Only) --}}
                    @if($order->status === 'pending')
                        <form action="{{ route('vendor.orders.ship', $order) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-6 py-3 text-sm font-medium text-white bg-amber-600 rounded-md hover:bg-amber-700 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                Mark as Shipped
                            </button>
                        </form>
                    @endif

                    {{-- Cancel Order (Vendor Only) --}}
                    @if(in_array($order->status, ['pending', 'shipped']))
                        <input type="checkbox" id="cancel-order-toggle-{{ $order->id }}" class="peer hidden" />

                        <label for="cancel-order-toggle-{{ $order->id }}" class="block w-full px-6 py-3 text-sm font-medium text-center text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            Cancel Order & Refund Buyer
                        </label>

                        {{-- Cancel Order Form (Hidden by default, shown when checkbox is checked) --}}
                        <form action="{{ route('vendor.orders.cancel', $order) }}" method="POST" class="hidden peer-checked:block mt-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                            @csrf
                            <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Cancellation <span class="text-red-600">*</span>
                            </label>
                            <textarea name="cancellation_reason" id="cancellation_reason" rows="4" required maxlength="1000" placeholder="Please explain why you're cancelling this order..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                            <p class="text-xs text-gray-600 mt-1">Buyer will be refunded minus network transaction fees.</p>

                            <div class="flex gap-2 mt-3">
                                <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                                    Confirm Cancellation
                                </button>
                                <label for="cancel-order-toggle-{{ $order->id }}" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors text-center cursor-pointer">
                                    Nevermind
                                </label>
                            </div>
                        </form>
                    @endif

                    {{-- Order Shipped Info --}}
                    @if($order->status === 'shipped')
                        <div class="w-full px-6 py-3 text-sm text-center text-blue-700 bg-blue-50 rounded-md border border-blue-200">
                            Order marked as shipped. Funds will be released when buyer confirms receipt.
                        </div>
                    @endif

                    {{-- Order Cancelled Info --}}
                    @if($order->status === 'cancelled')
                        <div class="w-full px-6 py-3 text-sm text-center text-red-700 bg-red-50 rounded-md border border-red-200">
                            <div class="font-semibold mb-1">Order Cancelled</div>
                            @if($order->cancellation_reason)
                                <div class="text-xs mt-2 text-left">
                                    <strong>Reason:</strong> {{ $order->cancellation_reason }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Dispute Status --}}
                    @if($order->hasActiveDispute())
                        <div class="w-full px-6 py-3 text-sm text-center text-yellow-700 bg-yellow-100 rounded-md border border-yellow-200">
                            Dispute is active for this order
                        </div>
                    @endif
                </div>
            </div>

            <!-- Buyer Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Buyer Information</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Username</div>
                        <div class="text-sm font-medium text-gray-900">
                            <a href="{{ route('profile.show_public_view', $order->user->username_pub) }}" class="text-amber-600 hover:text-purple-700 hover:underline">
                                {{ $order->user->username_pub }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Trust Level</div>
                        <div class="text-sm font-medium text-gray-900">{{ $order->user->trust_level }}</div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Payment Details</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Currency</span>
                        <span class="text-sm font-medium text-gray-900 uppercase">{{ $order->currency }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Amount</span>
                        <span class="text-sm font-medium text-gray-900">${{ number_format($order->usd_price, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Crypto Value</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($order->crypto_value, $order->currency === 'btc' ? 8 : 12) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Payment Method</span>
                        <span class="text-sm font-medium text-gray-900 capitalize">{{ $order->listing->payment_method }}</span>
                    </div>
                </div>
            </div>

            <!-- Shipping Information Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Shipping Details</h2>
                </div>
                <div class="p-6 space-y-3 text-sm text-gray-600">
                    <div class="flex items-center">
                        <span class="w-24 font-medium">Method:</span>
                        <span>{{ ucfirst($order->listing->shipping_method) }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-24 font-medium">Cost:</span>
                        <span>${{ number_format($order->listing->price_shipping, 2) }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-24 font-medium">Origin:</span>
                        <span>{{ $order->listing->originCountry->name }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-24 font-medium">Destination:</span>
                        <span>{{ $order->listing->destinationCountry->name }}</span>
                    </div>
                    @if($order->listing->return_policy)
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <h3 class="text-sm font-medium text-gray-900">Return Policy</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ $order->listing->return_policy }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Timestamps -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Timeline</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Created</div>
                        <div class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @if($order->shipped_at)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Shipped</div>
                        <div class="text-sm text-gray-900">{{ is_string($order->shipped_at) ? $order->shipped_at : $order->shipped_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @endif
                    @if($order->completed_at)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Completed</div>
                        <div class="text-sm text-gray-900">{{ is_string($order->completed_at) ? $order->completed_at : $order->completed_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @endif
                    @if($order->cancelled_at)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Cancelled</div>
                        <div class="text-sm text-gray-900">{{ is_string($order->cancelled_at) ? $order->cancelled_at : $order->cancelled_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
