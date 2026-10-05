@extends('layouts.site')
@section('content')
<header class="page-hero">
    <div class="container">
        @include('partials.breadcrumbs')
        <h1 class="display-1">{{ __('ui.nav.faq') }}</h1>
    </div>
</header>
<section class="section section--tight">
    <div class="container container--narrow">
        @foreach ($faqs as $group => $items)
            @include('partials.faq-list', ['faqs' => $items])
        @endforeach
    </div>
</section>
@endsection
