@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">

        {{-- Mobile: prev / next only --}}
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default leading-5 rounded-md">
                    &larr; {{ __('pagination.previous') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-amber-700 bg-white border border-gray-200 leading-5 rounded-md hover:bg-amber-50 transition-colors">
                    &larr; {{ __('pagination.previous') }}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-amber-700 bg-white border border-gray-200 leading-5 rounded-md hover:bg-amber-50 transition-colors">
                    {{ __('pagination.next') }} &rarr;
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default leading-5 rounded-md">
                    {{ __('pagination.next') }} &rarr;
                </span>
            @endif
        </div>

        {{-- Desktop: full pagination --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 leading-5">
                    {{ __('Showing') }}
                    @if ($paginator->firstItem())
                        <span class="font-medium text-gray-700">{{ $paginator->firstItem() }}</span>
                        {{ __('to') }}
                        <span class="font-medium text-gray-700">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {{ __('of') }}
                    <span class="font-medium text-gray-700">{{ $paginator->total() }}</span>
                    {{ __('results') }}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rtl:flex-row-reverse shadow-sm rounded-md">

                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                &larr;
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                           class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-amber-700 bg-white border border-gray-200 rounded-l-md leading-5 hover:bg-amber-50 transition-colors"
                           aria-label="{{ __('pagination.previous') }}">
                            &larr;
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-400 bg-white border border-gray-200 cursor-default leading-5">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-semibold text-white bg-amber-600 border border-amber-600 cursor-default leading-5">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                       class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-200 leading-5 hover:bg-amber-50 hover:text-amber-700 transition-colors"
                                       aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                           class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-amber-700 bg-white border border-gray-200 rounded-r-md leading-5 hover:bg-amber-50 transition-colors"
                           aria-label="{{ __('pagination.next') }}">
                            &rarr;
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                &rarr;
                            </span>
                        </span>
                    @endif

                </span>
            </div>
        </div>
    </nav>
@endif
