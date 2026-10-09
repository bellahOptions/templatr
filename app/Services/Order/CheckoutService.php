<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Download\DownloadSecurityManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Server-side authority for what a cart contains and what an order costs.
 *
 * Prices, availability and totals are always re-read from the database at the
 * moment an order is created; nothing submitted by the browser is trusted.
 */
class CheckoutService
{
    /**
     * Percentage of each item that is credited to the author.
     */
    public const AUTHOR_SHARE = 0.7;

    public function __construct(
        protected DownloadSecurityManager $downloadSecurity,
    ) {}

    /**
     * Resolve a cart (product id => anything) into purchasable products with a
     * server-computed total.
     *
     * @param  array<int|string, mixed>  $cart
     * @return array{products: Collection<int, Product>, total: float}
     *
     * @throws CheckoutException when the cart is empty or contains an item that
     *                           is missing, unpublished or otherwise unavailable
     */
    public function resolveCart(array $cart, bool $strict = true): array
    {
        $ids = array_values(array_filter(array_map('intval', array_keys($cart))));

        if ($ids === []) {
            throw new CheckoutException('Your cart is empty.');
        }

        $products = Product::with('author')->whereIn('id', $ids)->get()->keyBy('id');

        $unavailable = [];

        foreach ($ids as $id) {
            $product = $products->get($id);

            if (! $product || ! $this->isPurchasable($product)) {
                $unavailable[] = $product?->title ?? "item #{$id}";
            }
        }

        if ($unavailable !== [] && $strict) {
            throw new CheckoutException(
                'Some items in your cart are no longer available: '.implode(', ', $unavailable).'. Please remove them and try again.'
            );
        }

        $ordered = collect($ids)
            ->map(fn (int $id) => $products->get($id))
            ->filter(fn (?Product $product) => $product !== null && (! $strict || $this->isPurchasable($product)))
            ->values();

        if ($ordered->isEmpty()) {
            throw new CheckoutException('Your cart is empty.');
        }

        return [
            'products' => $ordered,
            'total' => round((float) $ordered->sum(fn (Product $product) => $this->priceFor($product)), 2),
        ];
    }

    /**
     * A product can only be bought when it is published and still has a price.
     */
    public function isPurchasable(Product $product): bool
    {
        return (bool) $product->is_published && $this->priceFor($product) > 0;
    }

    /**
     * Authoritative unit price for a product, from the database row only.
     */
    public function priceFor(Product $product): float
    {
        $price = $product->sale_price !== null ? $product->sale_price : $product->price;

        return round((float) $price, 2);
    }

    /**
     * Create the pending order (and its items) for a gateway checkout.
     *
     * The order starts `unpaid`, so nothing is downloadable or credited until a
     * provider transaction has been verified.
     *
     * @param  Collection<int, Product>  $products
     * @param  array<string, mixed>  $guestData
     */
    public function createPendingOrder(
        Collection $products,
        float $total,
        string $gateway,
        ?int $userId,
        array $guestData = [],
    ): Order {
        return DB::transaction(function () use ($products, $total, $gateway, $userId, $guestData) {
            $order = Order::create([
                'user_id' => $guestData === [] ? $userId : null,
                'guest_name' => $guestData['guest_name'] ?? null,
                'guest_email' => $guestData['guest_email'] ?? null,
                'guest_phone' => $guestData['guest_phone'] ?? null,
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'total_amount' => $total,
                'currency' => 'NGN',
                'status' => OrderStatus::Pending->value,
                'payment_method' => $gateway,
                'gateway' => $gateway,
                'payment_reference' => $this->uniqueReference(),
                'payment_status' => PaymentStatus::Unpaid->value,
            ]);

            $this->createItems($order, $products);

            return $order;
        });
    }

    /**
     * Create an order for an offline/manual payment.
     *
     * It remains `awaiting_approval` + `unpaid` until an administrator confirms
     * the money arrived and marks it paid — never automatically completed.
     *
     * @param  Collection<int, Product>  $products
     * @param  array<string, mixed>  $guestData
     */
    public function createManualOrder(
        Collection $products,
        float $total,
        ?int $userId,
        array $guestData = [],
        ?string $note = null,
    ): Order {
        return DB::transaction(function () use ($products, $total, $userId, $guestData, $note) {
            $order = Order::create([
                'user_id' => $guestData === [] ? $userId : null,
                'guest_name' => $guestData['guest_name'] ?? null,
                'guest_email' => $guestData['guest_email'] ?? null,
                'guest_phone' => $guestData['guest_phone'] ?? null,
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'total_amount' => $total,
                'currency' => 'NGN',
                'status' => OrderStatus::AwaitingApproval->value,
                'payment_method' => 'manual',
                'gateway' => null,
                'payment_reference' => $this->uniqueReference(),
                'payment_status' => PaymentStatus::Unpaid->value,
                'admin_note' => $note,
            ]);

            $this->createItems($order, $products);

            return $order;
        });
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    protected function createItems(Order $order, Collection $products): void
    {
        foreach ($products as $product) {
            $price = $this->priceFor($product);
            $authorEarnings = round($price * self::AUTHOR_SHARE, 2);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                // Snapshot the creator now: re-assigning the product later must
                // not redirect this sale's earnings.
                'author_id' => $product->user_id,
                'price' => $price,
                'author_earnings' => $authorEarnings,
                'platform_commission' => round($price - $authorEarnings, 2),
            ]);
        }
    }

    /**
     * A reference that is unique across the orders table.
     *
     * Retried a bounded number of times so a (theoretical) collision can never
     * create two pending orders sharing one provider reference.
     */
    protected function uniqueReference(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $reference = 'TXN-'.strtoupper(Str::random(16));

            if (! Order::where('payment_reference', $reference)->exists()) {
                return $reference;
            }
        }

        throw new CheckoutException('Unable to allocate a unique payment reference. Please try again.');
    }
}
