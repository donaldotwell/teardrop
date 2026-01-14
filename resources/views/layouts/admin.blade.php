<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page-title', 'Admin') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Tailwind CSS -->
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
    // Show warning if JavaScript is enabled
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

    {{-- Admin Header --}}
    <div class="bg-red-700 border-b border-red-800">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between py-3 text-sm">

                {{-- Admin Branding --}}
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-red-100">ADMIN PANEL</span>
                        <span class="text-red-300">|</span>
                        <span class="text-red-200">{{ config('app.name') }}</span>
                    </div>
                </div>

                {{-- Admin User Info & Actions --}}
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3 text-red-100">
                        <span>{{ auth()->user()->username_pub }}</span>
                        <span class="bg-red-600 px-2 py-1 rounded text-xs">Admin</span>
                    </div>

                    <div class="h-4 w-px bg-red-600"></div>

                    {{-- Quick Actions --}}
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('home') }}"
                           class="px-3 py-1 bg-red-600 text-red-50 rounded hover:bg-red-800 text-sm">
                            View Site
                        </a>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1 border border-red-600 text-red-100 rounded hover:bg-red-600 text-sm">
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Admin Navigation --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">

                {{-- Desktop Navigation --}}
                <div class="hidden md:flex space-x-1">
                    <a href="{{ route('admin.dashboard') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.dashboard') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.users.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Users
                    </a>
                    <a href="{{ route('admin.orders.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.orders.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Orders
                    </a>
                    <a href="{{ route('admin.listings.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.listings.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Listings
                    </a>
                    <a href="{{ route('admin.disputes.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.disputes.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Disputes
                    </a>
                    <a href="{{ route('admin.support.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.support.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Tickets
                    </a>
                    <a href="{{ route('admin.product-categories.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.product-categories.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Categories
                    </a>
                    <a href="{{ route('admin.products.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.products.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Products
                    </a>
                    <a href="{{ route('admin.reports') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.reports') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Reports
                    </a>
                    <a href="{{ route('admin.finalization-windows.index') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.finalization-windows.*') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Finalization Windows
                    </a>
                    <a href="{{ route('admin.settings') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 rounded
                              {{ request()->routeIs('admin.settings') ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        Settings
                    </a>
                </div>

                {{-- Mobile Navigation Toggle --}}
                <details class="md:hidden">
                    <summary class="p-2 cursor-pointer">
                        <span class="sr-only">Open admin menu</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </summary>

                    {{-- Mobile Menu --}}
                    <div class="absolute left-0 right-0 bg-white border-b border-gray-200 shadow-lg">
                        <a href="{{ route('admin.dashboard') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Users
                        </a>
                        <a href="{{ route('admin.orders.index') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Orders
                        </a>
                        <a href="{{ route('admin.listings.index') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Listings
                        </a>
                        <a href="{{ route('admin.product-categories.index') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Categories
                        </a>
                        <a href="{{ route('admin.products.index') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Products
                        </a>
                        <a href="{{ route('admin.reports') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50 border-b border-gray-100">
                            Reports
                        </a>
                        <a href="{{ route('admin.settings') }}"
                           class="block px-4 py-3 text-gray-700 hover:bg-yellow-50">
                            Settings
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
                <a href="{{ route('admin.dashboard') }}" class="text-yellow-700 hover:text-yellow-800">
                    Admin
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

    {{-- Admin Footer --}}
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <p>{{ config('app.name') }} - Admin Panel</p>
                <span>&copy; {{ date('Y') }} </span>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
