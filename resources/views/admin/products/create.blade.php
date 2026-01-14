@extends('layouts.admin')

@section('page-title', 'Create Product')

@section('breadcrumbs')
    <a href="{{ route('admin.products.index') }}" class="text-yellow-700 hover:text-yellow-800">Products</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-900">Create</span>
@endsection

@section('page-heading', 'Create New Product')
@section('page-description', 'Add a new product to the marketplace')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.products.store') }}" method="POST" class="space-y-6">
            @csrf

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
                        <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
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
                       value="{{ old('name') }}"
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

            <!-- Submit Buttons -->
            <div class="flex items-center space-x-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="px-6 py-2 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700">
                    Create Product
                </button>
                <a href="{{ route('admin.products.index') }}"
                   class="px-6 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
