@extends('layouts.admin')
@section('page-title', 'Financial Report')

@section('breadcrumbs')
    <a href="{{ route('admin.reports') }}" class="text-yellow-700 hover:text-yellow-800">Reports</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Financial</span>
@endsection

@section('page-heading')
    Financial Report
@endsection

@section('page-description')
    Detailed financial analysis and revenue metrics
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Date Range Filter --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Period</h3>

            <form method="GET" action="{{ route('admin.reports.financial') }}" class="flex items-end gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date"
                           name="start_date"
                           id="start_date"
                           value="{{ $startDate }}"
                           class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date"
                           name="end_date"
                           id="end_date"
                           value="{{ $endDate }}"
                           class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    Update Report
                </button>

                <a href="{{ route('admin.reports.export', 'financial') }}"
                   class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Export CSV
                </a>
            </form>
        </div>

        {{-- Revenue Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Total Revenue</div>
                <div class="text-2xl font-semibold text-green-600">
                    ${{ number_format($revenue->total_revenue ?? 0, 2) }}
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ number_format($revenue->total_orders ?? 0) }} orders
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Average Order Value</div>
                <div class="text-2xl font-semibold text-blue-600">
                    ${{ number_format($revenue->avg_order_value ?? 0, 2) }}
                </div>
                <div class="text-sm text-gray-500 mt-1">Per completed order</div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Bitcoin Revenue</div>
                <div class="text-2xl font-semibold text-orange-600">
                    ${{ number_format($revenue->btc_revenue ?? 0, 2) }}
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ $revenue->total_revenue > 0 ? number_format(($revenue->btc_revenue / $revenue->total_revenue) * 100, 1) : 0 }}% of total
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Monero Revenue</div>
                <div class="text-2xl font-semibold text-amber-600">
                    ${{ number_format($revenue->xmr_revenue ?? 0, 2) }}
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ $revenue->total_revenue > 0 ? number_format(($revenue->xmr_revenue / $revenue->total_revenue) * 100, 1) : 0 }}% of total
                </div>
            </div>
        </div>

        {{-- Daily Revenue Chart --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Revenue Trend</h3>

            @if($dailyRevenue->isNotEmpty())
                <div class="space-y-2">
                    @php
                        $maxRevenue = $dailyRevenue->max('revenue');
                    @endphp
                    @foreach($dailyRevenue as $day)
                        <div class="flex items-center">
                            <div class="w-24 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($day->date)->format('M d') }}
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="bg-gray-200 rounded-full h-6 relative">
                                    <div class="bg-green-500 h-6 rounded-full flex items-center justify-end pr-2"
                                         style="width: {{ $maxRevenue > 0 ? ($day->revenue / $maxRevenue) * 100 : 0 }}%">
                                        @if($day->revenue > 0)
                                            <span class="text-white text-xs font-medium">
                                                ${{ number_format($day->revenue, 0) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="w-16 text-sm text-gray-600 text-right">
                                {{ $day->orders }} orders
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-500 py-8">
                    No revenue data for the selected period.
                </div>
            @endif
        </div>

        {{-- Revenue Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Currency Breakdown --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue by Currency</h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-orange-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                <span class="text-orange-600 font-bold text-sm">₿</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Bitcoin (BTC)</div>
                                <div class="text-sm text-gray-500">Cryptocurrency payments</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">
                                ${{ number_format($revenue->btc_revenue ?? 0, 2) }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $revenue->total_revenue > 0 ? number_format(($revenue->btc_revenue / $revenue->total_revenue) * 100, 1) : 0 }}%
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <span class="text-amber-600 font-bold text-sm">ɱ</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Monero (XMR)</div>
                                <div class="text-sm text-gray-500">Privacy cryptocurrency</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">
                                ${{ number_format($revenue->xmr_revenue ?? 0, 2) }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $revenue->total_revenue > 0 ? number_format(($revenue->xmr_revenue / $revenue->total_revenue) * 100, 1) : 0 }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Key Metrics --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Key Metrics</h3>

                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Total Orders</span>
                        <span class="font-medium text-gray-900">{{ number_format($revenue->total_orders ?? 0) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Total Revenue</span>
                        <span class="font-medium text-gray-900">${{ number_format($revenue->total_revenue ?? 0, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Average Order Value</span>
                        <span class="font-medium text-gray-900">${{ number_format($revenue->avg_order_value ?? 0, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Revenue per Day</span>
                        <span class="font-medium text-gray-900">
                            @php
                                $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
                                $revenuePerDay = $days > 0 ? ($revenue->total_revenue ?? 0) / $days : 0;
                            @endphp
                            ${{ number_format($revenuePerDay, 2) }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Report Period</span>
                        <span class="font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($startDate)->format('M d') }} -
                            {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </span>
                    </div>
                </div>
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

                <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
                   class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    View Completed Orders
                </a>

                <a href="{{ route('admin.reports.users') }}"
                   class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                    User Report
                </a>
            </div>
        </div>
    </div>
@endsection
