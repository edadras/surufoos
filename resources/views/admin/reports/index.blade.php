@extends('admin.layout')
@section('title', 'گزارش‌ها')
@php($maxViews = max(1, max($views)))
@section('content')
<div class="a-head"><h1>گزارش بازدید و جستجو</h1>
    <div class="a-tabs">@foreach ([7, 30, 90] as $d)<a href="{{ route('admin.reports.index', ['days' => $d]) }}" class="{{ $days === $d ? 'is-active' : '' }}">{{ digits($d, 'fa') }} روز</a>@endforeach</div></div>
<section class="a-card">
    <h2>بازدید روزانه <small>مجموع {{ digits(number_format(array_sum($views)), 'fa') }}</small></h2>
    <div class="a-bars a-bars--wide">@foreach ($views as $date => $count)<span style="--h: {{ round($count / $maxViews * 100) }}%" title="{{ $date }}: {{ $count }}"><i></i></span>@endforeach</div>
    <p class="a-muted">بر اساس زبان: @foreach ($byLocale as $l => $t){{ strtoupper($l) }}: {{ digits(number_format($t), 'fa') }} &nbsp; @endforeach</p>
</section>
<div class="a-grid a-grid--even">
    <section class="a-card"><h2>صفحات پربازدید</h2><ol class="a-rank">@forelse ($topPages as $p)<li><a href="{{ url($p->path) }}" target="_blank" dir="ltr">{{ rawurldecode($p->path) }}</a><b>{{ digits(number_format($p->total), 'fa') }}</b></li>@empty<li class="a-muted">داده‌ای نیست.</li>@endforelse</ol></section>
    <section class="a-card"><h2>محصولات پربازدید</h2><ol class="a-rank">@forelse ($popular as $p)<li><a href="{{ route('admin.products.edit', $p) }}">{{ $p->tr('title', 'fa', true) }}</a><b>{{ digits($p->views_count, 'fa') }}</b></li>@empty<li class="a-muted">داده‌ای نیست.</li>@endforelse</ol></section>
    <section class="a-card"><h2>عبارت‌های جستجو</h2><ol class="a-rank">@forelse ($searchTerms as $t)<li><span>{{ $t->term }} <small>{{ strtoupper($t->locale) }} · {{ digits($t->last_results_count, 'fa') }} نتیجه</small></span><b>{{ digits($t->hits, 'fa') }}</b></li>@empty<li class="a-muted">داده‌ای نیست.</li>@endforelse</ol></section>
    <section class="a-card"><h2>جستجوهای بی‌نتیجه <small>فرصت محتوا</small></h2><ol class="a-rank">@forelse ($zeroResults as $t)<li><span>{{ $t->term }}</span><b>{{ digits($t->hits, 'fa') }}</b></li>@empty<li class="a-muted">موردی نیست.</li>@endforelse</ol></section>
</div>
@endsection
