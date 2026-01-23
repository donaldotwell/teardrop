@extends('layouts.admin')
@section('page-title', 'Order Details')

@section('breadcrumbs')
    <a href="{{ route('admin.orders.index') }}" class="text-yellow-700 hover:text-yellow-800">Orders</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">#{{ substr($order->uuid, 0, 8) }}</span>
@endsection

@section('page-heading')
    Order Details: #{{ substr($order->uuid, 0, 8) }}
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Order Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Order #{{ substr($order->uuid, 0, 8) }}</h2>
                    <p class="text-gray-600">Full ID: {{ $order->uuid }}</p>
                    <div class="flex items-center space-x-2 mt-2">
                        @if($order->status === 'pending')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        @elseif($order->status === 'completed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Completed
                            </span>
                        @elseif($order->status === 'cancelled')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Cancelled
                            </span>
                        @endif

                        <span class="text-sm text-gray-500">
                            Created {{ $order->created_at->format('M d, Y g:i A') }}
                        </span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    @if($order->status === 'pending')
                        <form action="{{ route('admin.orders.complete', $order) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                Mark Completed
                            </button>
                        </form>
                        <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Cancel Order
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Early Finalization Info --}}
            @if($order->is_early_finalized)
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-bold bg-green-100 text-green-800">
                            EARLY FINALIZED
                        </span>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-green-900">Direct Payment to Vendor</h3>
                            <div class="mt-2 space-y-1 text-sm text-green-700">
                                <div><strong>Finalized:</strong> {{ is_string($order->early_finalized_at) ? $order->early_finalized_at : $order->early_finalized_at->format('M d, Y \a\t h:i A') }}</div>
                                @if($order->finalizationWindow)
                                    <div><strong>Window:</strong> {{ $order->finalizationWindow->name }} ({{ $order->finalizationWindow->getHumanReadableDuration() }})</div>
                                @endif
                                @if($order->dispute_window_expires_at)
                                    <div>
                                        <strong>Dispute Window:</strong>
                                        @if($order->isDisputeWindowExpired())
                                            <span class="text-gray-600">Expired {{ $order->dispute_window_expires_at->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-amber-700">Expires {{ $order->dispute_window_expires_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                @endif
                                @if($order->direct_payment_txid)
                                    <div><strong>Vendor TX:</strong> <code class="text-xs bg-white px-2 py-0.5 rounded">{{ substr($order->direct_payment_txid, 0, 8) }}...</code></div>
                                @endif
                                @if($order->admin_fee_txid)
                                    <div><strong>Admin Fee TX:</strong> <code class="text-xs bg-white px-2 py-0.5 rounded">{{ substr($order->admin_fee_txid, 0, 8) }}...</code></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Order Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">${{ number_format($order->usd_price, 2) }}</div>
                    <div class="text-sm text-gray-600">Total Price (USD)</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-purple-600">
                        {{ number_format($order->crypto_value, 8) }}
                    </div>
                    <div class="text-sm text-gray-600">{{ strtoupper($order->currency) }} Amount</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-blue-600">{{ $order->quantity }}</div>
                    <div class="text-sm text-gray-600">Quantity</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-green-600">
                        ${{ number_format($order->usd_price / $order->quantity, 2) }}
                    </div>
                    <div class="text-sm text-gray-600">Price per Item</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Customer Information --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>

                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-700 font-bold text-lg">{{ substr($order->user->username_pub, 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $order->user->username_pub }}</div>
                        <div class="text-sm text-gray-600">Customer ID: {{ $order->user->id }}</div>
                    </div>
                </div>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Trust Level:</dt>
                        <dd class="font-medium text-gray-900">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Level {{ $order->user->trust_level }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Account Status:</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ $order->user->status }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Total Orders:</dt>
                        <dd class="font-medium text-gray-900">{{ $order->user->orders->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Member Since:</dt>
                        <dd class="font-medium text-gray-900">{{ $order->user->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Last Seen:</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $order->user->last_seen_at ? $order->user->last_seen_at->diffForHumans() : 'Never' }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.users.show', $order->user) }}"
                       class="text-sm text-yellow-600 hover:text-yellow-800">
                        View Customer Profile →
                    </a>
                </div>
            </div>

            {{-- Listing Information --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Listing Information</h3>

                <div class="mb-4">
                    <h4 class="font-medium text-gray-900 mb-2">{{ $order->listing->title }}</h4>
                    <p class="text-sm text-gray-600">{{ $order->listing->short_description }}</p>
                </div>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Listing ID:</dt>
                        <dd class="font-medium text-gray-900">{{ substr($order->listing->uuid, 0, 8) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Vendor:</dt>
                        <dd class="font-medium text-gray-900">{{ $order->listing->user->username_pub }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Listed Price:</dt>
                        <dd class="font-medium text-gray-900">${{ number_format($order->listing->price, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Shipping Cost:</dt>
                        <dd class="font-medium text-gray-900">${{ number_format($order->listing->price_shipping, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Payment Method:</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ $order->listing->payment_method }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Shipping Method:</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ $order->listing->shipping_method }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Origin:</dt>
                        <dd class="font-medium text-gray-900">{{ $order->listing->originCountry->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Destination:</dt>
                        <dd class="font-medium text-gray-900">{{ $order->listing->destinationCountry->name }}</dd>
                    </div>
                </dl>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.listings.show', $order->listing) }}"
                       class="text-sm text-yellow-600 hover:text-yellow-800">
                        View Full Listing →
                    </a>
                </div>
            </div>
        </div>

        {{-- Order Timeline --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Timeline</h3>

            <div class="space-y-4">
                {{-- Order Created --}}
                <div class="flex items-center space-x-4">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">Order Created</div>
                        <div class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y g:i A') }}</div>
                    </div>
                </div>

                {{-- Order Completed --}}
                @if($order->completed_at)
                    <div class="flex items-center space-x-4">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">Order Completed</div>
                            <div class="text-sm text-gray-500">{{ is_string($order->completed_at) ? $order->completed_at : $order->completed_at->format('M d, Y g:i A') }}</div>
                        </div>
                    </div>
                @endif

                {{-- Order Cancelled --}}
                @if($order->cancelled_at)
                    <div class="flex items-center space-x-4">
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">Order Cancelled</div>
                            <div class="text-sm text-gray-500">{{ is_string($order->cancelled_at) ? $order->cancelled_at : $order->cancelled_at->format('M d, Y g:i A') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Order Notes --}}
        @if($order->notes)
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Notes</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-700">{{ $order->notes }}</p>
                </div>
            </div>
        @endif

        {{-- Messages (if any) --}}
        @if($order->messages && $order->messages->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Messages</h3>

                <div class="space-y-4">
                    @foreach($order->messages as $message)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium text-gray-900">{{ $message->sender->username_pub }}</span>
                                    <span class="text-sm text-gray-500">to {{ $message->receiver->username_pub }}</span>
                                </div>
                                <span class="text-sm text-gray-500">{{ $message->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                            <p class="text-sm text-gray-700">{{ $message->message }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Related Orders --}}
        @php
            $relatedOrders = $order->user->orders()
                ->where('id', '!=', $order->id)
                ->with('listing')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        @endphp

        @if($relatedOrders->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Other Orders by This Customer</h3>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Listing</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($relatedOrders as $relatedOrder)
                            <tr>
                                <td class="px-4 py-2 text-sm">
                                    <a href="{{ route('admin.orders.show', $relatedOrder) }}"
                                       class="text-yellow-600 hover:text-yellow-800">
                                        #{{ substr($relatedOrder->uuid, 0, 8) }}
                                    </a>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    {{ $relatedOrder->listing->title ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    ${{ number_format($relatedOrder->usd_price, 2) }}
                                </td>
                                <td class="px-4 py-2">
                                    @if($relatedOrder->status === 'pending')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                    @elseif($relatedOrder->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">
                                    {{ $relatedOrder->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
