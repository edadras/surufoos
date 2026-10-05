@extends('layouts.site')
@section('body_class', 'page-article')
@php($t = $post->translation())
@section('content')
<article class="article">
    <header class="article__head">
        <div class="container container--narrow">
            @include('partials.breadcrumbs')
            <p class="acard__meta">
                @if ($post->category)<a href="{{ $post->category->url() }}">{{ $post->category->tr('title') }}</a>@endif
                <time datetime="{{ $post->published_at?->toDateString() }}">{{ jdate($post->published_at) }}</time>
                <span>{{ __('ui.magazine.reading', ['count' => digits($post->readingMinutes())]) }}</span>
            </p>
            <h1 class="display-1" data-split="lines">{{ $t->title }}</h1>
            @if ($t->short_description)<p class="lead">{{ strip_tags($t->short_description) }}</p>@endif
            @if ($post->authorName())<p class="article__author">{{ __('ui.magazine.by') }}: {{ $post->authorName() }}</p>@endif
        </div>
        @if ($post->cover)
            <figure class="article__cover" data-reveal-mask><x-picture :media="$post->cover" size="large" sizes="100vw" :eager="true"/></figure>
        @endif
    </header>
    <div class="container container--narrow">
        <div class="prose prose--article">{!! clean_html($t->description) !!}</div>

        @if ($post->gallery->isNotEmpty())
            <div class="gallery__grid gallery__grid--article">
                @foreach ($post->gallery as $g)
                    @if ($g->media)<figure class="gallery__item"><x-picture :media="$g->media" size="medium" sizes="(max-width: 768px) 90vw, 30vw"/>@if($c = $g->media->caption())<figcaption>{{ $c }}</figcaption>@endif</figure>@endif
                @endforeach
            </div>
        @endif

        @if ($post->tags->isNotEmpty())
            <ul class="tag-list">@foreach ($post->tags as $tag)<li><span class="tag">#{{ $tag->label() }}</span></li>@endforeach</ul>
        @endif
        <button type="button" class="btn btn--ghost btn--small" data-share data-share-title="{{ $t->title }}"><x-icon name="share" size="16"/> {{ __('ui.share') }}</button>
    </div>

    @if ($post->products->isNotEmpty())
        <section class="section slider-section">
            <div class="container">@include('partials.section-head', ['eyebrow' => __('ui.magazine.products')])</div>
            <div class="h-scroll" data-swipe>
                @foreach ($post->products as $p)<div class="h-scroll__item">@include('partials.product-card', ['product' => $p])</div>@endforeach
            </div>
        </section>
    @endif

    <section class="section section--tight comments" id="comments">
        <div class="container container--narrow">
            <h2 class="h2">{{ __('ui.comments.title') }}</h2>
            @forelse ($post->comments as $comment)
                <div class="comment"><strong>{{ $comment->name }}</strong><time>{{ jdate($comment->created_at) }}</time><p>{{ $comment->body }}</p></div>
            @empty
                <p class="muted">{{ __('ui.comments.none') }}</p>
            @endforelse
            <form action="{{ lroute('magazine.comment', ['post' => $post->id]) }}" method="post" class="form-card">
                @csrf
                @if ($errors->any())<div class="form-errors" role="alert"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <div class="field-row">
                    <div class="field"><label for="cm-name">{{ __('ui.form.name') }}</label><input id="cm-name" name="name" required maxlength="100" value="{{ old('name', auth()->user()?->name) }}"></div>
                    <div class="field"><label for="cm-email">{{ __('ui.form.email') }} <small>({{ __('ui.form.optional') }})</small></label><input id="cm-email" type="email" name="email" value="{{ old('email') }}" dir="ltr"></div>
                </div>
                <div class="field"><label for="cm-body">{{ __('ui.form.comment') }}</label><textarea id="cm-body" name="body" rows="4" required maxlength="2000">{{ old('body') }}</textarea></div>
                <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true">
                <button class="btn" type="submit">{{ __('ui.comments.submit') }}</button>
            </form>
        </div>
    </section>
</article>

@if ($related->isNotEmpty())
    <section class="section">
        <div class="container">
            @include('partials.section-head', ['eyebrow' => __('ui.magazine.related')])
            <div class="agrid">@foreach ($related as $r) @include('partials.post-card', ['post' => $r]) @endforeach</div>
        </div>
    </section>
@endif
@endsection
