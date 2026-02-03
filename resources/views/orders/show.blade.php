@extends('layouts.app')
@section('page-title', 'Order Details '. $order->uuid)


@section('content')
    <div class="py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <!-- Back Navigation -->
            <div class="mb-8">
                <a href="{{ route('orders.index') }}" class="inline-flex items-center text-sm font-medium text-amber-600 hover:text-amber-700 transition-colors">
                    ← Back to Orders
                </a>
            </div>

            <!-- Order Header -->
            <div class="flex flex-col justify-between gap-4 mb-8 md:flex-row md:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->uuid }}</h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-full bg-amber-100 text-amber-800">
                        {{ ucfirst($order->status) }}
                    </span>

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

            <!-- Early Finalization Banner -->
            @if($order->is_early_finalized)
                <div class="mb-8 overflow-hidden bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl shadow-lg border border-green-200">
                    <div class="p-6">
                        <div class="flex items-start space-x-3">
                            <span class="flex-shrink-0 inline-flex items-center px-3 py-1 rounded-md text-sm font-bold bg-green-100 text-green-800">
                                INSTANT PAYMENT
                            </span>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-green-900">This order used early finalization</h3>
                                <p class="mt-1 text-sm text-green-700">
                                    Payment was sent directly to the vendor at purchase time on {{ $order->early_finalized_at->format('M d, Y \a\t h:i A') }}
                                </p>

                                @if($order->dispute_window_expires_at)
                                    <div class="mt-4 p-3 bg-white rounded-lg border border-green-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">Dispute Window:</span>
                                            @if($order->isDisputeWindowExpired())
                                                <span class="text-sm font-semibold text-gray-500">Expired on {{ $order->dispute_window_expires_at->format('M d, Y') }}</span>
                                            @else
                                                <span class="text-sm font-semibold text-amber-700">
                                                    Expires {{ $order->dispute_window_expires_at->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!$order->isDisputeWindowExpired())
                                            <p class="mt-2 text-xs text-gray-600">
                                                You can file a dispute until {{ $order->dispute_window_expires_at->format('M d, Y \a\t h:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Content Grid -->
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Listing Details Card -->
                    <div class="overflow-hidden bg-white rounded-xl shadow-lg">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900">Listing Details</h2>
                            <div class="flex mt-6">
                                <div class="flex-shrink-0 w-32 h-32 overflow-hidden rounded-lg bg-gray-100">
                                    <x-image-gallery
                                        :images="$order->listing->media"
                                        :title="$order->listing->title"
                                        :modal-id="'gallery-order-' . $order->id"
                                    />
                                </div>
                                <div class="flex flex-col flex-1 ml-6">
                                    <h3 class="text-xl font-semibold text-gray-900">
                                        <a href="{{ route('listings.show', $order->listing) }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="text-amber-700 hover:text-amber-800 hover:underline transition-colors">
                                            {{ $order->listing->title }}
                                        </a>
                                    </h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ $order->listing->short_description }}</p>
                                    <div class="mt-4 space-y-2">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <span class="w-24 font-medium">Quantity:</span>
                                            <span>{{ $order->quantity }}</span>
                                        </div>
                                        <div class="pt-3 mt-2 border-t border-gray-200">
                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between text-sm text-gray-600">
                                                    <span class="font-medium">Subtotal:</span>
                                                    <span>${{ number_format($order->listing->price * $order->quantity, 2) }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-sm text-gray-600">
                                                    <span class="font-medium">Shipping:</span>
                                                    <span>${{ number_format($order->listing->price_shipping, 2) }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-base font-semibold text-gray-900 pt-2 border-t border-gray-200">
                                                    <span>Total USD:</span>
                                                    <span>${{ number_format($order->usd_price, 2) }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-sm text-amber-700 bg-amber-50 p-2 rounded">
                                                    <span class="font-medium">Paid in {{ strtoupper($order->currency) }}:</span>
                                                    <span class="font-mono">{{ number_format($order->crypto_value, 8) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Communication Card -->
                    <div class="overflow-hidden bg-white rounded-xl shadow-lg">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                                Communication with
                                <a href="{{ route('vendor.show', $otherParty) }}" class="text-amber-600 hover:text-amber-700 hover:underline">
                                    {{ $otherParty->username_pub }}
                                </a>
                            </h2>

                            {{-- Recent Messages --}}
                            @if($order->messages->isNotEmpty())
                                <div class="space-y-3 mb-6 max-h-96 overflow-y-auto bg-gray-50 rounded-lg p-4">
                                    @foreach($order->messages as $message)
                                        <div class="p-3 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-amber-100 ml-8' : 'bg-white mr-8 border border-gray-200' }}">
                                            <div class="flex justify-between items-start mb-1">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ $message->sender_id === auth()->id() ? 'You' : $otherParty->username_pub }}
                                                </span>
                                                <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
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
                            <form action="{{ route('orders.message', $order) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                        Send a message
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
                        <div class="overflow-hidden bg-white rounded-xl shadow-lg border border-yellow-200">
                            <div class="p-6">
                                <div class="mb-4">
                                    <h2 class="text-xl font-semibold text-gray-900">Dispute Information</h2>
                                </div>

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
                                        <span class="text-gray-900">{{ $order->dispute->created_at->format('M d, Y') }}</span>
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
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Review Section -->
                    @if($order->status === 'completed' && !$isVendor)
                        @if($order->review)
                            <!-- Existing Review Display -->
                            <div class="overflow-hidden bg-white rounded-xl shadow-lg border-2 border-green-200">
                                <div class="p-6">
                                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Review</h2>

                                    <div class="space-y-4">
                                        <!-- Stealth Rating -->
                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-sm font-medium text-gray-700">Stealth</span>
                                                <span class="text-sm font-semibold text-gray-900">{{ $order->review->rating_stealth }}/5</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ ($order->review->rating_stealth / 5) * 100 }}%"></div>
                                            </div>
                                        </div>

                                        <!-- Quality Rating -->
                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-sm font-medium text-gray-700">Quality</span>
                                                <span class="text-sm font-semibold text-gray-900">{{ $order->review->rating_quality }}/5</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ ($order->review->rating_quality / 5) * 100 }}%"></div>
                                            </div>
                                        </div>

                                        <!-- Delivery Rating -->
                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-sm font-medium text-gray-700">Delivery</span>
                                                <span class="text-sm font-semibold text-gray-900">{{ $order->review->rating_delivery }}/5</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ ($order->review->rating_delivery / 5) * 100 }}%"></div>
                                            </div>
                                        </div>

                                        <!-- Comment -->
                                        <div class="pt-3 mt-3 border-t border-gray-200">
                                            <p class="text-sm font-medium text-gray-700 mb-2">Your Comment</p>
                                            <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $order->review->comment }}</p>
                                        </div>

                                        <div class="text-xs text-gray-500 text-right">
                                            Reviewed on {{ $order->review->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Review Form -->
                            <div class="overflow-hidden bg-white rounded-xl shadow-lg border-2 border-amber-200">
                                <div class="p-6">
                                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Leave a Review</h2>

                                    <form action="{{ route('reviews.store', $order) }}" method="POST" class="space-y-4">
                                        @csrf

                                        <!-- Stealth Rating -->
                                        <div>
                                            <label for="rating_stealth" class="block text-sm font-medium text-gray-700 mb-2">
                                                Stealth Rating
                                            </label>
                                            <input
                                                type="number"
                                                name="rating_stealth"
                                                id="rating_stealth"
                                                min="1"
                                                max="5"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                                                placeholder="1-5"
                                            >
                                            @error('rating_stealth')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Quality Rating -->
                                        <div>
                                            <label for="rating_quality" class="block text-sm font-medium text-gray-700 mb-2">
                                                Quality Rating
                                            </label>
                                            <input
                                                type="number"
                                                name="rating_quality"
                                                id="rating_quality"
                                                min="1"
                                                max="5"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                                                placeholder="1-5"
                                            >
                                            @error('rating_quality')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Delivery Rating -->
                                        <div>
                                            <label for="rating_delivery" class="block text-sm font-medium text-gray-700 mb-2">
                                                Delivery Rating
                                            </label>
                                            <input
                                                type="number"
                                                name="rating_delivery"
                                                id="rating_delivery"
                                                min="1"
                                                max="5"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                                                placeholder="1-5"
                                            >
                                            @error('rating_delivery')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Comment -->
                                        <div>
                                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                                                Comment (max 140 characters)
                                            </label>
                                            <textarea
                                                name="comment"
                                                id="comment"
                                                rows="3"
                                                maxlength="140"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                                                placeholder="Share your experience..."
                                            ></textarea>
                                            @error('comment')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <button
                                            type="submit"
                                            class="w-full px-6 py-3 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                        >
                                            Submit Review
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Order Actions Card -->
                    <div class="overflow-hidden bg-white rounded-xl shadow-lg">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900">Order Actions</h2>
                            <div class="mt-6 space-y-3">
                                {{-- Complete Order Button (Buyer Only - confirms receipt and releases escrow) --}}
                                @if(!$isVendor && in_array($order->status, ['pending', 'shipped']) && in_array($order->currency, ['btc', 'xmr']))
                                    <form action="{{ route('orders.complete', $order) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full px-6 py-3 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                            Complete Order & Release Payment
                                        </button>
                                    </form>
                                @endif

                                {{-- Confirm Shipment (Vendor Only) --}}
                                @if($isVendor && $order->status === 'pending')
                                    <form action="{{ route('orders.ship', $order) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full px-6 py-3 text-sm font-medium text-white bg-amber-600 rounded-md hover:bg-amber-700 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                            Mark as Shipped
                                        </button>
                                    </form>
                                @endif

                                {{-- Dispute Actions --}}
                                @if($order->canCreateDispute())
                                    <a href="{{ route('disputes.create', $order) }}"
                                       class="w-full inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        Create Dispute
                                    </a>
                                @elseif($order->hasActiveDispute())
                                    <div class="w-full px-6 py-3 text-sm text-center text-yellow-700 bg-yellow-100 rounded-md border border-yellow-200">
                                        Dispute is active for this order
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Information Card -->
                    <div class="overflow-hidden bg-white rounded-xl shadow-lg">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900">Shipping Details</h2>
                            <div class="mt-6 space-y-3 text-sm text-gray-600">
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
                    </div>

                    <!-- Payment Details Card -->
                    <div class="overflow-hidden bg-white rounded-xl shadow-lg">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900">Payment Details</h2>
                            <div class="mt-6 space-y-3 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Payment Method:</span>
                                    <span class="font-medium">{{ ucfirst($order->listing->payment_method) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Total Price:</span>
                                    <span class="font-medium">${{ number_format($order->usd_price, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Crypto Value:</span>
                                    <span class="font-medium">{{ number_format($order->crypto_value, 8) }} {{ $order->currency }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
