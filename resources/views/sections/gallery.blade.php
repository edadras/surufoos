<section class="section gallery">
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow, 'title' => $content?->title, 'num' => $num])
        <div class="gallery__grid">
            @foreach ($media as $m)
                <figure class="gallery__item" data-reveal-mask>
                    <x-picture :media="$m" size="medium" sizes="(max-width: 768px) 50vw, 33vw"/>
                    @if ($cap = $m->caption())<figcaption>{{ $cap }}</figcaption>@endif
                </figure>
            @endforeach
        </div>
    </div>
</section>
