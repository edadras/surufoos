<div class="search-overlay" id="search-overlay" data-search hidden role="dialog" aria-modal="true" aria-label="{{ __('ui.search.title') }}">
    <div class="container search-overlay__inner">
        <form action="{{ lroute('search') }}" method="get" class="search-overlay__form" role="search">
            <label for="search-input" class="visually-hidden">{{ __('ui.search.placeholder') }}</label>
            <x-icon name="search" size="28"/>
            <input id="search-input" name="q" type="search" autocomplete="off" placeholder="{{ __('ui.search.placeholder') }}" data-search-input maxlength="100">
            <button type="button" class="icon-btn" data-search-close aria-label="{{ __('ui.close') }}"><x-icon name="close"/></button>
        </form>
        <div class="search-overlay__results" data-search-results aria-live="polite"></div>
    </div>
</div>
