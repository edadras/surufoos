@extends('admin.layout')
@section('title', 'گزارش فعالیت')
@section('content')
<div class="a-head"><h1>گزارش فعالیت مدیران</h1></div>
<form method="get" class="a-filters"><select name="action"><option value="">همه عملیات</option>@foreach (\App\Models\ActivityLog::ACTIONS as $k => $v)<option value="{{ $k }}" @selected(request('action') === $k)>{{ $v }}</option>@endforeach</select><button class="a-btn a-btn--sm">فیلتر</button></form>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>زمان</th><th>کاربر</th><th>عملیات</th><th>شرح</th><th>جزئیات</th><th>IP</th></tr></thead>
    <tbody>
    @forelse ($logs as $log)
        <tr><td>{{ jdate($log->created_at, 'd MMM y HH:mm', 'fa') }}</td><td>{{ $log->user?->name ?? 'سیستم' }}</td><td>{{ \App\Models\ActivityLog::ACTIONS[$log->action] ?? $log->action }}</td>
            <td class="a-wrap">{{ $log->description }}</td><td class="a-wrap" dir="ltr"><small>{{ $log->properties ? json_encode($log->properties, JSON_UNESCAPED_UNICODE) : '' }}</small></td><td dir="ltr">{{ $log->ip }}</td></tr>
    @empty
        <tr><td colspan="6" class="a-empty">فعالیتی ثبت نشده است.</td></tr>
    @endforelse
    </tbody>
</table></div>
@include('admin.partials.pagination', ['paginator' => $logs])
@endsection
