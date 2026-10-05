@php($favIds = app(\App\Services\FavoriteService::class)->ids())
<section class="slider-section section">
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow, 'title' => $content?->title, 'subtitle' => $content?->subtitle, 'num' => $num, 'link' => lroute('products.index')])
    </div>
    <div class="h-scroll" data-swipe>
        @foreach ($products as $product)
            <div class="h-scroll__item">@include('partials.product-card', ['product' => $product, 'favIds' => $favIds])</div>
        @endforeach
    </div>
</section>
