@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-between">
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default leading-5 rounded-md">
                &larr; {{ __('pagination.previous') }}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-amber-700 bg-white border border-gray-200 leading-5 rounded-md hover:bg-amber-50 transition-colors">
                &larr; {{ __('pagination.previous') }}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-amber-700 bg-white border border-gray-200 leading-5 rounded-md hover:bg-amber-50 transition-colors">
                {{ __('pagination.next') }} &rarr;
            </a>
        @else
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default leading-5 rounded-md">
                {{ __('pagination.next') }} &rarr;
            </span>
        @endif
    </nav>
@endif
