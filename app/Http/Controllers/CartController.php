<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $products = collect();

        if (!empty($cart)) {
            $products = Product::whereIn('id', array_keys($cart))->get();
            $total = 0;
            foreach ($products as $product) {
                $price = $product->sale_price ?? $product->price;
                $total += $price;
            }
        } else {
            $total = 0;
        }

        return view('cart.index', compact('products', 'cart', 'total'));
    }

    public function add(Product $product)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            return back()->with('info', 'This item is already in your cart.');
        }

        $cart[$product->id] = [
            'title' => $product->title,
            'price' => $product->sale_price ?? $product->price,
            'thumbnail' => $product->thumbnail,
            'slug' => $product->slug,
        ];

        session()->put('cart', $cart);

        return back()->with('success', 'Item added to cart!');
    }

    public function remove(Product $product)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }
}
