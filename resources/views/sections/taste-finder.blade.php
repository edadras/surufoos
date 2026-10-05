<section class="taste section" data-taste>
    <div class="container taste__grid">
        <div class="taste__intro">
            @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.taste.title'), 'title' => $content?->title ?: __('ui.taste.title'), 'subtitle' => $content?->subtitle ?: __('ui.taste.intro'), 'num' => $num])
        </div>
        <form class="taste__form" data-taste-form>
            @foreach ($questions as $q)
                <fieldset class="taste__q">
                    <legend>{{ $q->tr('quiz_question', null, true) ?: $q->tr('name', null, true) }}</legend>
                    <div class="taste__options">
                        @foreach ($q->values as $value)
                            <label class="taste__opt" style="--c: {{ $value->color ?? 'var(--c-accent)' }}">
                                <input type="checkbox" name="values[]" value="{{ $value->id }}">
                                <span>{{ $value->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
            <div class="taste__actions">
                <button type="submit" class="btn" data-magnetic>{{ __('ui.taste.submit') }}</button>
                <button type="reset" class="btn btn--ghost">{{ __('ui.taste.reset') }}</button>
            </div>
        </form>
    </div>
    <div class="container taste__results" data-taste-results aria-live="polite"></div>
</section>
