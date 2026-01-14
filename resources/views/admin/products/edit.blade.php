@extends('layouts.admin')

@section('page-title', 'Edit Product')

@section('breadcrumbs')
    <a href="{{ route('admin.products.index') }}" class="text-yellow-700 hover:text-yellow-800">Products</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-900">Edit</span>
@endsection

@section('page-heading', 'Edit Product')
@section('page-description', 'Update product details')

@section('content')
<div class="max-w-2xl">

    <!-- Warning if product has listings -->
    @if($listingsCount > 0)
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-800">Active Listings Warning</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        This product has <strong>{{ $listingsCount }} active listing(s)</strong>.
                        Changing the category will affect all associated listings.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.products.update', $product) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Category -->
            <div>
                <label for="product_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Category <span class="text-red-600">*</span>
                </label>
                <select name="product_category_id"
                        id="product_category_id"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500
                               @error('product_category_id') border-red-500 @enderror">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                                {{ (old('product_category_id', $product->product_category_id) == $category->id) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('product_category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Product Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Product Name <span class="text-red-600">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', $product->name) }}"
                       maxlength="50"
                       required
                       placeholder="Enter product name"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500
                              @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Maximum 50 characters. Must be unique within the selected category.</p>
            </div>

            <!-- Product Info -->
            <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Product UUID:</span>
                    <span class="font-mono text-gray-900">{{ $product->uuid }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Created:</span>
                    <span class="text-gray-900">{{ $product->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Updated:</span>
                    <span class="text-gray-900">{{ $product->updated_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Listings:</span>
                    <span class="text-gray-900 font-semibold">{{ $listingsCount }}</span>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="flex items-center space-x-4">
                    <button type="submit"
                            class="px-6 py-2 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700">
                        Update Product
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                       class="px-6 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200">
                        Cancel
                    </a>
                </div>

                @if($listingsCount == 0)
                    <form action="{{ route('admin.products.destroy', $product) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-6 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700">
                            Delete Product
                        </button>
                    </form>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
