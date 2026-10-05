@php($favIds = app(\App\Services\FavoriteService::class)->ids())
<section class="showcase section" data-showcase>
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.products.featured'), 'title' => $content?->title, 'subtitle' => $content?->subtitle, 'num' => $num, 'link' => lroute('products.index')])
        <div class="showcase__grid">
            @foreach ($products as $product)
                <div class="showcase__cell {{ $loop->first ? 'showcase__cell--hero' : '' }}">
                    @include('partials.product-card', ['product' => $product, 'variant' => $loop->first ? 'feature' : 'default', 'favIds' => $favIds])
                </div>
            @endforeach
        </div>
    </div>
</section>
