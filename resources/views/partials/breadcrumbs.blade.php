@php($crumbs = app(\App\Services\Seo::class)->breadcrumbs)
@if ($crumbs)
    <nav class="breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
        <ol>
            <li><a href="{{ lroute('home') }}">{{ __('ui.nav.home') }}</a></li>
            @foreach ($crumbs as [$label, $url])
                <li>@if ($url && ! $loop->last)<a href="{{ $url }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif</li>
            @endforeach
        </ol>
    </nav>
@endif
