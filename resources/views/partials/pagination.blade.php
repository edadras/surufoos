@if ($paginator->hasPages())
    <nav class="pagination" aria-label="pagination">
        @if ($paginator->onFirstPage())
            <span class="pagination__step is-disabled" aria-hidden="true"><x-icon name="chevron" class="flip-ltr"/></span>
        @else
            <a class="pagination__step" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('ui.prev') }}" data-page-link><x-icon name="chevron" class="flip-ltr"/></a>
        @endif
        <ol>
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="pagination__dots">…</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">{{ digits($page) }}</span>
                            @else
                                <a href="{{ $url }}" data-page-link>{{ digits($page) }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ol>
        @if ($paginator->hasMorePages())
            <a class="pagination__step" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('ui.next') }}" data-page-link><x-icon name="chevron" class="flip-rtl"/></a>
        @else
            <span class="pagination__step is-disabled" aria-hidden="true"><x-icon name="chevron" class="flip-rtl"/></span>
        @endif
    </nav>
@endif
