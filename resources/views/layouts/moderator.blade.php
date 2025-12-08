{{-- resources/views/layouts/moderator.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page-title', 'Moderator') - {{ config('app.name', 'Laravel') }}</title>

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
            background: #1e40af;
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
<body class="bg-gray-50 min-h-screen">
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
        <div class="bg-blue-900 rounded-lg p-6 mb-6 text-left">
            <h3 class="font-bold text-lg mb-2">To disable JavaScript:</h3>
            <ul class="space-y-2 text-sm">
                <li><strong>Tor Browser:</strong> Click the shield icon next to the address bar → Advanced Security Settings → Select "Safest" security level</li>
            </ul>
        </div>
        <a href="{{ url()->current() }}" class="inline-block bg-white text-blue-900 px-8 py-3 rounded-lg font-bold text-lg hover:bg-blue-50">
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
{{-- Moderator Header --}}
<div class="bg-blue-700 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="h-12 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                {{-- Role Badge --}}
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                    <span class="text-sm font-medium">
                            {{ auth()->user()->hasRole('admin') ? 'Admin' : 'Moderator' }}
                        </span>
                </div>

                {{-- Quick Stats --}}
                <div class="hidden sm:flex items-center space-x-3 text-blue-200">
                    <div class="text-xs">
                        <span class="font-medium">{{ \App\Models\ForumReport::pending()->count() }}</span>
                        <span>pending reports</span>
                    </div>
                </div>

                <div class="h-4 w-px bg-blue-600"></div>

                {{-- Quick Actions --}}
                <div class="flex items-center space-x-2">
                    <a href="{{ route('home') }}"
                       class="px-3 py-1 bg-blue-600 text-blue-50 rounded hover:bg-blue-800 text-sm">
                        View Site
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1 border border-blue-600 text-blue-100 rounded hover:bg-blue-600 text-sm">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Moderator Navigation --}}
<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex space-x-1">
                <a href="{{ route('moderator.dashboard') }}"
                   class="px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded
                              {{ request()->routeIs('moderator.dashboard') ? 'bg-blue-100 text-blue-700' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('moderator.tickets.index') }}"
                   class="px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded
                              {{ request()->routeIs('moderator.tickets.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                    Tickets
                    @if(\App\Models\SupportTicket::whereNull('assigned_to')->whereIn('category', ['account_issues', 'user_reports', 'content_moderation', 'dispute_appeals'])->whereIn('status', ['open', 'pending'])->count() > 0)
                        <span class="ml-1 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                {{ \App\Models\SupportTicket::whereNull('assigned_to')->whereIn('category', ['account_issues', 'user_reports', 'content_moderation', 'dispute_appeals'])->whereIn('status', ['open', 'pending'])->count() }}
                            </span>
                    @endif
                </a>
                <a href="{{ route('moderator.forum.moderate.reports') }}"
                   class="px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded
                              {{ request()->routeIs('moderator.forum.moderate.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                    Forum Reports
                    @if(\App\Models\ForumReport::pending()->count() > 0)
                        <span class="ml-1 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                {{ \App\Models\ForumReport::pending()->count() }}
                            </span>
                    @endif
                </a>
                <a href="{{ route('moderator.disputes.index') }}"
                   class="px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded
                              {{ request()->routeIs('moderator.disputes.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                    Disputes
                    @if(\App\Models\Dispute::whereNull('assigned_moderator_id')->whereIn('status', ['open'])->count() > 0)
                        <span class="ml-1 bg-purple-500 text-white text-xs px-2 py-1 rounded-full">
                                {{ \App\Models\Dispute::whereNull('assigned_moderator_id')->whereIn('status', ['open'])->count() }}
                            </span>
                    @endif
                </a>
                <a href="{{ route('moderator.users.index') }}"
                   class="px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded
                              {{ request()->routeIs('moderator.users.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                    User Management
                </a>
                <a href="{{ route('moderator.content.index') }}"
                   class="px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded
                              {{ request()->routeIs('moderator.content.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                    Content Review
                </a>
                <a href="{{ route('moderator.audit.index') }}"
                   class="px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded
                              {{ request()->routeIs('moderator.audit.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                    Audit Logs
                </a>
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('moderator.settings') }}"
                       class="px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded
                                  {{ request()->routeIs('moderator.settings') ? 'bg-blue-100 text-blue-700' : '' }}">
                        Settings
                    </a>
                @endif
            </div>
        </div>

        {{-- Mobile Navigation (details/summary) --}}
        <details class="md:hidden">
            <summary class="p-2 text-gray-700 hover:text-blue-700 cursor-pointer list-none">
                <span class="sr-only">Toggle moderator menu</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </summary>
            <div class="bg-white border-t border-gray-200">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="{{ route('moderator.dashboard') }}"
                       class="block px-3 py-2 text-gray-700 hover:bg-blue-50 rounded {{ request()->routeIs('moderator.dashboard') ? 'bg-blue-100 text-blue-700' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('moderator.tickets.index') }}"
                       class="block px-3 py-2 text-gray-700 hover:bg-blue-50 rounded {{ request()->routeIs('moderator.tickets.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                        Tickets
                        @if(\App\Models\SupportTicket::whereNull('assigned_to')->whereIn('category', ['account_issues', 'user_reports', 'content_moderation', 'dispute_appeals'])->whereIn('status', ['open', 'pending'])->count() > 0)
                            <span class="ml-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                {{ \App\Models\SupportTicket::whereNull('assigned_to')->whereIn('category', ['account_issues', 'user_reports', 'content_moderation', 'dispute_appeals'])->whereIn('status', ['open', 'pending'])->count() }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('moderator.forum.moderate.reports') }}"
                       class="block px-3 py-2 text-gray-700 hover:bg-blue-50 rounded {{ request()->routeIs('moderator.forum.moderate.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                        Forum Reports
                        @if(\App\Models\ForumReport::pending()->count() > 0)
                            <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                {{ \App\Models\ForumReport::pending()->count() }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('moderator.disputes.index') }}"
                       class="block px-3 py-2 text-gray-700 hover:bg-blue-50 rounded {{ request()->routeIs('moderator.disputes.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                        Disputes
                        @if(\App\Models\Dispute::whereNull('assigned_moderator_id')->whereIn('status', ['open'])->count() > 0)
                            <span class="ml-2 bg-purple-500 text-white text-xs px-2 py-1 rounded-full">
                                {{ \App\Models\Dispute::whereNull('assigned_moderator_id')->whereIn('status', ['open'])->count() }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('moderator.users.index') }}"
                       class="block px-3 py-2 text-gray-700 hover:bg-blue-50 rounded {{ request()->routeIs('moderator.users.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                        User Management
                    </a>
                    <a href="{{ route('moderator.content.index') }}"
                       class="block px-3 py-2 text-gray-700 hover:bg-blue-50 rounded {{ request()->routeIs('moderator.content.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                        Content Review
                    </a>
                    <a href="{{ route('moderator.audit.index') }}"
                       class="block px-3 py-2 text-gray-700 hover:bg-blue-50 rounded {{ request()->routeIs('moderator.audit.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                        Audit Logs
                    </a>
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('moderator.settings') }}"
                           class="block px-3 py-2 text-gray-700 hover:bg-blue-50 rounded {{ request()->routeIs('moderator.settings') ? 'bg-blue-100 text-blue-700' : '' }}">
                            Settings
                        </a>
                    @endif
                </div>
            </div>
        </details>
    </div>
</nav>

{{-- Main Content Area --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Page Header --}}
    <div class="mb-8">
        {{-- Breadcrumbs --}}
        @if(View::hasSection('breadcrumbs'))
            <nav class="flex text-sm text-gray-500 mb-4">
                <a href="{{ route('moderator.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                @if(!View::hasSection('breadcrumbs') || trim(View::yieldContent('breadcrumbs')) !== 'Dashboard')
                    <span class="mx-2">/</span>
                    @yield('breadcrumbs')
                @endif
            </nav>
        @endif

        {{-- Page Title and Description --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    @yield('page-heading', 'Moderator Panel')
                </h1>
                @if(View::hasSection('page-description'))
                    <p class="mt-1 text-sm text-gray-600">
                        @yield('page-description')
                    </p>
                @endif
            </div>

            {{-- Page Actions --}}
            @if(View::hasSection('page-actions'))
                <div class="flex items-center space-x-3">
                    @yield('page-actions')
                </div>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded">
            {{ session('warning') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <h4 class="font-medium mb-2">Please correct the following errors:</h4>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>
</div>

{{-- Footer --}}
<footer class="bg-white border-t border-gray-200 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between text-sm text-gray-500">
            <div>
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
            </div>
            <div class="flex items-center space-x-4">
                <span>Moderator Panel v1.0</span>
                <span>•</span>
                <a href="{{ route('home') }}" class="hover:text-blue-600">Back to Site</a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
