@extends('layouts.app')

@section('page-title', 'Create New Listing')

@section('breadcrumbs')
    <span class="text-gray-600">Create Listing</span>
@endsection

@section('page-heading')
    Create New Listing
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">

        {{-- Header Info --}}
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h2 class="font-semibold text-yellow-800 mb-1">Create Your Marketplace Listing</h2>
            <p class="text-sm text-yellow-700">Fill in all required fields to publish your product to the marketplace</p>
        </div>

        {{-- Main Form --}}
        <form method="POST" action="{{ route('listings.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            {{-- Product Information --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Product Information</h3>

                <div class="space-y-4">
                    {{-- Title --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Listing Title *</label>
                        <input type="text" name="title" id="title" required
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                               value="{{ old('title') }}"
                               placeholder="Enter a clear, descriptive title">
                        @error('title')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Product Category --}}
                    <div>
                        <label for="product_category_id" class="block text-sm font-medium text-gray-700 mb-1">Product Category *</label>
                        <select name="product_category_id" id="product_category_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">Select category</option>
                            @foreach($productCategories as $category)
                                <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_category_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Product --}}
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                        <select name="product_id" id="product_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">Select product</option>
                            @foreach($productCategories as $category)
                                @foreach($category->products as $product)
                                    <option value="{{ $product->id }}" data-category="{{ $category->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $category->name }} &mdash; {{ $product->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        @error('product_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Descriptions --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description *</label>
                            <textarea name="short_description" id="short_description" rows="3" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                      placeholder="Brief summary of your product">{{ old('short_description') }}</textarea>
                            @error('short_description')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Detailed Description *</label>
                            <textarea name="description" id="description" rows="3" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                      placeholder="Detailed product information">{{ old('description') }}</textarea>
                            @error('description')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Image Upload --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Images (Maximum 3)</label>
                        <input type="file" name="images[]" id="images" multiple accept="image/*"
                               class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-yellow-100 file:text-yellow-700">
                        <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, PNG, GIF. Max size: 2MB per image.</p>
                        @error('images')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Pricing & Inventory --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Pricing & Inventory</h3>

                <div class="grid gap-4 md:grid-cols-2">
                    {{-- Product Price --}}
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Product Price *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="price" id="price" step="0.01" required
                                   class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                   value="{{ old('price') }}"
                                   placeholder="0.00">
                        </div>
                        @error('price')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Shipping Cost --}}
                    <div>
                        <label for="price_shipping" class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="price_shipping" id="price_shipping" step="0.01" required
                                   class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                   value="{{ old('price_shipping') }}"
                                   placeholder="0.00">
                        </div>
                        @error('price_shipping')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Quantity --}}
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Available Quantity *</label>
                        <input type="number" name="quantity" id="quantity" required min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                               value="{{ old('quantity') }}"
                               placeholder="1">
                        @error('quantity')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- End Date --}}
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Listing End Date</label>
                        <input type="date" name="end_date" id="end_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                               value="{{ old('end_date') }}">
                        <p class="text-xs text-gray-500 mt-1">Leave blank for no expiration</p>
                        @error('end_date')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Shipping & Payment --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Shipping & Payment</h3>

                <div class="grid gap-4 md:grid-cols-2">
                    {{-- Shipping Method --}}
                    <div>
                        <label for="shipping_method" class="block text-sm font-medium text-gray-700 mb-1">Shipping Method *</label>
                        <select name="shipping_method" id="shipping_method" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">Select shipping method</option>
                            <option value="shipping" {{ old('shipping_method') == 'shipping' ? 'selected' : '' }}>Standard Shipping</option>
                            <option value="pickup" {{ old('shipping_method') == 'pickup' ? 'selected' : '' }}>Local Pickup</option>
                            <option value="delivery" {{ old('shipping_method') == 'delivery' ? 'selected' : '' }}>Local Delivery</option>
                        </select>
                        @error('shipping_method')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Payment Method --}}
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                        <select name="payment_method" id="payment_method" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">Select payment method</option>
                            <option value="escrow" {{ old('payment_method') == 'escrow' ? 'selected' : '' }}>Escrow Protection</option>
                            <option value="direct" {{ old('payment_method') == 'direct' ? 'selected' : '' }}>Direct Payment</option>
                        </select>
                        @error('payment_method')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Origin Country --}}
                    <div>
                        <label for="origin_country_id" class="block text-sm font-medium text-gray-700 mb-1">Origin Country *</label>
                        <select name="origin_country_id" id="origin_country_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">Select origin country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ old('origin_country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('origin_country_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Destination Country --}}
                    <div>
                        <label for="destination_country_id" class="block text-sm font-medium text-gray-700 mb-1">Destination Country *</label>
                        <select name="destination_country_id" id="destination_country_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">Select destination country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ old('destination_country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('destination_country_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Additional Details --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Additional Details</h3>

                <div class="space-y-4">
                    {{-- Tags --}}
                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Product Tags</label>
                        <input type="text" name="tags" id="tags"
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                               placeholder="electronics, vintage, handmade (separate with commas)"
                               value="{{ old('tags') }}">
                        <p class="text-xs text-gray-500 mt-1">Tags help buyers find your product more easily</p>
                        @error('tags')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Return Policy --}}
                    <div>
                        <label for="return_policy" class="block text-sm font-medium text-gray-700 mb-1">Return Policy</label>
                        <textarea name="return_policy" id="return_policy" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                  placeholder="Describe your return policy, refund conditions, and timeframes...">{{ old('return_policy') }}</textarea>
                        @error('return_policy')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Publish Status --}}
                    <div class="pt-4 border-t border-gray-100">
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" name="is_active" id="is_active"
                                   class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-700">Publish this listing immediately</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-7">Uncheck to save as draft</p>
                    </div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 py-3 px-6 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700">
                    Create Listing
                </button>
                <a href="{{ route('listings.index') }}"
                   class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
