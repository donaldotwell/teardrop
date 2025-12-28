@extends('layouts.vendor')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('vendor.orders.index') }}"
           class="text-purple-600 hover:text-purple-800 text-sm font-medium">
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
            <div>
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
                        @if($order->listing->media->first())
                            <img src="{{ $order->listing->media->first()->data_uri }}"
                                 alt="{{ $order->listing->title }}"
                                 class="w-24 h-24 object-contain bg-gray-50 border border-gray-200 rounded-lg p-2">
                        @else
                            <div class="w-24 h-24 flex items-center justify-center bg-gray-50 border border-gray-200 rounded-lg">
                                <span class="text-gray-400 text-xs">No Image</span>
                            </div>
                        @endif
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $order->listing->title }}</h3>
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
                        Reviewed {{ $order->review->created_at->format('F d, Y') }}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Buyer Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Buyer Information</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Username</div>
                        <div class="text-sm font-medium text-gray-900">{{ $order->user->username_pub }}</div>
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
                        <span class="text-sm font-medium text-gray-900">{{ number_format($order->crypto_value, 8) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Payment Method</span>
                        <span class="text-sm font-medium text-gray-900 capitalize">{{ $order->listing->payment_method }}</span>
                    </div>
                    @if($order->txid)
                    <div class="pt-3 border-t border-gray-100">
                        <div class="text-xs text-gray-500 mb-1">Transaction ID</div>
                        <div class="text-xs font-mono text-gray-900 break-all">{{ $order->txid }}</div>
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
                    @if($order->completed_at)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Completed</div>
                        <div class="text-sm text-gray-900">{{ $order->completed_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @endif
                    @if($order->cancelled_at)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Cancelled</div>
                        <div class="text-sm text-gray-900">{{ $order->cancelled_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($order->status === 'confirmed')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('vendor.orders.ship', $order) }}" method="post">
                        @csrf
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors">
                            Mark as Shipped
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
