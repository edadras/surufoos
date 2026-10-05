@extends('layouts.site')
@section('body_class', 'page-catalog')

@section('content')
<header class="page-hero page-hero--catalog" @if($category) style="--tint: {{ $category->themeColor() ?? 'var(--c-surface-warm)' }}" @endif>
    <div class="container page-hero__grid">
        <div>
            @include('partials.breadcrumbs')
            <h1 class="display-1" data-split="lines">{{ $category ? $category->tr('title') : __('ui.products.title') }}</h1>
            @if ($category && ($intro = $category->tr('short_description')))
                <p class="lead">{{ $intro }}</p>
            @elseif (! $category)
                <p class="lead">{{ __('ui.products.intro') }}</p>
            @endif
        </div>
        @if ($category?->image)
            <div class="page-hero__pack" data-parallax="-0.1"><x-picture :media="$category->image" size="medium" sizes="(max-width: 768px) 60vw, 30vw" :eager="true"/></div>
        @endif
    </div>
    @if ($category && $category->children->isNotEmpty())
        <div class="container chips-row">
            @foreach ($category->children as $child)
                @if ($u = $child->url())<a href="{{ $u }}" class="chip">{{ $child->tr('title') }}</a>@endif
            @endforeach
        </div>
    @endif
</header>

<div class="container catalog" data-catalog data-endpoint="{{ url()->current() }}">
    <aside class="catalog__filters" id="filters" data-filters-panel>
        <form method="get" action="{{ url()->current() }}" data-filter-form>
            <div class="filters__head">
                <h2 class="eyebrow"><x-icon name="filter" size="16"/> {{ __('ui.products.filters') }}</h2>
                <button type="button" class="icon-btn only-mobile" data-filters-close aria-label="{{ __('ui.close') }}"><x-icon name="close"/></button>
            </div>

            <div class="field field--search">
                <label for="f-q" class="visually-hidden">{{ __('ui.nav.search') }}</label>
                <input id="f-q" type="search" name="q" value="{{ $filters->query }}" placeholder="{{ __('ui.search.placeholder') }}" maxlength="100">
            </div>

            @if (! $category && $categories->isNotEmpty())
                <fieldset class="filter-group">
                    <legend>{{ __('ui.products.category') }}</legend>
                    <label class="radio"><input type="radio" name="category" value="" @checked(! $filters->categorySlug)> <span>{{ __('ui.magazine.all') }}</span></label>
                    @foreach ($categories as $cat)
                        @php($slug = $cat->translation(null, false)?->slug)
                        @if ($slug)
                            <label class="radio"><input type="radio" name="category" value="{{ $slug }}" @checked($filters->categorySlug === $slug)> <span>{{ $cat->tr('title') }}</span></label>
                        @endif
                    @endforeach
                </fieldset>
            @endif

            @foreach ($facets as $attribute)
                <fieldset class="filter-group">
                    <legend>{{ $attribute->tr('name', null, true) }}</legend>
                    <div class="chips-wrap">
                        @foreach ($attribute->values as $value)
                            <label class="chip chip--check" @if($value->color) style="--c: {{ $value->color }}" @endif>
                                <input type="checkbox" name="attr[{{ $attribute->code }}][]" value="{{ $value->code }}" @checked(in_array($value->code, $filters->attributes[$attribute->code] ?? [], true))>
                                <span>{{ $value->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach

            @if (settings()->showPrices() && $priceRange['max'] > 0)
                <fieldset class="filter-group">
                    <legend>{{ __('ui.products.price') }} <small>({{ __('ui.currency') }})</small></legend>
                    <div class="price-range">
                        <label><span>{{ __('ui.products.price_from') }}</span><input type="number" inputmode="numeric" name="min" min="0" step="1000" value="{{ $filters->priceMin }}" placeholder="{{ $priceRange['min'] }}"></label>
                        <label><span>{{ __('ui.products.price_to') }}</span><input type="number" inputmode="numeric" name="max" min="0" step="1000" value="{{ $filters->priceMax }}" placeholder="{{ $priceRange['max'] }}"></label>
                    </div>
                </fieldset>
            @endif

            <label class="switch">
                <input type="checkbox" name="in_stock" value="1" @checked($filters->inStockOnly)>
                <span class="switch__track" aria-hidden="true"></span>
                <span>{{ __('ui.products.in_stock_only') }}</span>
            </label>

            <input type="hidden" name="sort" value="{{ $filters->sort }}" data-sort-mirror>
            <div class="filters__actions">
                <button type="submit" class="btn btn--small no-js-only">{{ __('ui.products.apply') }}</button>
                <a href="{{ url()->current() }}" class="link-arrow" data-filter-reset>{{ __('ui.products.clear_filters') }}</a>
            </div>
        </form>
    </aside>

    <div class="catalog__main">
        <div class="catalog__bar">
            <p class="catalog__count" data-result-count>{{ __('ui.products.count', ['count' => digits($products->total())]) }}</p>
            <button type="button" class="btn btn--ghost btn--small only-mobile" data-filters-open aria-controls="filters"><x-icon name="filter" size="16"/> {{ __('ui.products.filters') }}</button>
            <label class="select">
                <span class="visually-hidden">{{ __('ui.products.sort') }}</span>
                <select name="sort" data-sort-select>
                    @foreach (\App\DTOs\ProductFilterData::SORTS as $sort)
                        <option value="{{ $sort }}" @selected($filters->sort === $sort)>{{ __('ui.products.sorts.'.$sort) }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div data-results>
            @include('site.products._results')
        </div>
    </div>
</div>

@if ($category && ($body = $category->tr('description')))
    <section class="section section--tight">
        <div class="container container--narrow prose">{!! rich_text($body) !!}</div>
    </section>
@endif
@endsection
