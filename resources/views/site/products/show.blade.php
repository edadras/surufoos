@extends('layouts.site')
@section('body_class', 'page-product')

@php
    $t = $product->translation();
    $img = $product->displayImage();
    $gallery = collect([$img])->concat($product->images->filter(fn ($i) => in_array($i->type->value, ['gallery', 'packshot'], true))->pluck('media'))->filter()->unique('id')->values();
    $lifestyle = $product->images->filter(fn ($i) => $i->type->value === 'lifestyle')->pluck('media')->filter();
    $ingredients = $product->ingredients;
    $nutrition = $product->nutritionFacts;
    $chapters = collect([
        'pack' => $t->story ?: $t->description,
        'ingredients' => $ingredients->isNotEmpty(),
        'nutrition' => $nutrition->isNotEmpty(),
        'serving' => $t->serving_suggestion ?: $t->how_to_use,
    ])->filter();
    $commerce = settings()->commerceEnabled();
@endphp
@push('preload')
    @if ($img && $img->variants)
        <link rel="preload" as="image" href="{{ $img->variantUrl('medium', 'webp') }}" imagesrcset="{{ $img->srcset('webp') }}" imagesizes="(max-width: 900px) 90vw, 45vw" fetchpriority="high">
    @endif
@endpush

@section('content')
<article class="product" style="--tint: {{ $product->themeColor() }}" data-product>
    <section class="product__intro">
        <div class="container product__intro-grid">
            <div class="product__gallery" data-gallery>
                <div class="product__stage">
                    <span class="product__halo" aria-hidden="true"></span>
                    @foreach ($gallery as $media)
                        <figure class="product__shot {{ $loop->first ? 'is-active' : '' }}" data-gallery-item="{{ $loop->index }}" @unless($loop->first) hidden @endunless>
                            <x-picture :media="$media" size="large" sizes="(max-width: 900px) 90vw, 45vw" :eager="$loop->first"/>
                        </figure>
                    @endforeach
                </div>
                @if ($gallery->count() > 1)
                    <div class="product__thumbs" role="tablist" aria-label="{{ __('ui.products.gallery') }}">
                        @foreach ($gallery as $media)
                            <button type="button" role="tab" class="{{ $loop->first ? 'is-active' : '' }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-gallery-thumb="{{ $loop->index }}">
                                <x-picture :media="$media" size="thumbnail" sizes="72px" alt=""/>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="product__info">
                @include('partials.breadcrumbs')
                @if ($product->primaryCategory)
                    <a href="{{ $product->primaryCategory->url() }}" class="eyebrow eyebrow--line">{{ $product->primaryCategory->tr('title') }}</a>
                @endif
                <h1 class="display-1 product__title" data-split="lines">{{ $t->title }}</h1>
                @if ($t->short_description)
                    <div class="lead product__lead">{!! rich_text($t->short_description) !!}</div>
                @endif

                <dl class="product__facts">
                    @if ($w = $product->weightLabel())
                        <div><dt>{{ __('ui.products.weight') }}</dt><dd>{{ $w }}</dd></div>
                    @endif
                    <div><dt>{{ __('ui.products.category') }}</dt><dd>{{ $product->categories->map(fn ($c) => $c->tr('title'))->filter()->implode('، ') ?: '—' }}</dd></div>
                    <div><dt>{{ __('ui.products.availability') }}</dt>
                        <dd><span class="stock stock--{{ $product->stock_status->value }}">{{ __('ui.stock.'.$product->stock_status->value) }}</span></dd></div>
                    @if ($product->sku)
                        <div><dt>{{ __('ui.products.sku') }}</dt><dd dir="ltr">{{ $product->sku }}</dd></div>
                    @endif
                </dl>

                @if ($product->attributeValues->isNotEmpty())
                    <ul class="tag-list">
                        @foreach ($product->attributeValues as $value)
                            <li><span class="tag" @if($value->color) style="--c: {{ $value->color }}" @endif>{{ $value->label() }}</span></li>
                        @endforeach
                    </ul>
                @endif

                @include('partials.product-price', ['product' => $product])

                <form class="product__buy" action="{{ $commerce ? lroute('cart.add') : '#' }}" method="post" data-add-to-cart>
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if ($product->variants->count() > 1)
                        <label class="select select--block">
                            <span>{{ __('ui.products.variant') }}</span>
                            <select name="variant_id">
                                @foreach ($product->variants as $variant)
                                    <option value="{{ $variant->id }}" @selected($variant->is_default)>
                                        {{ $variant->attributeValues->map->label()->implode(' / ') ?: $variant->sku }}
                                        @if (settings()->showPrices() && $variant->effectivePrice()) — {{ money($variant->effectivePrice()) }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <div class="product__buy-row">
                        @if ($commerce && $product->isPurchasable())
                            <div class="qty" data-qty>
                                <button type="button" data-qty-step="-1" aria-label="-"><x-icon name="minus" size="16"/></button>
                                <input type="number" name="quantity" value="1" min="1" max="99" inputmode="numeric" aria-label="{{ __('ui.cart.quantity') }}">
                                <button type="button" data-qty-step="1" aria-label="+"><x-icon name="plus" size="16"/></button>
                            </div>
                            <button type="submit" class="btn btn--wide" data-magnetic>{{ __('ui.products.add_to_cart') }} <x-icon name="bag" size="18"/></button>
                        @else
                            <a href="{{ lroute('contact') }}" class="btn btn--wide">{{ $product->effectivePrice() === null ? __('ui.products.contact_for_price') : __('ui.nav.contact') }}</a>
                        @endif
                        @include('partials.favorite-button', ['product' => $product, 'active' => $isFavorite])
                        <button type="button" class="icon-btn icon-btn--soft" data-share data-share-title="{{ $t->title }}" aria-label="{{ __('ui.share') }}"><x-icon name="share"/></button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    @if ($chapters->isNotEmpty())
        <section class="chapters" data-chapters>
            <div class="container chapters__grid">
                <div class="chapters__visual" aria-hidden="true">
                    <div class="chapters__sticky">
                        <div class="chapters__pack" data-chapter-pack><x-picture :media="$img" size="medium" sizes="40vw" alt=""/></div>
                        @foreach ($ingredients->take(8) as $ing)
                            <span class="chapters__ing" style="--i: {{ $loop->index }}; --n: {{ min(8, $ingredients->count()) }}">
                                @if ($ing->media)<x-picture :media="$ing->media" size="thumbnail" sizes="80px" alt=""/>@else{{ $ing->tr('name', null, true) }}@endif
                            </span>
                        @endforeach
                    </div>
                </div>
                <div class="chapters__text">
                    @if ($chapters->has('pack'))
                        <div class="chapter" data-chapter="pack">
                            <p class="eyebrow"><span class="eyebrow__num">{{ digits('01') }}</span>{{ __('ui.products.story') }}</p>
                            <div class="prose">{!! rich_text($chapters['pack']) !!}</div>
                            @if ($t->features)
                                <h3 class="h3">{{ __('ui.products.features') }}</h3>
                                <div class="prose">{!! rich_text($t->features) !!}</div>
                            @endif
                        </div>
                    @endif
                    @if ($chapters->has('ingredients'))
                        <div class="chapter" data-chapter="ingredients">
                            <p class="eyebrow"><span class="eyebrow__num">{{ digits('02') }}</span>{{ __('ui.products.ingredients') }}</p>
                            <ul class="ing-list">
                                @foreach ($ingredients as $ing)
                                    <li>
                                        <strong>{{ $ing->tr('name', null, true) }}</strong>
                                        @if ($ing->pivot->amount)<span class="ing-list__amount">{{ $ing->pivot->amount }}</span>@endif
                                        @if ($d = $ing->tr('description', null, true))<p>{{ $d }}</p>@endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($chapters->has('nutrition'))
                        <div class="chapter" data-chapter="nutrition">
                            <p class="eyebrow"><span class="eyebrow__num">{{ digits('03') }}</span>{{ __('ui.products.nutrition') }}</p>
                            @foreach ($nutrition->groupBy('per') as $per => $facts)
                                <table class="nutrition">
                                    <caption>{{ __('ui.products.nutrition_per', ['per' => digits($per)]) }}</caption>
                                    <tbody>
                                        @foreach ($facts as $fact)
                                            <tr>
                                                <th scope="row">{{ $fact->nutrient->label() }}</th>
                                                <td>{{ digits(rtrim(rtrim(number_format($fact->amount, 2, '.', ''), '0'), '.')) }} {{ __('units.'.$fact->unit) }}</td>
                                                <td class="nutrition__bar">@if($fact->daily_value_percent)<span style="--v: {{ min(100, $fact->daily_value_percent) }}%"></span><small>{{ digits($fact->daily_value_percent) }}٪</small>@endif</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                        </div>
                    @endif
                    @if ($chapters->has('serving'))
                        <div class="chapter" data-chapter="serving">
                            <p class="eyebrow"><span class="eyebrow__num">{{ digits('04') }}</span>{{ $t->serving_suggestion ? __('ui.products.serving') : __('ui.products.how_to_use') }}</p>
                            <div class="prose">{!! rich_text($chapters['serving']) !!}</div>
                            @if ($t->serving_suggestion && $t->how_to_use)
                                <h3 class="h3">{{ __('ui.products.how_to_use') }}</h3>
                                <div class="prose">{!! rich_text($t->how_to_use) !!}</div>
                            @endif
                        </div>
                    @endif
                    @if ($t->storage)
                        <div class="chapter chapter--note">
                            <p class="eyebrow">{{ __('ui.products.storage') }}</p>
                            <div class="prose">{!! rich_text($t->storage) !!}</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($lifestyle->isNotEmpty())
        <section class="section lifestyle">
            <div class="container">
                <div class="lifestyle__grid">
                    @foreach ($lifestyle as $m)
                        <figure class="lifestyle__item" data-reveal-mask><x-picture :media="$m" size="large" sizes="(max-width: 768px) 90vw, 45vw"/></figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($product->recipes->isNotEmpty())
        <section class="section">
            <div class="container">
                @include('partials.section-head', ['eyebrow' => __('ui.products.recipes_with'), 'link' => lroute('recipes.index', ['product' => $product->id])])
                <div class="rgrid">@foreach ($product->recipes as $recipe) @include('partials.recipe-card', ['recipe' => $recipe]) @endforeach</div>
            </div>
        </section>
    @endif
</article>

@foreach (['related' => __('ui.products.related'), 'complementary' => __('ui.products.complementary')] as $var => $label)
    @if ($$var->isNotEmpty())
        <section class="section slider-section">
            <div class="container">@include('partials.section-head', ['eyebrow' => $label])</div>
            <div class="h-scroll" data-swipe>
                @foreach ($$var as $p)
                    <div class="h-scroll__item">@include('partials.product-card', ['product' => $p])</div>
                @endforeach
            </div>
        </section>
    @endif
@endforeach
@endsection
