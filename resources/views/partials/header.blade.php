@php
    $menu = app(\App\Services\MenuService::class);
    $mainItems = $menu->items('main');
    $cartCount = $settings->commerceEnabled() ? app(\App\Services\CartService::class)->count() : 0;
    $favCount = count(app(\App\Services\FavoriteService::class)->ids());
    $alternates = app(\App\Services\Seo::class)->alternates;
    $overHero = request()->routeIs('home');
@endphp

@if ($settings->bool('topbar_enabled') && ($topText = $settings->get('topbar_text')))
    <div class="topbar">
        <div class="container topbar__inner">
            @if ($topLink = $settings->get('topbar_link'))
                <a href="{{ $topLink }}" class="topbar__msg">{{ $topText }} <x-icon name="arrow" size="14" class="flip-rtl"/></a>
            @else
                <p class="topbar__msg">{{ $topText }}</p>
            @endif
            @if ($second = $settings->get('topbar_secondary'))
                <p class="topbar__aside">{{ $second }}</p>
            @endif
        </div>
    </div>
@endif

<header class="site-header {{ $overHero ? 'is-over-hero' : '' }}" data-header>
    <div class="container site-header__inner">
        <a href="{{ lroute('home') }}" class="brand" aria-label="{{ $settings->siteName() }}">
            @if ($logo)
                <img src="{{ $logo->isSvg() ? $logo->url() : $logo->variantUrl('small') }}" alt="{{ $settings->siteName() }}" class="brand__logo brand__logo--dark" height="44">
                @if ($logoLight && $logoLight->id !== $logo->id)
                    <img src="{{ $logoLight->isSvg() ? $logoLight->url() : $logoLight->variantUrl('small') }}" alt="" aria-hidden="true" class="brand__logo brand__logo--light" height="44">
                @endif
            @else
                <span class="brand__word">{{ $settings->siteName() }}</span>
            @endif
        </a>

        <nav class="main-nav" aria-label="{{ __('ui.nav.main') }}">
            <ul class="main-nav__list">
                @foreach ($mainItems as $item)
                    <li class="main-nav__item {{ $item->is_mega ? 'has-mega' : '' }}">
                        <a href="{{ $item->href() }}" class="main-nav__link {{ $item->isCurrent() ? 'is-current' : '' }}"
                           @if($item->is_mega) aria-haspopup="true" aria-expanded="false" data-mega-trigger @endif
                           @if($item->isCurrent()) aria-current="page" @endif>{{ $item->label() }}</a>
                        @if ($item->is_mega)
                            @include('partials.mega-menu', ['mega' => $menu->mega()])
                        @elseif ($item->children->isNotEmpty())
                            <ul class="main-nav__sub">
                                @foreach ($item->children as $child)
                                    @if ($child->href())
                                        <li><a href="{{ $child->href() }}">{{ $child->label() }}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="header-tools">
            <button class="icon-btn" type="button" data-search-open aria-label="{{ __('ui.nav.search') }}"><x-icon name="search"/></button>
            <div class="lang-switch" data-lang-switch>
                <button class="icon-btn lang-switch__btn" type="button" aria-haspopup="true" aria-expanded="false" aria-label="{{ __('ui.language') }}">
                    <span class="lang-switch__code">{{ config("suru.locales.$locale.short") }}</span>
                </button>
                <ul class="lang-switch__menu" role="list">
                    @foreach (config('suru.locales') as $code => $meta)
                        <li>
                            <a href="{{ $alternates[$code] ?? route('home', ['locale' => $code]) }}" hreflang="{{ $code }}" lang="{{ $code }}"
                               class="{{ $code === $locale ? 'is-active' : '' }}" data-lang-link>{{ $meta['native'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <a href="{{ auth()->check() ? lroute('account.index') : lroute('account.login') }}" class="icon-btn hide-mobile" aria-label="{{ __('ui.nav.account') }}"><x-icon name="user"/></a>
            <a href="{{ lroute('favorites.index') }}" class="icon-btn hide-mobile" aria-label="{{ __('ui.nav.favorites') }}" data-fav-link>
                <x-icon name="heart"/><span class="count-dot" data-fav-count @if(!$favCount) hidden @endif>{{ digits($favCount) }}</span>
            </a>
            @if ($settings->commerceEnabled())
                <a href="{{ lroute('cart.show') }}" class="icon-btn" aria-label="{{ __('ui.nav.cart') }}" data-cart-link>
                    <x-icon name="bag"/><span class="count-dot" data-cart-count @if(!$cartCount) hidden @endif>{{ digits($cartCount) }}</span>
                </a>
            @endif
            <button class="icon-btn nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav" aria-label="{{ __('ui.open_menu') }}">
                <x-icon name="menu"/>
            </button>
        </div>
    </div>

    <div class="mobile-nav" id="mobile-nav" data-mobile-nav hidden>
        <nav aria-label="{{ __('ui.nav.main') }}">
            <ol class="mobile-nav__list">
                @foreach ($mainItems as $item)
                    <li><a href="{{ $item->href() }}"><span class="mobile-nav__num">{{ digits(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)) }}</span>{{ $item->label() }}</a></li>
                @endforeach
            </ol>
        </nav>
        <div class="mobile-nav__langs">
            @foreach (config('suru.locales') as $code => $meta)
                <a href="{{ $alternates[$code] ?? route('home', ['locale' => $code]) }}" lang="{{ $code }}" class="{{ $code === $locale ? 'is-active' : '' }}">{{ $meta['native'] }}</a>
            @endforeach
        </div>
    </div>
</header>
