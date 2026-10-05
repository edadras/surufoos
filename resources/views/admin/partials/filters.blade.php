{{-- List filter bar: search + optional status select. --}}
<form method="get" class="a-filters">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="جستجو…">
    @isset($statuses)
        <select name="status">
            <option value="">همه وضعیت‌ها</option>
            @foreach ($statuses as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
        </select>
    @endisset
    {{ $slot ?? '' }}
    <button class="a-btn a-btn--sm" type="submit">فیلتر</button>
    @if (request()->hasAny(['q', 'status', 'translation']))<a href="{{ url()->current() }}" class="a-btn a-btn--ghost a-btn--sm">حذف فیلتر</a>@endif
</form>
