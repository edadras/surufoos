<section class="section certs">
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.about.certificates'), 'title' => $content?->title, 'num' => $num])
        <ul class="certs__list">
            @foreach ($certificates as $cert)
                <li class="cert">
                    @if ($cert->media)<span class="cert__img"><x-picture :media="$cert->media" size="small" sizes="160px"/></span>@endif
                    <strong>{{ $cert->tr('title') }}</strong>
                    @if ($cert->issuer)<small>{{ $cert->issuer }}</small>@endif
                    @if ($d = $cert->tr('description'))<p>{{ $d }}</p>@endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
