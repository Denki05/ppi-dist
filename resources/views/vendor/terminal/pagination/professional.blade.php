@if ($paginator->hasPages())
<nav class="d-flex justify-content-center mt-3">
    <ul class="pagination pagination-sm mb-0">

        {{-- First --}}
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link"
               href="{{ $paginator->url(1) }}">&laquo;</a>
        </li>

        {{-- Prev --}}
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link"
               href="{{ $paginator->previousPageUrl() }}">&lsaquo;</a>
        </li>

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled">
                    <span class="page-link">{{ $element }}</span>
                </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                        <a class="page-link"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            <a class="page-link"
               href="{{ $paginator->nextPageUrl() }}">&rsaquo;</a>
        </li>

        {{-- Last --}}
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            <a class="page-link"
               href="{{ $paginator->url($paginator->lastPage()) }}">&raquo;</a>
        </li>

    </ul>
</nav>
@endif