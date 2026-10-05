@extends('admin.layout')
@section('title', $role->exists ? 'ویرایش نقش' : 'نقش جدید')
@section('content')
<form method="post" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="a-card a-narrow">
    @csrf @if($role->exists) @method('PUT') @endif
    <h1>{{ $role->exists ? $role->name : 'نقش جدید' }}</h1>
    <div class="a-row">
        <div class="a-field"><label for="name">نام</label><input id="name" name="name" required value="{{ old('name', $role->name) }}"></div>
        <div class="a-field"><label for="key">کلید</label><input id="key" name="key" required dir="ltr" value="{{ old('key', $role->key) }}" pattern="[a-z_]+"></div>
    </div>
    @if ($role->key === 'super_admin')
        <p class="a-muted">مدیر کل به همه بخش‌ها دسترسی دارد.</p>
    @else
        @foreach ($permissions as $group => $perms)
            <fieldset class="a-fieldset"><legend>{{ $group }}</legend>
                @foreach ($perms as $p)<label class="a-check"><input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked(in_array($p->id, old('permissions', $role->permissions->pluck('id')->all())))> {{ $p->name }}</label>@endforeach
            </fieldset>
        @endforeach
    @endif
    <button class="a-btn">ذخیره</button>
</form>
@endsection
