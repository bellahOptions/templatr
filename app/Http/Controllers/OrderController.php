<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ((int) $order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product', 'items.product.author');

        return view('orders.show', compact('order'));
    }
}
