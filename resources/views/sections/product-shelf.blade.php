{{-- New products on a shelf: vertical scroll is briefly converted into horizontal movement on desktop. --}}
@php($favIds = app(\App\Services\FavoriteService::class)->ids())
<section class="shelf section" data-shelf>
    <div class="shelf__pin" data-shelf-pin>
        <div class="container shelf__head">
            @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.products.new'), 'title' => $content?->title, 'subtitle' => $content?->subtitle, 'num' => $num, 'link' => lroute('products.index', ['sort' => 'newest'])])
        </div>
        <div class="shelf__viewport" data-swipe>
            <ul class="shelf__row" data-shelf-row>
                @foreach ($products as $product)
                    <li class="shelf__slot" style="--tint: {{ $product->themeColor() }}">
                        @include('partials.product-card', ['product' => $product, 'variant' => 'shelf', 'favIds' => $favIds])
                    </li>
                @endforeach
            </ul>
            <span class="shelf__board" aria-hidden="true"></span>
        </div>
    </div>
</section>
