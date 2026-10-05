@props(['name', 'size' => 20])
<svg width="{{ $size }}" height="{{ $size }}" aria-hidden="true" focusable="false" {{ $attributes->merge(['class' => 'icon']) }}><use href="#i-{{ $name }}"></use></svg>
