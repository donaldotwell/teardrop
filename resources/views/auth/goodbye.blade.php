@extends('layouts.auth')

@section('title', 'Signed Out')

@section('page-heading')
    <h1 class="text-2xl font-semibold text-gray-900">We'll miss you.</h1>
    <p class="text-gray-600 mt-1">You've been signed out.</p>
@endsection

@section('content')
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">

    <div class="h-1 bg-gradient-to-r from-yellow-500 to-amber-500"></div>

    <div class="p-8">

        <p class="text-gray-600 text-sm mb-6">
            We can't wait to see you back. Close this tab when you're done.
        </p>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-8">
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start gap-2">
                    <span class="text-amber-500 font-bold flex-shrink-0 leading-5">›</span>
                    Clear your clipboard if you copied any wallet addresses or keys
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-amber-500 font-bold flex-shrink-0 leading-5">›</span>
                    On a shared device, close the entire Tor Browser window
                </li>
            </ul>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('login') }}"
               class="flex-1 text-center py-2.5 px-4 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded transition-colors">
                Sign In Again
            </a>
            <a href="{{ route('register') }}"
               class="flex-1 text-center py-2.5 px-4 border border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 font-medium rounded transition-colors">
                Create Account
            </a>
        </div>

    </div>
</div>
@endsection
