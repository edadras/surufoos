@php
    $seo = app(\App\Services\Seo::class);
    $logo = $settings->media('logo_media_id');
    $logoLight = $settings->media('logo_light_media_id') ?? $logo;
    $favicon = $settings->media('favicon_media_id');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $seo->fullTitle() }}</title>
    @if ($desc = $seo->metaDescription())
        <meta name="description" content="{{ $desc }}">
    @endif
    <meta name="robots" content="{{ $seo->noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large' }}">
    <link rel="canonical" href="{{ $seo->canonicalUrl() }}">
    @foreach ($seo->alternates as $altLocale => $altUrl)
        <link rel="alternate" hreflang="{{ config("suru.locales.$altLocale.hreflang") }}" href="{{ $altUrl }}">
    @endforeach
    @if (isset($seo->alternates[\App\Support\Locales::source()]))
        <link rel="alternate" hreflang="x-default" href="{{ $seo->alternates[\App\Support\Locales::source()] }}">
    @endif
    <meta property="og:type" content="{{ $seo->type === 'product' ? 'product' : ($seo->type === 'article' ? 'article' : 'website') }}">
    <meta property="og:title" content="{{ $seo->title ?: $settings->siteName() }}">
    @if ($desc)<meta property="og:description" content="{{ $desc }}">@endif
    <meta property="og:url" content="{{ $seo->canonicalUrl() }}">
    <meta property="og:site_name" content="{{ $settings->siteName() }}">
    <meta property="og:locale" content="{{ config("suru.locales.$locale.og") }}">
    @if ($ogImage = $seo->imageUrl())
        <meta property="og:image" content="{{ $ogImage }}">
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo->title ?: $settings->siteName() }}">
    @if ($desc)<meta name="twitter:description" content="{{ $desc }}">@endif
    @if ($favicon)
        <link rel="icon" href="{{ $favicon->variantUrl('thumbnail') }}">
    @endif
    <meta name="theme-color" content="{{ $settings->get('color_surface', '#F5EFE4') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('preload')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($themeCss = $settings->themeCss())
        <style>{!! $themeCss !!}</style>
    @endif

    <script type="application/ld+json">{!! json_encode($seo->organizationSchema(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @foreach ($seo->allSchemas() as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endforeach
    {!! $settings->get('head_scripts') !!}
    <script>document.documentElement.classList.replace('no-js','js')</script>
</head>
<body class="@yield('body_class')" data-locale="{{ $locale }}">
    @include('partials.icons')
    <a class="skip-link" href="#main">{{ __('ui.skip_to_content') }}</a>

    @include('partials.header', ['logo' => $logo, 'logoLight' => $logoLight])

    <main id="main" tabindex="-1">
        @if (session('status'))
            <div class="flash" role="status" data-flash>{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    @unless (View::hasSection('hide_footer_cta'))
        @include('partials.footer-cta')
    @endunless
    @include('partials.footer', ['logo' => $logo])
    @include('partials.bottom-nav')
    @include('partials.search-overlay')

    <aside class="drawer" id="quick-view" aria-hidden="true" aria-label="{{ __('ui.products.quick_view') }}" data-drawer>
        <div class="drawer__scrim" data-drawer-close></div>
        <div class="drawer__panel" role="dialog" aria-modal="true" tabindex="-1">
            <button class="icon-btn drawer__close" type="button" data-drawer-close aria-label="{{ __('ui.close') }}"><x-icon name="close"/></button>
            <div class="drawer__body" data-drawer-body></div>
        </div>
    </aside>
    <div class="toast" role="status" aria-live="polite" data-toast></div>

    <script type="application/json" id="app-config">{!! json_encode([
        'locale' => $locale,
        'dir' => $dir,
        'searchUrl' => lroute('search.live'),
        'tasteUrl' => lroute('taste-finder'),
        'cartUrl' => lroute('cart.add'),
        'i18n' => [
            'favAdded' => __('ui.favorites.added'), 'favRemoved' => __('ui.favorites.removed'),
            'cartAdded' => __('ui.cart.added'), 'copied' => __('ui.copied'), 'hint' => __('ui.search.hint'),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
</body>
</html>
