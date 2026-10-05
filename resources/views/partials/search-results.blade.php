@if ($results['total'] === 0)
    <p class="search-empty">{{ __('ui.search.empty', ['term' => $term]) }}</p>
@else
    <div class="search-groups">
        @if ($results['products']->isNotEmpty())
            <section class="search-group search-group--products">
                <h2 class="eyebrow">{{ __('ui.search.products') }}</h2>
                <ul>
                    @foreach ($results['products'] as $p)
                        <li><a href="{{ $p->url() }}" class="search-hit" style="--tint: {{ $p->themeColor() }}">
                            <span class="search-hit__img"><x-picture :media="$p->displayImage()" size="thumbnail" sizes="64px"/></span>
                            <span><strong>{{ $p->tr('title') }}</strong><small>{{ $p->primaryCategory?->tr('title') }}</small></span>
                        </a></li>
                    @endforeach
                </ul>
            </section>
        @endif
        @foreach (['categories' => 'categories', 'recipes' => 'recipes', 'posts' => 'posts'] as $group => $label)
            @if ($results[$group]->isNotEmpty())
                <section class="search-group">
                    <h2 class="eyebrow">{{ __('ui.search.'.$label) }}</h2>
                    <ul>
                        @foreach ($results[$group] as $item)
                            <li><a href="{{ $item->url() }}" class="search-hit search-hit--text">{{ $item->tr('title') }} <x-icon name="arrow" size="14" class="flip-rtl"/></a></li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endforeach
    </div>
    <a href="{{ lroute('search', ['q' => $term]) }}" class="link-arrow">{{ __('ui.search.all_results', ['term' => $term]) }} <x-icon name="arrow" size="16" class="flip-rtl"/></a>
@endif
