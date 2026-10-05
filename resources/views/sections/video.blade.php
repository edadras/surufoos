@php
    $url = $section->setting('video_url');
    $embed = null;
    if (preg_match('~aparat\.com/v/([A-Za-z0-9]+)~', $url, $m)) { $embed = 'https://www.aparat.com/video/video/embed/videohash/'.$m[1].'/vt/frame'; }
    elseif (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/))([\w-]{11})~', $url, $m)) { $embed = 'https://www.youtube-nocookie.com/embed/'.$m[1].'?autoplay=1'; }
@endphp
<section class="section video-section">
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow, 'title' => $content?->title, 'subtitle' => $content?->subtitle, 'num' => $num])
        <div class="video-frame" data-video data-embed="{{ $embed }}">
            @if ($embed)
                <button type="button" class="video-frame__play" data-video-play aria-label="{{ $content?->title ?: 'play' }}">
                    @if ($poster)<x-picture :media="$poster" size="large" sizes="90vw" alt=""/>@endif
                    <span class="video-frame__btn"><x-icon name="play" size="28"/></span>
                </button>
            @else
                <video controls preload="none" @if($poster) poster="{{ $poster->variantUrl('large') }}" @endif src="{{ $url }}"></video>
            @endif
        </div>
    </div>
</section>
