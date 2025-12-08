@extends('layouts.auth')
@section('page-title', 'Login')

@section('page-heading')
    <h1 class="text-2xl font-semibold text-gray-900">Welcome Back</h1>
    <p class="text-gray-600 mt-1">Sign in to your account</p>
@endsection

@section('content')
    <div class="bg-white p-8 border border-gray-200 rounded-lg">
        <form action="{{ route('login') }}" method="post" class="space-y-6" autocomplete="off">
            @csrf

            <div class="space-y-1">
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text"
                       name="username"
                       id="username"
                       value="{{ old('username') }}"
                       class="block w-full px-3 py-2 border @error('username') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                       required
                       autofocus>
                @error('username')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password"
                       name="password"
                       id="password"
                       class="block w-full px-3 py-2 border @error('password') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                       required>
                @error('password')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-4">
                <button type="submit"
                        class="w-full py-3 px-4 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700 focus:outline-none focus:bg-yellow-700">
                    Sign In
                </button>

                <div class="text-center">
                    <span class="text-sm text-gray-600">New to {{ config('app.name') }}? </span>
                    <a href="{{ route('register') }}" class="text-sm text-yellow-700 hover:text-yellow-800">
                        Create an account
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection
