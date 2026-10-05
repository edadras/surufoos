<span class="a-trs">
    @foreach ($model->translationStatus() as $code => $ok)
        <span class="{{ $ok ? 'is-done' : 'is-missing' }}" title="{{ $ok ? 'ترجمه شده' : 'ترجمه ناقص' }}">{{ strtoupper($code) }} {{ $ok ? '✓' : '!' }}</span>
    @endforeach
</span>
