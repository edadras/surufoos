@extends('admin.layout')
@section('title', $attribute->exists ? 'ویرایش ویژگی' : 'ویژگی جدید')
@php
    $rows = old('values', $attribute->exists ? $attribute->values->map(fn ($v) => ['id' => $v->id, 'code' => $v->code, 'color' => $v->color, 'fa' => $v->tr('value', 'fa', false), 'en' => $v->tr('value', 'en', false), 'ar' => $v->tr('value', 'ar', false)])->all() : []);
@endphp
@section('content')
<form method="post" action="{{ $attribute->exists ? route('admin.attributes.update', $attribute) : route('admin.attributes.store') }}" data-dirty-guard>
    @csrf @if($attribute->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $attribute->exists ? $attribute->tr('name', 'fa', true) : 'ویژگی جدید' }}</h1><button class="a-btn">ذخیره</button></div>
    <div class="a-grid">
        <div class="a-grid__main">
            <section class="a-card">
                @include('admin.partials.translatable', ['model' => $attribute->exists ? $attribute : null, 'primary' => 'name', 'fields' => ['name' => ['نام ویژگی', 'text', true], 'quiz_question' => ['پرسش در «چی دوست داری؟»', 'text']]])
            </section>
            <section class="a-card">
                <h2>مقادیر</h2>
                <div data-repeater>
                    <div class="a-repeater" data-repeater-list data-sortable>
                        @foreach ($rows as $i => $row) @include('admin.attributes._value-row', ['i' => $i, 'row' => $row]) @endforeach
                    </div>
                    <template data-repeater-template>@include('admin.attributes._value-row', ['i' => '__INDEX__', 'row' => []])</template>
                    <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-repeater-add>+ افزودن مقدار</button>
                </div>
            </section>
        </div>
        <aside class="a-grid__side"><section class="a-card">
            <div class="a-field"><label for="code">کد (لاتین، در آدرس فیلترها)</label><input id="code" name="code" dir="ltr" required value="{{ old('code', $attribute->code) }}" pattern="[a-z0-9_-]+"></div>
            <div class="a-field"><label for="type">نوع</label><select id="type" name="type">@foreach (['multi' => 'چندانتخابی', 'select' => 'تک‌انتخابی', 'color' => 'رنگ'] as $k => $v)<option value="{{ $k }}" @selected(old('type', $attribute->type) === $k)>{{ $v }}</option>@endforeach</select></div>
            <div class="a-field"><label for="sort_order">ترتیب</label><input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $attribute->sort_order ?? 0) }}"></div>
            <label class="a-check"><input type="checkbox" name="is_filterable" value="1" @checked(old('is_filterable', $attribute->is_filterable))> قابل فیلتر در فهرست محصولات</label>
            <label class="a-check"><input type="checkbox" name="is_quiz" value="1" @checked(old('is_quiz', $attribute->is_quiz))> استفاده در «چی دوست داری؟»</label>
        </section></aside>
    </div>
</form>
@endsection
