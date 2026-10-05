@extends('admin.layout')
@section('title', $recipe->exists ? 'ویرایش دستور' : 'دستور جدید')
@section('content')
<form method="post" action="{{ $recipe->exists ? route('admin.recipes.update', $recipe) : route('admin.recipes.store') }}" data-dirty-guard>
    @csrf @if($recipe->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $recipe->exists ? $recipe->tr('title', 'fa', true) : 'دستور جدید' }}</h1>
        <div class="a-head__actions">@if($recipe->exists && ($u = $recipe->url('fa')))<a href="{{ $u }}" target="_blank" class="a-btn a-btn--ghost">نمایش</a>@endif<button class="a-btn">ذخیره</button></div></div>
    <div class="a-grid">
        <div class="a-grid__main">
            <section class="a-card">
                @include('admin.partials.translatable', ['model' => $recipe->exists ? $recipe : null, 'fields' => [
                    'title' => ['عنوان دستور', 'text', true], 'slug' => ['نامک', 'slug'], 'short_description' => ['معرفی کوتاه', 'textarea'],
                    'description' => ['مقدمه / توضیحات', 'rich'], 'tips' => ['نکته‌ها', 'textarea'], 'seo_title' => ['عنوان سئو', 'text'], 'seo_description' => ['توضیح سئو', 'textarea'],
                ]])
            </section>
            <section class="a-card">
                <h2>مواد لازم و مراحل <small>برای هر زبان جداگانه</small></h2>
                <div class="a-tr" data-tr>
                    <div class="a-tr__tabs" role="tablist">
                        @foreach (config('suru.locales') as $code => $meta)
                            <button type="button" role="tab" class="a-tr__tab {{ $loop->first ? 'is-active' : '' }}" data-tr-tab="{{ $code }}">{{ $meta['short'] }}</button>
                        @endforeach
                    </div>
                    @foreach (config('suru.locales') as $code => $meta)
                        @php
                            $lines = old("lines.$code", $recipe->ingredientLines->where('locale', $code)->map->only(['quantity', 'name', 'note', 'product_id'])->values()->all());
                            $steps = old("steps.$code", $recipe->steps->where('locale', $code)->map->only(['body', 'media_id', 'duration_minutes'])->values()->all());
                        @endphp
                        <div class="a-tr__pane" data-tr-pane="{{ $code }}" @unless($loop->first) hidden @endunless dir="{{ $meta['dir'] }}">
                            <h3>مواد لازم</h3>
                            <div data-repeater>
                                <div class="a-repeater" data-repeater-list data-sortable>
                                    @foreach ($lines as $i => $row) @include('admin.recipes._line', ['l' => $code, 'i' => $i, 'row' => $row]) @endforeach
                                </div>
                                <template data-repeater-template>@include('admin.recipes._line', ['l' => $code, 'i' => '__INDEX__', 'row' => []])</template>
                                <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-repeater-add>+ ماده</button>
                            </div>
                            <h3>مراحل</h3>
                            <div data-repeater>
                                <div class="a-repeater" data-repeater-list data-sortable>
                                    @foreach ($steps as $i => $row) @include('admin.recipes._step', ['l' => $code, 'i' => $i, 'row' => $row]) @endforeach
                                </div>
                                <template data-repeater-template>@include('admin.recipes._step', ['l' => $code, 'i' => '__INDEX__', 'row' => []])</template>
                                <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-repeater-add>+ مرحله</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="a-card">@include('admin.partials.media-multi', ['name' => 'gallery', 'label' => 'گالری', 'values' => old('gallery', $recipe->gallery->pluck('media_id')->all())])</section>
        </div>
        <aside class="a-grid__side">
            <section class="a-card">
                <div class="a-field"><label for="status">وضعیت</label><select id="status" name="status">@foreach (\App\Enums\ContentStatus::options() as $k => $v)<option value="{{ $k }}" @selected(old('status', $recipe->status?->value) === $k)>{{ $v }}</option>@endforeach</select></div>
                <div class="a-field"><label for="published_at">تاریخ انتشار</label><input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $recipe->published_at?->format('Y-m-d\TH:i')) }}"></div>
                <label class="a-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $recipe->is_featured))> دستور ویژه</label>
            </section>
            <section class="a-card">
                <div class="a-row">
                    <div class="a-field"><label for="prep">آماده‌سازی (دقیقه)</label><input type="number" id="prep" name="prep_minutes" value="{{ old('prep_minutes', $recipe->prep_minutes) }}"></div>
                    <div class="a-field"><label for="cook">پخت (دقیقه)</label><input type="number" id="cook" name="cook_minutes" value="{{ old('cook_minutes', $recipe->cook_minutes) }}"></div>
                </div>
                <div class="a-row">
                    <div class="a-field"><label for="servings">تعداد نفرات</label><input type="number" id="servings" name="servings" value="{{ old('servings', $recipe->servings) }}"></div>
                    <div class="a-field"><label for="difficulty">سختی</label><select id="difficulty" name="difficulty"><option value="">—</option>@foreach (\App\Enums\Difficulty::options() as $k => $v)<option value="{{ $k }}" @selected(old('difficulty', $recipe->difficulty?->value) === $k)>{{ $v }}</option>@endforeach</select></div>
                </div>
                <fieldset class="a-fieldset"><legend>ارزش غذایی هر وعده</legend>
                    <div class="a-row">@foreach (['calories' => 'کالری', 'protein' => 'پروتئین', 'carbs' => 'کربوهیدرات', 'fat' => 'چربی'] as $k => $v)
                        <div class="a-field"><label for="n-{{ $k }}">{{ $v }}</label><input id="n-{{ $k }}" name="nutrition[{{ $k }}]" value="{{ old("nutrition.$k", $recipe->nutrition[$k] ?? '') }}"></div>
                    @endforeach</div>
                </fieldset>
            </section>
            <section class="a-card">
                @include('admin.partials.media-field', ['name' => 'cover_media_id', 'label' => 'تصویر اصلی', 'value' => old('cover_media_id', $recipe->cover_media_id)])
                <div class="a-field"><label for="video_url">لینک ویدیو (آپارات / یوتیوب / فایل)</label><input id="video_url" name="video_url" dir="ltr" value="{{ old('video_url', $recipe->video_url) }}"></div>
                <div class="a-field"><label for="products">محصولات سورو در این دستور</label>
                    <select id="products" name="products[]" multiple size="8" class="a-multi">@foreach ($products as $p)<option value="{{ $p->id }}" @selected(in_array($p->id, old('products', $recipe->products->pluck('id')->all())))>{{ $p->tr('title', 'fa', true) }}</option>@endforeach</select></div>
            </section>
        </aside>
    </div>
</form>
@endsection
