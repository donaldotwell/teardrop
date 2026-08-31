@extends('layouts.vendor')

@section('page-title', 'Upload Card Base')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <div class="mb-6">
        <a href="{{ route('vendor.cards.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Cards</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Upload New Card Base</h1>
    </div>

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <form action="{{ route('vendor.cards.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Base Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name" name="name"
                       value="{{ old('name') }}"
                       maxlength="120"
                       placeholder="e.g. US-VISA-2024"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('name') border-red-400 @enderror"
                       required>
            </div>

            <div class="mb-5">
                <label for="price_usd" class="block text-sm font-medium text-gray-700 mb-1">
                    Price per Card (USD) <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 text-sm">$</span>
                    <input type="number"
                           id="price_usd" name="price_usd"
                           value="{{ old('price_usd') }}"
                           min="0.01" max="9999" step="0.01"
                           placeholder="10.00"
                           class="w-40 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('price_usd') border-red-400 @enderror"
                           required>
                    <span class="text-xs text-gray-400">USD per card</span>
                </div>
            </div>

            <div class="mb-5">
                <label for="discount_pct" class="block text-sm font-medium text-gray-700 mb-1">
                    Discount (%) <span class="text-gray-400 font-normal">optional</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="number"
                           id="discount_pct" name="discount_pct"
                           value="{{ old('discount_pct', 0) }}"
                           min="0" max="99" step="0.01"
                           placeholder="0"
                           class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('discount_pct') border-red-400 @enderror">
                    <span class="text-xs text-gray-400">% off the listed price (0 = no discount)</span>
                </div>
            </div>

            <div class="mb-6">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">
                    Card File <span class="text-red-500">*</span>
                </label>
                <input type="file"
                       id="file" name="file"
                       accept=".csv,.txt"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('file') border-red-400 @enderror"
                       required>
                <p class="text-xs text-gray-400 mt-1">Max 10 MB. Pipe-delimited (|). Header row optional.</p>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-purple-700 hover:bg-purple-800 text-white font-semibold rounded-lg transition-colors">
                Upload and Import
            </button>
        </form>
    </div>

    {{-- Format guide --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">File Format</h3>
        <p class="text-xs text-gray-600 mb-3">
            Pipe-delimited (<code>|</code>). One card per line. Header row is optional — if the first field of the first line is not a card number, it is treated as a header.
            Rows missing <strong>card_number</strong>, <strong>exp_month</strong>, <strong>exp_year</strong>, <strong>cvv</strong>, or <strong>cardholder_name</strong> are skipped.
        </p>
        <div class="bg-white border border-gray-200 rounded-lg p-3 overflow-x-auto mb-3">
            <code class="text-xs text-gray-700 font-mono whitespace-nowrap">
                4733360103190454|07|30|846|Tait Aarika|443 Evergreen Rd|Blackhawk|CO|80422|UNITED STATES|mcgrawramona764@gmail.com|303-175-5920
            </code>
        </div>
        <table class="w-full text-xs text-gray-600">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="pb-1 text-left font-semibold">Position</th>
                    <th class="pb-1 text-left font-semibold">Field</th>
                    <th class="pb-1 text-left font-semibold">Required</th>
                    <th class="pb-1 text-left font-semibold">Example</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr><td class="py-1 font-mono text-gray-400">0</td><td class="py-1 font-mono">card_number</td><td class="text-red-600">Yes</td><td>4733360103190454</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">1</td><td class="py-1 font-mono">exp_month</td><td class="text-red-600">Yes</td><td>07</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">2</td><td class="py-1 font-mono">exp_year</td><td class="text-red-600">Yes</td><td>30</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">3</td><td class="py-1 font-mono">cvv</td><td class="text-red-600">Yes</td><td>846</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">4</td><td class="py-1 font-mono">cardholder_name</td><td class="text-red-600">Yes</td><td>Tait Aarika</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">5</td><td class="py-1 font-mono">address</td><td class="text-gray-400">No</td><td>443 Evergreen Rd</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">6</td><td class="py-1 font-mono">city</td><td class="text-gray-400">No</td><td>Blackhawk</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">7</td><td class="py-1 font-mono">state</td><td class="text-gray-400">No</td><td>CO</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">8</td><td class="py-1 font-mono">zip</td><td class="text-gray-400">No</td><td>80422</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">9</td><td class="py-1 font-mono">country</td><td class="text-gray-400">No</td><td>UNITED STATES</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">10</td><td class="py-1 font-mono">email</td><td class="text-gray-400">No</td><td>user@example.com</td></tr>
                <tr><td class="py-1 font-mono text-gray-400">11</td><td class="py-1 font-mono">phone</td><td class="text-gray-400">No</td><td>303-175-5920</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
