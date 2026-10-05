@extends('layouts.site')
@section('body_class', 'page-about')
@section('content')
<header class="page-hero page-hero--about">
    <div class="container">
        @include('partials.breadcrumbs')
        <h1 class="display-1" data-split="lines">{{ $translation?->title ?: __('ui.nav.about') }}</h1>
        @if ($translation?->short_description)<p class="lead">{{ $translation->short_description }}</p>@endif
    </div>
    @if ($page?->cover)
        <div class="page-hero__media" data-reveal-mask><x-picture :media="$page->cover" size="large" sizes="100vw" :eager="true"/></div>
    @endif
</header>

@if ($translation?->description)
    <section class="section">
        <div class="container container--narrow prose prose--lead" data-reveal>{!! rich_text($translation->description) !!}</div>
    </section>
@endif

@foreach ($sections as $s)
    @include($s['view'], array_merge($s['data'], ['section' => $s['section'], 'content' => $s['content'], 'num' => digits(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT))]))
@endforeach

@if ($timeline->isNotEmpty() && ! $sections->contains(fn ($s) => $s['section']->type->value === 'timeline'))
    @include('sections.timeline', ['entries' => $timeline, 'content' => null, 'num' => ''])
@endif
@if ($certificates->isNotEmpty() && ! $sections->contains(fn ($s) => $s['section']->type->value === 'certificates'))
    @include('sections.certificates', ['certificates' => $certificates, 'content' => null, 'num' => ''])
@endif
@endsection
