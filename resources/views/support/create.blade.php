@extends('layouts.app')
@section('page-title', 'Create Support Ticket')

@section('breadcrumbs')
    <a href="{{ route('support.index') }}" class="text-amber-700 hover:text-amber-900">Support</a>
    <span class="text-amber-400">/</span>
    <span class="text-amber-700">Create Ticket</span>
@endsection

@section('page-heading')
    Create Support Ticket
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Get Support</h1>
            <p class="text-gray-600">Describe your issue and our support team will help you resolve it quickly.</p>
        </div>

        {{-- Create Ticket Form --}}
        <form action="{{ route('support.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Ticket Details</h3>

                {{-- Category Selection --}}
                <div class="mb-6">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select name="category" id="category" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Select a category...</option>
                        <option value="account" {{ old('category', $selectedCategory) === 'account' ? 'selected' : '' }}>
                            Account Issues
                        </option>
                        <option value="payments" {{ old('category', $selectedCategory) === 'payments' ? 'selected' : '' }}>
                            Payment Issues
                        </option>
                        <option value="orders" {{ old('category', $selectedCategory) === 'orders' ? 'selected' : '' }}>
                            Order Issues
                        </option>
                        <option value="technical" {{ old('category', $selectedCategory) === 'technical' ? 'selected' : '' }}>
                            Technical Issues
                        </option>
                        <option value="general" {{ old('category', $selectedCategory) === 'general' ? 'selected' : '' }}>
                            General Inquiry
                        </option>
                    </select>
                    @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Issue Type Selection --}}
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Issue Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Select issue type...</option>
                        @foreach($ticketTypes as $category => $types)
                            <optgroup label="{{ ucfirst($category) }} Issues">
                                @foreach($types as $typeValue => $typeLabel)
                                    <option value="{{ $typeValue }}"
                                            data-category="{{ $category }}"
                                        {{ old('type', $selectedType) === $typeValue ? 'selected' : '' }}>
                                        {{ $typeLabel }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Subject --}}
                <div class="mb-6">
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" id="subject" required
                           value="{{ old('subject') }}"
                           placeholder="Brief summary of your issue..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="6" required
                              placeholder="Please provide detailed information about your issue. Include any relevant details like error messages, transaction IDs, order numbers, etc."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">{{ old('description') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Be as specific as possible to help us resolve your issue quickly.</p>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Priority Selection --}}
                <div class="mb-6">
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        Priority
                    </label>
                    <select name="priority" id="priority"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>
                            Medium (Default - Most issues)
                        </option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>
                            Low (General questions)
                        </option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>
                            High (Account or payment issues)
                        </option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">We'll automatically adjust priority based on issue type if needed.</p>
                    @error('priority')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- File Attachment --}}
                <div class="mb-6">
                    <label for="attachment" class="block text-sm font-medium text-gray-700 mb-2">
                        Attachment (Optional)
                    </label>
                    <input type="file" name="attachment" id="attachment"
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <p class="mt-1 text-sm text-gray-500">
                        Upload screenshots, documents, or other files that help explain your issue.
                        Max 10MB. Supported: JPG, PNG, PDF, DOC, TXT
                    </p>
                    @error('attachment')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Additional Context for Specific Issue Types --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="text-sm font-medium text-blue-800 mb-3">Helpful Information</h4>
                <div class="text-sm text-blue-700 space-y-2">
                    <div id="help-account" class="hidden">
                        <p><strong>Account Issues:</strong> Please include your username and describe what happened when the issue occurred.</p>
                    </div>
                    <div id="help-payments" class="hidden">
                        <p><strong>Payment Issues:</strong> Please include transaction IDs, wallet addresses, amounts, and timestamps.</p>
                    </div>
                    <div id="help-orders" class="hidden">
                        <p><strong>Order Issues:</strong> Please include the order number and describe the specific problem with your order.</p>
                    </div>
                    <div id="help-technical" class="hidden">
                        <p><strong>Technical Issues:</strong> Please include browser information, error messages, and steps to reproduce the issue.</p>
                    </div>
                    <div id="help-general" class="hidden">
                        <p><strong>General Inquiry:</strong> Please provide as much context as possible about your question.</p>
                    </div>
                </div>
            </div>

            {{-- Important Information --}}
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-6">
                <h4 class="text-sm font-medium text-amber-800 mb-3">Before You Submit</h4>
                <div class="text-sm text-amber-700 space-y-2">
                    <p>• <strong>Response Time:</strong> We typically respond within 24 hours during business days</p>
                    <p>• <strong>High Priority:</strong> Account access and payment issues are prioritized</p>
                    <p>• <strong>Security:</strong> Never share your passwords or private keys in support tickets</p>
                    <p>• <strong>Updates:</strong> You'll receive email notifications when we respond to your ticket</p>
                    <p>• <strong>Follow-up:</strong> You can reply to your ticket to provide additional information</p>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex items-center justify-between space-x-4">
                <a href="{{ route('support.index') }}"
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                    Cancel
                </a>

                <button type="submit"
                        class="px-6 py-3 bg-amber-600 text-white rounded-md hover:bg-amber-700 transition-colors">
                    Create Support Ticket
                </button>
            </div>
        </form>

        {{-- Quick Help Links --}}
        <div class="mt-8 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Help</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Common Issues</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Password reset problems</li>
                        <li>• Bitcoin/Monero deposit delays</li>
                        <li>• Account verification help</li>
                        <li>• Order status questions</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Before Creating a Ticket</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Check if the issue is resolved</li>
                        <li>• Gather relevant information</li>
                        <li>• Take screenshots if applicable</li>
                        <li>• Note exact error messages</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Pure CSS Category/Type Filtering --}}
    <style>
        /* Hide all help sections by default */
        .help-section {
            display: none;
        }

        /* Show help section when category is selected */
        #category:valid ~ .help-container #help-account,
        #category:valid ~ .help-container #help-payments,
        #category:valid ~ .help-container #help-orders,
        #category:valid ~ .help-container #help-technical,
        #category:valid ~ .help-container #help-general {
            display: block;
        }
    </style>
@endsection
