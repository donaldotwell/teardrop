@extends('layouts.vendor')

@section('page-title', 'Upload New Base')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <div class="mb-6">
        <a href="{{ route('vendor.autoshop.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Autoshop</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Upload New Base</h1>
    </div>

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <form action="{{ route('vendor.autoshop.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Base Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name" name="name"
                       value="{{ old('name') }}"
                       maxlength="120"
                       placeholder="e.g. US-2024-Q1"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('name') border-red-400 @enderror"
                       required>
            </div>

            <div class="mb-5">
                <label for="price_usd" class="block text-sm font-medium text-gray-700 mb-1">
                    Price per Record (USD) <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 text-sm">$</span>
                    <input type="number"
                           id="price_usd" name="price_usd"
                           value="{{ old('price_usd') }}"
                           min="0.01" max="9999" step="0.01"
                           placeholder="5.00"
                           class="w-40 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('price_usd') border-red-400 @enderror"
                           required>
                    <span class="text-xs text-gray-400">USD per record</span>
                </div>
            </div>

            <div class="mb-6">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">
                    CSV File <span class="text-red-500">*</span>
                </label>
                <input type="file"
                       id="file" name="file"
                       accept=".csv,.txt"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500 @error('file') border-red-400 @enderror"
                       required>
                <p class="text-xs text-gray-400 mt-1">Max 10 MB. CSV with header row.</p>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-purple-700 hover:bg-purple-800 text-white font-semibold rounded-lg transition-colors">
                Upload and Import
            </button>
        </form>
    </div>

    {{-- CSV format guide --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Required CSV Format</h3>
        <p class="text-xs text-gray-600 mb-3">
            The first row must be a header. Column names are case-insensitive.
            Rows missing <strong>name</strong>, <strong>ssn</strong>, or <strong>dob</strong> are skipped automatically.
        </p>
        <div class="bg-white border border-gray-200 rounded-lg p-3 overflow-x-auto">
            <code class="text-xs text-gray-700 font-mono whitespace-nowrap">
                name,address,city,state,zip,phone_no,gender,ssn,dob
            </code>
        </div>
        <table class="mt-3 w-full text-xs text-gray-600">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="pb-1 text-left font-semibold">Column</th>
                    <th class="pb-1 text-left font-semibold">Required</th>
                    <th class="pb-1 text-left font-semibold">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr><td class="py-1 font-mono">name</td><td class="text-red-600">Yes</td><td>Full name</td></tr>
                <tr><td class="py-1 font-mono">ssn</td><td class="text-red-600">Yes</td><td>Social Security Number</td></tr>
                <tr><td class="py-1 font-mono">dob</td><td class="text-red-600">Yes</td><td>Date of Birth</td></tr>
                <tr><td class="py-1 font-mono">address</td><td class="text-gray-400">No</td><td>Street address</td></tr>
                <tr><td class="py-1 font-mono">city</td><td class="text-gray-400">No</td><td></td></tr>
                <tr><td class="py-1 font-mono">state</td><td class="text-gray-400">No</td><td></td></tr>
                <tr><td class="py-1 font-mono">zip</td><td class="text-gray-400">No</td><td></td></tr>
                <tr><td class="py-1 font-mono">phone_no</td><td class="text-gray-400">No</td><td></td></tr>
                <tr><td class="py-1 font-mono">gender</td><td class="text-gray-400">No</td><td></td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
