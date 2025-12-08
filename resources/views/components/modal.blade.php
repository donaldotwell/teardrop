<div class="relative">
    <!-- Hidden Checkbox -->
    <input type="checkbox" id="{{ $id }}" class="peer hidden" aria-hidden="true" />

    <!-- Trigger Button -->
    <label
        for="{{ $id }}"
        role="button"
        aria-haspopup="dialog"
        aria-expanded="false"
        class="{{ $triggerClass ?? 'inline-flex items-center justify-center px-6 py-3 font-medium transition-colors rounded-lg shadow-sm bg-primary-500 text-white hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2' }}"
    >
        {{ $triggerText ?? 'Open Modal' }}
    </label>

    <!-- Modal Backdrop -->
    <div
        class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/25 backdrop-blur-sm peer-checked:flex animate-in fade-in-0 items-center justify-center p-4"
    >
        <!-- Modal Content -->
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $id }}-title"
            class="relative w-full max-w-4xl bg-white rounded-xl shadow-2xl animate-in zoom-in-95 slide-in-from-bottom-4"
        >
            <!-- Close Button -->
            <label
                for="{{ $id }}"
                class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100/50 hover:bg-gray-100 transition-colors cursor-pointer text-gray-600 font-bold text-xl"
                tabindex="0"
                aria-label="Close modal"
            >
                ×
            </label>

            <!-- Modal Header -->
            @isset($title)
                <div class="p-6 pb-0">
                    <h2 id="{{ $id }}-title" class="text-2xl font-bold text-gray-900">
                        {{ $title }}
                    </h2>
                </div>
            @endisset

            <!-- Modal Body -->
            <div class="p-6 text-gray-600 prose">
                {{ $slot }}
            </div>

            <!-- Modal Footer -->
            @isset($footer)
                <div class="p-6 pt-4 border-t border-gray-200/50">
                    <div class="flex gap-3 justify-end">
                        {{ $footer }}
                    </div>
                </div>
            @endisset
        </div>
    </div>
</div>
