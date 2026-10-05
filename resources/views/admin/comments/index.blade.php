@extends('admin.layout')
@section('title', 'دیدگاه‌ها')
@section('content')
<div class="a-head"><h1>دیدگاه‌ها</h1></div>
@include('admin.partials.filters', ['statuses' => \App\Models\Comment::STATUSES])
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>نویسنده</th><th>دیدگاه</th><th>برای</th><th>تاریخ</th><th>وضعیت</th><th></th></tr></thead>
    <tbody>
    @forelse ($comments as $c)
        <tr>
            <td>{{ $c->name }}<br><small dir="ltr">{{ $c->email }}</small></td>
            <td class="a-wrap">{{ \Illuminate\Support\Str::limit($c->body, 200) }}</td>
            <td>{{ $c->commentable?->tr('title', 'fa', true) }}</td>
            <td>{{ jdate($c->created_at, 'd MMM y HH:mm', 'fa') }}</td>
            <td><span class="a-pill a-pill--{{ $c->status }}">{{ \App\Models\Comment::STATUSES[$c->status] }}</span></td>
            <td class="a-table__actions">
                @foreach (['approved' => 'تأیید', 'rejected' => 'رد'] as $s => $l)@if($c->status !== $s)<form method="post" action="{{ route('admin.comments.update', $c) }}" class="a-inline">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $s }}"><button class="a-btn a-btn--ghost a-btn--sm">{{ $l }}</button></form>@endif @endforeach
                @include('admin.partials.delete-form', ['action' => route('admin.comments.destroy', $c)])
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="a-empty">دیدگاهی ثبت نشده است.</td></tr>
    @endforelse
    </tbody>
</table></div>
@include('admin.partials.pagination', ['paginator' => $comments])
@endsection
