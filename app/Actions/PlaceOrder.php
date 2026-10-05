<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Jobs\NotifyNewOrder;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Turns the current cart into an order. Prices are re-read from the database
 * inside the transaction so a stale cart can never set its own price.
 */
class PlaceOrder
{
    public function __construct(private CartService $cart) {}

    public function execute(array $customer): Order
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) {
            throw new RuntimeException(__('ui.cart.empty'));
        }
        $totals = $this->cart->totals();

        $order = DB::transaction(function () use ($items, $totals, $customer) {
            $order = Order::create($customer + [
                'number' => $this->number(),
                'user_id' => auth()->id(),
                'status' => OrderStatus::Pending,
                'payment_status' => $customer['payment_method'] === 'cod' ? PaymentStatus::Unpaid : PaymentStatus::Pending,
                'locale' => app()->getLocale(),
                'subtotal' => $totals['subtotal'],
                'shipping_total' => $totals['shipping'],
                'grand_total' => $totals['total'],
            ]);

            foreach ($items as $item) {
                $price = $item->unitPrice();
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_title' => (string) $item->product->tr('title', 'fa', true),
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'unit_price' => $price,
                    'quantity' => $item->quantity,
                    'line_total' => $price * $item->quantity,
                ]);
                if ($item->product->stock_qty !== null) {
                    $item->product->decrement('stock_qty', min($item->quantity, max(0, $item->product->stock_qty)));
                }
            }

            $order->payments()->create([
                'gateway' => $customer['payment_method'],
                'amount' => $totals['total'],
                'status' => 'pending',
            ]);

            return $order;
        });

        $this->cart->clear();
        NotifyNewOrder::dispatch($order->id);

        return $order;
    }

    private function number(): string
    {
        do {
            $number = 'SR-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (Order::where('number', $number)->exists());

        return $number;
    }
}
