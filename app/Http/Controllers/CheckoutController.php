<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected PaymentManager $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

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

        $availableGateways = $this->paymentManager->getAvailableGateways();

        return view('checkout.index', compact('products', 'total', 'availableGateways'));
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

        $paymentMethod = $request->payment_method ?? 'direct';

        // If a payment gateway was selected, redirect to gateway
        if (in_array($paymentMethod, ['paystack', 'flutterwave', 'interswitch'])) {
            $reference = 'TXN-' . strtoupper(Str::random(16));

            session()->put('pending_payment', [
                'reference' => $reference,
                'amount' => $totalAmount,
                'products' => $products->pluck('id')->toArray(),
                'user_id' => Auth::id(),
                'gateway' => $paymentMethod,
            ]);

            try {
                $gateway = $this->paymentManager->gateway($paymentMethod);
                $result = $gateway->initializePayment([
                    'email' => Auth::user()->email,
                    'name' => Auth::user()->name,
                    'amount' => $totalAmount,
                    'reference' => $reference,
                    'callback_url' => route('checkout.callback', ['gateway' => $paymentMethod]),
                    'order_id' => null,
                ]);

                if ($result['success']) {
                    return redirect($result['authorization_url']);
                }

                return redirect()->route('checkout.index')
                    ->with('error', 'Payment initialization failed. Please try again.');
            } catch (\Exception $e) {
                return redirect()->route('checkout.index')
                    ->with('error', 'Payment gateway error. Please try again or use a different payment method.');
            }
        }

        // Direct payment (no gateway configured)
        $order = $this->completeOrder($products, $totalAmount, 'direct');

        if (!$order) {
            return redirect()->route('checkout.index')->with('error', 'Order processing failed.');
        }

        return redirect()->route('orders.confirmation', $order)->with('success', 'Payment successful! Your items are ready for download.');
    }

    public function callback(Request $request, string $gateway)
    {
        $pending = session()->get('pending_payment');

        if (!$pending || $pending['gateway'] !== $gateway) {
            return redirect()->route('cart.index')->with('error', 'Invalid payment session.');
        }

        try {
            $paymentGateway = $this->paymentManager->gateway($gateway);
            $reference = $request->reference ?? $pending['reference'];

            $verification = $paymentGateway->verifyPayment($reference);

            if ($verification['success']) {
                $products = Product::whereIn('id', $pending['products'])->get();
                $totalAmount = $pending['amount'];

                $order = $this->completeOrder($products, $totalAmount, $gateway);

                if ($order) {
                    session()->forget('pending_payment');
                    return redirect()->route('orders.confirmation', $order)
                        ->with('success', 'Payment successful! Your items are ready for download.');
                }
            }

            session()->forget('pending_payment');
            return redirect()->route('checkout.index')
                ->with('error', 'Payment verification failed. Please contact support.');
        } catch (\Exception $e) {
            session()->forget('pending_payment');
            return redirect()->route('checkout.index')
                ->with('error', 'Payment verification error. Please contact support.');
        }
    }

    protected function completeOrder($products, float $totalAmount, string $paymentMethod): ?Order
    {
        try {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount' => $totalAmount,
                'status' => 'completed',
                'payment_method' => $paymentMethod,
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

            return $order;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Order completion failed: ' . $e->getMessage());
            return null;
        }
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
