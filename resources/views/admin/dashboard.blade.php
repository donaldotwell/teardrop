@extends('layouts.admin')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
    <span class="text-gray-600">Dashboard</span>
@endsection

@section('page-heading')
    Admin Dashboard
@endsection

@section('page-description')
    Overview of marketplace activity and key metrics
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Key Metrics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Total Users</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_users']) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 font-bold">U</span>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        Manage Users →
                    </a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Total Orders</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_orders']) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 font-bold">O</span>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.orders.index') }}" class="text-sm text-green-600 hover:text-green-800">
                        Manage Orders →
                    </a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Total Disputes</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_disputes']) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-red-600 font-bold">D</span>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.disputes.index') }}" class="text-sm text-red-600 hover:text-red-800">
                        Manage Disputes →
                    </a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Total Tickets</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_tickets']) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="text-indigo-600 font-bold">T</span>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.support.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Manage Tickets →
                    </a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Total Listings</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_listings']) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-600 font-bold">L</span>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.listings.index') }}" class="text-sm text-yellow-600 hover:text-yellow-800">
                        Manage Listings →
                    </a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Total Revenue</div>
                        <div class="text-2xl font-semibold text-gray-900">${{ number_format($stats['total_revenue'], 2) }}</div>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-purple-600 font-bold">$</span>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-sm text-gray-500">From completed orders</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
                   class="p-4 border border-yellow-200 rounded-lg hover:border-yellow-300 hover:bg-yellow-50">
                    <div class="font-medium text-gray-900 mb-1">Pending Orders</div>
                    <div class="text-2xl font-semibold text-yellow-600 mb-2">{{ $stats['pending_orders'] }}</div>
                    <div class="text-sm text-gray-600">Require attention</div>
                </a>

                <a href="{{ route('admin.users.index', ['status' => 'banned']) }}"
                   class="p-4 border border-red-200 rounded-lg hover:border-red-300 hover:bg-red-50">
                    <div class="font-medium text-gray-900 mb-1">Banned Users</div>
                    <div class="text-2xl font-semibold text-red-600 mb-2">{{ $banned_users_count ?? 0 }}</div>
                    <div class="text-sm text-gray-600">May need review</div>
                </a>

                <a href="{{ route('admin.listings.index', ['status' => 'inactive']) }}"
                   class="p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50">
                    <div class="font-medium text-gray-900 mb-1">Inactive Listings</div>
                    <div class="text-2xl font-semibold text-gray-600 mb-2">{{ $inactive_listings_count ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Disabled listings</div>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Orders --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm text-yellow-600 hover:text-yellow-800">
                        View All →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recent_orders ?? [] as $order)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <span class="text-yellow-700 font-medium text-sm">{{ substr($order->user->username_pub, 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        #{{ substr($order->uuid, 0, 8) }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $order->user->username_pub }} • {{ $order->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    ${{ number_format($order->usd_price, 2) }}
                                </div>
                                <div class="text-sm">
                                    @if($order->status === 'pending')
                                        <span class="text-yellow-600">Pending</span>
                                    @elseif($order->status === 'completed')
                                        <span class="text-green-600">Completed</span>
                                    @else
                                        <span class="text-red-600">Cancelled</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            No recent orders to display.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Users --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-yellow-600 hover:text-yellow-800">
                        View All →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recent_users ?? [] as $user)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <span class="text-yellow-700 font-medium text-sm">{{ substr($user->username_pub, 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $user->username_pub }}</div>
                                    <div class="text-sm text-gray-500">
                                        TL{{ $user->trust_level }} • {{ $user->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm">
                                    @if($user->status === 'active')
                                        <span class="text-green-600">Active</span>
                                    @elseif($user->status === 'banned')
                                        <span class="text-red-600">Banned</span>
                                    @else
                                        <span class="text-gray-600">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            No recent users to display.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- System Status --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">System Status</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-sm text-gray-600 mb-1">Database</div>
                    <div class="text-lg font-semibold text-green-600">Online</div>
                    <div class="text-sm text-gray-500">{{ DB::connection()->getDatabaseName() }}</div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-gray-600 mb-1">Queue Status</div>
                    <div class="text-lg font-semibold text-green-600">Running</div>
                    <div class="text-sm text-gray-500">Background Jobs</div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-gray-600 mb-1">Cache Status</div>
                    <div class="text-lg font-semibold text-green-600">Active</div>
                    <div class="text-sm text-gray-500">Redis/File Cache</div>
                </div>
            </div>
        </div>

        {{-- Activity Summary --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Activity</h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-semibold text-blue-600">{{ $daily_stats['new_users'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">New Users</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-green-600">{{ $daily_stats['new_orders'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">New Orders</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-yellow-600">{{ $daily_stats['new_listings'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">New Listings</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-purple-600">${{ number_format($daily_stats['daily_revenue'] ?? 0, 2) }}</div>
                    <div class="text-sm text-gray-600">Revenue Today</div>
                </div>
            </div>
        </div>
    </div>
@endsection
