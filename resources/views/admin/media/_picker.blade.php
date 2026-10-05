<div class="a-picker" data-picker data-multiple="{{ $multiple ? 1 : 0 }}">
    <div class="a-picker__bar">
        <form class="a-filters" data-picker-search>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="جستجو…">
            <select name="folder"><option value="">همه پوشه‌ها</option>@foreach ($folders as $f)<option value="{{ $f->id }}" @selected(request('folder') == $f->id)>{{ $f->name }}</option>@endforeach</select>
            <button class="a-btn a-btn--sm">جستجو</button>
        </form>
        <label class="a-btn a-btn--ghost a-btn--sm a-picker__upload">بارگذاری جدید<input type="file" multiple accept="image/*" data-picker-upload hidden></label>
    </div>
    <div class="a-media-grid a-media-grid--picker">
        @forelse ($items as $m)
            <button type="button" class="a-media-item" data-pick="{{ $m->id }}" data-thumb="{{ $m->variantUrl('thumbnail') }}" title="{{ $m->original_name }}">
                <img src="{{ $m->variantUrl('thumbnail') }}" alt="{{ $m->alt('fa') }}" loading="lazy"><small>{{ \Illuminate\Support\Str::limit($m->alt('fa') ?: $m->original_name, 22) }}</small>
            </button>
        @empty
            <p class="a-empty">تصویری پیدا نشد.</p>
        @endforelse
    </div>
    <div class="a-picker__foot">
        @if ($items->previousPageUrl())<a href="{{ $items->previousPageUrl() }}" data-picker-page class="a-btn a-btn--ghost a-btn--sm">قبلی</a>@endif
        @if ($items->nextPageUrl())<a href="{{ $items->nextPageUrl() }}" data-picker-page class="a-btn a-btn--ghost a-btn--sm">بعدی</a>@endif
        @if ($multiple)<button type="button" class="a-btn a-btn--sm" data-picker-done>افزودن انتخاب‌شده‌ها</button>@endif
    </div>
</div>
