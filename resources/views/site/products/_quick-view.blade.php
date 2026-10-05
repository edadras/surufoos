@php
    $t = $product->translation();
    $gallery = collect([$product->displayImage()])->concat($product->images->pluck('media'))->filter()->unique('id')->take(5)->values();
@endphp
<div class="qv" style="--tint: {{ $product->themeColor() }}">
    <div class="qv__media">
        <span class="qv__halo" aria-hidden="true"></span>
        <div class="qv__main" data-qv-main><x-picture :media="$gallery->first()" size="medium" sizes="(max-width: 768px) 80vw, 30vw" :eager="true"/></div>
        @if ($gallery->count() > 1)
            <div class="qv__thumbs">
                @foreach ($gallery as $m)
                    <button type="button" data-qv-thumb="{{ $m->variantUrl('medium') }}" aria-label="{{ $m->alt() ?: $t->title }}"><x-picture :media="$m" size="thumbnail" sizes="56px" alt=""/></button>
                @endforeach
            </div>
        @endif
    </div>
    <div class="qv__info">
        @if ($product->primaryCategory)<p class="eyebrow">{{ $product->primaryCategory->tr('title') }}</p>@endif
        <h2 class="display-3">{{ $t->title }}</h2>
        <p class="qv__meta">
            @if ($w = $product->weightLabel())<span>{{ $w }}</span>@endif
            <span class="stock stock--{{ $product->stock_status->value }}">{{ __('ui.stock.'.$product->stock_status->value) }}</span>
        </p>
        @include('partials.product-price', ['product' => $product])
        @if ($t->short_description || $t->description)
            <div class="prose">{!! rich_text(\Illuminate\Support\Str::limit(strip_tags($t->short_description ?: $t->description), 420)) !!}</div>
        @endif

        @if ($product->ingredients->isNotEmpty())
            <h3 class="qv__h">{{ __('ui.products.ingredients') }}</h3>
            <p>{{ $product->ingredients->map(fn ($i) => $i->tr('name', null, true))->filter()->implode('، ') }}</p>
        @endif
        @if ($product->attributeValues->isNotEmpty())
            <ul class="tag-list">@foreach ($product->attributeValues as $v)<li><span class="tag">{{ $v->label() }}</span></li>@endforeach</ul>
        @endif
        @if ($product->nutritionFacts->isNotEmpty())
            <h3 class="qv__h">{{ __('ui.products.nutrition') }}</h3>
            <dl class="qv__nutrition">
                @foreach ($product->nutritionFacts->take(6) as $f)
                    <div><dt>{{ $f->nutrient->label() }}</dt><dd>{{ digits(rtrim(rtrim(number_format($f->amount, 2, '.', ''), '0'), '.')) }} {{ __('units.'.$f->unit) }}</dd></div>
                @endforeach
            </dl>
        @endif
        @foreach (['how_to_use', 'storage'] as $field)
            @if ($t->$field)
                <h3 class="qv__h">{{ __('ui.products.'.$field) }}</h3>
                <div class="prose">{!! rich_text(\Illuminate\Support\Str::limit(strip_tags($t->$field), 260)) !!}</div>
            @endif
        @endforeach

        <div class="qv__actions">
            @if (settings()->commerceEnabled() && $product->isPurchasable())
                <form action="{{ lroute('cart.add') }}" method="post" data-add-to-cart>
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button class="btn" type="submit">{{ __('ui.products.add_to_cart') }} <x-icon name="bag" size="18"/></button>
                </form>
            @endif
            <a href="{{ $product->url() }}" class="btn btn--ghost">{{ __('ui.products.details') }} <x-icon name="arrow" size="16" class="flip-rtl"/></a>
            @include('partials.favorite-button', ['product' => $product, 'active' => $isFavorite])
        </div>

        @if ($related->isNotEmpty())
            <h3 class="qv__h">{{ __('ui.products.related') }}</h3>
            <div class="qv__related">
                @foreach ($related as $r)
                    <a href="{{ $r->url() }}" class="qv__rel" style="--tint: {{ $r->themeColor() }}">
                        <x-picture :media="$r->displayImage()" size="thumbnail" sizes="96px"/>
                        <span>{{ $r->tr('title') }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
