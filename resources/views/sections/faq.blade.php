<section class="section faq-section">
    <div class="container container--narrow">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.nav.faq'), 'title' => $content?->title, 'num' => $num])
        @include('partials.faq-list', ['faqs' => $faqs])
    </div>
</section>
@push('preload')
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f->tr('question'), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags((string) $f->tr('answer'))]])->values()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush
