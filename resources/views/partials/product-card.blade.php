{{-- Product card: the packshot stands on a tinted plinth taken from the pack's own colours. --}}
@php
    $t = $product->translation();
    $img = $product->displayImage();
    $favIds ??= app(\App\Services\FavoriteService::class)->ids();
    $variant ??= 'default';
@endphp
@if ($t)
<article class="pcard pcard--{{ $variant }} {{ $img?->has_transparency ? 'has-cutout' : '' }}" style="--tint: {{ $product->themeColor() }}" data-pcard>
    <a href="{{ $product->url() }}" class="pcard__media" tabindex="-1" aria-hidden="true">
        <span class="pcard__plinth"></span>
        <span class="pcard__img" data-pcard-img>
            <x-picture :media="$img" size="small" sizes="(max-width: 640px) 70vw, (max-width: 1100px) 33vw, 22vw"/>
        </span>
    </a>
    @if ($product->badge || $t->badge_label || $product->isOnSale())
        <span class="pcard__badge">{{ $t->badge_label ?: ($product->badge ? __('ui.badges.'.$product->badge) : __('ui.badges.sale')) }}</span>
    @endif
    <div class="pcard__actions">
        @include('partials.favorite-button', ['product' => $product, 'active' => in_array($product->id, $favIds, true)])
        <button type="button" class="icon-btn icon-btn--soft" data-quick-view="{{ lroute('products.quick-view', ['product' => $product->id]) }}" aria-label="{{ __('ui.products.quick_view') }}: {{ $t->title }}">
            <x-icon name="eye"/>
        </button>
    </div>
    <div class="pcard__body">
        @if ($product->primaryCategory)
            <p class="pcard__cat">{{ $product->primaryCategory->tr('title') }}</p>
        @endif
        <h3 class="pcard__title"><a href="{{ $product->url() }}">{{ $t->title }}</a></h3>
        <div class="pcard__meta">
            @if ($w = $product->weightLabel())<span>{{ $w }}</span>@endif
            @if ($product->stock_status->value !== 'in_stock')<span class="stock stock--{{ $product->stock_status->value }}">{{ __('ui.stock.'.$product->stock_status->value) }}</span>@endif
        </div>
        @if ($t->short_description)
            <p class="pcard__desc">{{ \Illuminate\Support\Str::limit(strip_tags($t->short_description), 110) }}</p>
        @endif
        @include('partials.product-price', ['product' => $product])
    </div>
</article>
@endif
