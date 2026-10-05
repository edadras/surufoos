<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('number', 'like', '%'.$request->query('q').'%')
                ->orWhere('customer_name', 'like', '%'.$request->query('q').'%')->orWhere('customer_mobile', 'like', '%'.$request->query('q').'%')))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', ['order' => $order->load(['items', 'payments', 'user'])]);
    }

    public function update(Request $request, Order $order)
    {
        $order->update($request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'payment_status' => ['required', Rule::enum(PaymentStatus::class)],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]));

        return back()->with('success', 'سفارش به‌روزرسانی شد.');
    }
}
