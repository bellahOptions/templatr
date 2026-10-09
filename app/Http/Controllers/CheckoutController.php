<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Mail\OrderReceipt;
use App\Models\Order;
use App\Models\Product;
use App\Services\Download\DownloadSecurityManager;
use App\Services\Order\CheckoutException;
use App\Services\Order\CheckoutService;
use App\Services\Order\OrderFulfillmentService;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\PaymentVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected DownloadSecurityManager $downloadSecurity,
        protected CheckoutService $checkoutService,
        protected PaymentVerificationService $paymentVerification,
        protected OrderFulfillmentService $fulfillmentService,
    ) {}

    public function index()
    {
        $cart = session()->get('cart', []);
        $availableGateways = $this->paymentManager->getAvailableGateways();

        try {
            $resolved = $this->checkoutService->resolveCart($cart, strict: false);
        } catch (CheckoutException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $products = $resolved['products'];
        $total = $resolved['total'];

        return view('checkout.index', compact('products', 'total', 'availableGateways'));
    }

    public function process(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // ── Payment method: an explicit allow-list, never a default ──
        // Omitting or forging `payment_method` must not produce an order that is
        // paid or completed. Unknown values are rejected outright.
        $paymentMethod = (string) $request->input('payment_method', '');
        $availableGateways = array_keys($this->paymentManager->getAvailableGateways());

        $isOnlineGateway = in_array($paymentMethod, $availableGateways, true) && $this->paymentManager->isConfigured($paymentMethod);
        $isManual = $paymentMethod === 'manual';

        if (! $isOnlineGateway && ! $isManual) {
            Log::warning('Checkout rejected: unsupported payment method', [
                'payment_method' => $paymentMethod,
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Please choose a valid payment method to continue.');
        }

        // ── Guest details ──
        $guestData = [];
        if (! Auth::check()) {
            $validated = $request->validate([
                'guest_name' => 'required|string|max:255',
                'guest_email' => 'required|email|max:255',
                'guest_phone' => 'required|string|max:20',
                'terms_accepted' => ['required', 'accepted'],
            ], [
                'terms_accepted.required' => 'You must accept the Terms of Service to complete your purchase.',
                'terms_accepted.accepted' => 'You must accept the Terms of Service to complete your purchase.',
            ]);
            $guestData = [
                'guest_name' => $validated['guest_name'],
                'guest_email' => $validated['guest_email'],
                'guest_phone' => $validated['guest_phone'],
            ];
            session()->put('guest_data', $guestData);
        }

        // ── Server-side cart resolution and pricing ──
        try {
            $resolved = $this->checkoutService->resolveCart($cart, strict: true);
        } catch (CheckoutException $e) {
            return redirect()->route('checkout.index')->with('error', $e->getMessage());
        }

        $products = $resolved['products'];
        $totalAmount = $resolved['total'];
        $user = Auth::user();

        if ($isManual) {
            return $this->startManualOrder($products, $totalAmount, $user?->id, $guestData);
        }

        return $this->startGatewayOrder($request, $paymentMethod, $products, $totalAmount, $user?->id, $guestData);
    }

    /**
     * Offline / bank-transfer checkout.
     *
     * The order is created as `awaiting_approval` + `unpaid` and stays that way
     * until an administrator confirms the funds arrived. No file is downloadable
     * and no author is credited in the meantime.
     */
    protected function startManualOrder($products, float $totalAmount, ?int $userId, array $guestData)
    {
        $order = $this->checkoutService->createManualOrder($products, $totalAmount, $userId, $guestData);

        foreach ($order->items as $item) {
            if (! $userId) {
                $this->rememberDownloadToken($item);
            }
        }

        session()->forget('cart');

        $this->notifyAwaitingApproval($order);

        return redirect()->route('orders.confirmation', $order)
            ->with('info', 'Your order has been received. Payment must be approved by our team before your files are released.');
    }

    /**
     * Online gateway checkout: create the pending order, then hand the buyer to
     * the provider. Nothing is marked paid here.
     */
    protected function startGatewayOrder(Request $request, string $paymentMethod, $products, float $totalAmount, ?int $userId, array $guestData)
    {
        $order = $this->checkoutService->createPendingOrder($products, $totalAmount, $paymentMethod, $userId, $guestData);

        session()->put('pending_payment', [
            'reference' => $order->payment_reference,
            'amount' => $totalAmount,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'gateway' => $paymentMethod,
            'user_id' => $userId,
        ]);

        try {
            $gateway = $this->paymentManager->gateway($paymentMethod);
            $email = Auth::user()?->email ?? $guestData['guest_email'];
            $name = Auth::user()?->name ?? $guestData['guest_name'];
            $phone = $guestData['guest_phone'] ?? null;

            $result = $gateway->initializePayment([
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
                'amount' => $totalAmount,
                'currency' => $order->currencyCode(),
                'reference' => $order->payment_reference,
                'callback_url' => route('checkout.callback', ['gateway' => $paymentMethod]),
                'order_id' => $order->id,
            ]);

            if (($result['success'] ?? false) && ! empty($result['authorization_url'])) {
                // Record the provider's transaction id so verification can address
                // the transaction unambiguously (required by Flutterwave).
                if (! empty($result['transaction_id'])) {
                    $order->forceFill(['gateway_transaction_id' => (string) $result['transaction_id']])->save();
                }

                return redirect()->away($result['authorization_url']);
            }

            // Gateway rejected — cancel the pending order instead of leaving a
            // payable-looking row behind.
            $this->cancelPendingOrder($order, $result['message'] ?? 'Payment initialization failed.');

            session()->forget('pending_payment');

            return redirect()->route('checkout.index')
                ->with('error', 'Payment initialization failed. Please try again.');
        } catch (\Throwable $e) {
            Log::error('Payment gateway error during checkout: '.$e->getMessage(), ['order_id' => $order->id]);

            $this->cancelPendingOrder($order, 'Gateway error: '.$e->getMessage());

            session()->forget('pending_payment');

            return redirect()->route('checkout.index')
                ->with('error', 'Payment gateway error. Please try again or use a different payment method.');
        }
    }

    /**
     * The browser return leg of an online payment.
     *
     * The reference is never taken from the request: it comes from the pending
     * order recorded in the buyer's own session. The provider is then asked
     * directly whether the transaction succeeded, and the answer is validated
     * against the order before anything is marked paid.
     */
    public function callback(Request $request, string $gateway)
    {
        $pending = session()->get('pending_payment');

        if (! is_array($pending) || ($pending['gateway'] ?? null) !== $gateway) {
            return redirect()->route('cart.index')->with('error', 'Invalid payment session.');
        }

        // A success *redirect* proves nothing; refuse anything the provider did
        // not confirm. Paystack sends `trxref`/`reference`, Flutterwave sends
        // `tx_ref`/`transaction_id` — all are informational only.
        $order = Order::find($pending['order_id'] ?? 0);

        if (! $order) {
            session()->forget('pending_payment');

            return redirect()->route('cart.index')->with('error', 'We could not find your order. Please contact support.');
        }

        // Record the provider's own transaction id when the redirect carries one
        // (Flutterwave needs it to verify) — it is still never trusted as proof.
        if (! $order->gateway_transaction_id) {
            $providerTransactionId = $request->input('transaction_id');

            if ($providerTransactionId !== null && ctype_digit((string) $providerTransactionId)) {
                $order->forceFill(['gateway_transaction_id' => (string) $providerTransactionId])->save();
            }
        }

        try {
            if ($order->isPaid()) {
                session()->forget(['pending_payment', 'cart', 'guest_data']);
                session()->put('last_order_number', $order->order_number);

                return redirect()->route('orders.confirmation', $order)
                    ->with('success', 'Payment successful! Your items are ready for download.');
            }

            $result = $this->paymentVerification->settleFromProvider($order, 'callback');
        } catch (\Throwable $e) {
            Log::error('Payment callback verification error: '.$e->getMessage(), ['order_id' => $order->id]);
            $result = null;
        }

        if ($result?->verified) {
            foreach ($order->items as $item) {
                if (! $order->user_id) {
                    $this->rememberDownloadToken($item);
                }
            }

            session()->forget(['pending_payment', 'cart', 'guest_data']);
            session()->put('last_order_number', $order->order_number);

            return redirect()->route('orders.confirmation', $order)
                ->with('success', 'Payment successful! Your items are ready for download.');
        }

        session()->forget('pending_payment');

        Log::warning('Payment callback could not be verified', [
            'order_id' => $order->id,
            'reason' => $result?->reason ?? 'verification_error',
        ]);

        return redirect()->route('checkout.index')
            ->with('error', 'We could not verify your payment yet. If you were charged, your order will be confirmed automatically once the payment provider notifies us.');
    }

    /**
     * Issue a guest download token and keep the plain value in the buyer's own
     * session so the confirmation page can build their one-time download links.
     * Only the SHA-256 hash is persisted, never the token itself.
     */
    protected function rememberDownloadToken(\App\Models\OrderItem $orderItem, int $expiresInHours = 72): string
    {
        $token = $this->downloadSecurity->generateDownloadToken($orderItem, $expiresInHours);

        session()->put('download_token_'.$orderItem->id, $token);

        return $token;
    }

    /**
     * Mark a pending order cancelled after a failed initialization.
     *
     * The row is kept (auditable, and the buyer's confirmation link keeps
     * working) but it can never be settled afterwards.
     */
    protected function cancelPendingOrder(Order $order, string $reason): void
    {
        if ($order->isPaid()) {
            return;
        }

        $order->forceFill([
            'status' => OrderStatus::Failed->value,
            'admin_note' => Str::limit($reason, 500),
        ])->save();
    }

    protected function notifyAwaitingApproval(Order $order): void
    {
        try {
            if ($order->customer_email) {
                Mail::to($order->customer_email)->queue(new OrderReceipt($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to queue order receipt email: '.$e->getMessage());
        }

        try {
            $specificEmail = config('services.admin.notification_email');
            if ($specificEmail && filter_var($specificEmail, FILTER_VALIDATE_EMAIL)) {
                Notification::route('mail', $specificEmail)
                    ->notify(new \App\Notifications\NewPurchaseAdminNotification($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send new purchase notification: '.$e->getMessage());
        }
    }

    public function confirmation(Order $order)
    {
        // Primary check: session token set at order creation (works for auth + guest)
        $sessionToken = session('last_order_number');
        if ($sessionToken === $order->order_number) {
            $order->load('items.product', 'items.product.author');

            return view('checkout.confirmation', compact('order'));
        }

        // Fallback for authenticated users viewing their own past orders
        if (Auth::check() && (int) $order->user_id === Auth::id()) {
            $order->load('items.product', 'items.product.author');

            return view('checkout.confirmation', compact('order'));
        }

        abort(403);
    }
}
