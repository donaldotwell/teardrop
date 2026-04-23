@extends('layouts.autoshop')

@section('page-title', $base->name . ' — FSAID')

@section('page-heading'){{ $base->name }}@endsection

@section('breadcrumbs')
<a href="{{ route('autoshop.fsaid.index') }}" class="hover:text-gray-900">FSAID</a>
<span class="text-gray-400 mx-1">/</span>
<span class="truncate">{{ $base->name }}</span>
@endsection

@section('content')

    {{-- Base info bar --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-sm text-gray-600">
                Vendor: <span class="font-medium text-gray-900">{{ $base->vendor->username_pub }}</span>
            </div>
            <div class="sm:text-right">
                <div class="text-2xl font-bold text-amber-700">${{ number_format($base->price_usd, 2) }}</div>
                <div class="text-xs text-gray-500">per record</div>
            </div>
        </div>
        <div class="flex flex-wrap gap-4 mt-3 pt-3 border-t border-gray-100 text-sm text-gray-600">
            <span><strong class="text-green-700">{{ number_format($base->available_count) }}</strong> available</span>
            <span><strong class="text-gray-600">{{ number_format($base->sold_count) }}</strong> sold</span>
        </div>
    </div>

    {{-- Purchase form --}}
    <form action="{{ route('autoshop.fsaid.purchase') }}" method="POST">
        @csrf

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
            <div class="px-5 py-3 border-b border-teal-100 bg-teal-50">
                <p class="text-xs text-teal-800">
                    Select the records you want to purchase. Email credentials, SSN, DOB, and address revealed after purchase.
                    Selections apply to <strong>this page only</strong>.
                </p>
            </div>

            @if($records->isEmpty())
                <div class="p-10 text-center">
                    <p class="text-gray-500 text-sm mb-3">No available records in this base.</p>
                    <a href="{{ route('autoshop.fsaid.index') }}"
                       class="text-sm text-teal-700 hover:underline">Browse other bases</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2 w-10"></th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Name</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700 hidden sm:table-cell">DOB</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">State</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">ZIP</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700 hidden md:table-cell">Email</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700 hidden md:table-cell">E.Pass</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700 hidden lg:table-cell">Backup</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-700 hidden lg:table-cell">2FA</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700 hidden lg:table-cell">Enrollment</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-700">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($records as $record)
                            <tr class="hover:bg-teal-50 transition-colors">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox"
                                           name="fsaid_ids[]"
                                           value="{{ $record->id }}"
                                           class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                </td>
                                <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $record->first_name }} {{ $record->last_name }}</td>
                                <td class="px-4 py-2 text-gray-600 font-mono text-xs hidden sm:table-cell">{{ $record->dob ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $record->state ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-500 font-mono text-xs">{{ $record->zip ?? '—' }}</td>
                                <td class="px-4 py-2 text-center hidden md:table-cell">
                                    @if($record->email)
                                        <span class="text-green-600 font-bold">&#10003;</span>
                                    @else
                                        <span class="text-red-400">&#10007;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center hidden md:table-cell">
                                    @if($record->email_pass)
                                        <span class="text-green-600 font-bold">&#10003;</span>
                                    @else
                                        <span class="text-red-400">&#10007;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center hidden lg:table-cell">
                                    @if($record->backup_code)
                                        <span class="text-green-600 font-bold">&#10003;</span>
                                    @else
                                        <span class="text-red-400">&#10007;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center hidden lg:table-cell">
                                    @if($record->two_fa)
                                        <span class="text-green-600 font-bold">&#10003;</span>
                                    @else
                                        <span class="text-red-400">&#10007;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-xs hidden lg:table-cell">
                                    @if($record->enrollment)
                                        <span class="text-gray-700">{{ ucfirst($record->enrollment) }}</span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right font-mono text-amber-700">${{ number_format($base->price_usd, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Purchase bar --}}
                <div class="px-5 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="text-sm font-medium text-gray-700">Pay with:</label>
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="radio" name="currency" value="btc" checked class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                                Bitcoin (BTC)
                            </label>
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="radio" name="currency" value="xmr" class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                                Monero (XMR)
                            </label>
                        </div>
                        <div class="sm:ml-auto">
                            <button type="submit"
                                    class="w-full sm:w-auto px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition-colors">
                                Purchase Selected
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        ${{ number_format($base->price_usd, 2) }} per record. Deducted directly from your wallet — no escrow.
                    </p>
                </div>
            @endif
        </div>
    </form>

    <div>{{ $records->links() }}</div>

@endsection
