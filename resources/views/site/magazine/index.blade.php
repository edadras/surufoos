@extends('layouts.site')
@section('content')
<header class="page-hero">
    <div class="container">
        @include('partials.breadcrumbs')
        <h1 class="display-1" data-split="lines">{{ $category ? $category->tr('title') : __('ui.magazine.title') }}</h1>
        <p class="lead">{{ $category?->tr('short_description') ?: __('ui.magazine.intro') }}</p>
    </div>
    @if ($categories->isNotEmpty())
        <nav class="container chips-row" aria-label="{{ __('ui.magazine.title') }}">
            <a href="{{ lroute('magazine.index') }}" class="chip {{ ! $category ? 'is-active' : '' }}">{{ __('ui.magazine.all') }}</a>
            @foreach ($categories as $c)
                @if ($u = $c->url())<a href="{{ $u }}" class="chip {{ $category?->id === $c->id ? 'is-active' : '' }}">{{ $c->tr('title') }}</a>@endif
            @endforeach
        </nav>
    @endif
</header>
<section class="section section--tight">
    <div class="container">
        @if ($posts->isEmpty())
            <div class="empty-block"><p>{{ __('ui.magazine.empty') }}</p></div>
        @else
            <div class="agrid agrid--listing">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post, 'large' => $featured && $loop->first])
                @endforeach
            </div>
            {{ $posts->links() }}
        @endif
    </div>
</section>
@endsection
