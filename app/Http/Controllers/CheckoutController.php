<?php

namespace App\Http\Controllers;

use App\Mail\OrderReceipt;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewPurchaseAdminNotification;
use App\Services\Download\DownloadSecurityManager;
use App\Services\Payment\PaymentManager;
use App\Services\Webhook\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected PaymentManager $paymentManager;

    protected DownloadSecurityManager $downloadSecurity;

    protected WebhookService $webhookService;

    public function __construct(
        PaymentManager $paymentManager,
        DownloadSecurityManager $downloadSecurity,
        WebhookService $webhookService
    ) {
        $this->paymentManager = $paymentManager;
        $this->downloadSecurity = $downloadSecurity;
        $this->webhookService = $webhookService;
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

        // Validate guest fields if user is not authenticated
        $guestData = [];
        if (! Auth::check()) {
            $validated = $request->validate([
                'guest_name' => 'required|string|max:255',
                'guest_email' => 'required|email|max:255',
                'guest_phone' => 'required|string|max:20',
            ]);
            $guestData = [
                'guest_name' => $validated['guest_name'],
                'guest_email' => $validated['guest_email'],
                'guest_phone' => $validated['guest_phone'],
            ];
            session()->put('guest_data', $guestData);
        }

        $paymentMethod = $request->payment_method ?? 'direct';
        $user = Auth::user();

        // If a payment gateway was selected, redirect to gateway
        if (in_array($paymentMethod, ['paystack', 'flutterwave', 'interswitch'])) {
            $reference = 'TXN-'.strtoupper(Str::random(16));

            session()->put('pending_payment', array_merge([
                'reference' => $reference,
                'amount' => $totalAmount,
                'products' => $products->pluck('id')->toArray(),
                'user_id' => $user?->id,
                'gateway' => $paymentMethod,
            ], $guestData));

            try {
                $gateway = $this->paymentManager->gateway($paymentMethod);
                $email = $user?->email ?? $guestData['guest_email'];
                $name = $user?->name ?? $guestData['guest_name'];

                $result = $gateway->initializePayment([
                    'email' => $email,
                    'name' => $name,
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
        $order = $this->completeOrder($products, $totalAmount, 'direct', $guestData);

        if (! $order) {
            return redirect()->route('checkout.index')->with('error', 'Order processing failed.');
        }

        return redirect()->route('orders.confirmation', $order)->with('success', 'Payment successful! Your items are ready for download.');
    }

    public function callback(Request $request, string $gateway)
    {
        $pending = session()->get('pending_payment');

        if (! $pending || $pending['gateway'] !== $gateway) {
            return redirect()->route('cart.index')->with('error', 'Invalid payment session.');
        }

        try {
            $paymentGateway = $this->paymentManager->gateway($gateway);
            $reference = $request->reference ?? $pending['reference'];

            $verification = $paymentGateway->verifyPayment($reference);

            if ($verification['success']) {
                $products = Product::whereIn('id', $pending['products'])->get();
                $totalAmount = $pending['amount'];

                $guestData = [];
                if (! empty($pending['guest_name'])) {
                    $guestData = [
                        'guest_name' => $pending['guest_name'],
                        'guest_email' => $pending['guest_email'],
                        'guest_phone' => $pending['guest_phone'],
                    ];
                }

                $order = $this->completeOrder($products, $totalAmount, $gateway, $guestData);

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

    protected function completeOrder($products, float $totalAmount, string $paymentMethod, array $guestData = []): ?Order
    {
        try {
            $orderData = [
                'user_id' => Auth::id(),
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'total_amount' => $totalAmount,
                'status' => 'completed',
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
            ];

            // Add guest data if provided
            if (! empty($guestData)) {
                $orderData['user_id'] = null;
                $orderData['guest_name'] = $guestData['guest_name'];
                $orderData['guest_email'] = $guestData['guest_email'];
                $orderData['guest_phone'] = $guestData['guest_phone'];
            }

            $order = Order::create($orderData);

            foreach ($products as $product) {
                $price = $product->sale_price ?? $product->price;
                $authorEarnings = $price * 0.7; // 70% to author

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'price' => $price,
                    'author_earnings' => $authorEarnings,
                ]);

                // Generate download token for guest orders (72-hour expiry)
                if (! Auth::check()) {
                    $this->downloadSecurity->generateDownloadToken($orderItem, 72);
                }

                // Credit author's balance
                $product->author->increment('balance', $authorEarnings);
            }

            session()->forget('cart');
            session()->forget('guest_data');

            // Store last order number for guest access to confirmation
            if (! Auth::check()) {
                session()->put('last_order_number', $order->order_number);
            }

            // Send order receipt email via queue
            try {
                Mail::to($order->customer_email)
                    ->queue(new OrderReceipt($order));
            } catch (\Exception $e) {
                Log::warning('Failed to queue order receipt email: '.$e->getMessage());
            }

            // Send new purchase notification to admin and the specified email
            try {
                $adminUsers = User::where('role', 'admin')->get();
                foreach ($adminUsers as $admin) {
                    $admin->notify(new NewPurchaseAdminNotification($order));
                }
                // Also notify the specified email
                $specificEmail = 'muyiwadavis65@gmail.com';
                if (filter_var($specificEmail, FILTER_VALIDATE_EMAIL)) {
                    Notification::route('mail', $specificEmail)
                        ->notify(new NewPurchaseAdminNotification($order));
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send new purchase notification: '.$e->getMessage());
            }

            // Fire webhooks for order.paid event
            try {
                $this->webhookService->fire('order.paid', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => (float) $totalAmount,
                    'currency' => 'NGN',
                    'payment_method' => $paymentMethod,
                    'customer_email' => $order->customer_email,
                    'customer_name' => $order->customer_name ?? ($order->user?->name ?? 'Guest'),
                    'items' => $products->map(function ($p) {
                        return [
                            'product_id' => $p->id,
                            'title' => $p->title,
                            'price' => (float) ($p->sale_price ?? $p->price),
                        ];
                    })->toArray(),
                    'timestamp' => now()->toIso8601String(),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to fire webhook: '.$e->getMessage());
            }

            return $order;
        } catch (\Exception $e) {
            Log::error('Order completion failed: '.$e->getMessage());

            return null;
        }
    }

    public function confirmation(Order $order)
    {
        // Allow both the authenticated user and guest (via session order_number check) to view
        if (Auth::check()) {
            if ($order->user_id !== Auth::id()) {
                abort(403);
            }
        } else {
            // For guests, ensure they can only see their own order via session
            $lastOrderNumber = session('last_order_number');
            if (! $lastOrderNumber || $lastOrderNumber !== $order->order_number) {
                abort(403);
            }
        }

        $order->load('items.product', 'items.product.author');

        return view('checkout.confirmation', compact('order'));
    }
}
