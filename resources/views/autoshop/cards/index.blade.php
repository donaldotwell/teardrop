@extends('layouts.autoshop')

@section('page-title', 'Cards — Autoshop')
@section('page-heading', 'Browse Cards')
@section('breadcrumbs')<span>Cards</span>@endsection

@section('content')

@php
    $hasFilters     = request()->anyFilled(['vendor_id','base_id','state','country','cardholder','bin','email','price_min','price_max']);
    $advancedActive = request()->anyFilled(['bin','email','price_min','price_max','sort']);
@endphp

{{-- Filter card --}}
<form method="GET" action="{{ route('autoshop.cards.index') }}" class="mb-5">
    <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">

        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Filters</span>
        </div>

        {{-- Row 1: Vendor · Base · Country · State --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Vendor</label>
                <select name="vendor_id"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                    <option value="">All vendors</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                            {{ $v->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Base</label>
                <select name="base_id"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                    <option value="">All bases</option>
                    @foreach($activeBases as $b)
                        <option value="{{ $b->id }}" {{ request('base_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }} (${{ number_format($b->price_usd, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Country</label>
                <select name="country"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                    <option value="">Any country</option>
                    @foreach($countries as $c)
                        <option value="{{ $c }}" {{ request('country') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                <select name="state"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                    <option value="">Any state</option>
                    @foreach($states as $st)
                        <option value="{{ $st }}" {{ request('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Row 2: Cardholder name --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cardholder Name</label>
                <input type="text" name="cardholder" value="{{ request('cardholder') }}"
                       placeholder="Full or partial name"
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
            </div>
        </div>

        {{-- Advanced filters --}}
        <details {{ $advancedActive ? 'open' : '' }}>
            <summary class="cursor-pointer text-xs font-medium text-teal-700 hover:text-teal-800 select-none list-none">
                [+ More filters{{ $advancedActive ? ' (active)' : '' }}]
            </summary>

            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">BIN (first 6 digits)</label>
                    <input type="text" name="bin" value="{{ request('bin') }}"
                           placeholder="e.g. 473336" maxlength="6" pattern="\d{6}"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <select name="email"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                        <option value="">Any</option>
                        <option value="yes" {{ request('email') === 'yes' ? 'selected' : '' }}>Has email</option>
                        <option value="no"  {{ request('email') === 'no'  ? 'selected' : '' }}>No email</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Price (USD / card)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="price_min" value="{{ request('price_min') }}"
                               placeholder="Min" min="0" step="0.01"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
                        <span class="text-gray-400 flex-shrink-0">–</span>
                        <input type="number" name="price_max" value="{{ request('price_max') }}"
                               placeholder="Max" min="0" step="0.01"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sort by</label>
                    <select name="sort"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-teal-500 bg-white">
                        <option value="newest"     {{ $sort === 'newest'     ? 'selected' : '' }}>Newest first</option>
                        <option value="price_asc"  {{ $sort === 'price_asc'  ? 'selected' : '' }}>Price: low → high</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: high → low</option>
                    </select>
                </div>
            </div>
        </details>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors">
                Apply Filters
            </button>
            <a href="{{ route('autoshop.cards.index') }}"
               class="px-5 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                Reset
            </a>
        </div>

    </div>
</form>

{{-- Result count --}}
<p class="text-sm text-gray-500 mb-4">
    {{ number_format($records->total()) }} card{{ $records->total() !== 1 ? 's' : '' }} available
    @if($records->total() > 0)
        &mdash; ${{ number_format($records->min('price_usd'), 2) }}
        @if($records->min('price_usd') != $records->max('price_usd'))
            – ${{ number_format($records->max('price_usd'), 2) }}
        @endif
        / card
    @endif
</p>

{{-- Records + purchase form --}}
@if($records->isEmpty())
    <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
        <p class="text-gray-500 mb-2">No cards match your filters.</p>
        @if($hasFilters)
            <a href="{{ route('autoshop.cards.index') }}" class="text-sm text-teal-700 hover:underline">Clear filters</a>
        @endif
    </div>
@else

<form action="{{ route('autoshop.cards.purchase') }}" method="POST">
    @csrf

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
        <div class="px-4 py-3 bg-teal-50 border-b border-teal-100 text-xs text-teal-800">
            Select cards to buy. Full card number and CVV are revealed after purchase.
            All selected cards must be from the <strong>same vendor</strong>. Selections apply to this page only.
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 w-8"></th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Vendor</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Base</th>
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
                        $masked = strlen($digits) > 10
                            ? $bin . str_repeat('*', strlen($digits) - 10) . substr($digits, -4)
                            : $digits;
                        // detect card type
                        if (str_starts_with($digits, '4'))          $ctype = 'VISA';
                        elseif (preg_match('/^5[1-5]|^2[2-7]/', $digits)) $ctype = 'MC';
                        elseif (preg_match('/^3[47]/', $digits))    $ctype = 'AMEX';
                        elseif (preg_match('/^(6011|65|64[4-9])/', $digits)) $ctype = 'DISC';
                        else                                         $ctype = 'CARD';
                    @endphp
                    <tr class="hover:bg-teal-50 transition-colors">
                        <td class="px-3 py-2 text-center">
                            <input type="checkbox" name="card_ids[]" value="{{ $card->id }}"
                                   class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap">
                            <a href="{{ route('autoshop.cards.index', ['vendor_id' => $card->base_vendor_id]) }}"
                               class="hover:text-teal-700">{{ $card->vendor_name }}</a>
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap">
                            <a href="{{ route('autoshop.cards.index', ['vendor_id' => $card->base_vendor_id, 'base_id' => $card->base_id]) }}"
                               class="hover:text-teal-700">{{ $card->base_name }}</a>
                        </td>
                        <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $card->cardholder_name }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-gray-600">{{ $bin ?: '—' }}</td>
                        <td class="px-3 py-2 text-xs">
                            @php
                                $typeColor = match($ctype) {
                                    'VISA' => 'bg-blue-100 text-blue-700',
                                    'MC'   => 'bg-red-100 text-red-700',
                                    'AMEX' => 'bg-green-100 text-green-700',
                                    'DISC' => 'bg-orange-100 text-orange-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
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
                        <td class="px-3 py-2 text-right font-mono text-amber-700 font-medium whitespace-nowrap text-xs">
                            ${{ number_format($card->price_usd, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Purchase bar --}}
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
        <p class="text-xs text-gray-400 mt-3">
            Total = sum of each selected card's price. No escrow — deducted directly from your wallet.
        </p>
    </div>
</form>

<div class="mt-4">{{ $records->links() }}</div>

@endif

@endsection
