@extends('layouts.admin')

@section('title', 'Product Categories - Early Finalization Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Product Categories</h1>
            <div class="text-sm text-gray-600 mt-1">
                Configure early finalization settings per category
            </div>
        </div>
        <a href="{{ route('admin.product-categories.create') }}"
           class="px-6 py-2 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700">
            Create Category
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Categories Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-amber-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Active Listings</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Early Finalization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Window</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Min Vendor Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr class="{{ $category->allows_early_finalization ? 'bg-amber-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="{{ route('admin.product-categories.toggle-status', $category) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-2 py-1 text-xs rounded {{ $category->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $category->products->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $category->listings_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded {{ $category->allows_early_finalization ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $category->allows_early_finalization ? 'Enabled' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($category->finalizationWindow)
                                {{ $category->finalizationWindow->name }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Level {{ $category->min_vendor_level_for_early }}+
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('admin.product-categories.edit', $category) }}"
                               class="text-amber-600 hover:text-amber-900 font-medium">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No product categories found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Legend -->
    <div class="bg-gray-50 rounded-lg p-4 text-sm">
        <h3 class="font-semibold text-gray-900 mb-2">Legend</h3>
        <ul class="space-y-1 text-gray-600">
            <li><span class="inline-block w-3 h-3 bg-amber-50 border border-amber-200 mr-2"></span> Category has early finalization enabled</li>
            <li><strong>Min Vendor Level:</strong> Minimum vendor level required to use early finalization in this category</li>
            <li><strong>Window:</strong> The dispute window applied to orders in this category</li>
        </ul>
    </div>
</div>
@endsection
