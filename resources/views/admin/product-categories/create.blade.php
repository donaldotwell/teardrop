@extends('layouts.admin')

@section('page-title', 'Create Category')

@section('breadcrumbs')
    <a href="{{ route('admin.product-categories.index') }}" class="text-yellow-700 hover:text-yellow-800">Categories</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-900">Create</span>
@endsection

@section('page-heading', 'Create New Category')
@section('page-description', 'Add a new product category')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.product-categories.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Category Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Category Name <span class="text-red-600">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name') }}"
                       maxlength="50"
                       required
                       placeholder="Enter category name"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500
                              @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Maximum 50 characters. Must be unique.</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm">
                <p class="text-blue-800">
                    <strong>Note:</strong> After creating the category, you can configure early finalization settings
                    and add products through the edit page.
                </p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center space-x-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="px-6 py-2 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700">
                    Create Category
                </button>
                <a href="{{ route('admin.product-categories.index') }}"
                   class="px-6 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
