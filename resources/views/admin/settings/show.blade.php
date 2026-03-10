@extends('layouts.admin')
@section('page-title', 'Edit Settings')

@section('breadcrumbs')
    <a href="{{ route('admin.settings.index') }}" class="text-yellow-700 hover:text-yellow-800">Settings</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">{{ $allCategories[$category] }}</span>
@endsection

@section('page-heading')
    {{ $allCategories[$category] }}
@endsection

@section('page-description')
    Manage {{ strtolower($allCategories[$category]) }} settings
@endsection

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                <strong>✓</strong> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                <strong>✗</strong> {{ session('error') }}
            </div>
        @endif

        {{-- Settings Form --}}
        <form method="POST" action="{{ route('admin.settings.update-bulk', $category) }}" class="space-y-6">
            @csrf

            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $allCategories[$category] }}</h3>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse($settings as $setting)
                        <div class="px-6 py-4 hover:bg-gray-50 transition">
                            <div class="mb-2">
                                <label for="settings_{{ $setting->key }}" class="block text-sm font-medium text-gray-900">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                </label>
                                @if($setting->description)
                                    <p class="text-xs text-gray-500 mt-1">{{ $setting->description }}</p>
                                @endif
                            </div>

                            @switch($setting->data_type)
                                @case('boolean')
                                    <div class="flex items-center space-x-2">
                                        <input type="hidden"
                                               name="settings[{{ $setting->key }}]"
                                               value="0">
                                        <input type="checkbox"
                                               id="settings_{{ $setting->key }}"
                                               name="settings[{{ $setting->key }}]"
                                               value="1"
                                               {{ $setting->value === '1' ? 'checked' : '' }}
                                               class="rounded border-gray-300">
                                        <span class="text-sm text-gray-600">
                                            @if($setting->value === '1')
                                                <span class="text-green-600 font-medium">Enabled</span>
                                            @else
                                                <span class="text-gray-600">Disabled</span>
                                            @endif
                                        </span>
                                    </div>
                                    @break

                                @case('integer')
                                    <input type="number"
                                           id="settings_{{ $setting->key }}"
                                           name="settings[{{ $setting->key }}]"
                                           value="{{ $setting->value }}"
                                           step="1"
                                           min="0"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                           @error("settings.{$setting->key}") style="border-color: #f87171;" @enderror>
                                    @error("settings.{$setting->key}")
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                    @break

                                @case('decimal')
                                @case('float')
                                    <input type="number"
                                           id="settings_{{ $setting->key }}"
                                           name="settings[{{ $setting->key }}]"
                                           value="{{ $setting->value }}"
                                           step="0.01"
                                           min="0"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                           @error("settings.{$setting->key}") style="border-color: #f87171;" @enderror>
                                    @error("settings.{$setting->key}")
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                    @break

                                @case('json')
                                    <textarea id="settings_{{ $setting->key }}"
                                              name="settings[{{ $setting->key }}]"
                                              rows="4"
                                              class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 font-mono text-sm"
                                              @error("settings.{$setting->key}") style="border-color: #f87171;" @enderror>{{ $setting->value }}</textarea>
                                    @error("settings.{$setting->key}")
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                    @break

                                @default
                                    <input type="text"
                                           id="settings_{{ $setting->key }}"
                                           name="settings[{{ $setting->key }}]"
                                           value="{{ $setting->value }}"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                                           @error("settings.{$setting->key}") style="border-color: #f87171;" @enderror>
                                    @error("settings.{$setting->key}")
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                            @endswitch

                            <div class="mt-2 text-xs text-gray-400">
                                Type: <code class="bg-gray-100 px-1 py-0.5 rounded">{{ $setting->data_type }}</code>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">
                            No settings found in this category.
                        </div>
                    @endforelse
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                    <button type="submit"
                            class="px-6 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.settings.index') }}"
                       class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </div>
        </form>

        {{-- Category Navigation --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Other Categories</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($allCategories as $key => $label)
                    @if($key !== $category)
                        <a href="{{ route('admin.settings.show', $key) }}"
                           class="px-3 py-1 text-sm border border-gray-200 rounded hover:border-yellow-400 hover:bg-yellow-50 text-gray-700">
                            {{ $label }}
                        </a>
                    @else
                        <span class="px-3 py-1 text-sm bg-yellow-100 border border-yellow-300 text-yellow-800 rounded font-medium">
                            {{ $label }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>

    </div>
@endsection
