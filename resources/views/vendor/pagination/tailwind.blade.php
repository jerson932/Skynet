@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center py-4">
        <ul class="inline-flex items-center -space-x-px rounded-md overflow-hidden">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="px-2 py-1 bg-white text-gray-300 border border-gray-200">&laquo;</li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-1 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">&laquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="px-3 py-1 bg-white border border-gray-200 text-gray-500">{{ $element }}</li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="px-3 py-1 bg-blue-600 text-white border border-blue-600">{{ $page }}</li>
                        @else
                            <li><a href="{{ $url }}" class="px-3 py-1 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-1 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">&raquo;</a>
                </li>
            @else
                <li class="px-2 py-1 bg-white text-gray-300 border border-gray-200">&raquo;</li>
            @endif
        </ul>
    </nav>
@endif
