@extends('admin.layout')
@section('title', 'مجله')
@section('content')
<div class="a-head"><h1>مقالات مجله <small>{{ digits($posts->total(), 'fa') }}</small></h1><a href="{{ route('admin.posts.create') }}" class="a-btn">+ مقاله جدید</a></div>
@include('admin.partials.filters', ['statuses' => \App\Enums\ContentStatus::options()])
<div class="a-card a-card--flush">
    <table class="a-table">
        <thead><tr><th></th><th>عنوان</th><th>دسته</th><th>تاریخ انتشار</th><th>وضعیت</th><th>ترجمه</th><th></th></tr></thead>
        <tbody>
        @forelse ($posts as $post)
            <tr>
                <td class="a-table__thumb">@if($post->cover)<img src="{{ $post->cover->variantUrl('thumbnail') }}" alt="">@endif</td>
                <td><a href="{{ route('admin.posts.edit', $post) }}"><strong>{{ $post->tr('title', 'fa', true) }}</strong></a></td>
                <td>{{ $post->category?->tr('title', 'fa', true) ?? '—' }}</td>
                <td>{{ jdate($post->published_at, 'd MMMM y', 'fa') ?? '—' }}</td>
                <td>@include('admin.partials.status-badge', ['status' => $post->status])</td>
                <td>@include('admin.partials.tr-status', ['model' => $post])</td>
                <td class="a-table__actions"><a href="{{ route('admin.posts.edit', $post) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.posts.destroy', $post)])</td>
            </tr>
        @empty
            <tr><td colspan="7" class="a-empty">مقاله‌ای ثبت نشده است.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $posts])
@endsection
