<section class="section rich">
    <div class="container container--narrow">
        @include('partials.section-head', ['eyebrow' => $content->eyebrow, 'title' => $content->title, 'num' => $num])
        <div class="prose">{!! rich_text($content->body) !!}</div>
    </div>
</section>
