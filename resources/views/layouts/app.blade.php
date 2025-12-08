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
<div class="min-h-screen flex flex-col">
    <!-- Premium Status Bar -->
    <div class="bg-gradient-to-r from-amber-700 to-amber-800 border-b border-amber-600 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col sm:flex-row items-center justify-between py-2 gap-2 sm:gap-4 text-xs sm:text-sm">
                <!-- Market Ticker -->
                <div class="flex items-center space-x-3 overflow-x-auto scrollbar-hide">
                    <div class="flex items-center space-x-1">
                        <span class="text-amber-300 font-bold">BTC</span>
                        <span class="font-medium text-amber-100">Markets:</span>
                    </div>

                    @foreach(['USD', 'EUR', 'GBP'] as $currency)
                        <div class="flex items-center space-x-1">
                            <span class="text-amber-50">{{ $currency }}</span>
                            <span class="text-amber-200">{{ rand(50000, 200000)/100 }}</span>
                            <span class="text-emerald-400 text-xs">+2.5%</span>
                        </div>
                    @endforeach
                </div>

                <!-- User Widget -->
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="hidden sm:flex items-center space-x-2">
                            <span class="text-amber-300 font-bold">User:</span>
                            <span class="text-amber-100">{{ auth()->user()->username_pub }}</span>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Bitcoin Balance -->
                            <div class="flex items-center space-x-2">
                                <span class="text-amber-300 font-bold text-sm">BTC</span>
                                <div>
                                    <span class="text-amber-100 font-mono text-sm">{{ $user_balance['btc']['balance'] }}</span>
                                    <span class="text-amber-400 text-xs ml-2">≈ ${{ number_format($user_balance['btc']['usd_value'], 2) }}</span>
                                </div>
                            </div>

                            <!-- Monero Balance -->
                            <div class="flex items-center space-x-2">
                                <span class="text-amber-300 font-bold text-sm">XMR</span>
                                <div>
                                    <span class="text-amber-100 font-mono text-sm">{{ $user_balance['xmr']['balance'] }}</span>
                                    <span class="text-amber-400 text-xs ml-2">≈ ${{ number_format($user_balance['xmr']['usd_value'], 2) }}</span>
                                </div>
                            </div>

                            <div class="h-5 w-px bg-amber-600"></div>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="flex items-center space-x-1 px-3 py-1 rounded-md bg-amber-600 hover:bg-amber-700 transition-colors">
                                    <span class="text-amber-50 text-sm">Sign Out</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-amber-100 hover:text-white">Login</a>
                        <span class="text-amber-300">|</span>
                        <a href="{{ route('register') }}" class="text-amber-100 hover:text-white">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-4">
                    @foreach($navigation_links as $text => $url)
                        <a href="{{ $url }}" class="flex items-center text-sm text-gray-700 hover:bg-yellow-50 px-3 py-2 rounded-md">
                            {{ $text }}
                        </a>
                    @endforeach
                </div>

                <!-- Mobile Menu Toggle -->
                <label for="menu-toggle" class="md:hidden p-2 cursor-pointer">
                    <span class="block w-6 h-0.5 bg-gray-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-gray-600 mb-1"></span>
                    <span class="block w-6 h-0.5 bg-gray-600"></span>
                </label>
            </div>
        </div>

        <!-- Mobile Menu -->
        <input type="checkbox" id="menu-toggle" class="hidden">
        <div class="mobile-menu md:hidden bg-white border-t">
            @foreach($navigation_links as $text => $url)
                <a href="{{ $url }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-yellow-50">
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

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Oops!</strong>
                        <span class="block sm:inline">{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Success Messages -->
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <p class="text-center text-sm text-gray-600">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </p>
        </div>
    </footer>
</div>
</body>
</html>
