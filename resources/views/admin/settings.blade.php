@extends('layouts.admin')
@section('page-title', 'System Settings')

@section('breadcrumbs')
    <span class="text-gray-600">Settings</span>
@endsection

@section('page-heading')
    System Settings
@endsection

@section('page-description')
    Configure marketplace settings and system preferences
@endsection

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- System Information --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-1 gap-2">
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Application</h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">App Name:</dt>
                            <dd class="font-medium text-gray-900">{{ $settings['app_name'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Environment:</dt>
                            <dd class="font-medium text-gray-900 capitalize">{{ $settings['app_env'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Debug Mode:</dt>
                            <dd class="font-medium {{ $settings['app_debug'] ? 'text-red-600' : 'text-green-600' }}">
                                {{ $settings['app_debug'] ? 'Enabled' : 'Disabled' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Maintenance:</dt>
                            <dd class="font-medium {{ $settings['maintenance_mode'] ? 'text-yellow-600' : 'text-green-600' }}">
                                {{ $settings['maintenance_mode'] ? 'Active' : 'Inactive' }}
                            </dd>
                        </div>
                    </dl>
                </div>

            </div>
        </div>

        {{-- Platform Settings --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Settings</h3>

            <form action="{{ route('admin.settings.update') }}" method="post" class="space-y-6">
                @csrf

                {{-- Site Operations --}}
                <div>
                    <h4 class="font-medium text-gray-900 mb-4">Site Operations</h4>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">Maintenance Mode</div>
                                <div class="text-sm text-gray-600">Put the site in maintenance mode for updates</div>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox"
                                       name="site_maintenance"
                                       value="1"
                                       class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                                    {{ $settings['maintenance_mode'] ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Enable</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">New User Registrations</div>
                                <div class="text-sm text-gray-600">Allow new users to register accounts</div>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox"
                                       name="new_registrations"
                                       value="1"
                                       class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                                       checked>
                                <span class="ml-2 text-sm text-gray-700">Enable</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Listing Settings --}}
                <div class="border-t border-gray-100 pt-6">
                    <h4 class="font-medium text-gray-900 mb-4">Listing Settings</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="featured_listings_limit" class="block text-sm font-medium text-gray-700">
                                Featured Listings Limit
                            </label>
                            <input type="number"
                                   name="featured_listings_limit"
                                   id="featured_listings_limit"
                                   value="20"
                                   min="1"
                                   max="100"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <p class="text-xs text-gray-500">Maximum number of featured listings allowed</p>
                        </div>

                        <div class="space-y-1">
                            <label for="max_images_per_listing" class="block text-sm font-medium text-gray-700">
                                Max Images Per Listing
                            </label>
                            <input type="number"
                                   name="max_images_per_listing"
                                   id="max_images_per_listing"
                                   value="3"
                                   min="1"
                                   max="10"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <p class="text-xs text-gray-500">Maximum images allowed per listing</p>
                        </div>
                    </div>
                </div>

                {{-- Security Settings --}}
                <div class="border-t border-gray-100 pt-6">
                    <h4 class="font-medium text-gray-900 mb-4">Security Settings</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="session_lifetime" class="block text-sm font-medium text-gray-700">
                                Session Lifetime (minutes)
                            </label>
                            <input type="number"
                                   name="session_lifetime"
                                   id="session_lifetime"
                                   value="120"
                                   min="30"
                                   max="1440"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <p class="text-xs text-gray-500">How long users stay logged in</p>
                        </div>

                        <div class="space-y-1">
                            <label for="max_login_attempts" class="block text-sm font-medium text-gray-700">
                                Max Login Attempts
                            </label>
                            <input type="number"
                                   name="max_login_attempts"
                                   id="max_login_attempts"
                                   value="5"
                                   min="3"
                                   max="10"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500">
                            <p class="text-xs text-gray-500">Before account lockout</p>
                        </div>
                    </div>
                </div>

                {{-- Email Settings --}}
                <div class="border-t border-gray-100 pt-6">
                    <h4 class="font-medium text-gray-900 mb-4">Email Settings</h4>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">Order Notifications</div>
                                <div class="text-sm text-gray-600">Send email notifications for new orders</div>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox"
                                       name="email_order_notifications"
                                       value="1"
                                       class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                                       checked>
                                <span class="ml-2 text-sm text-gray-700">Enable</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">Admin Alerts</div>
                                <div class="text-sm text-gray-600">Send alerts for important system events</div>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox"
                                       name="admin_alerts"
                                       value="1"
                                       class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                                       checked>
                                <span class="ml-2 text-sm text-gray-700">Enable</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="border-t border-gray-100 pt-6">
                    <button type="submit"
                            class="px-6 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- Cache Management --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cache Management</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <form action="{{ route('admin.cache.clear') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="w-full p-4 border border-blue-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 text-center">
                        <div class="font-medium text-gray-900 mb-1">Clear Application Cache</div>
                        <div class="text-sm text-gray-600">Clear cached views, routes, and config</div>
                    </button>
                </form>

                <form action="{{ route('admin.cache.optimize') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="w-full p-4 border border-green-200 rounded-lg hover:border-green-300 hover:bg-green-50 text-center">
                        <div class="font-medium text-gray-900 mb-1">Optimize Application</div>
                        <div class="text-sm text-gray-600">Cache routes, config, and views</div>
                    </button>
                </form>

                <form action="{{ route('admin.queue.restart') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="w-full p-4 border border-yellow-200 rounded-lg hover:border-yellow-300 hover:bg-yellow-50 text-center">
                        <div class="font-medium text-gray-900 mb-1">Restart Queue Workers</div>
                        <div class="text-sm text-gray-600">Restart background job processing</div>
                    </button>
                </form>
            </div>
        </div>

        {{-- Database Maintenance --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Maintenance</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 border border-gray-200 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Database Statistics</h4>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Total Users:</dt>
                            <dd class="font-medium text-gray-900">{{ \App\Models\User::count() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Total Orders:</dt>
                            <dd class="font-medium text-gray-900">{{ \App\Models\Order::count() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Total Listings:</dt>
                            <dd class="font-medium text-gray-900">{{ \App\Models\Listing::count() }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="p-4 border border-gray-200 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Maintenance Actions</h4>
                    <div class="space-y-2">
                        <form action="{{ route('admin.db.cleanup') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Clean Old Data
                            </button>
                        </form>

                        <form action="{{ route('admin.db.backup') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                Create Backup
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-800 mb-4">Danger Zone</h3>
            <p class="text-sm text-red-700 mb-4">
                These actions are irreversible and can cause data loss. Please be extremely careful.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <form action="{{ route('admin.maintenance.enable') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="w-full p-4 border border-red-300 rounded-lg hover:bg-red-100 text-center">
                        <div class="font-medium text-red-800 mb-1">Enable Maintenance Mode</div>
                        <div class="text-sm text-red-600">Temporarily disable site access</div>
                    </button>
                </form>

                <form action="{{ route('admin.data.purge') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="w-full p-4 border border-red-300 rounded-lg hover:bg-red-100 text-center">
                        <div class="font-medium text-red-800 mb-1">Purge Old Data</div>
                        <div class="text-sm text-red-600">Permanently delete data older than 1 year</div>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
