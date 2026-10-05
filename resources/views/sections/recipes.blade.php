<section class="section recipes-section">
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.recipes.title'), 'title' => $content?->title, 'subtitle' => $content?->subtitle, 'num' => $num, 'link' => lroute('recipes.index')])
        <div class="rgrid">
            @foreach ($recipes as $recipe)
                @include('partials.recipe-card', ['recipe' => $recipe])
            @endforeach
        </div>
    </div>
</section>
