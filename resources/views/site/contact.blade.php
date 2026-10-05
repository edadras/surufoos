@extends('layouts.site')
@section('body_class', 'page-contact')
@php($phones = settings()->phones())
@section('content')
<header class="page-hero">
    <div class="container">
        @include('partials.breadcrumbs')
        <h1 class="display-1" data-split="lines">{{ $page?->tr('title') ?: __('ui.nav.contact') }}</h1>
        <p class="lead">{{ $page?->tr('short_description') ?: __('ui.contact.intro') }}</p>
    </div>
</header>
<section class="section section--tight">
    <div class="container contact__grid">
        <div class="contact__info">
            @if ($phones)
                <div class="contact__block"><p class="eyebrow">{{ __('ui.contact.phone') }}</p>
                    @foreach ($phones as $phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="contact__big" dir="ltr">{{ $phone }}</a>@endforeach
                </div>
            @endif
            @if ($email = settings('email'))
                <div class="contact__block"><p class="eyebrow">{{ __('ui.contact.email') }}</p><a href="mailto:{{ $email }}" class="contact__big">{{ $email }}</a></div>
            @endif
            @if ($address = settings('address'))
                <div class="contact__block"><p class="eyebrow">{{ __('ui.contact.address') }}</p><p>{{ $address }}</p></div>
            @endif
            @if ($factory = settings('factory_address'))
                <div class="contact__block"><p class="eyebrow">{{ __('ui.contact.factory') }}</p><p>{{ $factory }}</p></div>
            @endif
            @if ($hours = settings('working_hours'))
                <div class="contact__block"><p class="eyebrow">{{ __('ui.contact.hours') }}</p><p>{{ $hours }}</p></div>
            @endif
            @if ($socials = settings()->socials())
                <div class="contact__block"><p class="eyebrow">{{ __('ui.contact.social') }}</p>
                    <ul class="socials">@foreach ($socials as $n => $u)<li><a href="{{ $u }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($n) }}"><x-icon :name="$n"/></a></li>@endforeach</ul>
                </div>
            @endif
            @if ($page?->tr('description'))
                <div class="prose">{!! rich_text($page->tr('description')) !!}</div>
            @endif
        </div>

        <form action="{{ lroute('contact.store') }}" method="post" class="form-card" novalidate>
            @csrf
            @if ($errors->any())
                <div class="form-errors" role="alert"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <div class="field"><label for="c-name">{{ __('ui.form.name') }}</label><input id="c-name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"></div>
            <div class="field-row">
                <div class="field"><label for="c-mobile">{{ __('ui.form.mobile') }}</label><input id="c-mobile" name="mobile" value="{{ old('mobile') }}" inputmode="tel" autocomplete="tel" dir="ltr"></div>
                <div class="field"><label for="c-email">{{ __('ui.form.email') }}</label><input id="c-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" dir="ltr"></div>
            </div>
            <div class="field"><label for="c-subject">{{ __('ui.form.subject') }} <small>({{ __('ui.form.optional') }})</small></label><input id="c-subject" name="subject" value="{{ old('subject') }}" maxlength="150"></div>
            <div class="field"><label for="c-message">{{ __('ui.form.message') }}</label><textarea id="c-message" name="message" rows="6" required maxlength="5000">{{ old('message') }}</textarea></div>
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true">
            <button type="submit" class="btn" data-magnetic>{{ __('ui.contact.send') }} <x-icon name="arrow" size="18" class="flip-rtl"/></button>
        </form>
    </div>
</section>
@if (($lat = settings('map_lat')) && ($lng = settings('map_lng')))
    <section class="map-block" aria-label="{{ __('ui.contact.map') }}">
        <iframe title="{{ __('ui.contact.map') }}" loading="lazy" referrerpolicy="no-referrer"
                src="{{ settings('map_embed_url') ?: 'https://www.openstreetmap.org/export/embed.html?bbox='.($lng - 0.01).','.($lat - 0.006).','.($lng + 0.01).','.($lat + 0.006).'&layer=mapnik&marker='.$lat.','.$lng }}"></iframe>
    </section>
@endif
@endsection
