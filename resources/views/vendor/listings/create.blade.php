@extends('layouts.vendor')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Create New Listing</h1>
            <p class="text-sm text-gray-600 mt-1">Fill in the details to create your listing</p>
        </div>

        <!-- Form -->
        <form action="{{ route('vendor.listings.store') }}" method="post" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-600">*</span></label>
                <input type="text" name="title" id="title" maxlength="140" required
                       value="{{ old('title') }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Short Description -->
            <div>
                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">Short Description <span class="text-red-600">*</span></label>
                <input type="text" name="short_description" id="short_description" maxlength="255" required
                       value="{{ old('short_description') }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('short_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Full Description <span class="text-red-600">*</span></label>
                <textarea name="description" id="description" rows="6" required
                          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category and Product -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="product_category_id" class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-600">*</span></label>
                    <select name="product_category_id" id="product_category_id" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Select Category</option>
                        @foreach($productCategories as $category)
                            <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">Product <span class="text-red-600">*</span></label>
                    <select name="product_id" id="product_id" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Select Product</option>
                        @foreach($productCategories as $category)
                            @if($category->products->count() > 0)
                                <optgroup label="{{ $category->name }}">
                                    @foreach($category->products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Price and Shipping -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price (USD) <span class="text-red-600">*</span></label>
                    <input type="number" name="price" id="price" step="0.01" min="0" required
                           value="{{ old('price') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price_shipping" class="block text-sm font-medium text-gray-700 mb-2">Shipping Cost (USD) <span class="text-red-600">*</span></label>
                    <input type="number" name="price_shipping" id="price_shipping" step="0.01" min="0" required
                           value="{{ old('price_shipping', 0) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('price_shipping')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity <span class="text-red-600">*</span></label>
                    <input type="number" name="quantity" id="quantity" min="1" required
                           value="{{ old('quantity', 1) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Countries -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="origin_country_id" class="block text-sm font-medium text-gray-700 mb-2">Origin Country <span class="text-red-600">*</span></label>
                    <select name="origin_country_id" id="origin_country_id" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('origin_country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('origin_country_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="destination_country_id" class="block text-sm font-medium text-gray-700 mb-2">Destination Country <span class="text-red-600">*</span></label>
                    <select name="destination_country_id" id="destination_country_id" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('destination_country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('destination_country_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Shipping and Payment Methods -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="shipping_method" class="block text-sm font-medium text-gray-700 mb-2">Shipping Method <span class="text-red-600">*</span></label>
                    <select name="shipping_method" id="shipping_method" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="shipping" {{ old('shipping_method') == 'shipping' ? 'selected' : '' }}>Shipping</option>
                        <option value="pickup" {{ old('shipping_method') == 'pickup' ? 'selected' : '' }}>Pickup</option>
                        <option value="delivery" {{ old('shipping_method') == 'delivery' ? 'selected' : '' }}>Delivery</option>
                    </select>
                    @error('shipping_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method <span class="text-red-600">*</span></label>
                    <select name="payment_method" id="payment_method" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="escrow" {{ old('payment_method') == 'escrow' ? 'selected' : '' }}>Escrow</option>
                        <option value="direct" {{ old('payment_method') == 'direct' ? 'selected' : '' }}>Direct</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Images -->
            <div>
                <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Product Images (1-3 images) <span class="text-red-600">*</span></label>
                <input type="file" name="images[]" id="images" accept="image/jpeg,image/png,image/jpg,image/gif" multiple required
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <p class="mt-1 text-xs text-gray-500">Maximum 3 images, 2MB each. Formats: JPEG, PNG, JPG, GIF</p>
                @error('images')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tags -->
            <div>
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags (comma-separated)</label>
                <input type="text" name="tags" id="tags" placeholder="e.g., organic, premium, handmade"
                       value="{{ old('tags') }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('tags')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Return Policy -->
            <div>
                <label for="return_policy" class="block text-sm font-medium text-gray-700 mb-2">Return Policy <span class="text-red-600">*</span></label>
                <textarea name="return_policy" id="return_policy" rows="3" required
                          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('return_policy') }}</textarea>
                @error('return_policy')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- End Date -->
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date (optional)</label>
                <input type="date" name="end_date" id="end_date"
                       value="{{ old('end_date') }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('end_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                       class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Set listing as active</label>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="px-6 py-2.5 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors">
                    Create Listing
                </button>
                <a href="{{ route('vendor.listings.index') }}"
                   class="px-6 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
