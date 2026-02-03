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
                        <img src="{{ asset('logo-h.png') }}" alt="{{ config('app.name') }}" class="h-12 w-auto">
                    </a>
                </div>

                <!-- User Widget -->
                @auth
                    <div class="flex items-center gap-6">
                        <!-- Wallet Balances -->
                        <div class="hidden md:flex items-center gap-4 text-xs">
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-gray-500">BTC:</span>
                                <span class="font-mono font-semibold text-gray-900">{{ $user_balance['btc']['balance'] }}</span>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-gray-500">XMR:</span>
                                <span class="font-mono font-semibold text-gray-900">{{ $user_balance['xmr']['balance'] }}</span>
                            </div>
                        </div>
                        
                        <div class="h-8 w-px bg-gray-200 hidden md:block"></div>
                        
                        <!-- User Avatar & Username -->
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-amber-700 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-bold text-sm">{{ strtoupper(substr(auth()->user()->username_pri, 0, 1)) }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ auth()->user()->username_pri }}</span>
                        </div>
                        
                        <!-- Sign Out -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-900 transition-colors underline">
                                Sign Out
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors text-sm font-medium">Register</a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-amber-100 border-b border-amber-300 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-12">
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    @foreach($navigation_links as $text => $url)
                        @php
                            $isActive = request()->url() === $url || request()->fullUrl() === $url;
                        @endphp
                        <a href="{{ $url }}" class="flex items-center text-sm px-3 py-2 rounded-md transition-colors {{ $isActive ? 'bg-amber-500 text-white font-semibold' : 'text-amber-800 hover:bg-amber-200' }}">
                            {{ $text }}
                        </a>
                    @endforeach
                </div>

                <!-- Mobile Menu Toggle -->
                <label for="menu-toggle" class="md:hidden p-2 cursor-pointer">
                    <span class="block w-6 h-0.5 bg-amber-800 mb-1.5"></span>
                    <span class="block w-6 h-0.5 bg-amber-800 mb-1.5"></span>
                    <span class="block w-6 h-0.5 bg-amber-800"></span>
                </label>
            </div>
        </div>

        <!-- Mobile Menu -->
        <input type="checkbox" id="menu-toggle" class="hidden">
        <div class="mobile-menu md:hidden bg-amber-200 border-t border-amber-300">
            @foreach($navigation_links as $text => $url)
                @php
                    $isActive = request()->url() === $url || request()->fullUrl() === $url;
                @endphp
                <a href="{{ $url }}" class="block px-4 py-3 text-sm font-medium transition-colors {{ $isActive ? 'bg-amber-500 text-white font-semibold' : 'text-amber-800 hover:bg-amber-100' }}">
                    {{ $text }}
                </a>
            @endforeach
        </div>
    </nav>

    <!-- Breadcrumbs -->
    <div class="bg-amber-50 border-b border-amber-200">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="flex items-center space-x-2 text-sm overflow-x-auto">
                <a href="{{ route('home') }}" class="text-amber-700 hover:text-amber-900 font-medium whitespace-nowrap flex-shrink-0">
                    Home
                </a>
                <span class="text-amber-400 flex-shrink-0">/</span>
                <div class="truncate text-amber-700">
                    @yield('breadcrumbs')
                </div>
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
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Exchange Rates -->
                @if($btcRate && $xmrRate)
                    <div class="flex items-baseline gap-6 text-sm">
                        <div class="flex items-baseline gap-2">
                            <span class="text-amber-400 font-semibold">BTC</span>
                            <span class="text-gray-600">=</span>
                            <span class="text-white font-mono">${{ number_format($btcRate->usd_rate, 2) }}</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-amber-400 font-semibold">XMR</span>
                            <span class="text-gray-600">=</span>
                            <span class="text-white font-mono">${{ number_format($xmrRate->usd_rate, 2) }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-gray-500 text-sm">Exchange rates not available.</div>
                @endif

                <!-- Copyright -->
                <p class="text-xs text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name') }}
                </p>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
