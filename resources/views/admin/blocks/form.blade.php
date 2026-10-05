@extends('admin.layout')
@section('title', $def['title'])
@section('content')
<form method="post" action="{{ $item->exists ? route('admin.blocks.update', [$type, $item->id]) : route('admin.blocks.store', $type) }}">
    @csrf @if($item->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $item->exists ? 'ویرایش '.$def['singular'] : $def['singular'].' جدید' }}</h1><div class="a-head__actions"><a href="{{ route('admin.blocks.index', $type) }}" class="a-btn a-btn--ghost">بازگشت</a><button class="a-btn">ذخیره</button></div></div>
    <div class="a-grid">
        <div class="a-grid__main"><section class="a-card">
            @include('admin.partials.translatable', ['model' => $item->exists ? $item : null, 'primary' => $def['primary'], 'fields' => $def['translatable']])
        </section></div>
        <aside class="a-grid__side"><section class="a-card">
            <label class="a-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))> فعال</label>
            <div class="a-field"><label for="sort_order">ترتیب</label><input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}"></div>
            @foreach ($def['fields'] as $f => [$label, $kind])
                @if ($kind === 'media')
                    @include('admin.partials.media-field', ['name' => $f, 'label' => $label, 'value' => old($f, $item->$f)])
                @elseif ($kind === 'group')
                    <div class="a-field"><label for="{{ $f }}">{{ $label }}</label><select id="{{ $f }}" name="{{ $f }}">@foreach (\App\Models\TimelineEntry::GROUPS as $g => $gl)<option value="{{ $g }}" @selected(old($f, $item->$f ?? request('group')) === $g)>{{ $gl }}</option>@endforeach</select></div>
                @else
                    <div class="a-field"><label for="{{ $f }}">{{ $label }}</label><input id="{{ $f }}" name="{{ $f }}" type="{{ $kind === 'date' ? 'date' : ($kind === 'number' ? 'number' : 'text') }}" value="{{ old($f, $kind === 'date' ? $item->$f?->format('Y-m-d') : $item->$f) }}"></div>
                @endif
            @endforeach
        </section></aside>
    </div>
</form>
@endsection
