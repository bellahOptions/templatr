<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Order\CheckoutService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService) {}

    public function index()
    {
        $cart = session()->get('cart', []);
        $products = collect();
        $total = 0;

        if (! empty($cart)) {
            $products = Product::whereIn('id', array_keys($cart))->get();
            $total = 0;
            foreach ($products as $product) {
                $price = $product->sale_price ?? $product->price;
                $total += $price;
            }
        }

        return view('cart.index', compact('products', 'cart', 'total'));
    }

    public function add(Request $request, Product $product)
    {
        // Only products a buyer could actually complete a purchase for may enter
        // the cart. An unpublished or unpriced product is refused here as well as
        // at checkout, so the cart never advertises something that cannot be sold.
        if (! $product->is_published) {
            return $this->refuse($request, 'This product is not available for purchase.');
        }

        if ($this->checkoutService->priceFor($product) <= 0) {
            return $this->refuse($request, 'This product is not available for purchase.');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Already in cart', 'cart_count' => count($cart)]);
            }

            return back()->with('info', 'This item is already in your cart.');
        }

        $cart[$product->id] = [
            'title' => $product->title,
            'price' => $product->sale_price ?? $product->price,
            'thumbnail' => $product->thumbnail,
            'slug' => $product->slug,
        ];

        session()->put('cart', $cart);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Added to cart!', 'cart_count' => count($cart)]);
        }

        return back()->with('success', 'Item added to cart!');
    }

    protected function refuse(Request $request, string $message)
    {
        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => $message, 'cart_count' => count(session()->get('cart', []))], 422);
        }

        return back()->with('error', $message);
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
