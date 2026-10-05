<?php

namespace App\Http\Controllers\Site;

use App\Actions\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\Seo;
use Illuminate\Routing\Controllers\HasMiddleware;
use RuntimeException;

class CheckoutController extends Controller implements HasMiddleware
{
    use RequiresCommerce;

    public function show(CartService $cart, Seo $seo)
    {
        $items = $cart->items();
        if ($items->isEmpty()) {
            return redirect()->route('cart.show');
        }
        $seo->set(__('ui.checkout.title'))->routeAlternates('checkout.show');
        $seo->noindex = true;

        return view('site.checkout', [
            'items' => $items,
            'totals' => $cart->totals(),
            'methods' => settings()->json('payment_methods', ['cod']),
            'address' => auth()->user()?->addresses()->where('is_default', true)->first(),
        ]);
    }

    public function store(CheckoutRequest $request, PlaceOrder $placeOrder)
    {
        try {
            $order = $placeOrder->execute($request->validated());
        } catch (RuntimeException $e) {
            return redirect()->route('cart.show')->withErrors(['cart' => $e->getMessage()]);
        }
        session()->push('placed_orders', $order->number);

        return redirect()->route('checkout.complete', ['number' => $order->number]);
    }

    public function complete(string $number, Seo $seo)
    {
        $order = Order::with('items')->where('number', $number)->firstOrFail();
        $owns = in_array($number, session('placed_orders', []), true) || ($order->user_id && $order->user_id === auth()->id());
        abort_unless($owns, 404);
        $seo->set(__('ui.checkout.complete'));
        $seo->noindex = true;

        return view('site.order-complete', compact('order'));
    }
}
