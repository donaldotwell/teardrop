@extends('layouts.app')

@section('page-title', $base->name . ' — Autoshop')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="mb-5">
        <a href="{{ route('autoshop.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Autoshop</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    {{-- Base header --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $base->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">Vendor: {{ $base->vendor->username_pub }}</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-amber-700">${{ number_format($base->price_usd, 2) }}</div>
                <div class="text-xs text-gray-500">per record</div>
            </div>
        </div>
        <div class="flex gap-6 mt-4 pt-4 border-t border-gray-100 text-sm text-gray-600">
            <span><strong class="text-green-700">{{ number_format($base->available_count) }}</strong> available</span>
            <span><strong class="text-gray-600">{{ number_format($base->sold_count) }}</strong> sold</span>
        </div>
    </div>

    {{-- Purchase form wraps the whole table --}}
    <form action="{{ route('autoshop.purchase') }}" method="POST">
        @csrf

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
            <div class="px-5 py-3 border-b border-gray-100 bg-amber-50">
                <p class="text-xs text-amber-800">
                    Select the records you want to purchase. SSN, DOB, address, and phone are revealed after purchase.
                    Selections apply to <strong>this page only</strong> — paginate to buy more from separate pages.
                </p>
            </div>

            @if($records->isEmpty())
                <div class="p-8 text-center text-gray-500 text-sm">No available records on this page.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2 w-10">
                                    {{-- Select-all via label trick (no JS needed for visual affordance) --}}
                                </th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Name</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">City</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">State</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">ZIP</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Gender</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-700">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($records as $record)
                            <tr class="hover:bg-amber-50 cursor-pointer">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox"
                                           name="fullz_ids[]"
                                           value="{{ $record->id }}"
                                           class="w-4 h-4 accent-amber-600">
                                </td>
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $record->name }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $record->city ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $record->state ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $record->zip ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $record->gender ?? '—' }}</td>
                                <td class="px-4 py-2 text-right font-mono text-amber-700">${{ number_format($base->price_usd, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Purchase bar --}}
                <div class="px-5 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <label class="text-sm font-medium text-gray-700">Pay with:</label>
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="radio" name="currency" value="btc" checked class="accent-amber-600">
                                Bitcoin (BTC)
                            </label>
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="radio" name="currency" value="xmr" class="accent-orange-600">
                                Monero (XMR)
                            </label>
                        </div>
                        <div class="sm:ml-auto">
                            <button type="submit"
                                    class="w-full sm:w-auto px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition-colors">
                                Purchase Selected
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        Price: ${{ number_format($base->price_usd, 2) }} per record.
                        Payment is deducted directly from your wallet — no escrow.
                    </p>
                </div>
            @endif
        </div>
    </form>

    <div class="mt-2">{{ $records->links() }}</div>
</div>
@endsection
