<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') - {{ config('app.name') }}</title>
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
            background: #b45309;
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
        <div class="bg-amber-900 rounded-lg p-6 mb-6 text-left">
            <h3 class="font-bold text-lg mb-2">To disable JavaScript:</h3>
            <ul class="space-y-2 text-sm">
                <li><strong>Tor Browser:</strong> Click the shield icon next to the address bar → Advanced Security Settings → Select "Safest" security level</li>
            </ul>
        </div>
        <a href="{{ url()->current() }}" class="inline-block bg-white text-amber-900 px-8 py-3 rounded-lg font-bold text-lg hover:bg-amber-50">
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
    <header class="bg-white border-b border-gray-200 py-6">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex flex-col items-center md:items-start gap-4">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('logo-h.png') }}" alt="{{ config('app.name') }}" class="h-10 w-auto">
                    </a>
                    <div class="text-center md:text-left">
                        @yield('page-heading')
                    </div>
                </div>
                <nav class="flex gap-3">
                    <a href="{{ route('market-keys') }}"
                       class="px-4 py-2 text-amber-700 border border-amber-300 rounded hover:bg-amber-50">
                        Staff Keys
                    </a>
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 text-yellow-700 border border-yellow-300 rounded hover:bg-yellow-50">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-4 py-2 bg-yellow-600 text-white border border-yellow-600 rounded hover:bg-yellow-700">
                        Register
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
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
    </main>

    <footer class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <p class="text-sm text-gray-600">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </footer>
</div>
</body>
</html>
