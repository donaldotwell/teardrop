@extends('layouts.admin')

@section('title', 'Edit Canary')

@section('page-heading')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-amber-900">Edit Canary #{{ $canary->id }}</h1>
            <p class="text-amber-700 mt-1">Update warrant canary message</p>
        </div>
        <a href="{{ route('admin.canaries.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden border border-amber-200">
    <form action="{{ route('admin.canaries.update', $canary) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="p-6 space-y-6">
            <div class="bg-gray-50 border border-gray-200 rounded p-4">
                <p class="text-sm text-gray-600">
                    <strong>Created:</strong> {{ $canary->created_at->format('F j, Y \a\t g:i A') }} UTC
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    <strong>Last Updated:</strong> {{ $canary->updated_at->format('F j, Y \a\t g:i A') }} UTC
                </p>
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    Canary Message (PGP-Signed)
                </label>
                <textarea 
                    name="message" 
                    id="message" 
                    rows="20"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm @error('message') border-red-500 @enderror">{{ old('message', $canary->message) }}</textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">
                    Sign your updated message with your admin PGP key before saving.
                </p>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.canaries.index') }}" 
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-100">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                Update Canary
            </button>
        </div>
    </form>
</div>
@endsection
