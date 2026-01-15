@extends('layouts.app')
@section('page-title', $listing->title)

@section('content')
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Sidebar -->
        <div class="w-full lg:w-80 space-y-6">
            <!-- Vendor Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="space-y-6">
                    <h2 class="text-xl font-bold text-yellow-700 mb-4">Vendor Details</h2>
                    <div class="flex items-start gap-4">
                        <div class="shrink-0">
                            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                                <span class="text-2xl font-bold text-yellow-700">V</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-sm">
                                <span class="font-medium text-gray-600">Name:</span>
                                <a href="{{ route('vendor.show', $listing->user) }}" class="text-yellow-700 hover:text-yellow-800 hover:underline font-medium">
                                    {{ $listing->user->username_pub }}
                                </a>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Rating 4.5</span>
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Sales 100</span>
                            </div>
                            <div class="text-sm">
                                <span class="font-medium text-gray-600">Listings:</span>
                                <span class="text-yellow-700">50 Active</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-modal
                            id="pgpModal"
                            title="Vendor PGP Key"
                            triggerText="View PGP Key"
                            triggerClass="block w-full text-center py-2.5 text-sm border-2 border-yellow-700 text-yellow-700 rounded-lg hover:bg-yellow-700 hover:text-white transition-colors duration-200"
                        >
                            <div class="space-y-4">
                                <label class="block text-sm font-medium text-gray-700">PGP Public Key</label>
                                <textarea
                                    class="w-full h-48 p-3 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                    readonly
                                >{{ $listing->user->pgp_pub_key }}</textarea>
                            </div>
                            <x-slot:footer>
                                <label for="pgpModal" class="block w-full px-4 py-2.5 text-center border-2 border-yellow-700 text-yellow-700 rounded-lg hover:bg-yellow-700 hover:text-white transition-colors duration-200 cursor-pointer">
                                    Close
                                </label>
                            </x-slot:footer>
                        </x-modal>
                    </div>
                </div>
            </div>

            <!-- Categories Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <h2 class="text-xl font-bold text-yellow-700 mb-4">Categories</h2>
                <div class="space-y-1">
                    @foreach($productCategories as $category)
                        <div class="category-group">
                            <input type="checkbox" id="cat-{{ $category->uuid }}" class="hidden peer">
                            <label for="cat-{{ $category->uuid }}" class="flex items-center justify-between cursor-pointer group p-1 hover:bg-gray-50 rounded-lg transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-700 group-hover:text-yellow-700">{{ $category->name }}</span>
                                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">{{ $category->listings_count }}</span>
                                </div>
                                <span class="text-gray-400 transform transition-transform peer-checked:rotate-90">→</span>
                            </label>

                            @if($category->products->isNotEmpty())
                                <div class="pl-4 mt-1 space-y-1 hidden peer-checked:block">
                                    @foreach($category->products as $product)
                                        <a href="{{ route('home', ['cat' => $category->uuid, 'scat' => $product->uuid]) }}"
                                           class="flex items-center justify-between text-sm text-gray-600 hover:text-yellow-700 px-2 py-1.5 rounded hover:bg-gray-50">
                                            <span>{{ $product->name }}</span>
                                            <span class="text-xs bg-yellow-50 text-yellow-800 px-2 py-0.5 rounded-full">{{ $product->listings_count }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <!-- Product Header -->
                <div class="p-6 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $listing->title }}</h1>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-sm text-gray-600">Listed by</span>
                        <a href="{{ route('vendor.show', $listing->user) }}" class="text-sm text-yellow-700 hover:text-yellow-800 hover:underline font-medium">
                            {{ $listing->user->username_pub }}
                        </a>
                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">Trust Level {{ $listing->user->trust_level }}</span>
                    </div>
                </div>

                <!-- Product Body -->
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row gap-8">
                        <!-- Image Section -->
                        <div class="lg:w-1/3">
                            <div class="group relative">
                                @if($listing->media->first())
                                    <img src="{{ $listing->media->first()->data_uri }}"
                                         alt="{{ $listing->title }}"
                                         class="w-full h-96 object-contain rounded-xl bg-gray-50 p-4 border border-gray-200 transition-transform duration-300 group-hover:scale-105">
                                @else
                                    <div class="w-full h-96 flex items-center justify-center rounded-xl bg-gray-100 border border-gray-200">
                                        <span class="text-gray-400 text-lg">No Image</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Details Section -->
                        <div class="flex-1 space-y-6">
                            <!-- Pricing Card -->
                            <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                                <div class="space-y-6">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-bold text-yellow-700">${{ number_format($listing->price, 2) }}</span>
                                        <span class="text-sm text-gray-600">USD</span>
                                    </div>

                                    @if($listing->user_id === auth()->id())
                                        <!-- Message for vendor viewing their own listing -->
                                        <div class="bg-amber-100 border border-amber-300 rounded-lg p-4">
                                            <p class="text-sm text-amber-900 font-medium">This is your listing</p>
                                            <p class="text-sm text-amber-700 mt-1">You cannot purchase your own products.</p>
                                        </div>
                                    @else
                                        <form action="{{ route('orders.create', $listing) }}" method="get" class="space-y-4">
                                            @csrf
                                            <input type="hidden" name="listing_id" value="{{ $listing->id }}">

                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                                    <input type="number"
                                                           name="quantity"
                                                           id="quantity"
                                                           min="1"
                                                           value="1"
                                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Currency</label>
                                                    <select name="currency"
                                                            id="currency"
                                                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors">
                                                        <option value="btc">Bitcoin (BTC) - {{ $btcAmount }} BTC</option>
                                                        <option value="xmr">Monero (XMR) - {{ $xmrAmount }} XMR</option>
                                                    </select>
                                                    @error('currency')
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            <button type="submit"
                                                    class="w-full py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition-colors duration-200">
                                                Purchase Now
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <!-- Product Meta -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-yellow-700 font-bold">O</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Origin:</span>
                                        <span class="ml-2 text-gray-800">{{ $listing->originCountry->name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-yellow-700 font-bold">D</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Destination:</span>
                                        <span class="ml-2 text-gray-800">{{ $listing->destinationCountry->name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <div class="mt-8">
                        <x-tabs :tabs="[
                            'tab-reviews' => [
                                'label' => 'Reviews (' . $listing->reviews->count() . ')',
                                'content' => view('partials.listing-reviews', ['reviews' => $listing->reviews])->render()
                            ],
                            'tab-return-policy' => [
                                'label' => 'Return Policy',
                                'content' => $listing->return_policy ?: 'No return policy specified.'
                            ],
                            'tab-desc' => [
                                'label' => 'Description',
                                'content' => $listing->description
                            ]
                        ]"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
