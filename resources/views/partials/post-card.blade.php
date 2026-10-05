<article class="acard {{ ($large ?? false) ? 'acard--large' : '' }}">
    <a href="{{ $post->url() }}" class="acard__media" tabindex="-1" aria-hidden="true">
        <x-picture :media="$post->cover" size="{{ ($large ?? false) ? 'large' : 'medium' }}" sizes="(max-width: 768px) 90vw, {{ ($large ?? false) ? '60vw' : '33vw' }}"/>
    </a>
    <div class="acard__body">
        <p class="acard__meta">
            @if ($post->category)<span>{{ $post->category->tr('title') }}</span>@endif
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ jdate($post->published_at) }}</time>
        </p>
        <h3><a href="{{ $post->url() }}">{{ $post->tr('title') }}</a></h3>
        @if (($large ?? false) && ($ex = $post->tr('short_description')))<p>{{ \Illuminate\Support\Str::limit(strip_tags($ex), 180) }}</p>@endif
    </div>
</article>
