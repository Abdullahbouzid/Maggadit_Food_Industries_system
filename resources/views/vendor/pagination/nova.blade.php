@if ($paginator->hasPages())
<nav class="nova-pagination-nav" role="navigation" aria-label="Pagination">
    <ul class="nova-pagination-list">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="disabled"><span class="nova-page-btn" aria-disabled="true">‹</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" class="nova-page-btn" rel="prev" aria-label="Previous">‹</a></li>
        @endif

        @php
            $currentPage = $paginator->currentPage();
            $lastPage    = $paginator->lastPage();
            $startPage   = max(1, $currentPage - 2);
            $endPage     = min($lastPage, $currentPage + 2);
        @endphp

        @if ($startPage > 1)
            <li><a href="{{ $paginator->url(1) }}" class="nova-page-btn">1</a></li>
            @if ($startPage > 2)
                <li class="disabled"><span class="nova-page-btn">…</span></li>
            @endif
        @endif

        @for ($page = $startPage; $page <= $endPage; $page++)
            @if ($page == $currentPage)
                <li class="active"><span class="nova-page-btn nova-page-active">{{ $page }}</span></li>
            @else
                <li><a href="{{ $paginator->url($page) }}" class="nova-page-btn">{{ $page }}</a></li>
            @endif
        @endfor

        @if ($endPage < $lastPage)
            @if ($endPage < $lastPage - 1)
                <li class="disabled"><span class="nova-page-btn">…</span></li>
            @endif
            <li><a href="{{ $paginator->url($lastPage) }}" class="nova-page-btn">{{ $lastPage }}</a></li>
        @endif

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" class="nova-page-btn" rel="next" aria-label="Next">›</a></li>
        @else
            <li class="disabled"><span class="nova-page-btn" aria-disabled="true">›</span></li>
        @endif

    </ul>
</nav>
@endif