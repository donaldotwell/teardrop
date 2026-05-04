@extends('layouts.admin')

@section('page-title', 'Compose Message')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Compose Message</h1>
        <p class="text-sm text-gray-500 mt-1">Send a message to a group of users. Each recipient receives it in their personal inbox.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form action="{{ route('admin.messages.send') }}" method="POST">
        @csrf

        {{-- Recipient Scope --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Recipients</h2>
            <div class="space-y-3">

                <label class="flex items-center justify-between p-3 border rounded-lg cursor-pointer
                              {{ old('scope') === 'all' || !old('scope') ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-300' }}">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="scope" value="all"
                               {{ old('scope', 'all') === 'all' ? 'checked' : '' }}
                               class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500">
                        <div>
                            <span class="text-sm font-medium text-gray-800">All Users</span>
                            <p class="text-xs text-gray-500">Buyers, vendors and moderators — excludes admins and banned accounts</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full">
                        {{ number_format($counts['all']) }}
                    </span>
                </label>

                <label class="flex items-center justify-between p-3 border rounded-lg cursor-pointer
                              {{ old('scope') === 'vendors' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-300' }}">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="scope" value="vendors"
                               {{ old('scope') === 'vendors' ? 'checked' : '' }}
                               class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500">
                        <div>
                            <span class="text-sm font-medium text-gray-800">Vendors only</span>
                            <p class="text-xs text-gray-500">Active vendor accounts only</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full">
                        {{ number_format($counts['vendors']) }}
                    </span>
                </label>

                <label class="flex items-center justify-between p-3 border rounded-lg cursor-pointer
                              {{ old('scope') === 'moderators' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-300' }}">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="scope" value="moderators"
                               {{ old('scope') === 'moderators' ? 'checked' : '' }}
                               class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500">
                        <div>
                            <span class="text-sm font-medium text-gray-800">Moderators only</span>
                            <p class="text-xs text-gray-500">Staff and moderator accounts only</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full">
                        {{ number_format($counts['moderators']) }}
                    </span>
                </label>

            </div>
        </div>

        {{-- Message Body --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Message</h2>
            <textarea name="body"
                      rows="8"
                      maxlength="5000"
                      required
                      placeholder="Write your message here..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-500 resize-y @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
            <p class="text-xs text-gray-400 mt-1.5">Maximum 5,000 characters. Recipients will see this in their Messages inbox.</p>
        </div>

        {{-- Warning + Submit --}}
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-5">
            <p class="text-xs text-amber-800">
                This action cannot be undone. Sending is handled in the background — recipients will receive the message in their inbox within moments.
                You can navigate away immediately after submitting.
            </p>
        </div>

        <button type="submit"
                class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition-colors">
            Send Message
        </button>
    </form>
</div>
@endsection
