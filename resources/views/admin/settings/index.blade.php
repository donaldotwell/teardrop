@extends('layouts.admin')
@section('page-title', 'Application Settings')

@section('breadcrumbs')
    <span class="text-gray-600">Settings</span>
@endsection

@section('page-heading')
    Application Settings
@endsection

@section('page-description')
    Manage platform-wide settings and configuration
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Info Banner --}}
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                <strong>Note:</strong> Changes to settings take effect immediately. Ensure you have reviewed all changes before saving.
            </p>
        </div>

        {{-- Settings Categories Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories as $categoryKey => $categoryLabel)
                <a href="{{ route('admin.settings.show', $categoryKey) }}"
                   class="p-6 bg-white border border-gray-200 rounded-lg hover:border-yellow-400 hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $categoryLabel }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $settings->get($categoryKey, collect())->count() }} settings
                            </p>
                        </div>
                        <div class="text-yellow-600 text-2xl">→</div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Quick Stats --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Overview</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="text-sm text-gray-600">Total Settings</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $settings->flatten()->count() }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Categories</div>
                    <div class="text-3xl font-bold text-gray-900">{{ count($categories) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Last Updated</div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $settings->flatten()->max('updated_at')?->diffForHumans() ?? 'Never' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Key Settings Summary --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Key Fees Summary</h3>
            
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Vendor Conversion Fee:</dt>
                    <dd class="font-mono font-medium text-gray-900">${{ number_format(\App\Models\AppSetting::get('vendor_conversion_usd', 1000), 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Order Completion Fee:</dt>
                    <dd class="font-mono font-medium text-gray-900">{{ \App\Models\AppSetting::get('order_completion_percentage', 3) }}%</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Featured Listing Cost:</dt>
                    <dd class="font-mono font-medium text-gray-900">${{ number_format(\App\Models\AppSetting::get('featured_listing_usd', 10), 2) }}</dd>
                </div>
            </dl>

            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.settings.show', 'fees') }}"
                   class="text-yellow-600 hover:text-yellow-700 text-sm font-medium">
                    Edit All Fees →
                </a>
            </div>
        </div>

    </div>
@endsection
