@extends('layouts.app')

@section('page-title', 'My Orders')

@section('breadcrumbs')
    <span class="text-gray-600 font-medium">Orders</span>
@endsection

@section('content')
    <div class="space-y-6 max-w-6xl mx-auto">
        
        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h1 class="text-2xl font-bold text-gray-900">
                <span class="border-l-4 border-amber-500 pl-3">Order History</span>
            </h1>
        </div>

        {{-- Status Filter Tabs --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('orders.index') }}"
                   class="px-4 py-2 rounded {{ !request('status') ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    All Orders ({{ $statusCounts['all'] }})
                </a>
                <a href="{{ route('orders.index', ['status' => 'pending']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'pending' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Pending ({{ $statusCounts['pending'] }})
                </a>
                <a href="{{ route('orders.index', ['status' => 'shipped']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'shipped' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Shipped ({{ $statusCounts['shipped'] }})
                </a>
                <a href="{{ route('orders.index', ['status' => 'completed']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'completed' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Completed ({{ $statusCounts['completed'] }})
                </a>
                <a href="{{ route('orders.index', ['status' => 'cancelled']) }}"
                   class="px-4 py-2 rounded {{ request('status') === 'cancelled' ? 'bg-amber-100 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Cancelled ({{ $statusCounts['cancelled'] }})
                </a>
            </div>
        </div>

        {{-- Orders List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="space-y-6">
            @forelse ($orders as $order)
                <div class="border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <span class="font-medium text-gray-900">
                                        Order #{{ $order->id }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @switch($order->status)
                                            @case('pending')
                                                bg-amber-100 text-amber-700
                                                @break
                                            @case('completed')
                                                bg-green-100 text-green-700
                                                @break
                                            @case('cancelled')
                                                bg-red-100 text-red-700
                                                @break
                                            @default
                                                bg-gray-100 text-gray-700
                                        @endswitch">
                                        {{ ucfirst($order->status) }}
                                    </span>

                                    @if($order->dispute)
                                        <span class="px-2 py-1 text-xs rounded-full
                                            @switch($order->dispute->status)
                                                @case('open')
                                                    bg-yellow-100 text-yellow-700
                                                    @break
                                                @case('under_review')
                                                    bg-blue-100 text-blue-700
                                                    @break
                                                @case('resolved')
                                                    bg-emerald-100 text-emerald-700
                                                    @break
                                                @case('closed')
                                                    bg-gray-100 text-gray-700
                                                    @break
                                                @default
                                                    bg-gray-100 text-gray-700
                                            @endswitch">
                                            Dispute: {{ ucfirst(str_replace('_', ' ', $order->dispute->status)) }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</span>
                            </div>

                            <div class="mb-3">
                                <h3 class="font-semibold text-gray-900">
                                    {{ $order->listing->title ?? 'Listing Unavailable' }}
                                </h3>
                                @if($order->listing)
                                    <p class="text-sm text-gray-600">
                                        Vendor: {{ $order->listing->user->username_pub }}
                                    </p>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Amount:</span>
                                    <span class="font-medium">${{ number_format($order->usd_price, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Quantity:</span>
                                    <span class="font-medium">{{ $order->quantity }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Currency:</span>
                                    <span class="font-medium">{{ strtoupper($order->currency) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Crypto Amount:</span>
                                    <span class="font-medium">{{ $order->crypto_value }}</span>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('orders.show', $order) }}"
                                       class="text-amber-600 hover:text-amber-700 font-medium">
                                        View Details
                                    </a>

                                    @if($order->dispute)
                                        <a href="{{ route('disputes.show', $order->dispute) }}"
                                           class="text-blue-600 hover:text-blue-700 hover:text-blue-800 font-medium">
                                            View Dispute
                                        </a>
                                    @endif
                                </div>

                                <div class="flex items-center space-x-2">
                                    @if($order->canCreateDispute())
                                        <a href="{{ route('disputes.create', $order) }}"
                                           class="px-3 py-1.5 text-xs bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors">
                                            Create Dispute
                                        </a>
                                    @elseif($order->hasActiveDispute())
                                        <span class="px-3 py-1.5 text-xs bg-yellow-100 text-yellow-700 rounded-md">
                                            Dispute Active
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 bg-gray-400 rounded-full mb-4"></div>
                    <p class="mt-4 text-gray-500">No orders found.</p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 transition-colors">
                        Browse Listings
                    </a>
                </div>
            @endforelse
        </div>

        @if($orders->hasPages())
            <div class="mt-8">
                {{ $orders->appends(request()->except('page'))->links() }}
            </div>
        @endif
        </div>
    </div>
@endsection
