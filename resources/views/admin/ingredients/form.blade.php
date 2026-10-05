@extends('admin.layout')
@section('title', $ingredient->exists ? 'ویرایش ماده اولیه' : 'ماده اولیه جدید')
@section('content')
<form method="post" action="{{ $ingredient->exists ? route('admin.ingredients.update', $ingredient) : route('admin.ingredients.store') }}" data-dirty-guard>
    @csrf @if($ingredient->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $ingredient->exists ? $ingredient->tr('name', 'fa', true) : 'ماده اولیه جدید' }}</h1><button class="a-btn">ذخیره</button></div>
    <div class="a-grid">
        <div class="a-grid__main"><section class="a-card">
            @include('admin.partials.translatable', ['model' => $ingredient->exists ? $ingredient : null, 'primary' => 'name', 'fields' => ['name' => ['نام', 'text', true], 'description' => ['توضیح کوتاه (در کارت شناور)', 'textarea']]])
        </section></div>
        <aside class="a-grid__side"><section class="a-card">
            @include('admin.partials.media-field', ['name' => 'media_id', 'label' => 'تصویر (ترجیحاً PNG شفاف)', 'value' => old('media_id', $ingredient->media_id)])
            <div class="a-field"><label for="color">رنگ</label><div class="a-color"><input type="color" value="{{ old('color', $ingredient->color ?: '#e3a43a') }}" data-color-sync="color"><input id="color" name="color" dir="ltr" value="{{ old('color', $ingredient->color) }}"></div></div>
        </section></aside>
    </div>
</form>
@endsection
