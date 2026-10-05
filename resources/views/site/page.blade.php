@extends('layouts.site')
@section('body_class', 'page-builder')

@section('content')
    <header class="page-hero">
        <div class="container">
            @include('partials.breadcrumbs')
            <h1 class="display-1" data-split="lines">{{ $page->tr('title') }}</h1>
            @if ($intro = $page->tr('short_description'))<p class="lead">{{ $intro }}</p>@endif
        </div>
        @if ($page->cover)
            <div class="page-hero__media" data-reveal-mask><x-picture :media="$page->cover" size="large" sizes="100vw" :eager="true"/></div>
        @endif
    </header>
    @if ($body = $page->tr('description'))
        <section class="section"><div class="container container--narrow prose">{!! rich_text($body) !!}</div></section>
    @endif
    @foreach ($sections as $s)
        @include($s['view'], array_merge($s['data'], [
            'section' => $s['section'],
            'content' => $s['content'],
            'num' => digits(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)),
        ]))
    @endforeach
@endsection
