<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function paystackSecret(): string
{
    return (string) config('services.paystack.secret');
}

function browserToken(): string
{
    return Str::random(40);
}

function checkoutPost(array $data, string $token): \Illuminate\Testing\TestResponse
{
    return test()->withSession(['_token' => $token])
        ->post(route('checkout.process'), array_merge(['_token' => $token], $data));
}

function buyerWithCart(Product $product, array $extraSession = []): User
{
    $user = User::factory()->create(['email_verified_at' => now()]);

    test()->actingAs($user)->withSession(array_merge([
        'cart' => [$product->id => ['title' => $product->title, 'price' => $product->price]],
    ], $extraSession));

    return $user;
}

function sellableProduct(array $attributes = []): Product
{
    return Product::factory()->create(array_merge([
        'price' => 5000,
        'sale_price' => null,
        'is_published' => true,
    ], $attributes));
}

/**
 * A pending gateway order belonging to the given buyer.
 */
function pendingGatewayOrder(Product $product, User $user, string $reference = 'TXN-TESTREFERENCE01'): Order
{
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.strtoupper(Str::random(8)),
        'total_amount' => $product->price,
        'currency' => 'NGN',
        'status' => OrderStatus::Pending->value,
        'payment_method' => 'paystack',
        'gateway' => 'paystack',
        'payment_reference' => $reference,
        'payment_status' => PaymentStatus::Unpaid->value,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'author_id' => $product->user_id,
        'price' => $product->price,
        'author_earnings' => round($product->price * 0.7, 2),
        'platform_commission' => round($product->price * 0.3, 2),
    ]);

    return $order;
}

function paystackWebhook(array $payload, ?string $secret = null): \Illuminate\Testing\TestResponse
{
    $body = json_encode($payload);
    $signature = hash_hmac('sha512', $body, $secret ?? paystackSecret());

    return test()->call('POST', route('payment.webhook', ['gateway' => 'paystack']), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ], $body);
}

// ─── Payment method manipulation ─────────────────────────────────────────────

test('a client cannot create a paid order by omitting the payment method', function () {
    $product = sellableProduct();
    buyerWithCart($product);
    $token = browserToken();

    checkoutPost([], $token)->assertSessionHas('error');

    expect(Order::count())->toBe(0)
        ->and(Product::find($product->id)->author->fresh()->balance)->toEqual(0);
});

test('a client cannot create a paid order by selecting the legacy direct method', function () {
    $product = sellableProduct();
    buyerWithCart($product);
    $token = browserToken();

    checkoutPost(['payment_method' => 'direct'], $token)->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});

test('an unsupported payment method is rejected instead of defaulting to success', function () {
    $product = sellableProduct();
    buyerWithCart($product);
    $token = browserToken();

    checkoutPost(['payment_method' => 'free-money'], $token)->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});

test('a configured gateway creates an unpaid pending order and redirects to the provider', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');

    Http::fake([
        'api.paystack.co/customer' => Http::response(['status' => true], 200),
        'api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/abc123',
                'reference' => 'TXN-TESTREFERENCE01',
                'access_code' => 'abc123',
                'id' => 987654,
            ],
        ], 200),
    ]);

    $product = sellableProduct(['price' => 5000]);
    buyerWithCart($product);

    $response = checkoutPost(['payment_method' => 'paystack'], browserToken());

    $response->assertRedirect('https://checkout.paystack.com/abc123');

    $order = Order::first();
    expect($order)->not->toBeNull()
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid->value)
        ->and($order->status)->toBe(OrderStatus::Pending->value)
        ->and((float) $order->total_amount)->toBe(5000.0)
        ->and($order->gateway_transaction_id)->toBe('987654');
});

test('the order total is computed from the database, not the client', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');

    Http::fake([
        'api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/abc123',
                'reference' => 'TXN-TESTREFERENCE01',
                'id' => 1,
            ],
        ], 200),
    ]);

    $product = sellableProduct(['price' => 5000, 'sale_price' => 3000]);
    buyerWithCart($product);

    checkoutPost([
        'payment_method' => 'paystack',
        'amount' => 1,
        'total_amount' => 1,
        'price' => 1,
    ], browserToken());

    $order = Order::first();

    expect((float) $order->total_amount)->toBe(3000.0)
        ->and((float) $order->items->first()->price)->toBe(3000.0);
});

// ─── Webhook authentication ──────────────────────────────────────────────────

test('a webhook with an invalid signature is rejected', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');
    $product = sellableProduct();
    $order = pendingGatewayOrder($product, User::factory()->create());

    $body = json_encode([
        'event' => 'charge.success',
        'data' => ['reference' => $order->payment_reference, 'status' => 'success', 'amount' => 500000, 'currency' => 'NGN', 'id' => 1],
    ]);

    test()->call('POST', route('payment.webhook', ['gateway' => 'paystack']), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => 'not-a-valid-signature',
    ], $body)->assertStatus(401);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid->value);
});

test('a webhook fails closed when the provider secret is missing', function () {
    config()->set('services.paystack.secret', '');
    $product = sellableProduct();
    $order = pendingGatewayOrder($product, User::factory()->create());

    paystackWebhook([
        'event' => 'charge.success',
        'data' => ['reference' => $order->payment_reference, 'status' => 'success', 'amount' => 500000, 'currency' => 'NGN', 'id' => 1],
    ], '')->assertStatus(503);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid->value);
});

test('a flutterwave webhook fails closed when the secret hash is not configured', function () {
    config()->set('services.flutterwave.secret_hash', '');

    $product = sellableProduct();
    $order = pendingGatewayOrder($product, User::factory()->create());

    test()->call('POST', route('payment.webhook', ['gateway' => 'flutterwave']), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_VERIF_HASH' => 'anything',
    ], json_encode([
        'event' => 'charge.completed',
        'data' => ['tx_ref' => $order->payment_reference, 'status' => 'successful', 'amount' => 5000, 'currency' => 'NGN', 'id' => 2],
    ]))->assertStatus(503);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid->value);
});

// ─── Validation of the transaction ───────────────────────────────────────────

test('a valid paystack webhook marks the order paid, once', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');
    config()->set('queue.default', 'sync');

    $product = sellableProduct(['price' => 5000]);
    $order = pendingGatewayOrder($product, User::factory()->create());

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'reference' => $order->payment_reference,
            'status' => 'success',
            'amount' => 500000,
            'currency' => 'NGN',
            'id' => 555,
        ],
    ];

    paystackWebhook($payload)->assertOk();

    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::Paid->value)
        ->and($order->paid_at)->not->toBeNull();

    // Author credited exactly once, and recorded in the ledger.
    expect((float) $product->author->fresh()->balance)->toBe(3500.0)
        ->and($order->items->first()->earning()->count())->toBe(1);
});

test('a replayed webhook does not credit the author twice', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');
    config()->set('queue.default', 'sync');

    $product = sellableProduct(['price' => 5000]);
    $order = pendingGatewayOrder($product, User::factory()->create());

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'reference' => $order->payment_reference,
            'status' => 'success',
            'amount' => 500000,
            'currency' => 'NGN',
            'id' => 777,
        ],
    ];

    paystackWebhook($payload)->assertOk();
    paystackWebhook($payload)->assertOk();
    paystackWebhook($payload)->assertOk();

    expect((float) $product->author->fresh()->balance)->toBe(3500.0)
        ->and(PaymentEvent::where('gateway', 'paystack')->count())->toBe(1);
});

test('a webhook whose amount differs from the order is rejected', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');

    $product = sellableProduct(['price' => 5000]);
    $order = pendingGatewayOrder($product, User::factory()->create());

    paystackWebhook([
        'event' => 'charge.success',
        'data' => [
            'reference' => $order->payment_reference,
            'status' => 'success',
            'amount' => 100, // 1 naira for a 5000 naira order
            'currency' => 'NGN',
            'id' => 888,
        ],
    ])->assertOk();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid->value)
        ->and((float) $product->author->fresh()->balance)->toBe(0.0);
});

test('a webhook with a mismatched currency is rejected', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');

    $product = sellableProduct(['price' => 5000]);
    $order = pendingGatewayOrder($product, User::factory()->create());

    paystackWebhook([
        'event' => 'charge.success',
        'data' => [
            'reference' => $order->payment_reference,
            'status' => 'success',
            'amount' => 500000,
            'currency' => 'USD',
            'id' => 999,
        ],
    ])->assertOk();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid->value);
});

test('a flutterwave webhook cannot settle an order created for paystack', function () {
    config()->set('services.flutterwave.secret_hash', 'flw_hash');
    config()->set('services.flutterwave.secret', 'flw_secret');

    $product = sellableProduct(['price' => 5000]);
    $order = pendingGatewayOrder($product, User::factory()->create());

    test()->call('POST', route('payment.webhook', ['gateway' => 'flutterwave']), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_VERIF_HASH' => 'flw_hash',
    ], json_encode([
        'event' => 'charge.completed',
        'data' => ['tx_ref' => $order->payment_reference, 'status' => 'successful', 'amount' => 5000, 'currency' => 'NGN', 'id' => 42],
    ]))->assertOk();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid->value);
});

test('a webhook for an unknown reference is ignored', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');

    paystackWebhook([
        'event' => 'charge.success',
        'data' => ['reference' => 'TXN-DOESNOTEXIST', 'status' => 'success', 'amount' => 500000, 'currency' => 'NGN', 'id' => 4242],
    ])->assertOk();

    expect(Order::where('payment_status', PaymentStatus::Paid->value)->count())->toBe(0);
});

test('a non-successful charge event never marks an order paid', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');

    $product = sellableProduct();
    $order = pendingGatewayOrder($product, User::factory()->create());

    paystackWebhook([
        'event' => 'charge.failed',
        'data' => ['reference' => $order->payment_reference, 'status' => 'failed', 'amount' => 500000, 'currency' => 'NGN', 'id' => 1],
    ])->assertOk();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid->value);
});

// ─── Callback path ───────────────────────────────────────────────────────────

test('a success redirect alone does not mark an order paid', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');

    Http::fake([
        'api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => [
                'status' => 'abandoned',
                'reference' => 'TXN-TESTREFERENCE01',
                'amount' => 500000,
                'currency' => 'NGN',
                'id' => 1,
            ],
        ], 200),
    ]);

    $product = sellableProduct(['price' => 5000]);
    $user = User::factory()->create();
    $order = pendingGatewayOrder($product, $user, 'TXN-TESTREFERENCE01');

    $this->actingAs($user)
        ->withSession(['pending_payment' => ['gateway' => 'paystack', 'order_id' => $order->id, 'reference' => $order->payment_reference]])
        ->get(route('checkout.callback', ['gateway' => 'paystack']).'?reference=TXN-TESTREFERENCE01&trxref=TXN-TESTREFERENCE01')
        ->assertRedirect(route('checkout.index'));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid->value);
});

test('the callback ignores a client-supplied reference that is not on the order', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');

    Http::fake([
        'api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => [
                'status' => 'success',
                'reference' => 'TXN-ATTACKER',
                'amount' => 500000,
                'currency' => 'NGN',
                'id' => 1,
            ],
        ], 200),
    ]);

    $product = sellableProduct(['price' => 5000]);
    $user = User::factory()->create();
    $order = pendingGatewayOrder($product, $user);

    $this->actingAs($user)
        ->withSession(['pending_payment' => ['gateway' => 'paystack', 'order_id' => $order->id, 'reference' => $order->payment_reference]])
        ->get(route('checkout.callback', ['gateway' => 'paystack']).'?reference=TXN-ATTACKER')
        ->assertRedirect(route('checkout.index'));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid->value);
});

test('a verified callback settles the order', function () {
    config()->set('services.paystack.secret', 'sk_test_fake');
    config()->set('queue.default', 'sync');

    Http::fake([
        'api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => [
                'status' => 'success',
                'reference' => 'TXN-TESTREFERENCE01',
                'amount' => 500000,
                'currency' => 'NGN',
                'id' => 1,
            ],
        ], 200),
    ]);

    $product = sellableProduct(['price' => 5000]);
    $user = User::factory()->create();
    $order = pendingGatewayOrder($product, $user, 'TXN-TESTREFERENCE01');

    $this->actingAs($user)
        ->withSession(['pending_payment' => ['gateway' => 'paystack', 'order_id' => $order->id, 'reference' => $order->payment_reference]])
        ->get(route('checkout.callback', ['gateway' => 'paystack']).'?trxref=TXN-TESTREFERENCE01')
        ->assertRedirect(route('orders.confirmation', $order));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Paid->value)
        ->and((float) $product->author->fresh()->balance)->toBe(3500.0);
});

test('a callback with no pending session is refused', function () {
    $this->get(route('checkout.callback', ['gateway' => 'paystack']).'?reference=anything')
        ->assertRedirect(route('cart.index'));
});

// ─── Manual / offline payments ───────────────────────────────────────────────

test('an offline order stays unpaid and awaiting approval', function () {
    $product = sellableProduct(['price' => 5000]);
    buyerWithCart($product);

    $response = checkoutPost(['payment_method' => 'manual'], browserToken());

    $order = Order::first();
    expect($order)->not->toBeNull()
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid->value)
        ->and($order->status)->toBe(OrderStatus::AwaitingApproval->value)
        ->and($order->payment_method)->toBe('manual');

    $response->assertRedirect(route('orders.confirmation', $order));

    // No money moved for anyone.
    expect((float) $product->author->fresh()->balance)->toBe(0.0);
});

// ─── Unavailable products ────────────────────────────────────────────────────

test('an unpublished product cannot be checked out', function () {
    $product = sellableProduct(['is_published' => false]);
    buyerWithCart($product);

    checkoutPost(['payment_method' => 'manual'], browserToken())->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});

test('a product that no longer exists cannot be checked out', function () {
    $product = sellableProduct();
    buyerWithCart($product);

    // Simulate a cart holding an id that has since vanished.
    test()->withSession([
        'cart' => [999999 => ['title' => 'Ghost', 'price' => 100]],
    ]);

    checkoutPost(['payment_method' => 'manual'], browserToken())->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});
