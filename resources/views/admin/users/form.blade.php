@extends('admin.layout')
@section('title', $user->exists ? 'ویرایش کاربر' : 'کاربر جدید')
@section('content')
<form method="post" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="a-card a-narrow">
    @csrf @if($user->exists) @method('PUT') @endif
    <h1>{{ $user->exists ? $user->name : 'کاربر جدید' }}</h1>
    <div class="a-field"><label for="name">نام</label><input id="name" name="name" required value="{{ old('name', $user->name) }}"></div>
    <div class="a-field"><label for="email">ایمیل</label><input id="email" type="email" name="email" required dir="ltr" value="{{ old('email', $user->email) }}"></div>
    <div class="a-field"><label for="mobile">موبایل</label><input id="mobile" name="mobile" dir="ltr" value="{{ old('mobile', $user->mobile) }}"></div>
    <div class="a-field"><label for="password">رمز عبور {{ $user->exists ? '(خالی = بدون تغییر)' : '' }}</label><input id="password" type="password" name="password" dir="ltr" autocomplete="new-password" @unless($user->exists) required @endunless></div>
    <label class="a-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))> فعال</label>
    <label class="a-check"><input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $user->is_admin))> دسترسی به پنل مدیریت</label>
    <fieldset class="a-fieldset"><legend>نقش‌ها</legend>
        @foreach ($roles as $r)<label class="a-check"><input type="checkbox" name="roles[]" value="{{ $r->id }}" @checked(in_array($r->id, old('roles', $user->roles->pluck('id')->all())))> {{ $r->name }}</label>@endforeach
    </fieldset>
    @if ($user->exists && $user->hasTwoFactorEnabled())<label class="a-check"><input type="checkbox" name="reset_2fa" value="1"> بازنشانی ورود دومرحله‌ای این کاربر</label>@endif
    <button class="a-btn">ذخیره</button>
</form>
@endsection
