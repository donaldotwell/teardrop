@extends('layouts.admin')
@section('page-title', 'Listing Details')

@section('breadcrumbs')
    <a href="{{ route('admin.listings.index') }}" class="text-yellow-700 hover:text-yellow-800">Listings</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">{{ substr($listing->uuid, 0, 8) }}</span>
@endsection

@section('page-heading')
    Listing Details: {{ $listing->title }}
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Listing Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $listing->title }}</h2>
                    <p class="text-gray-600">Listing ID: {{ $listing->uuid }}</p>
                    <div class="flex items-center space-x-2 mt-2">
                        @if($listing->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Inactive
                            </span>
                        @endif

                        @if($listing->is_featured)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Featured
                            </span>
                        @endif

                        <span class="text-sm text-gray-500">
                            Created {{ $listing->created_at->format('M d, Y g:i A') }}
                        </span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    @if($listing->is_featured)
                        <form action="{{ route('admin.listings.unfeature', $listing) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                Unfeature
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.listings.feature', $listing) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                Feature
                            </button>
                        </form>
                    @endif

                    @if($listing->is_active)
                        <form action="{{ route('admin.listings.disable', $listing) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Disable
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.listings.enable', $listing) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                Enable
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('listings.show', $listing) }}"
                       target="_blank"
                       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        View Public Page
                    </a>
                </div>
            </div>

            {{-- Listing Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-2xl font-semibold text-gray-900">${{ number_format($listing->price, 2) }}</div>
                    <div class="text-sm text-gray-600">Item Price (USD)</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-purple-600">
                        ${{ number_format($listing->price_shipping, 2) }}
                    </div>
                    <div class="text-sm text-gray-600">Shipping Price</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-blue-600">
                        @php $availableStock = $listing->getAvailableStock(); @endphp
                        @if($listing->quantity === null)
                            <span class="text-green-600">Unlimited</span>
                        @else
                            {{ $availableStock }}
                            @if($availableStock <= 0)
                                <span class="text-red-600 text-sm">(OUT OF STOCK)</span>
                            @elseif($availableStock <= 5)
                                <span class="text-orange-600 text-sm">(Low)</span>
                            @endif
                        @endif
                    </div>
                    <div class="text-sm text-gray-600">Available Stock</div>
                    @if($listing->quantity !== null)
                        <div class="text-xs text-gray-500 mt-1">
                            Total: {{ $listing->quantity }} | Sold: {{ $listing->getSoldQuantity() }}
                        </div>
                    @endif
                </div>
                <div class="text-center">
                    <div class="text-2xl font-semibold text-green-600">
                        {{ number_format($listing->views) }}
                    </div>
                    <div class="text-sm text-gray-600">Total Views</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Vendor Information --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendor Information</h3>

                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-700 font-bold text-lg">{{ substr($listing->user->username_pub, 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $listing->user->username_pub }}</div>
                        <div class="text-sm text-gray-600">User ID: {{ $listing->user->id }}</div>
                    </div>
                </div>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Trust Level:</dt>
                        <dd class="font-medium text-gray-900">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Level {{ $listing->user->trust_level }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Account Status:</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ $listing->user->status }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Vendor Level:</dt>
                        <dd class="font-medium text-gray-900">{{ $listing->user->vendor_level }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Total Listings:</dt>
                        <dd class="font-medium text-gray-900">{{ $listing->user->listings->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Member Since:</dt>
                        <dd class="font-medium text-gray-900">{{ $listing->user->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Last Seen:</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $listing->user->last_seen_at ? $listing->user->last_seen_at->diffForHumans() : 'Never' }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.users.show', $listing->user) }}"
                       class="text-sm text-yellow-600 hover:text-yellow-800">
                        View Vendor Profile →
                    </a>
                </div>
            </div>

            {{-- Category and Location Information --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Category and Location</h3>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-600 mb-1">Category:</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $listing->product->productCategory->name ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 mb-1">Product:</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $listing->product->name ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 mb-1">Origin Country:</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $listing->originCountry->name ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 mb-1">Destination Country:</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $listing->destinationCountry->name ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 mb-1">Shipping Method:</dt>
                        <dd class="font-medium text-gray-900 capitalize">
                            {{ $listing->shipping_method }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 mb-1">Payment Method:</dt>
                        <dd class="font-medium text-gray-900 capitalize">
                            {{ $listing->payment_method }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Product Images --}}
        @if($listing->media->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Images</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($listing->media as $media)
                        <div class="relative">
                            <img src="{{ $media->data_uri }}"
                                 alt="{{ $listing->title }}"
                                 class="w-full h-64 object-contain rounded-lg bg-gray-50 p-4 border border-gray-200">
                            <div class="absolute top-2 right-2 bg-white px-2 py-1 rounded text-xs text-gray-600">
                                Order: {{ $media->order }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Short Description --}}
        @if($listing->short_description)
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Short Description</h3>
                <p class="text-gray-700">{{ $listing->short_description }}</p>
            </div>
        @endif

        {{-- Full Description --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Full Description</h3>
            <div class="prose max-w-none text-gray-700">
                {!! nl2br(e($listing->description)) !!}
            </div>
        </div>

        {{-- Return Policy --}}
        @if($listing->return_policy)
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Return Policy</h3>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($listing->return_policy)) !!}
                </div>
            </div>
        @endif

        {{-- Tags --}}
        @if($listing->tags && is_array($listing->tags) && count($listing->tags) > 0)
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tags</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($listing->tags as $tag)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Timestamps --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Created At:</dt>
                    <dd class="font-medium text-gray-900">{{ $listing->created_at->format('M d, Y g:i A') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Last Updated:</dt>
                    <dd class="font-medium text-gray-900">{{ $listing->updated_at->format('M d, Y g:i A') }}</dd>
                </div>
                @if($listing->end_date)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">End Date:</dt>
                        <dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($listing->end_date)->format('M d, Y g:i A') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

    </div>
@endsection
