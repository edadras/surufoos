@php
    $ctaTitle = $settings->get('footer_cta_title');
    $ctaIds = array_filter(array_map('intval', explode(',', (string) $settings->get('footer_cta_product_ids'))));
    $ctaProducts = $ctaTitle ? \App\Support\CacheGroups::remember(\App\Support\CacheGroups::HOME, 'footer-cta:'.$locale.':'.implode('-', $ctaIds), fn () => $ctaIds
        ? \App\Models\Product::published()->translatedIn()->forCard()->whereIn('id', $ctaIds)->get()
        : app(\App\Services\CatalogService::class)->featured(3)) : collect();
@endphp
@if ($ctaTitle)
    <section class="footer-cta" data-footer-cta>
        <div class="container footer-cta__inner">
            <div class="footer-cta__copy">
                <h2 class="display-2" data-split>{{ $ctaTitle }}</h2>
                @if ($text = $settings->get('footer_cta_text'))<p class="lead">{{ $text }}</p>@endif
                <a href="{{ $settings->get('footer_cta_url', lroute('products.index')) }}" class="btn btn--light" data-magnetic>
                    {{ $settings->get('footer_cta_label', __('ui.hero.cta_products')) }} <x-icon name="arrow" size="18" class="flip-rtl"/>
                </a>
            </div>
            @if ($ctaProducts->isNotEmpty())
                <div class="footer-cta__stack" aria-hidden="true">
                    @foreach ($ctaProducts->take(3) as $p)
                        <span class="footer-cta__pack" style="--i: {{ $loop->index }}; --tint: {{ $p->themeColor() }}">
                            <x-picture :media="$p->displayImage()" size="small" sizes="220px" alt=""/>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
