@extends('layouts.admin')
@section('page-title', 'Listings Management')

@section('breadcrumbs')
    <span class="text-gray-600">Listings</span>
@endsection

@section('page-heading')
    Listings Management
@endsection

@section('page-description')
    Manage marketplace listings, featured items, and vendor content
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Total Listings</div>
                <div class="text-2xl font-semibold text-gray-900">{{ $stats['total_listings'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Active</div>
                <div class="text-2xl font-semibold text-green-600">{{ $stats['active_listings'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Featured</div>
                <div class="text-2xl font-semibold text-yellow-600">{{ $stats['featured_listings'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Inactive</div>
                <div class="text-2xl font-semibold text-red-600">{{ $stats['inactive_listings'] ?? 0 }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Total Views</div>
                <div class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_views'] ?? 0) }}</div>
            </div>
        </div>

        {{-- Filters and Search --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="GET" action="{{ route('admin.listings.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    {{-- Search --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Title, ID, vendor..."
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    {{-- Featured Filter --}}
                    <div>
                        <label for="featured" class="block text-sm font-medium text-gray-700 mb-1">Featured</label>
                        <select name="featured"
                                id="featured"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Listings</option>
                            <option value="yes" {{ request('featured') == 'yes' ? 'selected' : '' }}>Featured</option>
                            <option value="no" {{ request('featured') == 'no' ? 'selected' : '' }}>Not Featured</option>
                        </select>
                    </div>

                    {{-- Category Filter --}}
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category"
                                id="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->uuid }}" {{ request('category') == $category->uuid ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort Options --}}
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                        <select name="sort"
                                id="sort"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                            <option value="updated_at" {{ request('sort') == 'updated_at' ? 'selected' : '' }}>Last Updated</option>
                            <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Title</option>
                            <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Price</option>
                            <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Views</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Filter Listings
                    </button>
                    <a href="{{ route('admin.listings.index') }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                        Clear Filters
                    </a>
                    <a href="{{ route('admin.listings.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Export CSV
                    </a>
                </div>
            </form>
        </div>

        {{-- Bulk Actions --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <form action="{{ route('admin.listings.bulk') }}" method="POST" id="bulk-form" class="flex items-center gap-4">
                @csrf
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>
                    <select name="action"
                            class="px-3 py-1 border border-gray-300 rounded focus:outline-none focus:border-yellow-500">
                        <option value="">Select Action</option>
                        <option value="feature">Feature Selected</option>
                        <option value="unfeature">Unfeature Selected</option>
                        <option value="enable">Enable Selected</option>
                        <option value="disable">Disable Selected</option>
                    </select>
                    <button type="submit"
                            class="px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Apply
                    </button>
                </div>
                <div class="text-sm text-gray-500">
                    <span id="selected-count">0</span> listing(s) selected
                </div>
            </form>
        </div>

        {{-- Listings Table --}}
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Listings ({{ $listings->total() }} total)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="select-all" class="w-4 h-4 text-yellow-600 border-gray-300 rounded">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Listing</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($listings as $listing)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <input type="checkbox"
                                       name="listing_ids[]"
                                       value="{{ $listing->id }}"
                                       class="listing-checkbox w-4 h-4 text-yellow-600 border-gray-300 rounded"
                                       form="bulk-form">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                                            {{ $listing->title }}
                                            @if($listing->is_featured)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Featured
                                                    </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">ID: {{ substr($listing->uuid, 0, 8) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-yellow-700 font-medium text-sm">{{ substr($listing->user->username_pub, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $listing->user->username_pub }}</div>
                                        <div class="text-sm text-gray-500">TL{{ $listing->user->trust_level }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $listing->product->productCategory->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">${{ number_format($listing->price, 2) }}</div>
                                <div class="text-sm text-gray-500">+${{ number_format($listing->price_shipping, 2) }} shipping</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($listing->is_active)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($listing->views) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $listing->created_at->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $listing->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.listings.show', $listing) }}"
                                       class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                        View
                                    </a>

                                    @if($listing->is_featured)
                                        <form action="{{ route('admin.listings.unfeature', $listing) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                                Unfeature
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.listings.feature', $listing) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">
                                                Feature
                                            </button>
                                        </form>
                                    @endif

                                    @if($listing->is_active)
                                        <form action="{{ route('admin.listings.disable', $listing) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">
                                                Disable
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.listings.enable', $listing) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">
                                                Enable
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                No listings found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($listings->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $listings->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
