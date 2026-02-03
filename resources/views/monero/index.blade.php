@extends('layouts.app')

@section('page-title', 'Monero Wallet')

@section('content')
    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Monero Wallet</h1>
                <p class="text-gray-600">Manage your Monero deposits and transactions</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('monero.topup') }}"
                   class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    Top Up Wallet
                </a>
                <a href="{{ route('wallet.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium transition-colors">
                    Back to Wallets
                </a>
            </div>
        </div>

        <!-- Wallet Stats -->
        <div class="grid gap-6 md:grid-cols-3 mb-8">
            <div class="p-6 bg-orange-50 border-2 border-orange-100 rounded-xl">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Current Balance</h3>
                <p class="text-2xl font-mono font-bold text-orange-700">
                    {{ number_format($walletStats['balance'], 12) }} XMR
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    ≈ ${{ number_format(\App\Repositories\MoneroRepository::convertToUsd($walletStats['balance']), 2) }} USD
                </p>
            </div>

            <div class="p-6 bg-gray-50 rounded-xl">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Received</h3>
                <p class="text-2xl font-mono font-bold text-gray-900">
                    {{ number_format($walletStats['total_received'], 12) }} XMR
                </p>
                <p class="text-sm text-gray-600 mt-1">All-time deposits</p>
            </div>

            <div class="p-6 bg-gray-50 rounded-xl">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Sent</h3>
                <p class="text-2xl font-mono font-bold text-gray-900">
                    {{ number_format($walletStats['total_sent'], 12) }} XMR
                </p>
                <p class="text-sm text-gray-600 mt-1">All-time withdrawals</p>
            </div>
        </div>

        <!-- Withdraw Monero -->
        <div class="mb-8 p-6 bg-blue-50 border border-blue-200 rounded-xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Withdraw Monero</h3>
            
            <form method="POST" action="{{ route('monero.withdraw') }}" class="max-w-2xl">
                @csrf
                
                <div class="grid gap-4 md:grid-cols-2 mb-4">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Recipient Address <span class="text-red-600">*</span>
                        </label>
                        <input type="text" 
                               id="address" 
                               name="address" 
                               value="{{ old('address') }}"
                               required
                               placeholder="4..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 font-mono text-sm
                                      @error('address') border-red-500 @enderror">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount (XMR) <span class="text-red-600">*</span>
                        </label>
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               value="{{ old('amount') }}"
                               required
                               step="0.000000000001"
                               min="0.001"
                               max="{{ $xmrWallet->balance }}"
                               placeholder="0.000000000000"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 font-mono
                                      @error('amount') border-red-500 @enderror">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Available: {{ number_format($xmrWallet->balance, 12) }} XMR
                        </p>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="pin" class="block text-sm font-medium text-gray-700 mb-2">
                        Security PIN <span class="text-red-600">*</span>
                    </label>
                    <input type="password" 
                           id="pin" 
                           name="pin" 
                           required
                           maxlength="6"
                           placeholder="Enter your 6-digit PIN"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500
                                  @error('pin') border-red-500 @enderror">
                    @error('pin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-blue-200">
                    <div class="text-sm text-gray-600">
                        <p class="font-medium">Minimum: 0.001 XMR</p>
                        <p>Network fees will be deducted from your balance</p>
                    </div>
                    <button type="submit" 
                            class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors">
                        Withdraw Monero
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Transactions -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Transactions</h2>

            @if($recentTransactions->count() > 0)
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ref ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
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
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentTransactions as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs font-mono text-gray-600" title="{{ $tx->uuid }}">
                                            {{ substr($tx->uuid, 0, 8) }}...
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $tx->type === 'deposit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($tx->type) }}
                                    </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono text-gray-900">
                                            {{ number_format($tx->amount, 12) }} XMR
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
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Transactions Yet</h3>
                    <p class="text-gray-600 mb-6">Start by making your first Monero deposit.</p>
                    <a href="{{ route('monero.topup') }}"
                       class="inline-flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors">
                        Make First Deposit
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
