@php($favIds = app(\App\Services\FavoriteService::class)->ids())
@if ($products->isEmpty())
    <div class="empty-block">
        <p>{{ __('ui.products.no_results') }}</p>
        @if ($filters->isFiltered())
            <a href="{{ url()->current() }}" class="btn btn--ghost" data-filter-reset>{{ __('ui.products.clear_filters') }}</a>
        @endif
    </div>
@else
    <div class="pgrid" data-pgrid>
        @foreach ($products as $product)
            @include('partials.product-card', ['product' => $product, 'favIds' => $favIds])
        @endforeach
    </div>
    {{ $products->links() }}
@endif
