@extends('admin.layout')
@section('title', $category->exists ? 'ویرایش دسته' : 'دسته جدید')
@section('content')
<form method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" data-dirty-guard>
    @csrf @if($category->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $category->exists ? $category->tr('title', 'fa', true) : 'دسته جدید' }}</h1><button class="a-btn">ذخیره</button></div>
    <div class="a-grid">
        <div class="a-grid__main">
            <section class="a-card">
                @include('admin.partials.translatable', ['model' => $category->exists ? $category : null, 'fields' => [
                    'title' => ['نام دسته', 'text', true], 'slug' => ['نامک', 'slug'], 'short_description' => ['توضیح کوتاه', 'textarea'],
                    'description' => ['توضیحات', 'rich'], 'seo_title' => ['عنوان سئو', 'text'], 'seo_description' => ['توضیح سئو', 'textarea'],
                ]])
            </section>
        </div>
        <aside class="a-grid__side">
            <section class="a-card">
                <div class="a-field"><label for="status">وضعیت</label><select id="status" name="status">@foreach (\App\Enums\ContentStatus::options() as $k => $v)<option value="{{ $k }}" @selected(old('status', $category->status?->value) === $k)>{{ $v }}</option>@endforeach</select></div>
                <div class="a-field"><label for="parent_id">دسته والد</label><select id="parent_id" name="parent_id"><option value="">— دسته اصلی —</option>@foreach ($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id) == $p->id)>{{ $p->tr('title', 'fa', true) }}</option>@endforeach</select></div>
                <div class="a-field"><label for="sort_order">ترتیب</label><input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"></div>
                <label class="a-check"><input type="checkbox" name="show_in_menu" value="1" @checked(old('show_in_menu', $category->show_in_menu))> نمایش در مگامنو</label>
                <div class="a-field"><label for="theme_color">رنگ دسته</label><div class="a-color"><input type="color" value="{{ old('theme_color', $category->theme_color ?: '#ede3d1') }}" data-color-sync="theme_color"><input id="theme_color" name="theme_color" dir="ltr" value="{{ old('theme_color', $category->theme_color) }}" placeholder="خودکار از تصویر"></div></div>
            </section>
            <section class="a-card">
                @include('admin.partials.media-field', ['name' => 'image_media_id', 'label' => 'تصویر شاخص (ترجیحاً بدون پس‌زمینه)', 'value' => old('image_media_id', $category->image_media_id)])
                @include('admin.partials.media-field', ['name' => 'cover_media_id', 'label' => 'کاور (پس‌زمینه)', 'value' => old('cover_media_id', $category->cover_media_id)])
                @include('admin.partials.media-field', ['name' => 'icon_media_id', 'label' => 'آیکون / گرافیک', 'value' => old('icon_media_id', $category->icon_media_id)])
            </section>
        </aside>
    </div>
</form>
@endsection
