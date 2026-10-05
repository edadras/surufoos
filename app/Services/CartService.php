<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    public const COOKIE = 'suru_cart';

    private ?Cart $cart = null;

    public function __construct(private SettingsService $settings) {}

    public function current(bool $create = false): ?Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        $token = request()->cookie(self::COOKIE);
        $cart = null;
        if (auth()->check()) {
            $cart = Cart::where('user_id', auth()->id())->latest('updated_at')->first();
        }
        if (! $cart && $token) {
            $cart = Cart::where('token', $token)->first();
            if ($cart && auth()->check() && ! $cart->user_id) {
                $cart->update(['user_id' => auth()->id()]);
            }
        }
        if (! $cart && $create) {
            $cart = Cart::create(['token' => Str::random(48), 'user_id' => auth()->id()]);
        }
        if ($cart) {
            Cookie::queue(self::COOKIE, $cart->token, 60 * 24 * 30, null, null, null, true, false, 'lax');
        }

        return $this->cart = $cart;
    }

    public function add(Product $product, int $quantity = 1, ?ProductVariant $variant = null): CartItem
    {
        $cart = $this->current(true);
        $item = $cart->items()->firstOrNew(['product_id' => $product->id, 'product_variant_id' => $variant?->id]);
        $item->quantity = min(99, ($item->exists ? $item->quantity : 0) + max(1, $quantity));
        $item->save();
        $cart->touch();

        return $item;
    }

    public function update(CartItem $item, int $quantity): void
    {
        $quantity <= 0 ? $item->delete() : $item->update(['quantity' => min(99, $quantity)]);
    }

    public function items()
    {
        $cart = $this->current();
        if (! $cart) {
            return collect();
        }

        return $cart->items()->with(['product' => fn ($q) => $q->forCard(), 'variant'])->get()
            ->filter(fn ($item) => $item->product && $item->product->isPurchasable());
    }

    public function count(): int
    {
        return (int) ($this->current()?->items()->sum('quantity') ?? 0);
    }

    public function totals(): array
    {
        $subtotal = $this->items()->sum(fn ($item) => $item->unitPrice() * $item->quantity);
        $flat = (int) $this->settings->get('shipping_flat', 0);
        $freeOver = (int) $this->settings->get('free_shipping_over', 0);
        $shipping = ($subtotal === 0 || ($freeOver > 0 && $subtotal >= $freeOver)) ? 0 : $flat;

        return ['subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $subtotal + $shipping];
    }

    public function clear(): void
    {
        $this->current()?->items()->delete();
    }
}
