<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\Seo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class CartController extends Controller implements HasMiddleware
{
    use RequiresCommerce;

    public function __construct(private CartService $cart) {}

    public function show(Seo $seo)
    {
        $seo->set(__('ui.cart.title'))->routeAlternates('cart.show');
        $seo->noindex = true;

        return view('site.cart', ['items' => $this->cart->items(), 'totals' => $this->cart->totals()]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'variant_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);
        $product = Product::published()->findOrFail($data['product_id']);
        $variant = isset($data['variant_id']) ? ProductVariant::where('product_id', $product->id)->findOrFail($data['variant_id']) : null;
        abort_unless($product->isPurchasable(), 422, __('ui.cart.unavailable'));

        $this->cart->add($product, $data['quantity'] ?? 1, $variant);

        return $request->wantsJson()
            ? response()->json(['count' => $this->cart->count(), 'message' => __('ui.cart.added')])
            : redirect()->route('cart.show');
    }

    public function update(Request $request, int $item)
    {
        $line = $this->ownedItem($item);
        $this->cart->update($line, (int) $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']])['quantity']);

        return $this->respond($request);
    }

    public function remove(Request $request, int $item)
    {
        $this->ownedItem($item)->delete();

        return $this->respond($request);
    }

    private function ownedItem(int $id): CartItem
    {
        $cart = $this->cart->current();
        abort_unless($cart, 404);

        return $cart->items()->findOrFail($id);
    }

    private function respond(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'count' => $this->cart->count(),
                'html' => view('site._cart-body', ['items' => $this->cart->items(), 'totals' => $this->cart->totals()])->render(),
            ]);
        }

        return redirect()->route('cart.show');
    }
}
