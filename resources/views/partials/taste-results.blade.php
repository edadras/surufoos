@if ($products->isEmpty())
    <p class="taste__empty">{{ __('ui.taste.empty') }}</p>
@else
    <h3 class="taste__results-title">{{ __('ui.taste.results') }}</h3>
    <div class="taste__results-grid">
        @foreach ($products as $product)
            <div class="taste__result" style="--i: {{ $loop->index }}">
                @include('partials.product-card', ['product' => $product, 'variant' => 'compact'])
                <span class="taste__match">{{ __('ui.taste.match', ['count' => digits($product->match_score)]) }}</span>
            </div>
        @endforeach
    </div>
@endif
