@props([
    'media' => null,
    'size' => 'medium',
    'sizes' => '(max-width: 768px) 100vw, 50vw',
    'eager' => false,
    'alt' => null,
    'imgClass' => '',
])
@if ($media)
    @php
        $altText = $alt ?? $media->alt();
        $w = $media->width;
        $h = $media->height;
    @endphp
    @if ($media->isSvg() || ! $media->variants)
        <img src="{{ $media->url() }}" alt="{{ $altText }}" @if($w) width="{{ $w }}" height="{{ $h }}" @endif
             loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" @if($eager) fetchpriority="high" @endif
             style="object-position: {{ $media->objectPosition() }}" {{ $attributes->merge(['class' => $imgClass]) }}>
    @else
        <picture {{ $attributes->only('class') }}>
            @if ($media->hasFormat('avif'))
                <source type="image/avif" srcset="{{ $media->srcset('avif') }}" sizes="{{ $sizes }}">
            @endif
            <source type="image/webp" srcset="{{ $media->srcset('webp') }}" sizes="{{ $sizes }}">
            <img src="{{ $media->variantUrl($size) }}" srcset="{{ $media->srcset() }}" sizes="{{ $sizes }}" alt="{{ $altText }}"
                 @if($w) width="{{ $w }}" height="{{ $h }}" @endif loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
                 @if($eager) fetchpriority="high" @endif style="object-position: {{ $media->objectPosition() }}" class="{{ $imgClass }}">
        </picture>
    @endif
@endif
