@extends('layouts.vendor')
@section('page-title', 'Dashboard')
@section('breadcrumbs')
    <span class="text-gray-600">Dashboard</span>
@endsection
@section('page-heading', 'Vendor Dashboard')

@section('content')
    <div class="space-y-6">
        {{-- Statistics Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Total Listings --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Listings</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_listings'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 text-xl font-bold">L</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">
                    {{ $stats['active_listings'] }} active
                </p>
            </div>

            {{-- Total Orders --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Orders</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_orders'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 text-xl font-bold">O</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">
                    {{ $stats['pending_orders'] }} pending
                </p>
            </div>

            {{-- Total Sales --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Sales</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2">${{ number_format($stats['total_sales'], 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-600 text-xl font-bold">$</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">
                    Avg. Rating: {{ number_format($stats['avg_rating'], 1) }}/5
                </p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('vendor.listings.create') }}"
                   class="px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-center font-medium">
                    Create New Listing
                </a>
                <a href="{{ route('vendor.listings.index') }}"
                   class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-center font-medium">
                    Manage Listings
                </a>
                <a href="{{ route('vendor.orders.index') }}"
                   class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-center font-medium">
                    View Orders
                </a>
                <a href="{{ route('vendor.analytics') }}"
                   class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-center font-medium">
                    View Analytics
                </a>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
                <a href="{{ route('vendor.orders.index') }}" class="text-sm text-yellow-600 hover:text-yellow-700 font-medium">
                    View All
                </a>
            </div>

            @if($recentOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Order ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Listing</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Buyer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-mono">{{ $order->id }}</td>
                                    <td class="px-4 py-3 text-sm">{{ Str::limit($order->listing->title, 30) }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $order->user->username_pub }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">${{ number_format($order->usd_price, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('vendor.orders.show', $order) }}"
                                           class="text-sm text-yellow-600 hover:text-yellow-700 font-medium">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No orders yet.</p>
                </div>
            @endif
        </div>

        {{-- Top Listings --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Top Listings by Views</h2>
                <a href="{{ route('vendor.listings.index') }}" class="text-sm text-yellow-600 hover:text-yellow-700 font-medium">
                    View All
                </a>
            </div>

            @if($topListings->count() > 0)
                <div class="space-y-4">
                    @foreach($topListings as $listing)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <a href="{{ route('listings.show', $listing) }}"
                                   class="text-sm font-medium text-gray-900 hover:text-yellow-600">
                                    {{ $listing->title }}
                                </a>
                                <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500">
                                    <span>{{ $listing->views }} views</span>
                                    <span>${{ number_format($listing->price, 2) }}</span>
                                    @if($listing->is_featured)
                                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full">Featured</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('vendor.listings.edit', $listing) }}"
                               class="ml-4 px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm">
                                Edit
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No listings yet.</p>
                    <a href="{{ route('vendor.listings.create') }}"
                       class="mt-4 inline-block px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                        Create Your First Listing
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
