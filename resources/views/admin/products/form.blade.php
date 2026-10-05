@extends('admin.layout')
@section('title', $product->exists ? 'ویرایش محصول' : 'محصول جدید')
@php
    $ingredientRows = old('ingredients', $product->exists ? $product->ingredients->map(fn ($i) => ['ingredient_id' => $i->id] + $i->pivot->only(['amount', 'position_x', 'position_y', 'is_highlight']))->all() : []);
    $nutritionRows = old('nutrition', $product->exists ? $product->nutritionFacts->map(fn ($n) => ['nutrient' => $n->nutrient->value] + $n->only(['amount', 'unit', 'per', 'daily_value_percent']))->all() : []);
    $variantRows = old('variants', $product->exists ? $product->variants->map(fn ($v) => $v->only(['id', 'sku', 'price', 'sale_price', 'stock_qty', 'net_weight', 'weight_unit', 'is_default']) + ['stock_status' => $v->stock_status->value, 'attribute_values' => $v->attributeValues->pluck('id')->all()])->all() : []);
    $selectedCats = old('categories', $product->categories->pluck('id')->all());
    $selectedValues = old('attribute_values', $product->attributeValues->pluck('id')->all());
    $gallery = old('gallery', $product->images->filter(fn ($i) => $i->type->value === 'gallery')->pluck('media_id')->all());
    $lifestyle = old('lifestyle', $product->images->filter(fn ($i) => $i->type->value === 'lifestyle')->pluck('media_id')->all());
    $allValues = $attributes->flatMap(fn ($a) => $a->values->map(fn ($v) => ['id' => $v->id, 'label' => $a->tr('name', 'fa', true).': '.$v->label('fa')]));
@endphp
@section('content')
<form method="post" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="a-form" data-dirty-guard>
    @csrf @if($product->exists) @method('PUT') @endif
    <div class="a-head">
        <h1>{{ $product->exists ? $product->tr('title', 'fa', true) : 'محصول جدید' }}</h1>
        <div class="a-head__actions">
            @if ($product->exists && ($url = $product->url('fa')))<a href="{{ $url }}" target="_blank" class="a-btn a-btn--ghost">نمایش در سایت</a>@endif
            <button class="a-btn" type="submit">ذخیره</button>
        </div>
    </div>

    <div class="a-grid">
        <div class="a-grid__main">
            <section class="a-card">
                <h2>متن و محتوا</h2>
                @include('admin.partials.translatable', ['model' => $product->exists ? $product : null, 'fields' => [
                    'title' => ['نام محصول', 'text', true],
                    'slug' => ['نامک (آدرس)', 'slug'],
                    'short_description' => ['توضیح کوتاه', 'textarea'],
                    'description' => ['توضیحات کامل', 'rich'],
                    'story' => ['داستان محصول (Product Story)', 'rich'],
                    'features' => ['ویژگی‌ها', 'textarea'],
                    'how_to_use' => ['نحوه مصرف', 'textarea'],
                    'serving_suggestion' => ['پیشنهاد سرو', 'textarea'],
                    'storage' => ['شرایط نگهداری', 'textarea'],
                    'badge_label' => ['متن برچسب (اختیاری)', 'text'],
                    'seo_title' => ['عنوان سئو', 'text'],
                    'seo_description' => ['توضیح سئو', 'textarea'],
                ]])
            </section>

            <section class="a-card">
                <h2>تصاویر</h2>
                <div class="a-row">
                    @include('admin.partials.media-field', ['name' => 'main_media_id', 'label' => 'تصویر اصلی', 'value' => old('main_media_id', $product->main_media_id)])
                    @include('admin.partials.media-field', ['name' => 'packshot_media_id', 'label' => 'عکس بسته بدون پس‌زمینه (PNG شفاف)', 'value' => old('packshot_media_id', $product->packshot_media_id), 'hint' => 'در Hero، کارت محصول و صحنه‌های تعاملی استفاده می‌شود.'])
                </div>
                @include('admin.partials.media-multi', ['name' => 'gallery', 'label' => 'گالری محصول', 'values' => $gallery])
                @include('admin.partials.media-multi', ['name' => 'lifestyle', 'label' => 'تصاویر Lifestyle', 'values' => $lifestyle])
            </section>

            <section class="a-card">
                <h2>مواد اولیه <small>برای بخش «درون سورو» و صفحه محصول</small></h2>
                <div data-repeater>
                    <div class="a-repeater" data-repeater-list data-sortable>
                        @foreach ($ingredientRows as $i => $row)
                            @include('admin.products._ingredient-row', ['i' => $i, 'row' => $row])
                        @endforeach
                    </div>
                    <template data-repeater-template>@include('admin.products._ingredient-row', ['i' => '__INDEX__', 'row' => []])</template>
                    <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-repeater-add>+ افزودن ماده اولیه</button>
                    <small class="a-hint">موقعیت X/Y (۰ تا ۱۰۰) جای هر ماده در صحنه دور محصول است؛ خالی بماند خودکار چیده می‌شود. ماده جدید را از منوی «مواد اولیه» بسازید.</small>
                </div>
            </section>

            <section class="a-card">
                <h2>ارزش غذایی</h2>
                <div data-repeater>
                    <div class="a-repeater" data-repeater-list data-sortable>
                        @foreach ($nutritionRows as $i => $row)
                            @include('admin.products._nutrition-row', ['i' => $i, 'row' => $row])
                        @endforeach
                    </div>
                    <template data-repeater-template>@include('admin.products._nutrition-row', ['i' => '__INDEX__', 'row' => []])</template>
                    <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-repeater-add>+ افزودن ردیف</button>
                </div>
            </section>

            <section class="a-card">
                <h2>تنوع‌ها (Variants) <small>اختیاری — مثلاً وزن‌های مختلف</small></h2>
                <div data-repeater>
                    <div class="a-repeater" data-repeater-list>
                        @foreach ($variantRows as $i => $row)
                            @include('admin.products._variant-row', ['i' => $i, 'row' => $row])
                        @endforeach
                    </div>
                    <template data-repeater-template>@include('admin.products._variant-row', ['i' => '__INDEX__', 'row' => []])</template>
                    <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-repeater-add>+ افزودن تنوع</button>
                </div>
            </section>
        </div>

        <aside class="a-grid__side">
            <section class="a-card">
                <h2>انتشار</h2>
                <div class="a-field"><label for="status">وضعیت</label>
                    <select id="status" name="status">@foreach (\App\Enums\ContentStatus::options() as $k => $v)<option value="{{ $k }}" @selected(old('status', $product->status?->value) === $k)>{{ $v }}</option>@endforeach</select></div>
                <div class="a-field"><label for="published_at">تاریخ انتشار</label><input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $product->published_at?->format('Y-m-d\TH:i')) }}"></div>
                <div class="a-field"><label for="sort_order">ترتیب نمایش</label><input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}"></div>
                <label class="a-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))> محصول منتخب</label>
                <label class="a-check"><input type="checkbox" name="is_new" value="1" @checked(old('is_new', $product->is_new))> محصول جدید (قفسه)</label>
                <label class="a-check"><input type="checkbox" name="show_inside" value="1" @checked(old('show_inside', $product->show_inside))> نمایش در «درون سورو»</label>
            </section>

            <section class="a-card">
                <h2>قیمت و موجودی</h2>
                <div class="a-field"><label for="sku">کد محصول (SKU)</label><input id="sku" name="sku" dir="ltr" value="{{ old('sku', $product->sku) }}"></div>
                <div class="a-row">
                    <div class="a-field"><label for="price">قیمت (تومان)</label><input id="price" name="price" inputmode="numeric" dir="ltr" value="{{ old('price', $product->price) }}"></div>
                    <div class="a-field"><label for="sale_price">قیمت با تخفیف</label><input id="sale_price" name="sale_price" inputmode="numeric" dir="ltr" value="{{ old('sale_price', $product->sale_price) }}"></div>
                </div>
                <div class="a-row">
                    <div class="a-field"><label for="stock_status">موجودی</label>
                        <select id="stock_status" name="stock_status">@foreach (\App\Enums\StockStatus::options() as $k => $v)<option value="{{ $k }}" @selected(old('stock_status', $product->stock_status?->value) === $k)>{{ $v }}</option>@endforeach</select></div>
                    <div class="a-field"><label for="stock_qty">تعداد</label><input type="number" id="stock_qty" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty) }}"></div>
                </div>
                <div class="a-row">
                    <div class="a-field"><label for="net_weight">وزن / حجم</label><input id="net_weight" name="net_weight" inputmode="decimal" dir="ltr" value="{{ old('net_weight', $product->net_weight) }}"></div>
                    <div class="a-field"><label for="weight_unit">واحد</label>
                        <select id="weight_unit" name="weight_unit"><option value=""></option>@foreach (['g' => 'گرم', 'kg' => 'کیلوگرم', 'ml' => 'میلی‌لیتر', 'l' => 'لیتر', 'pcs' => 'عدد'] as $k => $v)<option value="{{ $k }}" @selected(old('weight_unit', $product->weight_unit) === $k)>{{ $v }}</option>@endforeach</select></div>
                </div>
            </section>

            <section class="a-card">
                <h2>دسته‌بندی</h2>
                <div class="a-field"><label for="primary_category_id">دسته اصلی</label>
                    <select id="primary_category_id" name="primary_category_id"><option value="">—</option>@foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('primary_category_id', $product->primary_category_id) == $c->id)>{{ $c->tr('title', 'fa', true) }}</option>@endforeach</select></div>
                <div class="a-checklist">
                    @foreach ($categories as $c)
                        <label class="a-check"><input type="checkbox" name="categories[]" value="{{ $c->id }}" @checked(in_array($c->id, $selectedCats))> {{ $c->parent_id ? '— ' : '' }}{{ $c->tr('title', 'fa', true) }}</label>
                    @endforeach
                </div>
            </section>

            <section class="a-card">
                <h2>ویژگی‌ها <small>فیلترها و «چی دوست داری؟»</small></h2>
                @forelse ($attributes as $attr)
                    <fieldset class="a-fieldset"><legend>{{ $attr->tr('name', 'fa', true) }}</legend>
                        @foreach ($attr->values as $v)
                            <label class="a-check"><input type="checkbox" name="attribute_values[]" value="{{ $v->id }}" @checked(in_array($v->id, $selectedValues))> {{ $v->label('fa') }}</label>
                        @endforeach
                    </fieldset>
                @empty
                    <p class="a-muted">هنوز ویژگی‌ای تعریف نشده است.</p>
                @endforelse
                <div class="a-field"><label for="tags">برچسب‌ها (با ویرگول جدا کنید)</label><input id="tags" name="tags" value="{{ old('tags', $product->tags->map(fn ($t) => $t->tr('name', 'fa', true))->implode('، ')) }}"></div>
            </section>

            <section class="a-card">
                <h2>ظاهر</h2>
                <div class="a-field"><label for="theme_color">رنگ تم محصول</label>
                    <div class="a-color"><input type="color" id="theme_color_picker" value="{{ old('theme_color', $product->theme_color ?: '#e9e2d3') }}" data-color-sync="theme_color"><input id="theme_color" name="theme_color" dir="ltr" value="{{ old('theme_color', $product->theme_color) }}" placeholder="خودکار از تصویر"></div>
                    <small class="a-hint">خالی بگذارید تا از رنگ غالب عکس بسته استخراج شود.</small></div>
                <div class="a-field"><label for="badge">نشان</label>
                    <select id="badge" name="badge"><option value="">بدون نشان</option>@foreach (['new' => 'جدید', 'sale' => 'تخفیف', 'bestseller' => 'پرفروش', 'organic' => 'ارگانیک', 'limited' => 'محدود'] as $k => $v)<option value="{{ $k }}" @selected(old('badge', $product->badge) === $k)>{{ $v }}</option>@endforeach</select></div>
            </section>
        </aside>
    </div>
</form>
@if ($product->exists)
    <div class="a-danger-zone">@include('admin.partials.delete-form', ['action' => route('admin.products.destroy', $product), 'label' => 'حذف محصول'])</div>
@endif
@endsection
