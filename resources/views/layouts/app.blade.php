<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page-title') | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .mobile-menu { display: none; }
        #menu-toggle:checked ~ .mobile-menu { display: block; }
        /* Hide JS warning by default */
        .js-warning { display: none; }
        @if(app()->environment('production'))
        /* Full page overlay for production */
        .js-warning-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #dc2626;
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
        <div class="bg-red-700 rounded-lg p-6 mb-6 text-left">
            <h3 class="font-bold text-lg mb-2">To disable JavaScript:</h3>
            <ul class="space-y-2 text-sm">
                <li><strong>Tor Browser:</strong> Click the shield icon next to the address bar → Advanced Security Settings → Select "Safest" security level</li>
            </ul>
        </div>
        <a href="{{ url()->current() }}" class="inline-block bg-white text-red-600 px-8 py-3 rounded-lg font-bold text-lg hover:bg-red-50">
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
    <!-- Modern Header -->
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('logo-h.png') }}" alt="{{ config('app.name') }}" class="h-10 w-auto">
                    </a>
                </div>

                <!-- User Widget -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- User Info -->
                        <div class="flex items-center space-x-3 px-4 py-2 bg-amber-50 rounded-lg border border-amber-200">
                            <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-amber-700 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm">{{ strtoupper(substr(auth()->user()->username_pub, 0, 1)) }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-amber-600 font-medium">User</span>
                                <span class="text-sm font-bold text-gray-900">{{ auth()->user()->username_pub }}</span>
                            </div>
                        </div>

                        <!-- Wallet Balances -->
                        <div class="hidden md:flex items-center space-x-3 px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 font-medium">BTC Balance</span>
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-sm font-mono font-bold text-gray-900">{{ $user_balance['btc']['balance'] }}</span>
                                    <span class="text-xs text-gray-500">≈ ${{ number_format($user_balance['btc']['usd_value'], 2) }}</span>
                                </div>
                            </div>
                            <div class="w-px h-10 bg-gray-300"></div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 font-medium">XMR Balance</span>
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-sm font-mono font-bold text-gray-900">{{ $user_balance['xmr']['balance'] }}</span>
                                    <span class="text-xs text-gray-500">≈ ${{ number_format($user_balance['xmr']['usd_value'], 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
                                Sign Out
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium transition-colors">Login</a>
                        <a href="{{ route('register') }}" class="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors font-medium">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-gray-800 to-gray-700 border-b border-gray-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-12">
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    @foreach($navigation_links as $text => $url)
                        @php
                            $isActive = request()->url() === $url || request()->fullUrl() === $url;
                        @endphp
                        <a href="{{ $url }}" class="flex items-center text-sm px-3 py-2 rounded-md {{ $isActive ? 'bg-amber-600 text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                            {{ $text }}
                        </a>
                    @endforeach
                </div>

                <!-- Mobile Menu Toggle -->
                <label for="menu-toggle" class="md:hidden p-2 cursor-pointer">
                    <span class="block w-6 h-0.5 bg-gray-300 mb-1.5"></span>
                    <span class="block w-6 h-0.5 bg-gray-300 mb-1.5"></span>
                    <span class="block w-6 h-0.5 bg-gray-300"></span>
                </label>
            </div>
        </div>

        <!-- Mobile Menu -->
        <input type="checkbox" id="menu-toggle" class="hidden">
        <div class="mobile-menu md:hidden bg-gray-800 border-t border-gray-600">
            @foreach($navigation_links as $text => $url)
                @php
                    $isActive = request()->url() === $url || request()->fullUrl() === $url;
                @endphp
                <a href="{{ $url }}" class="block px-4 py-3 text-sm font-medium {{ $isActive ? 'bg-amber-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700/50' }}">
                    {{ $text }}
                </a>
            @endforeach
        </div>
    </nav>

    <!-- Breadcrumbs -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('home') }}" class="text-yellow-700 hover:text-yellow-600">
                    <span class="font-medium">Home</span>
                </a>
                <span class="text-gray-300">/</span>
                @yield('breadcrumbs')
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-2">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">
                    @yield('page-heading')
                </h1>

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

                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded text-red-800">
                        <div class="font-medium">Error</div>
                        <div class="text-sm">{{ session('error') }}</div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
                        <div class="font-medium">Warning</div>
                        <div class="text-sm">{{ session('warning') }}</div>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800 mt-8">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Exchange Rates -->
            <div class="flex flex-col items-center justify-center gap-4 mb-6">
                @if($btcRate && $xmrRate)
                    <div class="flex items-center gap-6">
                        <div class="flex items-center space-x-2 px-4 py-2 bg-gray-800 rounded-lg border border-gray-700">
                            <span class="text-amber-400 font-bold text-sm">BTC</span>
                            <span class="text-gray-400">=</span>
                            <span class="text-white font-mono font-semibold">${{ number_format($btcRate->usd_rate, 2) }}</span>
                            <span class="text-gray-500 text-xs">USD</span>
                        </div>
                        <div class="flex items-center space-x-2 px-4 py-2 bg-gray-800 rounded-lg border border-gray-700">
                            <span class="text-amber-400 font-bold text-sm">XMR</span>
                            <span class="text-gray-400">=</span>
                            <span class="text-white font-mono font-semibold">${{ number_format($xmrRate->usd_rate, 2) }}</span>
                            <span class="text-gray-500 text-xs">USD</span>
                        </div>
                    </div>
                @else
                    <div class="text-gray-500 text-sm">
                        Exchange rates not available.
                    </div>
                @endif
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 pt-4">
                <p class="text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
