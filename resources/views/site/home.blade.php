@extends('layouts.site')
@section('body_class', 'page-home')

@section('content')
    @forelse ($sections as $s)
        @include($s['view'], array_merge($s['data'], [
            'section' => $s['section'],
            'content' => $s['content'],
            'num' => digits(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)),
        ]))
    @empty
        <section class="section empty-state">
            <div class="container container--narrow">
                <h1 class="display-2">{{ settings()->siteName() }}</h1>
                @if ($tag = settings('tagline'))<p class="lead">{{ $tag }}</p>@endif
                <a href="{{ lroute('products.index') }}" class="btn">{{ __('ui.hero.cta_products') }}</a>
            </div>
        </section>
    @endforelse
@endsection
