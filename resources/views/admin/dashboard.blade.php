@extends('admin.layout')
@section('title', 'داشبورد')
@php($maxViews = max(1, max($views)))
@section('content')
<div class="a-stats">
    <a href="{{ route('admin.products.index') }}" class="a-stat"><span>محصولات</span><strong>{{ digits($stats['products'], 'fa') }}</strong><small>{{ digits($stats['products_published'], 'fa') }} منتشرشده</small></a>
    <a href="{{ route('admin.categories.index') }}" class="a-stat"><span>دسته‌بندی‌ها</span><strong>{{ digits($stats['categories'], 'fa') }}</strong></a>
    <a href="{{ route('admin.posts.index') }}" class="a-stat"><span>مقالات</span><strong>{{ digits($stats['posts'], 'fa') }}</strong></a>
    <a href="{{ route('admin.recipes.index') }}" class="a-stat"><span>دستورهای غذایی</span><strong>{{ digits($stats['recipes'], 'fa') }}</strong></a>
    <a href="{{ route('admin.contacts.index', ['unread' => 1]) }}" class="a-stat {{ $stats['messages_unread'] ? 'a-stat--alert' : '' }}"><span>پیام‌های جدید</span><strong>{{ digits($stats['messages_unread'], 'fa') }}</strong></a>
    @if (settings()->commerceEnabled())<a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="a-stat"><span>سفارش در انتظار</span><strong>{{ digits($stats['orders_pending'], 'fa') }}</strong></a>@endif
    <a href="{{ route('admin.reports.index') }}" class="a-stat"><span>بازدید امروز / ۳۰ روز</span><strong>{{ digits($stats['views_today'], 'fa') }}</strong><small>{{ digits(number_format($stats['views_30']), 'fa') }}</small></a>
</div>

<div class="a-grid">
    <div class="a-grid__main">
        <section class="a-card">
            <h2>نیازمند توجه</h2>
            @if (empty($issues))
                <p class="a-ok">✓ موردی برای رسیدگی وجود ندارد.</p>
            @else
                <ul class="a-issues">
                    @foreach ($issues as $issue)
                        <li class="a-issue a-issue--{{ $issue['level'] }}"><a href="{{ $issue['url'] }}"><span>{{ $issue['label'] }}</span><b>{{ digits($issue['count'], 'fa') }}</b></a></li>
                    @endforeach
                </ul>
            @endif
            @if ($stats['products'] === 0)
                <div class="a-alert a-alert--warn">هنوز محصولی وجود ندارد. محتوای واقعی را از <a href="{{ route('admin.import.index') }}">درون‌ریزی Suru Food</a> منتقل کنید.</div>
            @endif
        </section>
        <section class="a-card">
            <h2>بازدید ۱۴ روز اخیر</h2>
            <div class="a-bars" role="img" aria-label="نمودار بازدید روزانه">
                @foreach ($views as $date => $count)
                    <span style="--h: {{ round($count / $maxViews * 100) }}%" title="{{ jdate(\Illuminate\Support\Carbon::parse($date), 'd MMM', 'fa') }}: {{ digits($count, 'fa') }}"><i></i><small>{{ digits(\Illuminate\Support\Carbon::parse($date)->format('d'), 'fa') }}</small></span>
                @endforeach
            </div>
        </section>
        <section class="a-card">
            <h2>آخرین پیام‌ها</h2>
            <table class="a-table">
                @forelse ($recentMessages as $m)
                    <tr class="{{ $m->read_at ? '' : 'is-unread' }}"><td><a href="{{ route('admin.contacts.show', $m) }}">{{ $m->name }}</a></td><td>{{ \Illuminate\Support\Str::limit($m->subject ?: $m->message, 60) }}</td><td>{{ jdate($m->created_at, 'd MMM HH:mm', 'fa') }}</td></tr>
                @empty
                    <tr><td class="a-empty">پیامی نیست.</td></tr>
                @endforelse
            </table>
        </section>
    </div>
    <aside class="a-grid__side">
        <section class="a-card">
            <h2>محصولات پربازدید</h2>
            <ol class="a-rank">@forelse ($popular as $p)<li><a href="{{ route('admin.products.edit', $p) }}">{{ $p->tr('title', 'fa', true) }}</a><b>{{ digits($p->views_count, 'fa') }}</b></li>@empty<li class="a-muted">هنوز داده‌ای ثبت نشده.</li>@endforelse</ol>
        </section>
        <section class="a-card">
            <h2>عبارت‌های جستجو</h2>
            <ol class="a-rank">@forelse ($searchTerms as $t)<li><span>{{ $t->term }} <small>{{ strtoupper($t->locale) }}</small></span><b>{{ digits($t->hits, 'fa') }}</b></li>@empty<li class="a-muted">هنوز جستجویی ثبت نشده.</li>@endforelse</ol>
        </section>
        @if ($recentOrders->isNotEmpty())
            <section class="a-card"><h2>آخرین سفارش‌ها</h2>
                <ol class="a-rank">@foreach ($recentOrders as $o)<li><a href="{{ route('admin.orders.show', $o) }}" dir="ltr">{{ $o->number }}</a><b>{{ $o->status->label() }}</b></li>@endforeach</ol>
            </section>
        @endif
    </aside>
</div>
@endsection
