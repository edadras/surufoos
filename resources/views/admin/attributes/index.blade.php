@extends('admin.layout')
@section('title', 'ویژگی‌ها')
@section('content')
<div class="a-head"><h1>ویژگی‌های محصول</h1><a href="{{ route('admin.attributes.create') }}" class="a-btn">+ ویژگی جدید</a></div>
<p class="a-muted">ویژگی‌های «قابل فیلتر» در صفحه محصولات و ویژگی‌های «پرسش‌نامه» در بخش «چی دوست داری؟» نمایش داده می‌شوند. مقادیری که به هیچ محصول منتشرشده‌ای وصل نیستند در سایت دیده نمی‌شوند.</p>
<div class="a-card a-card--flush">
    <table class="a-table">
        <thead><tr><th>نام</th><th>کد</th><th>مقادیر</th><th>فیلتر</th><th>پرسش‌نامه</th><th>ترجمه</th><th></th></tr></thead>
        <tbody>
        @forelse ($attributes as $a)
            <tr>
                <td><a href="{{ route('admin.attributes.edit', $a) }}"><strong>{{ $a->tr('name', 'fa', true) }}</strong></a></td>
                <td dir="ltr">{{ $a->code }}</td>
                <td>{{ $a->values->map(fn ($v) => $v->label('fa'))->implode('، ') }}</td>
                <td>{{ $a->is_filterable ? '✓' : '—' }}</td>
                <td>{{ $a->is_quiz ? '✓' : '—' }}</td>
                <td>@include('admin.partials.tr-status', ['model' => $a])</td>
                <td class="a-table__actions"><a href="{{ route('admin.attributes.edit', $a) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.attributes.destroy', $a)])</td>
            </tr>
        @empty
            <tr><td colspan="7" class="a-empty">ویژگی‌ای تعریف نشده است.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
