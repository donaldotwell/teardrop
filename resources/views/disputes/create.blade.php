@extends('layouts.app')
@section('page-title', 'Create Dispute')

@section('breadcrumbs')
    <a href="{{ route('orders.show', $order) }}" class="text-amber-700 hover:text-amber-600">
        Order #{{ substr($order->uuid, 0, 8) }}
    </a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600">Create Dispute</span>
@endsection

@section('page-heading')
    Create Dispute
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        {{-- Order Information --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-600 mb-1">Order ID</div>
                    <div class="font-medium">#{{ substr($order->uuid, 0, 8) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Order Date</div>
                    <div class="font-medium">{{ $order->created_at->format('M d, Y') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Product</div>
                    <div class="font-medium">{{ $order->listing->title }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Amount Paid</div>
                    <div class="font-medium">${{ number_format($order->usd_price, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Quantity</div>
                    <div class="font-medium">{{ $order->quantity }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Vendor</div>
                    <div class="font-medium">{{ $order->listing->user->username_pub }}</div>
                </div>
            </div>
        </div>

        {{-- Dispute Form --}}
        <form action="{{ route('disputes.store', $order) }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Dispute Information</h3>

                {{-- Dispute Type --}}
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Dispute Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="">Select dispute type...</option>
                        @foreach($disputeTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Subject --}}
                <div class="mb-6">
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" id="subject" required
                           value="{{ old('subject') }}"
                           placeholder="Brief summary of the issue..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="5" required
                              placeholder="Please provide detailed information about the issue..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">{{ old('description') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Provide as much detail as possible to help resolve the dispute quickly.</p>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Disputed Amount --}}
                <div class="mb-6">
                    <label for="disputed_amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Disputed Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="disputed_amount" id="disputed_amount"
                               step="0.01" min="0" max="{{ $order->usd_price }}" required
                               value="{{ old('disputed_amount', $order->usd_price) }}"
                               class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Maximum amount: ${{ number_format($order->usd_price, 2) }}</p>
                    @error('disputed_amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Buyer Evidence --}}
                <div class="mb-6">
                    <label for="buyer_evidence" class="block text-sm font-medium text-gray-700 mb-2">
                        Initial Evidence/Notes
                    </label>
                    <textarea name="buyer_evidence" id="buyer_evidence" rows="3"
                              placeholder="Any initial evidence or additional notes to support your dispute..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">{{ old('buyer_evidence') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">You can upload files after creating the dispute.</p>
                    @error('buyer_evidence')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Important Information --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="text-sm font-medium text-blue-800 mb-3">Important Information</h4>
                <div class="text-sm text-blue-700 space-y-2">
                    <p>• Once you create a dispute, the vendor will be notified and given a chance to respond.</p>
                    @if($order->listing->payment_method === 'escrow')
                        <p>• Since this is an escrow order, funds will be held until the dispute is resolved.</p>
                    @endif
                    <p>• You'll be able to upload evidence and communicate with the vendor through the dispute system.</p>
                    <p>• An admin will review the case if a resolution cannot be reached between parties.</p>
                    <p>• Please be honest and provide accurate information to ensure fair resolution.</p>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex items-center justify-between space-x-4">
                <a href="{{ route('orders.show', $order) }}"
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                    Cancel
                </a>

                <button type="submit"
                        class="px-6 py-3 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors">
                    Create Dispute
                </button>
            </div>
        </form>
    </div>


@endsection
