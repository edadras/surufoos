<section class="banners section section--tight">
    <div class="container banners__list">
        @foreach ($banners as $banner)
            <a href="{{ $banner->url ?: '#' }}" class="banner banner--{{ $banner->layout }}" style="--tint: {{ $banner->color ?? 'var(--c-surface-warm)' }}; --ink: {{ $banner->text_color ?? 'var(--c-text)' }}" @if(!$banner->url) aria-disabled="true" @endif data-banner>
                <span class="banner__media" data-parallax="0.08">
                    @if ($banner->mobileMedia)
                        <span class="only-mobile"><x-picture :media="$banner->mobileMedia" size="medium" sizes="100vw" alt="{{ $banner->title }}"/></span>
                    @endif
                    @if ($banner->desktopMedia)
                        <span class="{{ $banner->mobileMedia ? 'only-desktop' : '' }}"><x-picture :media="$banner->desktopMedia" size="large" sizes="(max-width: 768px) 100vw, 90vw" alt="{{ $banner->title }}"/></span>
                    @endif
                </span>
                @if ($banner->title || $banner->subtitle)
                    <span class="banner__copy">
                        @if ($banner->title)<strong class="display-3">{{ $banner->title }}</strong>@endif
                        @if ($banner->subtitle)<span>{{ $banner->subtitle }}</span>@endif
                        @if ($banner->cta_label)<span class="btn btn--small">{{ $banner->cta_label }} <x-icon name="arrow" size="16" class="flip-rtl"/></span>@endif
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</section>
