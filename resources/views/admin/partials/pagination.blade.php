@if ($paginator->hasPages())
    <nav class="a-pagination">
        @if ($paginator->previousPageUrl())<a href="{{ $paginator->previousPageUrl() }}">قبلی</a>@endif
        <span>صفحه {{ digits($paginator->currentPage(), 'fa') }} از {{ digits($paginator->lastPage(), 'fa') }}</span>
        @if ($paginator->nextPageUrl())<a href="{{ $paginator->nextPageUrl() }}">بعدی</a>@endif
    </nav>
@endif
