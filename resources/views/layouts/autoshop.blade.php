<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page-title', 'Autoshop') - {{ config('app.name', 'Laravel') }}</title>

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

<div class="min-h-screen flex flex-col">

    {{-- Autoshop Header --}}
    <div class="bg-teal-700 border-b border-teal-800">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between py-3 text-sm">

                {{-- Branding --}}
                <div class="flex items-center space-x-3">
                    <span class="font-bold text-teal-100 tracking-wide">AUTOSHOP</span>
                    <span class="text-teal-400">|</span>
                    <span class="text-teal-200">{{ config('app.name') }}</span>
                </div>

                {{-- User info & actions --}}
                <div class="flex items-center space-x-4">

                    {{-- Wallet balances --}}
                    @if(isset($user_balance))
                    <div class="hidden md:flex items-center gap-4 text-xs">
                        <div class="flex flex-col">
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-teal-300">BTC:</span>
                                <span class="font-mono font-semibold text-white">{{ $user_balance['btc']['balance'] ?? '0.00000000' }}</span>
                            </div>
                            <span class="text-[10px] text-teal-300 font-mono">${{ number_format($user_balance['btc']['usd_value'] ?? 0, 2) }}</span>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-teal-300">XMR:</span>
                                <span class="font-mono font-semibold text-white">{{ $user_balance['xmr']['balance'] ?? '0.000000000000' }}</span>
                            </div>
                            <span class="text-[10px] text-teal-300 font-mono">${{ number_format($user_balance['xmr']['usd_value'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="h-6 w-px bg-teal-600 hidden md:block"></div>
                    @endif

                    <span class="text-teal-100">{{ auth()->user()->username_pub }}</span>

                    <div class="h-4 w-px bg-teal-600"></div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('home') }}"
                           class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded text-sm transition-colors">
                            Market
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1 border border-teal-500 text-teal-100 hover:bg-teal-600 rounded text-sm transition-colors">
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Autoshop Navigation --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-12">

                {{-- Desktop nav --}}
                <div class="hidden md:flex space-x-1">
                    <a href="{{ route('autoshop.fullz.index') }}"
                       class="px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 rounded transition-colors
                              {{ request()->routeIs('autoshop.fullz.*') ? 'bg-teal-50 text-teal-700 font-medium' : '' }}">
                        Fullz
                    </a>
                    <a href="{{ route('autoshop.fsaid.index') }}"
                       class="px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 rounded transition-colors
                              {{ request()->routeIs('autoshop.fsaid.*') ? 'bg-teal-50 text-teal-700 font-medium' : '' }}">
                        FSAID
                    </a>
                </div>

                {{-- Mobile nav --}}
                <details class="md:hidden relative">
                    <summary class="px-3 py-2 cursor-pointer text-sm text-gray-700 rounded hover:bg-gray-100">
                        Menu
                    </summary>
                    <div class="absolute left-0 top-full bg-white border border-gray-200 rounded-lg shadow-lg z-20 min-w-40 mt-1">
                        <a href="{{ route('autoshop.fullz.index') }}"
                           class="block px-4 py-3 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 border-b border-gray-100
                                  {{ request()->routeIs('autoshop.fullz.*') ? 'bg-teal-50 text-teal-700 font-medium' : '' }}">
                            Fullz
                        </a>
                        <a href="{{ route('autoshop.fsaid.index') }}"
                           class="block px-4 py-3 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700
                                  {{ request()->routeIs('autoshop.fsaid.*') ? 'bg-teal-50 text-teal-700 font-medium' : '' }}">
                            FSAID
                        </a>
                    </div>
                </details>

                {{-- My Purchases quick-link (context-aware) --}}
                @if(request()->routeIs('autoshop.fsaid.*'))
                <a href="{{ route('autoshop.fsaid.my-purchases') }}"
                   class="text-sm text-teal-700 hover:underline
                          {{ request()->routeIs('autoshop.fsaid.my-purchases') || request()->routeIs('autoshop.fsaid.receipt') ? 'font-medium' : '' }}">
                    My Purchases
                </a>
                @else
                <a href="{{ route('autoshop.fullz.my-purchases') }}"
                   class="text-sm text-teal-700 hover:underline
                          {{ request()->routeIs('autoshop.fullz.my-purchases') || request()->routeIs('autoshop.fullz.receipt') ? 'font-medium' : '' }}">
                    My Purchases
                </a>
                @endif
            </div>
        </div>
    </nav>

    {{-- Breadcrumbs --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-2.5">
            <nav class="flex items-center space-x-2 text-sm text-gray-500">
                <a href="{{ route('autoshop.fullz.index') }}" class="text-teal-700 hover:text-teal-800">
                    Autoshop
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

            {{-- Flash messages --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                    <div class="font-medium text-sm">Error</div>
                    @foreach ($errors->all() as $error)
                        <div class="text-sm mt-1">{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                    <div class="font-medium text-sm">Success</div>
                    <div class="text-sm mt-1">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                    <div class="font-medium text-sm">Error</div>
                    <div class="text-sm mt-1">{{ session('error') }}</div>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800">
                    <div class="font-medium text-sm">Warning</div>
                    <div class="text-sm mt-1">{{ session('warning') }}</div>
                </div>
            @endif

            {{-- Content --}}
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between text-sm text-gray-500">
                <p>{{ config('app.name') }} — Autoshop</p>
                <span>&copy; {{ date('Y') }}</span>
            </div>
        </div>
    </footer>

</div>
</body>
</html>
