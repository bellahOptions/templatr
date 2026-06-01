<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $products = Product::whereIn('id', array_keys($cart))->get();
        $total = 0;

        foreach ($products as $product) {
            $price = $product->sale_price ?? $product->price;
            $total += $price;
        }

        return view('checkout.index', compact('products', 'total'));
    }

    public function process(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $products = Product::whereIn('id', array_keys($cart))->get();
        $totalAmount = 0;

        foreach ($products as $product) {
            $totalAmount += $product->sale_price ?? $product->price;
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'total_amount' => $totalAmount,
            'status' => 'completed',
            'payment_method' => $request->payment_method ?? 'direct',
            'payment_status' => 'paid',
        ]);

        foreach ($products as $product) {
            $price = $product->sale_price ?? $product->price;
            $authorEarnings = $price * 0.7; // 70% to author

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $price,
                'author_earnings' => $authorEarnings,
            ]);

            // Credit author's balance
            $product->author->increment('balance', $authorEarnings);
            $product->increment('download_count');
        }

        session()->forget('cart');

        return redirect()->route('orders.confirmation', $order)->with('success', 'Payment successful! Your items are ready for download.');
    }

    public function confirmation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product', 'items.product.author');

        return view('checkout.confirmation', compact('order'));
    }
}
