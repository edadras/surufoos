@extends('admin.layout')
@section('title', $category->exists ? 'ویرایش دسته مجله' : 'دسته مجله جدید')
@section('content')
<form method="post" action="{{ $category->exists ? route('admin.post-categories.update', $category) : route('admin.post-categories.store') }}">
    @csrf @if($category->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $category->exists ? $category->tr('title', 'fa', true) : 'دسته جدید' }}</h1><button class="a-btn">ذخیره</button></div>
    <section class="a-card">
        <div class="a-field a-field--inline"><label for="sort_order">ترتیب</label><input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"></div>
        @include('admin.partials.translatable', ['model' => $category->exists ? $category : null, 'fields' => [
            'title' => ['عنوان', 'text', true], 'slug' => ['نامک', 'slug'], 'short_description' => ['توضیح', 'textarea'],
            'seo_title' => ['عنوان سئو', 'text'], 'seo_description' => ['توضیح سئو', 'textarea'],
        ]])
    </section>
</form>
@endsection
