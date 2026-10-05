@extends('admin.layout')
@section('title', 'ترجمه: '.$label)
@section('content')
<form method="post" action="{{ route('admin.translations.update', [$type, $model->id]) }}" data-dirty-guard>
    @csrf @method('PUT')
    <div class="a-head"><h1>{{ \Illuminate\Support\Str::limit($model->tr($primary, 'fa', true) ?? $label, 70) }}</h1>
        <div class="a-head__actions"><a href="{{ route('admin.translations.index', ['type' => $type]) }}" class="a-btn a-btn--ghost">بازگشت</a><button class="a-btn">ذخیره ترجمه‌ها</button></div></div>
    <div class="a-tr-columns">
        @foreach (config('suru.locales') as $code => $meta)
            @php($t = $model->translations->firstWhere('locale', $code))
            <section class="a-card" dir="{{ $meta['dir'] }}" lang="{{ $code }}" data-tr-column="{{ $code }}">
                <h2>{{ $meta['native'] }} <span class="a-tr__state {{ $t ? 'is-done' : 'is-missing' }}">{{ $t ? '✓' : '!' }}</span>
                    @if ($code !== 'fa')<button type="button" class="a-btn a-btn--ghost a-btn--sm" data-tr-copy-column="{{ $code }}">کپی از فارسی</button>@endif</h2>
                @foreach ($fields as $name => $kind)
                    <div class="a-field"><label>{{ $name }}</label>
                        @if ($kind === 'text')<input name="translations[{{ $code }}][{{ $name }}]" value="{{ old("translations.$code.$name", $t?->$name) }}" data-tr-field="{{ $name }}">
                        @else<textarea name="translations[{{ $code }}][{{ $name }}]" rows="{{ $kind === 'rich' ? 10 : 3 }}" data-tr-field="{{ $name }}">{{ old("translations.$code.$name", $t?->$name) }}</textarea>@endif
                    </div>
                @endforeach
            </section>
        @endforeach
    </div>
</form>
@endsection
