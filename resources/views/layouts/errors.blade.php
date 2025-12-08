<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .js-warning {
            display: none;
        }
        @if(app()->environment('production'))
        .js-warning-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%);
            color: white;
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }
        .js-warning-overlay h1 {
            font-size: 6rem;
            margin-bottom: 2rem;
        }
        .js-warning-overlay h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .js-warning-overlay p {
            font-size: 1.125rem;
            max-width: 600px;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .js-warning-overlay .instructions {
            background: rgba(0, 0, 0, 0.2);
            padding: 1.5rem;
            border-radius: 0.5rem;
            max-width: 500px;
            margin-bottom: 2rem;
            text-align: left;
        }
        .js-warning-overlay .instructions h3 {
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .js-warning-overlay .instructions ul {
            list-style: disc;
            margin-left: 1.5rem;
            line-height: 1.8;
        }
        .js-warning-overlay a {
            display: inline-block;
            background: white;
            color: #991b1b;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .js-warning-overlay a:hover {
            background: #f3f4f6;
        }
        @endif
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(app()->environment('production'))
            var overlay = document.querySelector('.js-warning-overlay');
            if (overlay) {
                overlay.style.display = 'flex';
            }
            @else
            var warning = document.querySelector('.js-warning');
            if (warning) {
                warning.style.display = 'block';
            }
            @endif
        });
    </script>
</head>
<body class="bg-gradient-to-br from-amber-50 to-amber-100">
@if(app()->environment('production'))
<noscript>
    <style>
        .js-warning-overlay {
            display: none !important;
        }
    </style>
</noscript>
<div class="js-warning-overlay">
    <h2>Access Denied</h2>
    <p>
        This site requires JavaScript to be disabled in your browser. Please disable JavaScript 
        and refresh this page to continue.
    </p>
    <div class="instructions">
        <h3>To disable JavaScript:</h3>
        <ul>
            <li><strong>Tor Browser:</strong> Click the shield icon next to the address bar → Advanced Security Settings → Select "Safest" security level</li>
        </ul>
    </div>
    <a href="{{ url()->current() }}">Refresh Page</a>
</div>
@else
<noscript>
    <div style="background: #10b981; color: white; padding: 1rem; text-align: center; font-weight: bold;">
        Access granted.
    </div>
</noscript>
<div class="js-warning" style="background: #ef4444; color: white; padding: 1rem; text-align: center; font-weight: bold;">
    Warning: JavaScript detected. JavaScript must be disabled to visit this page.
</div>
@endif
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full text-center">
        <!-- Error Icon -->
        <div class="mx-auto mb-8 text-amber-600">
            <div class="h-24 w-24 mx-auto flex items-center justify-center text-6xl font-bold">!</div>
        </div>

        <!-- Error Content -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-amber-200">
            <h1 class="text-4xl md:text-5xl font-bold text-amber-800 mb-4">
                @yield('code')
            </h1>
            <div class="text-lg text-gray-600 mb-6">
                @yield('message')
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-center gap-3 mt-8">
                <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 transition-colors">
                    Return Home
                </a>
                <a href="{{ url()->current() }}" class="inline-flex items-center px-6 py-3 border border-amber-600 text-sm font-medium rounded-md text-amber-700 bg-white hover:bg-amber-50 transition-colors">
                    Refresh Page
                </a>
            </div>
        </div>

        <!-- Technical Details (Optional) -->
        @hasSection('details')
            <div class="mt-8 text-sm text-gray-500 text-left bg-amber-50 p-4 rounded-lg">
                @yield('details')
            </div>
        @endif
    </div>
</div>
</body>
</html>
