<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(protected OrderFulfillmentService $fulfillmentService) {}

    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->latest()->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('user', 'items.product', 'items.product.author', 'items.earning');

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update an order's status.
     *
     * Marking an order paid is a privileged, explicit act: it is the only
     * non-provider route to `paid`, and it is what releases downloads and
     * creator earnings. It goes through the same guarded state machine as a
     * webhook, and fulfilment is queued rather than run inline.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,awaiting_approval,processing,completed,cancelled,failed',
            'payment_status' => 'required|in:unpaid,pending,paid,refunded',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $targetPayment = PaymentStatus::from($validated['payment_status']);
        $targetStatus = OrderStatus::from($validated['status']);
        $currentPayment = PaymentStatus::tryFrom((string) $order->payment_status) ?? PaymentStatus::Unpaid;

        if ($targetPayment !== $currentPayment && ! $currentPayment->canTransitionTo($targetPayment)) {
            return back()->with('error', "Cannot move payment status from {$currentPayment->value} to {$targetPayment->value}.");
        }

        $currentStatus = OrderStatus::tryFrom((string) $order->status) ?? OrderStatus::Pending;

        if ($targetStatus !== $currentStatus && ! $currentStatus->canTransitionTo($targetStatus)) {
            return back()->with('error', "Cannot move order status from {$currentStatus->value} to {$targetStatus->value}.");
        }

        // Marking paid from the admin panel must be a deliberate, recorded act.
        if ($targetPayment === PaymentStatus::Paid && $currentPayment !== PaymentStatus::Paid) {
            if (! $request->user()?->isAdmin()) {
                abort(403);
            }

            Log::warning('Order marked paid manually by admin', [
                'order_id' => $order->id,
                'admin_id' => $request->user()->id,
                'previous_payment_status' => $currentPayment->value,
            ]);
        }

        DB::transaction(function () use ($order, $validated, $targetPayment, $targetStatus): void {
            /** @var Order $locked */
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            $locked->forceFill([
                'status' => $targetStatus->value,
                'payment_status' => $targetPayment->value,
                'admin_note' => $validated['admin_note'] ?? $locked->admin_note,
                'paid_at' => $targetPayment === PaymentStatus::Paid
                    ? ($locked->paid_at ?? now())
                    : $locked->paid_at,
            ])->save();
        });

        if ($targetPayment === PaymentStatus::Paid) {
            $order->refresh();
            $this->fulfillmentService->dispatch($order);
        }

        return back()->with('success', 'Order status updated successfully.');
    }
}
