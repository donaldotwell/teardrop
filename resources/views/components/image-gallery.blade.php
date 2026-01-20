@props(['images', 'title' => 'Product Images', 'modalId'])

@php
    $imageCollection = is_a($images, 'Illuminate\Support\Collection') ? $images : collect($images);
    $firstImage = $imageCollection->first();
    $imageCount = $imageCollection->count();
@endphp

<div class="relative">
    @if($imageCount > 0)
        <!-- Main Image with Click to Open Gallery -->
        <div class="relative group cursor-pointer">
            <label for="{{ $modalId }}" class="cursor-pointer">
                <img src="{{ $firstImage->data_uri }}"
                     alt="{{ $title }}"
                     class="w-full h-full object-contain bg-white border border-gray-200 rounded-lg p-2 transition-transform duration-300 group-hover:scale-105">

                @if($imageCount > 1)
                    <!-- Image Counter Badge -->
                    <div class="absolute bottom-3 right-3 bg-black/75 text-white text-xs px-3 py-1.5 rounded-full font-medium backdrop-blur-sm">
                        <span class="font-bold">{{ $imageCount }}</span> {{ $imageCount === 1 ? 'image' : 'images' }}
                    </div>
                @endif

                <!-- Hover Overlay -->
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all rounded-lg flex items-center justify-center">
                    <span class="text-white font-medium opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 px-4 py-2 rounded-lg">
                        Click to view {{ $imageCount > 1 ? 'all images' : 'image' }}
                    </span>
                </div>
            </label>
        </div>

        <!-- Hidden Checkbox -->
        <input type="checkbox" id="{{ $modalId }}" class="peer hidden" aria-hidden="true" />

        <!-- Modal Backdrop -->
        <div class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/80 backdrop-blur-sm peer-checked:flex animate-in fade-in-0 items-center justify-center p-4">
            <!-- Modal Content -->
            <div role="dialog"
                 aria-modal="true"
                 aria-labelledby="{{ $modalId }}-title"
                 class="relative w-full max-w-6xl bg-white rounded-xl shadow-2xl animate-in zoom-in-95 slide-in-from-bottom-4 max-h-[90vh] overflow-hidden">

                <!-- Close Button -->
                <label for="{{ $modalId }}"
                       class="absolute top-4 right-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-black/50 hover:bg-black/70 transition-colors cursor-pointer text-white font-bold text-2xl"
                       tabindex="0"
                       aria-label="Close gallery">
                    ×
                </label>

                <!-- Modal Header -->
                <div class="p-6 pb-4 border-b border-gray-200 bg-gray-50">
                    <h2 id="{{ $modalId }}-title" class="text-2xl font-bold text-gray-900">
                        {{ $title }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $imageCount }} {{ $imageCount === 1 ? 'image' : 'images' }}</p>
                </div>

                <!-- Modal Body - Image Grid -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                    <div class="grid grid-cols-1 @if($imageCount > 1) md:grid-cols-2 @endif gap-6">
                        @foreach($imageCollection as $index => $image)
                            <div class="relative group">
                                <div class="bg-gray-100 rounded-lg overflow-hidden border-2 border-gray-200 hover:border-yellow-400 transition-colors">
                                    <img src="{{ $image->data_uri }}"
                                         alt="{{ $title }} - Image {{ $index + 1 }}"
                                         class="w-full h-auto object-contain p-4 max-h-[500px]">
                                </div>
                                <div class="absolute top-2 left-2 bg-yellow-600 text-white text-xs px-2 py-1 rounded-full font-medium">
                                    {{ $index + 1 }} / {{ $imageCount }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    <label for="{{ $modalId }}"
                           class="block w-full px-4 py-2.5 text-center border-2 border-yellow-700 text-yellow-700 rounded-lg hover:bg-yellow-700 hover:text-white transition-colors duration-200 cursor-pointer font-medium">
                        Close Gallery
                    </label>
                </div>
            </div>
        </div>
    @else
        <!-- No Image Placeholder -->
        <div class="w-full h-full flex items-center justify-center bg-gray-100 border border-gray-200 rounded-lg">
            <span class="text-gray-400 text-sm">No images</span>
        </div>
    @endif
</div>
