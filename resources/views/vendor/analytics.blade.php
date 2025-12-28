@extends('layouts.vendor')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-gray-900">Sales Analytics</h1>
        <p class="text-sm text-gray-600 mt-1">Track your monthly performance</p>
    </div>

    <!-- Monthly Sales Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($monthlySales->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-purple-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Avg. Order Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $totalOrders = 0;
                            $totalRevenue = 0;
                        @endphp
                        @foreach($monthlySales as $monthData)
                            @php
                                $totalOrders += $monthData->count;
                                $totalRevenue += $monthData->revenue;
                                $avgOrderValue = $monthData->count > 0 ? $monthData->revenue / $monthData->count : 0;
                                $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $monthData->month)->format('F Y');
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $monthLabel }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ number_format($monthData->count) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">${{ number_format($monthData->revenue, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">${{ number_format($avgOrderValue, 2) }}</div>
                                </td>
                            </tr>
                        @endforeach
                        <!-- Totals Row -->
                        <tr class="bg-purple-50 font-medium">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">Total</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ number_format($totalOrders) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">${{ number_format($totalRevenue, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">
                                    ${{ $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2) : '0.00' }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="text-gray-400 text-lg mb-2">No Sales Data</div>
                <p class="text-sm text-gray-500">Your sales analytics will appear here once you make your first sale</p>
            </div>
        @endif
    </div>

    <!-- Summary Cards -->
    @if($monthlySales->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-600 mb-2">Total Orders (12 months)</div>
            <div class="text-3xl font-bold text-purple-700">{{ number_format($totalOrders) }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-600 mb-2">Total Revenue (12 months)</div>
            <div class="text-3xl font-bold text-purple-700">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-600 mb-2">Average Monthly Revenue</div>
            <div class="text-3xl font-bold text-purple-700">
                ${{ number_format($monthlySales->count() > 0 ? $totalRevenue / $monthlySales->count() : 0, 2) }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
