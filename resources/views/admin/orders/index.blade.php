@extends('layouts.admin')
@section('page-title', 'Orders Management')

@section('breadcrumbs')
    <span class="text-gray-600">Orders</span>
@endsection

@section('page-heading')
    Orders Management
@endsection

@section('page-description')
    Monitor and manage all marketplace orders and transactions
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Total Orders</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $stats['total_orders'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Pending</div>
                <div class="text-2xl font-semibold text-yellow-600">{{ $stats['pending_orders'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Completed</div>
                <div class="text-2xl font-semibold text-green-600">{{ $stats['completed_orders'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Cancelled</div>
                <div class="text-2xl font-semibold text-red-600">{{ $stats['cancelled_orders'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Total Value</div>
                <div class="text-2xl font-semibold text-gray-900">${{ number_format($stats['total_value'] ?? 0, 2) }}</div>
            </div>
        </div>

        {{-- Filters and Search --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    {{-- Search --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Order ID, username..."
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    {{-- Currency Filter --}}
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <select name="currency"
                                id="currency"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Currencies</option>
                            <option value="btc" {{ request('currency') == 'btc' ? 'selected' : '' }}>Bitcoin (BTC)</option>
                            <option value="xmr" {{ request('currency') == 'xmr' ? 'selected' : '' }}>Monero (XMR)</option>
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date"
                               name="date_from"
                               id="date_from"
                               value="{{ request('date_from') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date"
                               name="date_to"
                               id="date_to"
                               value="{{ request('date_to') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Filter Orders
                    </button>
                    <a href="{{ route('admin.orders.index') }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                        Clear Filters
                    </a>
                    <a href="{{ route('admin.orders.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Export CSV
                    </a>
                </div>
            </form>
        </div>

        {{-- Orders Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Orders ({{ $orders->total() }} total)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Listing</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    #{{ substr($order->uuid, 0, 8) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Qty: {{ $order->quantity }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-yellow-700 font-medium text-sm">{{ substr($order->user->username_pub, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $order->user->username_pub }}</div>
                                        <div class="text-sm text-gray-500">TL{{ $order->user->trust_level }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                                    {{ $order->listing->title }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    by {{ $order->listing->user->username_pub }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    ${{ number_format($order->usd_price, 2) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($order->crypto_value, 8) }} {{ strtoupper($order->currency) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                @elseif($order->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                @elseif($order->status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Cancelled
                                        </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $order->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                        View
                                    </a>
                                    @if($order->status === 'pending')
                                        <form action="{{ route('admin.orders.complete', $order) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">
                                                Complete
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No orders found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Order Activity</h3>

            <div class="space-y-3">
                @forelse($recent_activity ?? [] as $activity)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 rounded-full
                                {{ $activity['type'] === 'completed' ? 'bg-green-500' : '' }}
                                {{ $activity['type'] === 'cancelled' ? 'bg-red-500' : '' }}
                                {{ $activity['type'] === 'created' ? 'bg-blue-500' : '' }}">
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</div>
                                <div class="text-sm text-gray-500">{{ $activity['time'] }}</div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            ${{ number_format($activity['amount'], 2) }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-4">
                        No recent activity to display.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
