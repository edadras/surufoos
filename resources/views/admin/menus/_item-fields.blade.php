@php($uid = $item->id ?? 'new')
<div class="a-row">
    @foreach (config('suru.locales') as $code => $meta)
        <div class="a-field"><label>برچسب {{ $meta['short'] }}</label><input name="translations[{{ $code }}][label]" value="{{ $item->exists ? $item->tr('label', $code, false) : '' }}" dir="{{ $meta['dir'] }}" @if($code === 'fa') required @endif></div>
    @endforeach
</div>
<div class="a-field"><label>نوع لینک</label>
    <select name="type" data-menu-type>
        @foreach (['route' => 'صفحه سیستمی', 'category' => 'دسته محصول', 'product' => 'محصول', 'page' => 'صفحه', 'url' => 'لینک دلخواه'] as $k => $v)<option value="{{ $k }}" @selected($item->type === $k)>{{ $v }}</option>@endforeach
    </select></div>
<div class="a-field" data-menu-for="route"><label>صفحه</label><select name="route_name">@foreach (\App\Models\MenuItem::ROUTES as $k => $v)<option value="{{ $k }}" @selected($item->route_name === $k)>{{ $v }}</option>@endforeach</select></div>
<div class="a-field" data-menu-for="category"><label>دسته</label><select name="linkable_id" data-menu-linkable>@foreach ($categories as $c)<option value="{{ $c->id }}" @selected($item->linkable_type === 'category' && $item->linkable_id == $c->id)>{{ $c->tr('title', 'fa', true) }}</option>@endforeach</select></div>
<div class="a-field" data-menu-for="product"><label>محصول</label><select name="linkable_id" data-menu-linkable>@foreach ($products as $p)<option value="{{ $p->id }}" @selected($item->linkable_type === 'product' && $item->linkable_id == $p->id)>{{ $p->tr('title', 'fa', true) }}</option>@endforeach</select></div>
<div class="a-field" data-menu-for="page"><label>صفحه</label><select name="linkable_id" data-menu-linkable>@foreach ($pages as $pg)<option value="{{ $pg->id }}" @selected($item->linkable_type === 'page' && $item->linkable_id == $pg->id)>{{ $pg->tr('title', 'fa', true) }}</option>@endforeach</select></div>
<div class="a-row" data-menu-for="url">
    @foreach (config('suru.locales') as $code => $meta)
        <div class="a-field"><label>آدرس {{ $meta['short'] }}</label><input name="translations[{{ $code }}][url]" value="{{ $item->exists ? $item->tr('url', $code, false) : '' }}" dir="ltr"></div>
    @endforeach
</div>
<div class="a-field"><label>زیرمجموعه</label><select name="parent_id"><option value="">— آیتم اصلی —</option>@foreach ($menu->rootItems as $root)@if($root->id !== $item->id)<option value="{{ $root->id }}" @selected($item->parent_id == $root->id)>{{ $root->label('fa') }}</option>@endif @endforeach</select></div>
<label class="a-check"><input type="checkbox" name="is_mega" value="1" @checked($item->is_mega)> نمایش مگامنوی تصویری محصولات</label>
<label class="a-check"><input type="checkbox" name="is_active" value="1" @checked($item->is_active ?? true)> فعال</label>
