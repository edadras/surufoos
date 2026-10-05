@extends('admin.layout')
@section('title', $page->exists ? 'ویرایش صفحه' : 'صفحه جدید')
@section('content')
<form method="post" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" data-dirty-guard>
    @csrf @if($page->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $page->exists ? ($page->tr('title', 'fa', true) ?? \App\Models\Page::SYSTEM_KEYS[$page->key] ?? '') : 'صفحه جدید' }}</h1>
        <div class="a-head__actions">@if($page->exists && ($u = $page->url('fa')))<a href="{{ $u }}" target="_blank" class="a-btn a-btn--ghost">نمایش</a>@endif<button class="a-btn">ذخیره</button></div></div>
    <div class="a-grid">
        <div class="a-grid__main"><section class="a-card">
            @include('admin.partials.translatable', ['model' => $page->exists ? $page : null, 'fields' => [
                'title' => ['عنوان', 'text', true], 'slug' => ['نامک', 'slug'], 'short_description' => ['معرفی کوتاه (زیر عنوان)', 'textarea'],
                'description' => ['متن صفحه', 'rich'], 'seo_title' => ['عنوان سئو', 'text'], 'seo_description' => ['توضیح سئو', 'textarea'],
            ]])
        </section></div>
        <aside class="a-grid__side"><section class="a-card">
            <div class="a-field"><label for="status">وضعیت</label><select id="status" name="status">@foreach (\App\Enums\ContentStatus::options() as $k => $v)<option value="{{ $k }}" @selected(old('status', $page->status?->value) === $k)>{{ $v }}</option>@endforeach</select></div>
            @include('admin.partials.media-field', ['name' => 'cover_media_id', 'label' => 'تصویر کاور', 'value' => old('cover_media_id', $page->cover_media_id)])
        </section></aside>
    </div>
</form>

@if ($page->exists)
    <section class="a-card">
        <div class="a-head"><h2>سکشن‌های صفحه <small>برای تغییر ترتیب بکشید</small></h2>
            <form method="post" action="{{ route('admin.pages.sections.store', $page) }}" class="a-inline-form">@csrf
                <select name="type" required><option value="">نوع سکشن…</option>@foreach ($types as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                <button class="a-btn a-btn--sm">+ افزودن سکشن</button>
            </form>
        </div>
        <ol class="a-sections" data-sortable data-reorder-url="{{ route('admin.pages.sections.reorder', $page) }}">
            @forelse ($page->sections as $section)
                <li class="a-section {{ $section->is_active ? '' : 'is-off' }}" data-id="{{ $section->id }}">
                    <span class="a-drag">⋮⋮</span>
                    <div class="a-section__info"><strong>{{ $section->type->label() }}</strong><small>{{ $section->tr('title', 'fa', true) ?? 'بدون عنوان — از داده‌های خودکار استفاده می‌کند' }}</small></div>
                    @include('admin.partials.tr-status', ['model' => $section])
                    <form method="post" action="{{ route('admin.pages.sections.toggle', [$page, $section]) }}">@csrf @method('PATCH')<button class="a-btn a-btn--ghost a-btn--sm">{{ $section->is_active ? 'غیرفعال کن' : 'فعال کن' }}</button></form>
                    <a href="{{ route('admin.pages.sections.edit', [$page, $section]) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>
                    @include('admin.partials.delete-form', ['action' => route('admin.pages.sections.destroy', [$page, $section])])
                </li>
            @empty
                <li class="a-empty">هنوز سکشنی اضافه نشده است.</li>
            @endforelse
        </ol>
        <p class="a-hint">سکشنی که داده ندارد (مثلاً بنر فعال یا محصول منتشرشده‌ای ندارد) در سایت نمایش داده نمی‌شود.</p>
    </section>
@endif
@endsection
