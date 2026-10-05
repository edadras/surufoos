@if (settings()->showPrices() && $product->effectivePrice() !== null)
    <p class="price">
        @if ($product->isOnSale())
            <del aria-label="old price">{{ money($product->price) }}</del>
        @endif
        <span class="price__now">{{ money($product->effectivePrice()) }}</span>
    </p>
@endif
