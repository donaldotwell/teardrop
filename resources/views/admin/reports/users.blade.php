@extends('layouts.admin')
@section('page-title', 'Users Report')

@section('breadcrumbs')
    <a href="{{ route('admin.reports') }}" class="text-yellow-700 hover:text-yellow-800">Reports</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Users</span>
@endsection

@section('page-heading')
    Users Report
@endsection

@section('page-description')
    User growth, role distribution, and activity metrics
@endsection

@section('content')
    <div class="space-y-6">

        {{-- User Status Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Total Users</div>
                <div class="text-2xl font-semibold text-blue-600">{{ number_format($userStatus['total']) }}</div>
                <div class="text-sm text-gray-500 mt-1">All registered accounts</div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Active Users</div>
                <div class="text-2xl font-semibold text-green-600">{{ number_format($userStatus['active']) }}</div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ $userStatus['total'] > 0 ? number_format(($userStatus['active'] / $userStatus['total']) * 100, 1) : 0 }}% of total
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Banned Users</div>
                <div class="text-2xl font-semibold text-red-600">{{ number_format($userStatus['banned']) }}</div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ $userStatus['total'] > 0 ? number_format(($userStatus['banned'] / $userStatus['total']) * 100, 1) : 0 }}% of total
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Inactive Users</div>
                <div class="text-2xl font-semibold text-gray-600">{{ number_format($userStatus['inactive']) }}</div>
                <div class="text-sm text-gray-500 mt-1">Not seen in 30+ days</div>
            </div>
        </div>

        {{-- Vendor Count --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600 mb-1">Registered Vendors</div>
                    <div class="text-3xl font-semibold text-amber-600">{{ number_format($vendorCount) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Users with vendor role</div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Vendor Ratio</div>
                    <div class="text-lg font-medium text-gray-700">
                        {{ $userStatus['total'] > 0 ? number_format(($vendorCount / $userStatus['total']) * 100, 1) : 0 }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Role Breakdown and User Growth --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Role Breakdown --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Role Distribution</h3>

                @if($roleBreakdown->isNotEmpty())
                    <div class="space-y-3">
                        @php
                            $maxRoleCount = $roleBreakdown->max('count');
                        @endphp
                        @foreach($roleBreakdown as $role)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 capitalize">{{ $role->name }}</span>
                                    <span class="text-sm text-gray-500">{{ number_format($role->count) }}</span>
                                </div>
                                <div class="bg-gray-200 rounded-full h-4">
                                    <div class="bg-yellow-500 h-4 rounded-full"
                                         style="width: {{ $maxRoleCount > 0 ? ($role->count / $maxRoleCount) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-4">No role data available.</div>
                @endif
            </div>

            {{-- User Growth --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">User Growth (Last 12 Months)</h3>

                @if($userGrowth->isNotEmpty())
                    <div class="space-y-2">
                        @php
                            $maxGrowth = $userGrowth->max('count');
                        @endphp
                        @foreach($userGrowth as $month)
                            <div class="flex items-center">
                                <div class="w-20 text-sm text-gray-600">
                                    {{ \Carbon\Carbon::createFromDate($month->year, $month->month, 1)->format('M Y') }}
                                </div>
                                <div class="flex-1 mx-3">
                                    <div class="bg-gray-200 rounded-full h-5">
                                        <div class="bg-blue-500 h-5 rounded-full flex items-center justify-end pr-2"
                                             style="width: {{ $maxGrowth > 0 ? ($month->count / $maxGrowth) * 100 : 0 }}%">
                                            @if($month->count > 0)
                                                <span class="text-white text-xs font-medium">{{ $month->count }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="w-12 text-sm text-gray-600 text-right">{{ $month->count }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-4">No growth data available.</div>
                @endif
            </div>
        </div>

        {{-- Top Buyers --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Buyers (by Completed Orders)</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topBuyers as $index => $buyer)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    #{{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="{{ route('admin.users.show', $buyer) }}" class="text-yellow-700 hover:text-yellow-800">
                                        {{ $buyer->username }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($buyer->orders_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $buyer->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($buyer->status === 'active')
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                    @elseif($buyer->status === 'banned')
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Banned</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ ucfirst($buyer->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No completed orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.reports') }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    ← Back to Reports
                </a>

                <a href="{{ route('admin.reports.export', 'users') }}"
                   class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Export CSV
                </a>

                <a href="{{ route('admin.reports.financial') }}"
                   class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                    Financial Report
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    View All Users
                </a>
            </div>
        </div>
    </div>
@endsection
