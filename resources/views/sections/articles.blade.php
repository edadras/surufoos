<section class="section articles-section">
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.magazine.title'), 'title' => $content?->title, 'num' => $num, 'link' => lroute('magazine.index')])
        <div class="agrid">
            @foreach ($posts as $post)
                @include('partials.post-card', ['post' => $post, 'large' => $loop->first])
            @endforeach
        </div>
    </div>
</section>
