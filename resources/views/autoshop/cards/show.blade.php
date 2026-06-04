@extends('layouts.autoshop')

@section('page-title', $base->name . ' — Cards Autoshop')
@section('page-heading', $base->name)

@section('breadcrumbs')
<a href="{{ route('autoshop.cards.index') }}" class="hover:text-gray-900">Cards</a>
<span class="text-gray-400 mx-1">/</span>
<span>{{ $base->name }}</span>
@endsection

@section('content')

<div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div>
            <div class="text-xs text-gray-500 mb-0.5">Vendor</div>
            <div class="font-medium">{{ $base->vendor->username_pub }}</div>
        </div>
        <div>
            <div class="text-xs text-gray-500 mb-0.5">Price / card</div>
            <div class="font-bold text-amber-700">${{ number_format($base->price_usd, 2) }}</div>
        </div>
        <div>
            <div class="text-xs text-gray-500 mb-0.5">Available</div>
            <div class="font-bold text-green-700">{{ number_format($base->available_count) }}</div>
        </div>
        <div>
            <div class="text-xs text-gray-500 mb-0.5">Total in base</div>
            <div class="font-medium">{{ number_format($base->record_count) }}</div>
        </div>
    </div>
</div>

<form action="{{ route('autoshop.cards.purchase') }}" method="POST">
    @csrf

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 w-8"></th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Cardholder</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">BIN</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Type</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Exp</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">State</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Country</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-700">Email</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-700">Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($records as $card)
                    @php
                        $digits = preg_replace('/\D/', '', $card->card_number);
                        $bin    = substr($digits, 0, 6);
                        if (str_starts_with($digits, '4'))                   $ctype = 'VISA';
                        elseif (preg_match('/^5[1-5]|^2[2-7]/', $digits))   $ctype = 'MC';
                        elseif (preg_match('/^3[47]/', $digits))             $ctype = 'AMEX';
                        elseif (preg_match('/^(6011|65|64[4-9])/', $digits)) $ctype = 'DISC';
                        else                                                  $ctype = 'CARD';
                        $typeColor = match($ctype) {
                            'VISA' => 'bg-blue-100 text-blue-700',
                            'MC'   => 'bg-red-100 text-red-700',
                            'AMEX' => 'bg-green-100 text-green-700',
                            'DISC' => 'bg-orange-100 text-orange-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-teal-50 transition-colors">
                        <td class="px-3 py-2 text-center">
                            <input type="checkbox" name="card_ids[]" value="{{ $card->id }}"
                                   class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        </td>
                        <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $card->cardholder_name }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-gray-600">{{ $bin ?: '—' }}</td>
                        <td class="px-3 py-2 text-xs">
                            <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $typeColor }}">{{ $ctype }}</span>
                        </td>
                        <td class="px-3 py-2 font-mono text-xs text-gray-600">{{ $card->exp_month }}/{{ $card->exp_year }}</td>
                        <td class="px-3 py-2 text-gray-600 text-xs">{{ $card->state ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-600 text-xs">{{ $card->country ?? '—' }}</td>
                        <td class="px-3 py-2 text-center">
                            @if($card->email)
                                <span class="text-green-600 font-bold">&#10003;</span>
                            @else
                                <span class="text-red-400">&#10007;</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-amber-700 font-medium text-xs">
                            ${{ number_format($card->price_usd, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl px-5 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex flex-wrap items-center gap-4">
                <label class="text-sm font-medium text-gray-700">Pay with:</label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="currency" value="btc" checked class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                    <span>Bitcoin (BTC)</span>
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="currency" value="xmr" class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                    <span>Monero (XMR)</span>
                </label>
            </div>
            <button type="submit"
                    class="sm:ml-auto w-full sm:w-auto px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-sm rounded-lg transition-colors shadow-sm">
                Purchase Selected
            </button>
        </div>
    </div>
</form>

<div class="mt-4">{{ $records->links() }}</div>

@endsection
