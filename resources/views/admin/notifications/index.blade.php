@extends('layouts.admin')

@section('page-title', 'Broadcast Notification — Admin')
@section('page-heading', 'Broadcast Notification')

@section('breadcrumbs')
<a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900">Dashboard</a>
<span class="text-gray-400 mx-1">/</span>
<span>Broadcast Notification</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl p-6">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">
            Send to All Users
        </h2>

        <form method="POST" action="{{ route('admin.notifications.broadcast') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Type</label>
                <select name="type" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400">
                    <option value="">— select —</option>
                    <option value="system" {{ old('type') === 'system' ? 'selected' : '' }}>System</option>
                    <option value="order" {{ old('type') === 'order' ? 'selected' : '' }}>Order</option>
                    <option value="dispute" {{ old('type') === 'dispute' ? 'selected' : '' }}>Dispute</option>
                    <option value="support" {{ old('type') === 'support' ? 'selected' : '' }}>Support</option>
                    <option value="forum" {{ old('type') === 'forum' ? 'selected' : '' }}>Forum</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" maxlength="255" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400"
                       placeholder="Notification title">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Body</label>
                <textarea name="body" rows="4" maxlength="1000" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400"
                          placeholder="Notification message body">{{ old('body') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    Link URL <span class="font-normal text-gray-400">(optional — relative path, e.g. /orders)</span>
                </label>
                <input type="text" name="url" value="{{ old('url') }}" maxlength="500"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400"
                       placeholder="/orders">
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Broadcast to All Users
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
