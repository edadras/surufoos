@extends('admin.layout')
@section('title', $post->exists ? 'ویرایش مقاله' : 'مقاله جدید')
@section('content')
<form method="post" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" data-dirty-guard>
    @csrf @if($post->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $post->exists ? $post->tr('title', 'fa', true) : 'مقاله جدید' }}</h1>
        <div class="a-head__actions">@if($post->exists && ($u = $post->url('fa')))<a href="{{ $u }}" target="_blank" class="a-btn a-btn--ghost">نمایش</a>@endif<button class="a-btn">ذخیره</button></div></div>
    <div class="a-grid">
        <div class="a-grid__main">
            <section class="a-card">
                @include('admin.partials.translatable', ['model' => $post->exists ? $post : null, 'fields' => [
                    'title' => ['عنوان', 'text', true], 'slug' => ['نامک', 'slug'], 'short_description' => ['خلاصه (Excerpt)', 'textarea'],
                    'description' => ['متن مقاله', 'rich'], 'seo_title' => ['عنوان سئو', 'text'], 'seo_description' => ['توضیح سئو', 'textarea'],
                ]])
            </section>
            <section class="a-card">
                @include('admin.partials.media-multi', ['name' => 'gallery', 'label' => 'گالری', 'values' => old('gallery', $post->gallery->pluck('media_id')->all())])
            </section>
        </div>
        <aside class="a-grid__side">
            <section class="a-card">
                <div class="a-field"><label for="status">وضعیت</label><select id="status" name="status">@foreach (\App\Enums\ContentStatus::options() as $k => $v)<option value="{{ $k }}" @selected(old('status', $post->status?->value) === $k)>{{ $v }}</option>@endforeach</select></div>
                <div class="a-field"><label for="published_at">تاریخ انتشار</label><input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"></div>
                <div class="a-field"><label for="post_category_id">دسته</label><select id="post_category_id" name="post_category_id"><option value="">—</option>@foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('post_category_id', $post->post_category_id) == $c->id)>{{ $c->tr('title', 'fa', true) }}</option>@endforeach</select></div>
                <div class="a-field"><label for="author_name">نام نویسنده (نمایشی)</label><input id="author_name" name="author_name" value="{{ old('author_name', $post->author_name) }}"></div>
                <div class="a-field"><label for="reading_minutes">زمان مطالعه (دقیقه، خالی = خودکار)</label><input type="number" id="reading_minutes" name="reading_minutes" value="{{ old('reading_minutes', $post->reading_minutes) }}"></div>
                <label class="a-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured))> مقاله ویژه</label>
            </section>
            <section class="a-card">
                @include('admin.partials.media-field', ['name' => 'cover_media_id', 'label' => 'تصویر کاور', 'value' => old('cover_media_id', $post->cover_media_id)])
                <div class="a-field"><label for="products">محصولات مرتبط</label>
                    <select id="products" name="products[]" multiple size="8" class="a-multi">@foreach ($products as $p)<option value="{{ $p->id }}" @selected(in_array($p->id, old('products', $post->products->pluck('id')->all())))>{{ $p->tr('title', 'fa', true) }}</option>@endforeach</select></div>
                <div class="a-field"><label for="tags">برچسب‌ها</label><input id="tags" name="tags" value="{{ old('tags', $post->tags->map(fn ($t) => $t->tr('name', 'fa', true))->implode('، ')) }}"></div>
            </section>
        </aside>
    </div>
</form>
@endsection
