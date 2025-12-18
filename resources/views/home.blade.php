{{-- resources/views/home/index.blade.php --}}
@extends('layouts.app')
@section('page-title', 'Marketplace')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- User Profile Card --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-yellow-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <span class="text-white text-xl font-bold">
                            {{ substr(auth()->user()->username_pub, 0, 1) }}
                        </span>
                    </div>
                    <h3 class="font-semibold text-gray-900">{{ auth()->user()->username_pub }}</h3>
                    <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-3 py-1 rounded-full mt-2">
                        Trust Level {{ auth()->user()->trust_level }}
                    </span>
                </div>

                {{-- Wallet Balances --}}
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Bitcoin</div>
                            <div class="text-xs text-gray-500">BTC</div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono text-sm">{{ $user_balance['btc']['balance'] }}</div>
                            <div class="text-xs text-gray-500">${{ number_format($user_balance['btc']['usd_value'], 2) }}</div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Monero</div>
                            <div class="text-xs text-gray-500">XMR</div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono text-sm">{{ $user_balance['xmr']['balance'] }}</div>
                            <div class="text-xs text-gray-500">${{ number_format($user_balance['xmr']['usd_value'], 2) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="space-y-2">
                    <a href="{{ route('orders.index') }}"
                       class="block w-full text-center py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors">
                        My Orders
                    </a>
                    <a href="{{ route('profile.show') }}"
                       class="block w-full text-center py-2 border border-yellow-600 text-yellow-600 rounded hover:bg-yellow-600 hover:text-white transition-colors">
                        View Profile
                    </a>
                </div>
            </div>

            {{-- Quick Search --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Quick Search</h3>
                <form method="GET" action="{{ route('home') }}" class="space-y-3">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search listings..."
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500">
                    <button type="submit"
                            class="w-full py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                        Search
                    </button>
                </form>
            </div>

            {{-- Categories --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Categories</h3>
                <div class="space-y-2">
                    {{-- Filter Options --}}
                    <div class="pb-3 mb-3 border-b border-gray-200 space-y-2">
                        <a href="{{ route('home') }}"
                           class="flex items-center justify-between p-2 rounded transition-colors
                                  {{ !request('filter') ? 'bg-yellow-100 hover:bg-yellow-200' : 'hover:bg-gray-50' }}">
                            <span class="text-sm font-medium {{ !request('filter') ? 'text-yellow-700' : 'text-gray-700' }}">
                                All Sections
                            </span>
                        </a>
                        <a href="{{ route('home', ['filter' => 'all'] + request()->except('filter')) }}"
                           class="flex items-center justify-between p-2 rounded transition-colors
                                  {{ request('filter') === 'all' ? 'bg-yellow-100 hover:bg-yellow-200' : 'hover:bg-gray-50' }}">
                            <span class="text-sm font-medium {{ request('filter') === 'all' ? 'text-yellow-700' : 'text-gray-700' }}">
                                View All Listings
                            </span>
                        </a>
                        <a href="{{ route('home', ['filter' => 'featured'] + request()->except('filter')) }}"
                           class="flex items-center justify-between p-2 rounded transition-colors
                                  {{ request('filter') === 'featured' ? 'bg-yellow-100 hover:bg-yellow-200' : 'hover:bg-gray-50' }}">
                            <span class="text-sm font-medium {{ request('filter') === 'featured' ? 'text-yellow-700' : 'text-gray-700' }}">
                                Featured Only
                            </span>
                            <span class="px-2 py-0.5 bg-yellow-200 text-yellow-900 text-xs rounded-full">★</span>
                        </a>
                    </div>

                    @foreach($productCategories as $category)
                        @php
                            $isCategoryActive = request('cat') === $category->uuid;
                            $hasActiveSubcategory = $category->products->contains('uuid', request('scat'));
                        @endphp
                        <div class="category-group">
                            <input type="checkbox" id="cat-{{ $category->uuid }}" class="hidden peer" {{ ($isCategoryActive || $hasActiveSubcategory) ? 'checked' : '' }}>
                            <label for="cat-{{ $category->uuid }}" class="flex items-center justify-between p-2 rounded transition-colors cursor-pointer group hover:bg-gray-50">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium {{ $isCategoryActive ? 'text-yellow-700' : 'text-gray-700' }} group-hover:text-yellow-700">
                                        {{ $category->name }}
                                    </span>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $isCategoryActive ? 'bg-yellow-200 text-yellow-900' : 'bg-gray-200 text-gray-700' }}">
                                        {{ $category->listings_count }}
                                    </span>
                                </div>
                                <span class="text-gray-400 transform transition-transform peer-checked:rotate-90">→</span>
                            </label>

                            {{-- Subcategories (Products) --}}
                            @if($category->products->isNotEmpty())
                                <div class="ml-4 space-y-1 bg-gray-50 rounded p-2 hidden peer-checked:block">
                                    @foreach($category->products as $product)
                                        @php
                                            $isSubcategoryActive = request('scat') === $product->uuid && $isCategoryActive;
                                        @endphp
                                        <a href="{{ route('home', ['cat' => $category->uuid, 'scat' => $product->uuid]) }}"
                                           class="flex items-center justify-between py-1 px-2 rounded transition-colors
                                                  {{ $isSubcategoryActive ? 'bg-yellow-200' : 'hover:bg-white' }}">
                                            <span class="text-sm {{ $isSubcategoryActive ? 'text-yellow-700 font-medium' : 'text-gray-600' }}">
                                                {{ $product->name }}
                                            </span>
                                            <span class="text-xs px-2 py-0.5 rounded-full
                                                       {{ $isSubcategoryActive ? 'bg-yellow-300 text-yellow-900' : 'bg-gray-200 text-gray-700' }}">
                                                {{ $product->listings_count }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Featured/Featured Listings --}}
            @if($featured_listings->isNotEmpty())
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Featured Listings</h2>
                            <p class="text-sm text-gray-500">Premium featured vendors</p>
                        </div>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                            Featured
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($featured_listings as $listing)
                            <div class="border-2 border-yellow-200 rounded-lg overflow-hidden hover:border-yellow-400 hover:shadow-lg transition-all">
                                {{-- Listing Image --}}
                                @if($listing->media->isNotEmpty())
                                    <div class="aspect-video bg-gray-100 overflow-hidden relative">
                                        <img src="{{ $listing->media->first()->data_uri }}"
                                             alt="{{ $listing->title }}"
                                             class="w-full h-full object-cover transition-transform duration-300 hover:scale-110">
                                        <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                                            FEATURED
                                        </div>
                                    </div>
                                @else
                                    <div class="aspect-video bg-gray-200 flex items-center justify-center relative">
                                        <span class="text-gray-400 text-sm">No Image</span>
                                        <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                                            FEATURED
                                        </div>
                                    </div>
                                @endif

                                <div class="p-4 bg-yellow-50 flex flex-col" style="min-height: 320px;">
                                    {{-- Listing Header --}}
                                    <div class="mb-3 flex-shrink-0">
                                        <h3 class="font-medium text-gray-900 mb-2 line-clamp-2 min-h-[3rem]">
                                            {{ $listing->title }}
                                        </h3>
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                            {{ $listing->short_description }}
                                        </p>
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-gray-500">Vendor:</span>
                                                <a href="{{ route('vendor.show', $listing->user) }}" class="font-medium text-yellow-700 hover:text-yellow-800 hover:underline">
                                                    {{ $listing->user->username_pub }}
                                                </a>
                                                <span class="bg-yellow-200 text-yellow-900 text-xs px-2 py-0.5 rounded">
                                                    TL{{ $listing->user->trust_level }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Shipping Route --}}
                                    <div class="mb-3 p-2 bg-white rounded text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-700">{{ $listing->originCountry->name }}</span>
                                            <span class="text-gray-400 mx-2">→</span>
                                            <span class="font-medium text-gray-700">{{ $listing->destinationCountry->name }}</span>
                                        </div>
                                    </div>

                                    {{-- Price & Stock --}}
                                    <div class="mb-3 flex items-center justify-between">
                                        <div>
                                            <div class="text-2xl font-bold text-yellow-700">
                                                ${{ number_format($listing->price, 2) }}
                                            </div>
                                            @if($listing->quantity)
                                                <div class="text-xs text-gray-600">
                                                    {{ $listing->quantity }} available
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500">Shipping</div>
                                            <div class="text-sm font-medium text-gray-700">
                                                ${{ number_format($listing->price_shipping, 2) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Meta Info --}}
                                    <div class="mb-3 flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ $listing->views }} views</span>
                                        <span>{{ $listing->created_at->diffForHumans() }}</span>
                                    </div>

                                    {{-- Action Button --}}
                                    <a href="{{ route('listings.show', $listing) }}"
                                       class="block w-full text-center py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors font-medium">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- All Listings Combined (when filter=all) --}}
            @if($filter === 'all' && $all_listings->isNotEmpty())
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">All Listings</h2>
                            <p class="text-sm text-gray-500">All marketplace products combined</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($all_listings as $listing)
                            <div class="border {{ $listing->is_featured ? 'border-2 border-yellow-200' : 'border-gray-200' }} rounded-lg overflow-hidden hover:border-yellow-300 hover:shadow-md transition-all">
                                {{-- Listing Image --}}
                                @if($listing->media->isNotEmpty())
                                    <div class="aspect-video bg-gray-100 overflow-hidden relative">
                                        <img src="{{ $listing->media->first()->data_uri }}"
                                             alt="{{ $listing->title }}"
                                             class="w-full h-full object-cover transition-transform duration-300 hover:scale-110">
                                        @if($listing->is_featured)
                                            <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                                                FEATURED
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="aspect-video bg-gray-200 flex items-center justify-center relative">
                                        <span class="text-gray-400 text-sm">No Image</span>
                                        @if($listing->is_featured)
                                            <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                                                FEATURED
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="p-4 flex flex-col {{ $listing->is_featured ? 'bg-yellow-50' : '' }}" style="min-height: 320px;">
                                    {{-- Listing Header --}}
                                    <div class="mb-3 flex-shrink-0">
                                        <h3 class="font-medium text-gray-900 mb-2 line-clamp-2 min-h-[3rem]">
                                            {{ $listing->title }}
                                        </h3>
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                            {{ $listing->short_description }}
                                        </p>
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-gray-500">Vendor:</span>
                                                <a href="{{ route('vendor.show', $listing->user) }}" class="font-medium {{ $listing->is_featured ? 'text-yellow-700 hover:text-yellow-800' : 'text-yellow-600 hover:text-yellow-700' }} hover:underline">
                                                    {{ $listing->user->username_pub }}
                                                </a>
                                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded">
                                                    TL{{ $listing->user->trust_level }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Shipping Route --}}
                                    <div class="mb-3 p-2 {{ $listing->is_featured ? 'bg-white' : 'bg-gray-50' }} rounded text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-700">{{ $listing->originCountry->name }}</span>
                                            <span class="text-gray-400 mx-2">→</span>
                                            <span class="font-medium text-gray-700">{{ $listing->destinationCountry->name }}</span>
                                        </div>
                                    </div>

                                    {{-- Price & Stock --}}
                                    <div class="mb-3 flex items-center justify-between">
                                        <div>
                                            <div class="text-2xl font-bold {{ $listing->is_featured ? 'text-yellow-700' : 'text-yellow-600' }}">
                                                ${{ number_format($listing->price, 2) }}
                                            </div>
                                            @if($listing->quantity)
                                                <div class="text-xs text-gray-500">
                                                    {{ $listing->quantity }} available
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500">Shipping</div>
                                            <div class="text-sm font-medium text-gray-700">
                                                ${{ number_format($listing->price_shipping, 2) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Meta Info --}}
                                    <div class="mb-3 flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ $listing->views }} views</span>
                                        <span>{{ $listing->created_at->diffForHumans() }}</span>
                                    </div>

                                    {{-- Action Button --}}
                                    <a href="{{ route('listings.show', $listing) }}"
                                       class="block w-full text-center py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($all_listings->hasPages())
                        <div class="mt-6">
                            {{ $all_listings->appends(request()->except('page'))->links() }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Featured Only View (when filter=featured) --}}
            @if($filter === 'featured' && $all_listings->isNotEmpty())
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Featured Listings Only</h2>
                            <p class="text-sm text-gray-500">Premium featured products</p>
                        </div>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                            Featured
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($all_listings as $listing)
                            <div class="border-2 border-yellow-200 rounded-lg overflow-hidden hover:border-yellow-400 hover:shadow-lg transition-all">
                                {{-- Listing Image --}}
                                @if($listing->media->isNotEmpty())
                                    <div class="aspect-video bg-gray-100 overflow-hidden relative">
                                        <img src="{{ $listing->media->first()->data_uri }}"
                                             alt="{{ $listing->title }}"
                                             class="w-full h-full object-cover transition-transform duration-300 hover:scale-110">
                                        <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                                            FEATURED
                                        </div>
                                    </div>
                                @else
                                    <div class="aspect-video bg-gray-200 flex items-center justify-center relative">
                                        <span class="text-gray-400 text-sm">No Image</span>
                                        <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                                            FEATURED
                                        </div>
                                    </div>
                                @endif

                                <div class="p-4 bg-yellow-50 flex flex-col" style="min-height: 320px;">
                                    {{-- Same content as featured listings above --}}
                                    <div class="mb-3 flex-shrink-0">
                                        <h3 class="font-medium text-gray-900 mb-2 line-clamp-2 min-h-[3rem]">
                                            {{ $listing->title }}
                                        </h3>
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                            {{ $listing->short_description }}
                                        </p>
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-gray-500">Vendor:</span>
                                                <a href="{{ route('vendor.show', $listing->user) }}" class="font-medium text-yellow-700 hover:text-yellow-800 hover:underline">
                                                    {{ $listing->user->username_pub }}
                                                </a>
                                                <span class="bg-yellow-200 text-yellow-900 text-xs px-2 py-0.5 rounded">
                                                    TL{{ $listing->user->trust_level }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 p-2 bg-white rounded text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-700">{{ $listing->originCountry->name }}</span>
                                            <span class="text-gray-400 mx-2">→</span>
                                            <span class="font-medium text-gray-700">{{ $listing->destinationCountry->name }}</span>
                                        </div>
                                    </div>

                                    <div class="mb-3 flex items-center justify-between">
                                        <div>
                                            <div class="text-2xl font-bold text-yellow-700">
                                                ${{ number_format($listing->price, 2) }}
                                            </div>
                                            @if($listing->quantity)
                                                <div class="text-xs text-gray-600">
                                                    {{ $listing->quantity }} available
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500">Shipping</div>
                                            <div class="text-sm font-medium text-gray-700">
                                                ${{ number_format($listing->price_shipping, 2) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ $listing->views }} views</span>
                                        <span>{{ $listing->created_at->diffForHumans() }}</span>
                                    </div>

                                    <a href="{{ route('listings.show', $listing) }}"
                                       class="block w-full text-center py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors font-medium">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($all_listings->hasPages())
                        <div class="mt-6">
                            {{ $all_listings->appends(request()->except('page'))->links() }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Regular Listings (default view only) --}}
            @if(!$filter && $regular_listings->isNotEmpty())
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">All Listings</h2>
                        <p class="text-sm text-gray-500">Browse marketplace products</p>
                    </div>
                    <a href="{{ route('listings.index', ['filter' => 'all']) }}" class="text-sm text-yellow-600 hover:text-yellow-700">
                        View All →
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($regular_listings as $listing)
                        <div class="border border-gray-200 rounded-lg overflow-hidden hover:border-yellow-300 hover:shadow-md transition-all">
                            {{-- Listing Image --}}
                            @if($listing->media->isNotEmpty())
                                <div class="aspect-video bg-gray-100 overflow-hidden">
                                    <img src="{{ $listing->media->first()->data_uri }}"
                                         alt="{{ $listing->title }}"
                                         class="w-full h-full object-cover transition-transform duration-300 hover:scale-110">
                                </div>
                            @else
                                <div class="aspect-video bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-400 text-sm">No Image</span>
                                </div>
                            @endif

                            <div class="p-4 flex flex-col" style="min-height: 320px;">
                                {{-- Listing Header --}}
                                <div class="mb-3 flex-shrink-0">
                                    <h3 class="font-medium text-gray-900 mb-2 line-clamp-2 min-h-[3rem]">
                                        {{ $listing->title }}
                                    </h3>
                                    <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                        {{ $listing->short_description }}
                                    </p>
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-gray-500">Vendor:</span>
                                            <a href="{{ route('vendor.show', $listing->user) }}" class="font-medium text-yellow-600 hover:text-yellow-700 hover:underline">
                                                {{ $listing->user->username_pub }}
                                            </a>
                                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded">
                                                TL{{ $listing->user->trust_level }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Shipping Route --}}
                                <div class="mb-3 p-2 bg-gray-50 rounded text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-gray-700">{{ $listing->originCountry->name }}</span>
                                        <span class="text-gray-400 mx-2">→</span>
                                        <span class="font-medium text-gray-700">{{ $listing->destinationCountry->name }}</span>
                                    </div>
                                </div>

                                {{-- Price & Stock --}}
                                <div class="mb-3 flex items-center justify-between">
                                    <div>
                                        <div class="text-2xl font-bold text-yellow-600">
                                            ${{ number_format($listing->price, 2) }}
                                        </div>
                                        @if($listing->quantity)
                                            <div class="text-xs text-gray-500">
                                                {{ $listing->quantity }} available
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">Shipping</div>
                                        <div class="text-sm font-medium text-gray-700">
                                            ${{ number_format($listing->price_shipping, 2) }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Meta Info --}}
                                <div class="mb-3 flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ $listing->views }} views</span>
                                    <span>{{ $listing->created_at->diffForHumans() }}</span>
                                </div>

                                {{-- Action Button --}}
                                <a href="{{ route('listings.show', $listing) }}"
                                   class="block w-full text-center py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <div class="text-gray-500 mb-4">
                                @if(request('search'))
                                    No listings found matching your search.
                                @elseif(request('cat') || request('scat'))
                                    No listings in this category.
                                @else
                                    No listings available at the moment.
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($regular_listings->hasPages())
                    <div class="mt-6">
                        {{ $regular_listings->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
@endsection
