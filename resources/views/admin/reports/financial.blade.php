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

        {{-- Export Action --}}
        <div class="flex justify-end">
            <a href="{{ route('admin.reports.export', 'financial') }}"
               class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Export CSV
            </a>
        </div>

        {{-- Revenue Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Total USD Revenue</div>
                <div class="text-2xl font-semibold text-green-600">
                    ${{ number_format($totalUsdRevenue, 2) }}
                </div>
                <div class="text-sm text-gray-500 mt-1">All completed orders</div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Bitcoin Volume</div>
                <div class="text-2xl font-semibold text-orange-600">
                    {{ number_format($btcRevenue, 8) }} BTC
                </div>
                <div class="text-sm text-gray-500 mt-1">Total BTC received</div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Monero Volume</div>
                <div class="text-2xl font-semibold text-amber-600">
                    {{ number_format($xmrRevenue, 8) }} XMR
                </div>
                <div class="text-sm text-gray-500 mt-1">Total XMR received</div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="text-sm text-gray-600 mb-1">Vendor Conversions</div>
                <div class="text-2xl font-semibold text-purple-600">
                    {{ number_format($vendorConversions) }}
                </div>
                <div class="text-sm text-gray-500 mt-1">Fee transactions</div>
            </div>
        </div>

        {{-- Escrow Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Active Escrow Balance</h3>
                <div class="text-3xl font-bold text-blue-600">{{ number_format($activeEscrow, 8) }}</div>
                <p class="text-sm text-gray-500 mt-1">Funds currently held in escrow</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Released Escrow Transactions</h3>
                <div class="text-3xl font-bold text-green-600">{{ number_format($releasedEscrow) }}</div>
                <p class="text-sm text-gray-500 mt-1">Escrow wallets successfully released</p>
            </div>
        </div>

        {{-- Monthly Revenue Table --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Revenue (Last 12 Months)</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue (USD)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Order Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($monthlyRevenue as $row)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::createFromDate($row->year, $row->month, 1)->format('M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    ${{ number_format($row->revenue, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $row->order_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ $row->order_count > 0 ? number_format($row->revenue / $row->order_count, 2) : '0.00' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No revenue data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                                <span class="text-orange-600 font-bold text-sm">B</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Bitcoin (BTC)</div>
                                <div class="text-sm text-gray-500">Cryptocurrency payments</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">
                                {{ number_format($btcRevenue, 8) }} BTC
                            </div>
                            <div class="text-sm text-gray-500">
                                @php
                                    $totalCrypto = $btcRevenue + $xmrRevenue;
                                @endphp
                                {{ $totalCrypto > 0 ? number_format(($btcRevenue / $totalCrypto) * 100, 1) : 0 }}% of crypto volume
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-amber-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                                <span class="text-amber-600 font-bold text-sm">M</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Monero (XMR)</div>
                                <div class="text-sm text-gray-500">Privacy cryptocurrency</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">
                                {{ number_format($xmrRevenue, 8) }} XMR
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $totalCrypto > 0 ? number_format(($xmrRevenue / $totalCrypto) * 100, 1) : 0 }}% of crypto volume
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
                        <span class="text-sm text-gray-600">Total USD Revenue</span>
                        <span class="font-medium text-gray-900">${{ number_format($totalUsdRevenue, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">BTC Volume</span>
                        <span class="font-medium text-gray-900">{{ number_format($btcRevenue, 8) }} BTC</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">XMR Volume</span>
                        <span class="font-medium text-gray-900">{{ number_format($xmrRevenue, 8) }} XMR</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Active Escrow</span>
                        <span class="font-medium text-gray-900">{{ number_format($activeEscrow, 8) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Released Escrows</span>
                        <span class="font-medium text-gray-900">{{ number_format($releasedEscrow) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Vendor Conversions</span>
                        <span class="font-medium text-gray-900">{{ number_format($vendorConversions) }}</span>
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
