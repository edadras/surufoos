@if ($items->isEmpty())
    <div class="empty-block"><p>{{ __('ui.cart.empty') }}</p><a class="btn" href="{{ lroute('products.index') }}">{{ __('ui.hero.cta_products') }}</a></div>
@else
    <div class="cart">
        <ul class="cart__lines">
            @foreach ($items as $item)
                <li class="cart-line" style="--tint: {{ $item->product->themeColor() }}">
                    <a href="{{ $item->product->url() }}" class="cart-line__img"><x-picture :media="$item->product->displayImage()" size="thumbnail" sizes="96px"/></a>
                    <div class="cart-line__info">
                        <a href="{{ $item->product->url() }}"><strong>{{ $item->product->tr('title') }}</strong></a>
                        @if ($w = $item->product->weightLabel())<small>{{ $w }}</small>@endif
                        <span>{{ money($item->unitPrice()) }}</span>
                    </div>
                    <form action="{{ lroute('cart.update', ['item' => $item->id]) }}" method="post" class="qty" data-cart-update>
                        @csrf @method('PATCH')
                        <button type="button" data-qty-step="-1" aria-label="-"><x-icon name="minus" size="16"/></button>
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="99" inputmode="numeric" aria-label="{{ __('ui.cart.quantity') }}">
                        <button type="button" data-qty-step="1" aria-label="+"><x-icon name="plus" size="16"/></button>
                    </form>
                    <strong class="cart-line__total">{{ money($item->unitPrice() * $item->quantity) }}</strong>
                    <form action="{{ lroute('cart.remove', ['item' => $item->id]) }}" method="post" data-cart-update>
                        @csrf @method('DELETE')
                        <button class="icon-btn" type="submit" aria-label="{{ __('ui.cart.remove') }}"><x-icon name="close" size="18"/></button>
                    </form>
                </li>
            @endforeach
        </ul>
        <aside class="cart__summary">
            <dl>
                <div><dt>{{ __('ui.cart.subtotal') }}</dt><dd>{{ money($totals['subtotal']) }}</dd></div>
                <div><dt>{{ __('ui.cart.shipping') }}</dt><dd>{{ $totals['shipping'] ? money($totals['shipping']) : __('ui.cart.free') }}</dd></div>
                <div class="cart__total"><dt>{{ __('ui.cart.total') }}</dt><dd>{{ money($totals['total']) }}</dd></div>
            </dl>
            <a href="{{ lroute('checkout.show') }}" class="btn btn--wide">{{ __('ui.cart.checkout') }} <x-icon name="arrow" size="18" class="flip-rtl"/></a>
        </aside>
    </div>
@endif
