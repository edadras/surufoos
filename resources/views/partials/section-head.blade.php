{{-- Shared editorial section heading: numbered eyebrow, display title, optional link. --}}
<header class="section-head {{ $class ?? '' }}">
    @if (! empty($eyebrow))
        <p class="eyebrow"><span class="eyebrow__num">{{ $num ?? '' }}</span>{{ $eyebrow }}</p>
    @endif
    @if (! empty($title))
        <h2 class="display-2" data-split>{{ $title }}</h2>
    @endif
    @if (! empty($subtitle))
        <p class="lead">{{ $subtitle }}</p>
    @endif
    @if (! empty($link))
        <a href="{{ $link }}" class="link-arrow">{{ $linkLabel ?? __('ui.view_all') }} <x-icon name="arrow" size="16" class="flip-rtl"/></a>
    @endif
</header>
