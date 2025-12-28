@extends('layouts.vendor')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-8">
            <div class="flex items-center gap-6">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center">
                    <span class="text-4xl font-bold text-purple-700">{{ strtoupper(substr($vendor->username_pub, 0, 1)) }}</span>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white">{{ $vendor->username_pub }}</h1>
                    <p class="text-purple-100 mt-1">Vendor since {{ $vendor->vendor_since ? $vendor->vendor_since->format('F Y') : 'N/A' }}</p>
                    <div class="flex items-center gap-3 mt-3">
                        <span class="bg-white/20 text-white text-sm px-3 py-1 rounded-full">Level {{ $vendor->vendor_level }}</span>
                        <span class="bg-white/20 text-white text-sm px-3 py-1 rounded-full">Trust Level {{ $vendor->trust_level }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 border-b border-gray-200">
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-3xl font-bold text-purple-700">{{ $vendor->listings()->count() }}</div>
                <div class="text-sm text-gray-600 mt-1">Total Listings</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-3xl font-bold text-purple-700">{{ $vendor->listings()->where('is_active', true)->count() }}</div>
                <div class="text-sm text-gray-600 mt-1">Active Listings</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-3xl font-bold text-purple-700">
                    {{ \App\Models\Order::whereHas('listing', function($q) use ($vendor) { $q->where('user_id', $vendor->id); })->where('status', 'completed')->count() }}
                </div>
                <div class="text-sm text-gray-600 mt-1">Completed Sales</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-3xl font-bold text-purple-700">
                    {{ number_format(\App\Models\Review::whereHas('listing', function($q) use ($vendor) { $q->where('user_id', $vendor->id); })->selectRaw('AVG((rating_stealth + rating_quality + rating_delivery) / 3) as avg_rating')->value('avg_rating') ?? 0, 1) }}
                </div>
                <div class="text-sm text-gray-600 mt-1">Average Rating</div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="p-6 space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Account Information</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Public Username</span>
                        <span class="text-sm text-gray-900">{{ $vendor->username_pub }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Private Username</span>
                        <span class="text-sm text-gray-900 font-mono">{{ $vendor->username_pri }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Vendor Level</span>
                        <span class="text-sm text-gray-900">{{ $vendor->vendor_level }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Trust Level</span>
                        <span class="text-sm text-gray-900">{{ $vendor->trust_level }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Account Status</span>
                        <span class="text-sm">
                            @if($vendor->status === 'active')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">Active</span>
                            @else
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full">{{ ucfirst($vendor->status) }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm font-medium text-gray-600">Member Since</span>
                        <span class="text-sm text-gray-900">{{ $vendor->created_at->format('F d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- PGP Key Section -->
            @if($vendor->pgp_pub_key)
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">PGP Public Key</h2>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <textarea readonly class="w-full h-48 font-mono text-xs text-gray-700 bg-transparent border-0 focus:ring-0 resize-none">{{ $vendor->pgp_pub_key }}</textarea>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
