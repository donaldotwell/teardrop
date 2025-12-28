<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page-title', 'Vendor') - {{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css'])
    <style>
        .js-warning { display: none; }
        @if(app()->environment('production'))
        .js-warning-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #991b1b;
            color: white;
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        @endif
    </style>
</head>
<body class="bg-gray-50">
@if(app()->environment('production'))
<noscript>
    <style>.js-warning-overlay { display: none !important; }</style>
</noscript>
<div class="js-warning-overlay">
    <div class="max-w-2xl text-center">
        <h2 class="text-4xl font-bold mb-4">Access Denied</h2>
        <p class="text-xl mb-6">
            This site requires JavaScript to be disabled in your browser.
            Please disable JavaScript and refresh this page to continue.
        </p>
        <div class="bg-red-900 rounded-lg p-6 mb-6 text-left">
            <h3 class="font-bold text-lg mb-2">To disable JavaScript:</h3>
            <ul class="space-y-2 text-sm">
                <li><strong>Tor Browser:</strong> Click the shield icon next to the address bar → Advanced Security Settings → Select "Safest" security level</li>
            </ul>
        </div>
        <a href="{{ url()->current() }}" class="inline-block bg-white text-red-900 px-8 py-3 rounded-lg font-bold text-lg hover:bg-red-50">
            Refresh Page
        </a>
    </div>
</div>
@else
<noscript>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 text-center">
        Access granted.
    </div>
</noscript>
<div class="js-warning bg-red-100 border-l-4 border-red-500 text-red-700 p-4 text-center">
    Warning: JavaScript detected. JavaScript must be disabled to visit this page.
</div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(app()->environment('production'))
        var overlay = document.querySelector('.js-warning-overlay');
        if (overlay) overlay.style.display = 'flex';
        @else
        var warning = document.querySelector('.js-warning');
        if (warning) warning.style.display = 'block';
        @endif
    });
</script>

<div class="min-h-screen flex flex-col">

    {{-- Vendor Header --}}
    <div class="bg-purple-700 border-b border-purple-800">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between py-3 text-sm">

                {{-- Vendor Branding --}}
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-purple-100">VENDOR PANEL</span>
                        <span class="text-purple-300">|</span>
                        <span class="text-purple-200">{{ config('app.name') }}</span>
                    </div>
                </div>

                {{-- Vendor User Info & Actions --}}
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3 text-purple-100">
                        <span>{{ auth()->user()->username_pub }}</span>
                        <span class="bg-purple-600 px-2 py-1 rounded text-xs">Vendor</span>
                    </div>

                    <div class="h-4 w-px bg-purple-600"></div>

                    {{-- Quick Actions --}}
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('home') }}"
                           class="px-3 py-1 bg-purple-600 text-purple-50 rounded hover:bg-purple-800 text-sm">
                            View Site
                        </a>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1 border border-purple-600 text-purple-100 rounded hover:bg-purple-600 text-sm">
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vendor Navigation --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">

                {{-- Desktop Navigation --}}
                <div class="hidden md:flex space-x-1">
                    <a href="{{ route('vendor.dashboard') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('vendor.dashboard') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('vendor.listings.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('vendor.listings.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        My Listings
                    </a>
                    <a href="{{ route('vendor.orders.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('vendor.orders.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Orders
                    </a>
                    <a href="{{ route('vendor.sales') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('vendor.sales') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Sales
                    </a>
                    <a href="{{ route('vendor.reviews') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('vendor.reviews') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Reviews
                    </a>
                    <a href="{{ route('vendor.analytics') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('vendor.analytics') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Analytics
                    </a>
                </div>

                {{-- Mobile Navigation Toggle --}}
                <details class="md:hidden">
                    <summary class="p-2 cursor-pointer text-gray-700">
                        <span class="sr-only">Open vendor menu</span>
                        Menu
                    </summary>

                    {{-- Mobile Menu --}}
                    <div class="absolute left-0 right-0 bg-white border-b border-gray-200 shadow-lg z-10">
                        <a href="{{ route('vendor.dashboard') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Dashboard
                        </a>
                        <a href="{{ route('vendor.listings.index') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            My Listings
                        </a>
                        <a href="{{ route('vendor.orders.index') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Orders
                        </a>
                        <a href="{{ route('vendor.sales') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Sales
                        </a>
                        <a href="{{ route('vendor.reviews') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Reviews
                        </a>
                        <a href="{{ route('vendor.analytics') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50">
                            Analytics
                        </a>
                    </div>
                </details>
            </div>
        </div>
    </nav>

    {{-- Breadcrumbs --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="flex items-center space-x-2 text-sm text-gray-600">
                <a href="{{ route('vendor.dashboard') }}" class="text-yellow-700 hover:text-yellow-800">
                    Vendor
                </a>
                <span class="text-gray-400">/</span>
                @yield('breadcrumbs')
            </nav>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 py-6">

            {{-- Page Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    @yield('page-heading')
                </h1>
                @hasSection('page-description')
                    <p class="text-gray-600 mt-1">@yield('page-description')</p>
                @endif
            </div>

            {{-- Flash Messages --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded text-red-800">
                    <div class="font-medium">Error</div>
                    @foreach ($errors->all() as $error)
                        <div class="text-sm">{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded text-green-800">
                    <div class="font-medium">Success</div>
                    <div class="text-sm">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
                    <div class="font-medium">Warning</div>
                    <div class="text-sm">{{ session('warning') }}</div>
                </div>
            @endif

            {{-- Content --}}
            @yield('content')
        </div>
    </main>

    {{-- Vendor Footer --}}
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <p>{{ config('app.name') }} - Vendor Panel</p>
                <span>&copy; {{ date('Y') }} </span>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
