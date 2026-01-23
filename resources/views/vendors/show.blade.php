@extends('layouts.app')
@section('page-title', 'Vendor Profile - ' . $user->username_pub)

@section('breadcrumbs')
    <a href="{{ route('home') }}" class="text-yellow-700 hover:text-yellow-600">Marketplace</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600">Vendors</span>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600">{{ $user->username_pub }}</span>
@endsection

@section('content')
    <div class="py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <!-- Vendor Header -->
            <div class="mb-8 overflow-hidden bg-white rounded-xl shadow-lg border border-gray-200">
                <div class="p-8">
                    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-yellow-700 font-bold text-2xl">{{ substr($user->username_pub, 0, 1) }}</span>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">{{ $user->username_pub }}</h1>
                                @if($user->rating > 0)
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-2xl font-bold text-yellow-600">{{ number_format($user->rating, 2) }}</span>
                                        <span class="text-yellow-500">★★★★★</span>
                                        <span class="text-sm text-gray-600">({{ $totalReviews }} {{ $totalReviews === 1 ? 'review' : 'reviews' }})</span>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 mt-2">No reviews yet</p>
                                @endif
                                <div class="flex flex-wrap items-center gap-2 mt-3">
                                    @if($user->vendor_since)
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                            Member since {{ $user->vendor_since->format('M Y') }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        Trust Level {{ $user->trust_level }}
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        Vendor Level {{ $user->vendor_level }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    @if($user->last_seen_at)
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                            Last seen {{ $user->last_seen_at->diffForHumans() }}
                                        </span>
                                    @endif
                                    @if($user->last_login_at)
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                            Last login {{ $user->last_login_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vendor Statistics Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200">
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <div class="text-sm text-gray-600 mb-1">Active Listings</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $activeListingsCount }}</div>
                            <div class="text-xs text-gray-500 mt-1">of {{ $totalListingsCount }} total</div>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="text-sm text-gray-600 mb-1">Total Views</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($totalViews) }}</div>
                            <div class="text-xs text-gray-500 mt-1">across all listings</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <div class="text-sm text-gray-600 mb-1">Completed Orders</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $completedOrders }}</div>
                            <div class="text-xs text-gray-500 mt-1">successful sales</div>
                        </div>
                        <div class="bg-{{ $disputedOrders > 0 ? 'red' : 'gray' }}-50 rounded-lg p-4 border border-{{ $disputedOrders > 0 ? 'red' : 'gray' }}-200">
                            <div class="text-sm text-gray-600 mb-1">Active Disputes</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $disputedOrders }}</div>
                            <div class="text-xs text-gray-500 mt-1">ongoing issues</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PGP Key Section (if available) --}}
            @if($user->pgp_pub_key)
                <div class="mb-8 overflow-hidden bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Encrypted Communication</h2>
                                <p class="text-sm text-gray-600 mt-1">This vendor supports PGP encrypted messages</p>
                            </div>
                            <x-modal
                                id="pgpModal"
                                title="Vendor PGP Public Key"
                                triggerText="View PGP Key"
                                triggerClass="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm font-medium"
                            >
                                <div class="space-y-4">
                                    <p class="text-sm text-gray-600">Use this public key to send encrypted messages to {{ $user->username_pub }}</p>
                                    <label class="block text-sm font-medium text-gray-700">PGP Public Key</label>
                                    <textarea
                                        class="w-full h-64 p-3 border border-gray-300 rounded-lg font-mono text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                        readonly
                                    >{{ $user->pgp_pub_key }}</textarea>
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
            @endif

            <!-- Main Content Grid -->
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Left Column - Stats -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Rating Statistics Card -->
                    <div class="overflow-hidden bg-white rounded-xl shadow-lg border border-gray-200">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Vendor Ratings</h2>

                            @if($totalReviews > 0)
                                <!-- Overall Rating -->
                                <div class="mb-6 p-4 bg-yellow-50 rounded-lg border-2 border-yellow-200">
                                    <div class="text-center">
                                        <div class="text-4xl font-bold text-yellow-700">{{ $ratingBreakdown['overall'] }}</div>
                                        <div class="text-sm text-gray-600 mt-1">Overall Rating</div>
                                        <div class="text-xs text-gray-500 mt-2">Based on {{ $totalReviews }} {{ $totalReviews === 1 ? 'review' : 'reviews' }}</div>
                                    </div>
                                </div>

                                <!-- Rating Breakdown -->
                                <div class="space-y-4">
                                    <!-- Stealth Rating -->
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium text-gray-700">Stealth</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $ratingBreakdown['stealth'] }}/5.0</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="bg-yellow-500 h-3 rounded-full transition-all" style="width: {{ ($ratingBreakdown['stealth'] / 5) * 100 }}%"></div>
                                        </div>
                                    </div>

                                    <!-- Quality Rating -->
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium text-gray-700">Quality</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $ratingBreakdown['quality'] }}/5.0</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="bg-yellow-500 h-3 rounded-full transition-all" style="width: {{ ($ratingBreakdown['quality'] / 5) * 100 }}%"></div>
                                        </div>
                                    </div>

                                    <!-- Delivery Rating -->
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium text-gray-700">Delivery</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $ratingBreakdown['delivery'] }}/5.0</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="bg-yellow-500 h-3 rounded-full transition-all" style="width: {{ ($ratingBreakdown['delivery'] / 5) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <p class="text-gray-500 text-sm">No reviews yet</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Dispute Statistics Card -->
                    <div class="overflow-hidden bg-white rounded-xl shadow-lg">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Dispute History</h2>

                            @if($disputeStats['total'] > 0)
                                <!-- Total Disputes Overview -->
                                <div class="mb-6 p-4 {{ $disputedOrders > 0 ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200' }} rounded-lg border-2">
                                    <div class="text-center">
                                        <div class="text-4xl font-bold text-gray-900">{{ $disputeStats['total'] }}</div>
                                        <div class="text-sm text-gray-600 mt-1">Total Disputes</div>
                                        <div class="text-xs {{ $disputedOrders > 0 ? 'text-red-600' : 'text-gray-500' }} mt-2 font-medium">
                                            {{ $disputedOrders }} active {{ $disputedOrders === 1 ? 'dispute' : 'disputes' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Dispute Breakdown by Status -->
                                <div class="space-y-3">
                                    <!-- Open Disputes -->
                                    @if($disputeStats['open'] > 0)
                                        <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                            <span class="text-sm font-medium text-gray-700">Open</span>
                                            <span class="px-3 py-1 bg-yellow-200 text-yellow-900 text-xs font-bold rounded-full">{{ $disputeStats['open'] }}</span>
                                        </div>
                                    @endif

                                    <!-- Under Review -->
                                    @if($disputeStats['under_review'] > 0)
                                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                                            <span class="text-sm font-medium text-gray-700">Under Review</span>
                                            <span class="px-3 py-1 bg-blue-200 text-blue-900 text-xs font-bold rounded-full">{{ $disputeStats['under_review'] }}</span>
                                        </div>
                                    @endif

                                    <!-- Waiting Vendor Response -->
                                    @if($disputeStats['waiting_vendor'] > 0)
                                        <div class="flex justify-between items-center p-3 bg-orange-50 rounded-lg border border-orange-200">
                                            <span class="text-sm font-medium text-gray-700">Waiting Vendor Response</span>
                                            <span class="px-3 py-1 bg-orange-200 text-orange-900 text-xs font-bold rounded-full">{{ $disputeStats['waiting_vendor'] }}</span>
                                        </div>
                                    @endif

                                    <!-- Waiting Buyer Response -->
                                    @if($disputeStats['waiting_buyer'] > 0)
                                        <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                                            <span class="text-sm font-medium text-gray-700">Waiting Buyer Response</span>
                                            <span class="px-3 py-1 bg-purple-200 text-purple-900 text-xs font-bold rounded-full">{{ $disputeStats['waiting_buyer'] }}</span>
                                        </div>
                                    @endif

                                    <!-- Escalated -->
                                    @if($disputeStats['escalated'] > 0)
                                        <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-200">
                                            <span class="text-sm font-medium text-gray-700">Escalated</span>
                                            <span class="px-3 py-1 bg-red-200 text-red-900 text-xs font-bold rounded-full">{{ $disputeStats['escalated'] }}</span>
                                        </div>
                                    @endif

                                    <!-- Resolved -->
                                    @if($disputeStats['resolved'] > 0)
                                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg border border-green-200">
                                            <span class="text-sm font-medium text-gray-700">Resolved</span>
                                            <span class="px-3 py-1 bg-green-200 text-green-900 text-xs font-bold rounded-full">{{ $disputeStats['resolved'] }}</span>
                                        </div>
                                    @endif

                                    <!-- Closed -->
                                    @if($disputeStats['closed'] > 0)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            <span class="text-sm font-medium text-gray-700">Closed</span>
                                            <span class="px-3 py-1 bg-gray-200 text-gray-900 text-xs font-bold rounded-full">{{ $disputeStats['closed'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Dispute Rate Indicator -->
                                @if($completedOrders > 0)
                                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="text-xs text-gray-600 mb-1">Dispute Rate</div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-2xl font-bold {{ ($disputeStats['total'] / $completedOrders) > 0.1 ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ number_format(($disputeStats['total'] / $completedOrders) * 100, 1) }}%
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                ({{ $disputeStats['total'] }} disputes / {{ $completedOrders }} completed orders)
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-8">
                                    <div class="text-green-600 font-semibold mb-2">No Disputes</div>
                                    <p class="text-gray-500 text-sm">This vendor has a clean dispute record</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Tabbed Content -->
                <div class="lg:col-span-2">
                    <div class="overflow-hidden bg-white rounded-xl shadow-lg">
                        <x-tabs :tabs="[
                            'listings' => [
                                'label' => 'Active Listings (' . $activeListingsCount . ')',
                                'content' => view('partials.vendor-listings', ['user' => $user])->render()
                            ],
                            'reviews' => [
                                'label' => 'Reviews (' . $totalReviews . ')',
                                'content' => view('partials.vendor-reviews', ['user' => $user])->render()
                            ],
                        ]"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
