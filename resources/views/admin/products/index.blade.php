@extends('layouts.admin')

@section('page-title', 'Products Management')

@section('breadcrumbs')
    <span class="text-gray-900">Products</span>
@endsection

@section('page-heading', 'Products Management')
@section('page-description', 'Manage all products across categories')

@section('content')
<div class="space-y-6">

    <!-- Header with Create Button -->
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex space-x-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search products..."
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">

                <select name="category"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Search
                </button>

                @if(request('search') || request('category'))
                    <a href="{{ route('admin.products.index') }}"
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <a href="{{ route('admin.products.create') }}"
           class="px-6 py-2 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700">
            Create Product
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-yellow-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Product Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Active Listings</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600">{{ $product->productCategory->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded {{ $product->listings_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $product->listings_count }} listing{{ $product->listings_count != 1 ? 's' : '' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $product->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-3">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="text-yellow-600 hover:text-yellow-900 font-medium">Edit</a>

                            @if($product->listings_count == 0)
                                <form action="{{ route('admin.products.destroy', $product) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-900 font-medium">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400" title="Cannot delete: has active listings">Delete</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <p class="mb-2">No products found.</p>
                            <a href="{{ route('admin.products.create') }}"
                               class="text-yellow-600 hover:text-yellow-800 font-medium">
                                Create your first product
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-lg">
            {{ $products->links() }}
        </div>
    @endif

    <!-- Stats -->
    <div class="bg-gray-50 rounded-lg p-4 text-sm">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <p class="text-gray-600">Total Products</p>
                <p class="text-2xl font-bold text-gray-900">{{ $products->total() }}</p>
            </div>
            <div>
                <p class="text-gray-600">Categories</p>
                <p class="text-2xl font-bold text-gray-900">{{ $categories->count() }}</p>
            </div>
            <div>
                <p class="text-gray-600">Showing</p>
                <p class="text-2xl font-bold text-gray-900">{{ $products->count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
