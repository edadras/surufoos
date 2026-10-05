<button type="button" class="icon-btn icon-btn--soft fav-btn {{ $active ? 'is-active' : '' }}" data-favorite="{{ lroute('favorites.toggle', ['product' => $product->id]) }}"
        aria-pressed="{{ $active ? 'true' : 'false' }}" aria-label="{{ $active ? __('ui.products.unfavorite') : __('ui.products.favorite') }}"
        data-label-on="{{ __('ui.products.unfavorite') }}" data-label-off="{{ __('ui.products.favorite') }}">
    <x-icon name="heart"/>
</button>
