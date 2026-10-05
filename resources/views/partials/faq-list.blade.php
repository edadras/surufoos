<div class="faq-list">
    @foreach ($faqs as $faq)
        <details class="faq" @if($loop->first) open @endif>
            <summary><span>{{ $faq->tr('question') }}</span><x-icon name="plus" class="faq__icon"/></summary>
            <div class="faq__answer prose">{!! rich_text($faq->tr('answer')) !!}</div>
        </details>
    @endforeach
</div>
