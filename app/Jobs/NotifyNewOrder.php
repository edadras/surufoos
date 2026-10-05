<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotifyNewOrder implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = Order::with('items')->find($this->orderId);
        $to = settings('order_notify_email');
        if (! $order || ! $to) {
            return;
        }

        $lines = $order->items->map(fn ($i) => "- {$i->product_title} × {$i->quantity} = ".number_format($i->line_total))->implode("\n");
        Mail::raw(
            "سفارش جدید {$order->number}\n\n{$order->customer_name} — {$order->customer_mobile}\n{$order->province}، {$order->city}\n{$order->address}\n\n{$lines}\n\nجمع کل: ".number_format($order->grand_total)."\n\n".route('admin.orders.show', $order),
            fn ($mail) => $mail->to($to)->subject("سفارش جدید {$order->number}")
        );
    }
}
