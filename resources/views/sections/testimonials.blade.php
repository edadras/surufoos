<section class="section quotes" data-quotes>
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow, 'title' => $content?->title, 'num' => $num])
        <div class="quotes__track" data-swipe>
            @foreach ($testimonials as $q)
                <figure class="quote">
                    <blockquote>{{ $q->tr('body') }}</blockquote>
                    <figcaption>
                        @if ($q->avatar)<span class="quote__avatar"><x-picture :media="$q->avatar" size="thumbnail" sizes="48px" alt=""/></span>@endif
                        <span><strong>{{ $q->tr('author_name') }}</strong>@if($t = $q->tr('author_title'))<small>{{ $t }}</small>@endif</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
