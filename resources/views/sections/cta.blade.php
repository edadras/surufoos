<section class="section cta-band" data-cta>
    <div class="container cta-band__inner">
        <div>
            <h2 class="display-2" data-split>{{ $content->title }}</h2>
            @if ($content->subtitle)<p class="lead">{{ $content->subtitle }}</p>@endif
            @if ($content->cta_label)
                <a href="{{ $content->cta_url ?: lroute('products.index') }}" class="btn btn--light" data-magnetic>{{ $content->cta_label }} <x-icon name="arrow" size="18" class="flip-rtl"/></a>
            @endif
        </div>
        @if ($media->isNotEmpty())
            <div class="cta-band__media" aria-hidden="true">
                @foreach ($media->take(3) as $m)
                    <span style="--i: {{ $loop->index }}"><x-picture :media="$m" size="small" sizes="200px" alt=""/></span>
                @endforeach
            </div>
        @endif
    </div>
</section>
