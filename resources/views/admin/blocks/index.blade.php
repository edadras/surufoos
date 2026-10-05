@extends('admin.layout')
@section('title', $def['title'])
@section('content')
<div class="a-head"><h1>{{ $def['title'] }}</h1><a href="{{ route('admin.blocks.create', $type) }}" class="a-btn">+ {{ $def['singular'] }} جدید</a></div>
@if ($type === 'timeline')
    <div class="a-filters">@foreach (\App\Models\TimelineEntry::GROUPS as $g => $label)<a href="{{ route('admin.blocks.index', ['type' => $type, 'group' => $g]) }}" class="a-btn a-btn--ghost a-btn--sm {{ request('group') === $g ? 'is-active' : '' }}">{{ $label }}</a>@endforeach</div>
@endif
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>عنوان</th>@if(isset($def['fields']['group']))<th>گروه</th>@endif<th>ترتیب</th><th>فعال</th><th>ترجمه</th><th></th></tr></thead>
    <tbody>
    @forelse ($items as $item)
        <tr>
            <td><a href="{{ route('admin.blocks.edit', [$type, $item->id]) }}">{{ \Illuminate\Support\Str::limit($item->tr($def['primary'], 'fa', true), 90) }}</a></td>
            @if(isset($def['fields']['group']))<td>{{ \App\Models\TimelineEntry::GROUPS[$item->group] ?? $item->group }}</td>@endif
            <td>{{ digits($item->sort_order, 'fa') }}</td><td>{{ $item->is_active ? '✓' : '—' }}</td>
            <td>@include('admin.partials.tr-status', ['model' => $item])</td>
            <td class="a-table__actions"><a href="{{ route('admin.blocks.edit', [$type, $item->id]) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.blocks.destroy', [$type, $item->id])])</td>
        </tr>
    @empty
        <tr><td colspan="6" class="a-empty">موردی ثبت نشده است.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
