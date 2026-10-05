@extends('admin.layout')
@section('title', 'ریدایرکت')
@section('content')
<form method="post" action="{{ $redirect->exists ? route('admin.redirects.update', $redirect) : route('admin.redirects.store') }}" class="a-card a-narrow">
    @csrf @if($redirect->exists) @method('PUT') @endif
    <h1>{{ $redirect->exists ? 'ویرایش ریدایرکت' : 'ریدایرکت جدید' }}</h1>
    <div class="a-field"><label for="from_path">آدرس مبدأ (مسیر)</label><input id="from_path" name="from_path" dir="ltr" required value="{{ old('from_path', $redirect->from_path) }}" placeholder="/product/old-slug"></div>
    <div class="a-field"><label for="to_path">آدرس مقصد</label><input id="to_path" name="to_path" dir="ltr" required value="{{ old('to_path', $redirect->to_path) }}" placeholder="/fa/products/new-slug"></div>
    <div class="a-field"><label for="status_code">نوع</label><select id="status_code" name="status_code">@foreach ([301 => '۳۰۱ دائمی', 302 => '۳۰۲ موقت', 307 => '۳۰۷', 308 => '۳۰۸'] as $k => $v)<option value="{{ $k }}" @selected(old('status_code', $redirect->status_code) == $k)>{{ $v }}</option>@endforeach</select></div>
    <label class="a-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $redirect->is_active))> فعال</label>
    <button class="a-btn">ذخیره</button>
</form>
@endsection
