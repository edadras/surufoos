<div class="mega" data-mega>
    <div class="container mega__grid">
        @if ($mega['categories']->isNotEmpty())
            <div class="mega__cats">
                <p class="eyebrow">{{ __('ui.nav.categories') }}</p>
                <ul>
                    @foreach ($mega['categories'] as $cat)
                        @if ($url = $cat->url())
                            <li>
                                <a href="{{ $url }}" style="--tint: {{ $cat->themeColor() ?? 'var(--c-accent)' }}" data-mega-cat>
                                    @if ($cat->icon ?? $cat->image)
                                        <span class="mega__cat-thumb"><x-picture :media="$cat->icon ?? $cat->image" size="thumbnail" sizes="48px" alt=""/></span>
                                    @endif
                                    <span>{{ $cat->tr('title') }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
                <a href="{{ lroute('products.index') }}" class="link-arrow">{{ __('ui.view_all') }} <x-icon name="arrow" size="16" class="flip-rtl"/></a>
            </div>
        @endif
        @if ($mega['featured']->isNotEmpty())
            <div class="mega__featured">
                <p class="eyebrow">{{ __('ui.products.featured') }}</p>
                <div class="mega__products">
                    @foreach ($mega['featured'] as $p)
                        <a href="{{ $p->url() }}" class="mega__product" style="--tint: {{ $p->themeColor() }}">
                            <span class="mega__product-img"><x-picture :media="$p->displayImage()" size="small" sizes="160px"/></span>
                            <span class="mega__product-name">{{ $p->tr('title') }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        @if (! empty($mega['promo']['title']))
            <a href="{{ $mega['promo']['url'] ?? lroute('about') }}" class="mega__promo">
                @isset($mega['promo']['media'])
                    <x-picture :media="$mega['promo']['media']" size="medium" sizes="360px" class="mega__promo-img"/>
                @endisset
                <span class="mega__promo-text">
                    <strong>{{ $mega['promo']['title'] }}</strong>
                    @isset($mega['promo']['text'])<span>{{ $mega['promo']['text'] }}</span>@endisset
                </span>
            </a>
        @endif
    </div>
</div>
