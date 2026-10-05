<nav class="bottom-nav" aria-label="{{ __('ui.nav.quick_links') }}">
    <a href="{{ lroute('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}"><x-icon name="home"/><span>{{ __('ui.nav.home') }}</span></a>
    <a href="{{ lroute('products.index') }}" class="{{ request()->routeIs('products.*', 'category.*') ? 'is-active' : '' }}"><x-icon name="grid"/><span>{{ __('ui.nav.products') }}</span></a>
    <button type="button" data-search-open><x-icon name="search"/><span>{{ __('ui.nav.search') }}</span></button>
    <a href="{{ lroute('favorites.index') }}" class="{{ request()->routeIs('favorites.*') ? 'is-active' : '' }}"><x-icon name="heart"/><span>{{ __('ui.nav.favorites') }}</span></a>
    @if ($settings->commerceEnabled())
        <a href="{{ lroute('cart.show') }}" class="{{ request()->routeIs('cart.*') ? 'is-active' : '' }}"><x-icon name="bag"/><span>{{ __('ui.nav.cart') }}</span></a>
    @else
        <a href="{{ lroute('contact') }}" class="{{ request()->routeIs('contact') ? 'is-active' : '' }}"><x-icon name="phone"/><span>{{ __('ui.nav.contact') }}</span></a>
    @endif
</nav>
