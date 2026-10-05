@php
    $menu = app(\App\Services\MenuService::class);
    $footerItems = $menu->items('footer');
    $categories = app(\App\Services\CatalogService::class)->categories();
    $socials = $settings->socials();
    $phones = $settings->phones();
@endphp
<footer class="site-footer">
    <div class="container">
        <div class="site-footer__top">
            <div class="site-footer__brand">
                <a href="{{ lroute('home') }}" class="brand brand--footer">
                    @if ($logo)
                        <img src="{{ $logo->isSvg() ? $logo->url() : $logo->variantUrl('small') }}" alt="{{ $settings->siteName() }}" height="48" loading="lazy">
                    @else
                        <span class="brand__word">{{ $settings->siteName() }}</span>
                    @endif
                </a>
                @if ($about = $settings->get('short_about'))
                    <p>{{ $about }}</p>
                @endif
                @if ($socials)
                    <ul class="socials">
                        @foreach ($socials as $network => $url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}"><x-icon :name="$network"/></a></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($categories->isNotEmpty())
                <nav class="site-footer__col" aria-label="{{ __('ui.nav.products') }}">
                    <h2 class="eyebrow">{{ __('ui.nav.products') }}</h2>
                    <ul>
                        @foreach ($categories->take(7) as $cat)
                            @if ($u = $cat->url())<li><a href="{{ $u }}">{{ $cat->tr('title') }}</a></li>@endif
                        @endforeach
                    </ul>
                </nav>
            @endif

            <nav class="site-footer__col" aria-label="{{ __('ui.nav.quick_links') }}">
                <h2 class="eyebrow">{{ __('ui.nav.quick_links') }}</h2>
                <ul>
                    @forelse ($footerItems as $item)
                        <li><a href="{{ $item->href() }}">{{ $item->label() }}</a></li>
                    @empty
                        <li><a href="{{ lroute('about') }}">{{ __('ui.nav.about') }}</a></li>
                        <li><a href="{{ lroute('magazine.index') }}">{{ __('ui.nav.magazine') }}</a></li>
                        <li><a href="{{ lroute('recipes.index') }}">{{ __('ui.nav.recipes') }}</a></li>
                        <li><a href="{{ lroute('contact') }}">{{ __('ui.nav.contact') }}</a></li>
                    @endforelse
                </ul>
            </nav>

            <div class="site-footer__col">
                <h2 class="eyebrow">{{ __('ui.nav.contact') }}</h2>
                <ul class="contact-list">
                    @foreach ($phones as $phone)
                        <li><x-icon name="phone" size="16"/><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" dir="ltr">{{ $phone }}</a></li>
                    @endforeach
                    @if ($email = $settings->get('email'))
                        <li><x-icon name="mail" size="16"/><a href="mailto:{{ $email }}">{{ $email }}</a></li>
                    @endif
                    @if ($address = $settings->get('address'))
                        <li><x-icon name="pin" size="16"/><span>{{ $address }}</span></li>
                    @endif
                </ul>
            </div>

            <div class="site-footer__col site-footer__news">
                <h2 class="eyebrow">{{ __('ui.newsletter.title') }}</h2>
                <p>{{ __('ui.newsletter.text') }}</p>
                <form action="{{ lroute('newsletter.store') }}" method="post" class="inline-form" data-ajax-form>
                    @csrf
                    <label for="nl-email" class="visually-hidden">{{ __('ui.newsletter.placeholder') }}</label>
                    <input id="nl-email" type="email" name="email" required placeholder="{{ __('ui.newsletter.placeholder') }}" autocomplete="email">
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true">
                    <button type="submit" class="btn btn--small">{{ __('ui.newsletter.submit') }}</button>
                </form>
            </div>
        </div>

        <div class="site-footer__bottom">
            <p>© {{ digits(now()->year) }} {{ $settings->get('copyright', $settings->siteName()) }} — {{ __('ui.footer.rights') }}</p>
            <ul class="site-footer__langs">
                @foreach (config('suru.locales') as $code => $meta)
                    <li><a href="{{ app(\App\Services\Seo::class)->alternates[$code] ?? route('home', ['locale' => $code]) }}" lang="{{ $code }}" @if($code === $locale) aria-current="true" @endif>{{ $meta['native'] }}</a></li>
                @endforeach
            </ul>
            @if ($badges = $settings->get('trust_badges_html'))
                <div class="trust-badges">{!! $badges !!}</div>
            @endif
        </div>
    </div>
</footer>
