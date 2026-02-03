@extends('layouts.admin')
@section('page-title', 'Reports & Analytics')

@section('breadcrumbs')
    <span class="text-gray-600">Reports</span>
@endsection

@section('page-heading')
    Reports & Analytics
@endsection

@section('page-description')
    Marketplace performance metrics and business intelligence
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Quick Export Actions --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Exports</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.orders.export') }}"
                   class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Export All Orders
                </a>
                <a href="{{ route('admin.listings.export') }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Export All Listings
                </a>
                <a href="{{ route('admin.users.export') }}"
                   class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                    Export User List
                </a>
                <a href="{{ route('admin.reports.financial') }}"
                   class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    Financial Report
                </a>
            </div>
        </div>

        {{-- Revenue Analytics --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Overview</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-semibold text-green-600">
                                ${{ number_format(\App\Models\Order::where('status', 'completed')->whereMonth('updated_at', now()->month)->sum('usd_price'), 2) }}
                            </div>
                            <div class="text-sm text-gray-600">This Month</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-semibold text-blue-600">
                                ${{ number_format(\App\Models\Order::where('status', 'completed')->whereYear('updated_at', now()->year)->sum('usd_price'), 2) }}
                            </div>
                            <div class="text-sm text-gray-600">This Year</div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="font-medium text-gray-900 mb-3">Weekly Revenue Trend</h4>
                        <div class="space-y-2">
                            @forelse($weeklyRevenue ?? [] as $week)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Week {{ $week->week }}</span>
                                    <span class="font-medium text-gray-900">${{ number_format($week->revenue, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No revenue data available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">User Growth</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-semibold text-amber-600">
                                {{ \App\Models\User::whereMonth('created_at', now()->month)->count() }}
                            </div>
                            <div class="text-sm text-gray-600">New This Month</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-semibold text-yellow-600">
                                {{ \App\Models\User::where('status', 'active')->count() }}
                            </div>
                            <div class="text-sm text-gray-600">Active Users</div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="font-medium text-gray-900 mb-3">Monthly User Registration</h4>
                        <div class="space-y-2">
                            @forelse($monthlyUsers ?? [] as $month)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Month {{ $month->month }}</span>
                                    <span class="font-medium text-gray-900">{{ number_format($month->count) }} users</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No user data available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Vendors --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Vendors by Revenue</h3>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Trust Level</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Revenue</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Avg Order</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @forelse($topVendors ?? [] as $index => $vendor)
                        <tr>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                #{{ $index + 1 }}
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <span class="text-yellow-700 font-medium text-sm">{{ substr($vendor->username_pub, 0, 1) }}</span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">{{ $vendor->username_pub }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Level {{ $vendor->trust_level }}
                                    </span>
                            </td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                ${{ number_format($vendor->revenue ?? 0, 2) }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">
                                {{ $vendor->orders_count ?? 0 }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">
                                ${{ $vendor->orders_count > 0 ? number_format(($vendor->revenue ?? 0) / $vendor->orders_count, 2) : '0.00' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No vendor data available.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Order Statistics --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Statistics</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-3 bg-yellow-50 rounded">
                            <div class="text-lg font-semibold text-yellow-600">
                                {{ \App\Models\Order::where('status', 'pending')->count() }}
                            </div>
                            <div class="text-xs text-gray-600">Pending</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded">
                            <div class="text-lg font-semibold text-green-600">
                                {{ \App\Models\Order::where('status', 'completed')->count() }}
                            </div>
                            <div class="text-xs text-gray-600">Completed</div>
                        </div>
                        <div class="text-center p-3 bg-red-50 rounded">
                            <div class="text-lg font-semibold text-red-600">
                                {{ \App\Models\Order::where('status', 'cancelled')->count() }}
                            </div>
                            <div class="text-xs text-gray-600">Cancelled</div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="font-medium text-gray-900 mb-3">Completion Rate</h4>
                        @php
                            $totalOrders = \App\Models\Order::count();
                            $completedOrders = \App\Models\Order::where('status', 'completed')->count();
                            $completionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;
                        @endphp
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $completionRate }}%"></div>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-900">{{ number_format($completionRate, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Statistics</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-3 bg-blue-50 rounded">
                            <div class="text-lg font-semibold text-blue-600">
                                {{ \App\Models\Listing::where('is_active', true)->count() }}
                            </div>
                            <div class="text-xs text-gray-600">Active Listings</div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded">
                            <div class="text-lg font-semibold text-amber-600">
                                {{ \App\Models\Listing::where('is_featured', true)->count() }}
                            </div>
                            <div class="text-xs text-gray-600">Featured</div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="font-medium text-gray-900 mb-3">Category Distribution</h4>
                        <div class="space-y-2">
                            @foreach(\App\Models\ProductCategory::withCount('listings')->orderBy('listings_count', 'desc')->limit(5)->get() as $category)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">{{ $category->name }}</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $category->listings_count }} listings</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activity Summary --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity Summary</h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">
                        {{ \App\Models\User::whereDate('created_at', today())->count() }}
                    </div>
                    <div class="text-sm text-gray-600">New Users Today</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">
                        {{ \App\Models\Order::whereDate('created_at', today())->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Orders Today</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">
                        {{ \App\Models\Listing::whereDate('created_at', today())->count() }}
                    </div>
                    <div class="text-sm text-gray-600">New Listings Today</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">
                        ${{ number_format(\App\Models\Order::where('status', 'completed')->whereDate('updated_at', today())->sum('usd_price'), 2) }}
                    </div>
                    <div class="text-sm text-gray-600">Revenue Today</div>
                </div>
            </div>
        </div>
    </div>
@endsection
