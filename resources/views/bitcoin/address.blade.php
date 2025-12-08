@extends('layouts.app')

@section('page-title', 'Bitcoin Address Details')

@section('content')
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Address Details</h1>
                <p class="text-gray-600">Bitcoin Address #{{ $address->address_index }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('bitcoin.topup') }}"
                   class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Use for Deposit
                </a>
                <a href="{{ route('bitcoin.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    Back to Wallet
                </a>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
            <!-- Address Info -->
            <div class="space-y-6">
                <!-- Address Display -->
                <div class="p-6 bg-gradient-to-br from-amber-50 to-white border-2 border-amber-100 rounded-xl">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Bitcoin Address</h3>
                    <div class="space-y-3">
                        <div>
                            <label for="address-field" class="block text-sm text-gray-600 mb-2">
                                Click field to select, then press Ctrl+C (or Cmd+C) to copy
                            </label>
                            <input type="text"
                                   value="{{ $address->address }}"
                                   readonly
                                   class="w-full p-3 bg-white border border-amber-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 select-all cursor-pointer"
                                   id="address-field"
                                   onclick="this.select()">
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Status:</span>
                            @if($address->is_used)
                                <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full">Used</span>
                            @else
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full">Active</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Address Statistics -->
                <div class="p-6 bg-gray-50 rounded-xl">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Statistics</h3>

                    <div class="grid gap-4 grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Current Balance</label>
                            <p class="text-lg font-mono font-bold text-gray-900">
                                {{ number_format($address->balance, 8) }} BTC
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Total Received</label>
                            <p class="text-lg font-mono font-bold text-gray-900">
                                {{ number_format($address->total_received, 8) }} BTC
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Transaction Count</label>
                            <p class="text-lg font-bold text-gray-900">{{ $address->tx_count }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Address Index</label>
                            <p class="text-lg font-bold text-gray-900">#{{ $address->address_index }}</p>
                        </div>
                    </div>
                </div>

                <!-- Address Timeline -->
                <div class="p-6 bg-white border border-gray-200 rounded-xl">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Timeline</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Generated:</span>
                            <span class="text-gray-900">{{ $address->created_at->format('M d, Y H:i') }}</span>
                        </div>

                        @if($address->first_used_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600">First Used:</span>
                                <span class="text-gray-900">{{ $address->first_used_at->format('M d, Y H:i') }}</span>
                            </div>
                        @endif

                        @if($address->last_used_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Used:</span>
                                <span class="text-gray-900">{{ $address->last_used_at->format('M d, Y H:i') }}</span>
                            </div>
                        @endif

                        @if(!$address->is_used)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="text-green-600 font-medium">Ready for use</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="space-y-6">
                <div class="text-center p-6 bg-white border-2 border-gray-200 rounded-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">QR Code</h3>
                    <div class="flex justify-center mb-4">
                        {!! $qrCode !!}
                    </div>
                    <p class="text-sm text-gray-600">
                        Scan this QR code with your Bitcoin wallet
                    </p>
                </div>

                <!-- Usage Instructions -->
                @if(!$address->is_used)
                    <div class="p-6 bg-blue-50 border border-blue-200 rounded-xl">
                        <h3 class="text-lg font-semibold text-blue-800 mb-3">How to Use This Address</h3>

                        <ol class="space-y-2 text-sm text-blue-700">
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">1</span>
                                Copy the address above or scan the QR code
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">2</span>
                                Use it in your Bitcoin wallet to send funds
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">3</span>
                                Wait for blockchain confirmation
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5">4</span>
                                Funds will appear in your wallet
                            </li>
                        </ol>
                    </div>
                @endif

                <!-- Privacy Notice -->
                <div class="p-6 bg-yellow-50 border border-yellow-200 rounded-xl">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-3">Privacy Recommendation</h3>
                    <p class="text-sm text-yellow-700">
                        For enhanced privacy, consider generating a new address for each transaction.
                        This makes it harder to track your transaction history on the blockchain.
                    </p>
                    @if($address->is_used)
                        <div class="mt-3">
                            <a href="{{ route('bitcoin.generate-address') }}"
                               class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                                Generate New Address
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        @if($address->transactions->count() > 0)
            <div class="mt-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Transaction History</h2>

                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Transaction ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($address->transactions as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono text-gray-900">
                                            {{ substr($tx->txid, 0, 12) }}...
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono text-gray-900">
                                            {{ number_format($tx->amount, 8) }} BTC
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                        <span class="inline-block w-2 h-2 rounded-full mr-2
                                            {{ $tx->status_color === 'green' ? 'bg-green-400' :
                                               ($tx->status_color === 'yellow' ? 'bg-yellow-400' : 'bg-red-400') }}">
                                        </span>
                                            <span class="text-sm text-gray-900">{{ ucfirst($tx->status) }}</span>
                                            @if($tx->confirmations > 0)
                                                <span class="ml-1 text-xs text-gray-500">({{ $tx->confirmations }})</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $tx->created_at->format('M d, Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-8 text-center py-12 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Transactions Yet</h3>
                <p class="text-gray-600 mb-6">This address hasn't been used for any transactions.</p>
                @if(!$address->is_used)
                    <a href="{{ route('bitcoin.topup') }}"
                       class="inline-flex items-center px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition-colors">
                        Use This Address
                    </a>
                @endif
            </div>
        @endif
    </div>
@endsection
