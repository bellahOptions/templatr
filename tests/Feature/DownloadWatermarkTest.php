<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Download\DownloadSecurityManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
| `csrf_token()` reads the test HTTP session, which stays empty until a request
| has run. Seeding `_token` explicitly lets the download POST reach the action
| exactly the way the Blade buttons do.
|
| Purchased originals live on the private `private_assets` disk, mirroring
| production: `public/storage` cannot reach that path.
|
*/

function csrfToken(): string
{
    return Str::random(40);
}

function downloadPost(string $url, array $data = [], ?string $token = null): TestResponse
{
    $token ??= csrfToken();

    return test()->withSession(['_token' => $token])
        ->post($url, array_merge(['_token' => $token], $data));
}

function buyer(): User
{
    return User::factory()->create(['email_verified_at' => now()]);
}

function publishedProduct(array $attributes = []): Product
{
    return Product::factory()->create($attributes + ['file_path' => null, 'storage_disk' => null]);
}

/**
 * Place an original on the private asset disk and attach it to a product.
 */
function attachPrivateFile(Product $product, string $relativePath, string $contents): string
{
    Storage::disk('private_assets')->put($relativePath, $contents);
    $product->update([
        'file_path' => $relativePath,
        'storage_disk' => 'private_assets',
        'original_file_name' => basename($relativePath),
    ]);

    return $relativePath;
}

/**
 * Attach a real archive to a product and return its storage path.
 */
function attachArchive(Product $product, array $entries = ['template/index.html' => '<h1>Original template</h1>']): string
{
    $path = 'products/files/'.$product->id.'/kit.zip';
    $absolute = Storage::disk('private_assets')->path($path);

    @mkdir(dirname($absolute), 0777, true);
    $zip = new ZipArchive;
    $zip->open($absolute, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($entries as $name => $content) {
        $zip->addFromString($name, $content);
    }

    $zip->close();
    $product->update([
        'file_path' => $path,
        'storage_disk' => 'private_assets',
        'original_file_name' => 'kit.zip',
    ]);

    return $path;
}

function purchase(Product $product, User $user): OrderItem
{
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.strtoupper(Str::random(8)),
        'total_amount' => $product->price,
        'currency' => 'NGN',
        'status' => 'completed',
        'payment_method' => 'manual',
        'payment_status' => 'paid',
    ]);

    return OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'author_id' => $product->user_id,
        'price' => $product->price,
        'author_earnings' => 0,
    ]);
}

function readZipEntries(string $binary): array
{
    $temp = tempnam(sys_get_temp_dir(), 'wm');
    file_put_contents($temp, $binary);

    $zip = new ZipArchive;
    $entries = [];

    if ($zip->open($temp) === true) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $entries[$name] = $zip->getFromIndex($i);
        }
        $zip->close();
    }

    unlink($temp);

    return $entries;
}

/**
 * The notice file injected into a delivered archive, if any.
 */
function noticeFrom(array $entries): ?string
{
    return collect($entries)->first(fn ($value, $key) => str_contains($key, 'WATERMARK'));
}

/**
 * Boot the storage fakes used by every download test.
 */
function fakeStorages(): void
{
    Storage::fake('private_assets');
    Storage::fake('public');
    Storage::fake('local');
}

// ─── Purchase notice on every download ───────────────────────────────────────

test('a purchased download is branded with the purchase notice', function () {
    fakeStorages();

    $product = publishedProduct(['title' => 'Aurora Dashboard Kit']);
    attachArchive($product, ['template/index.html' => '<h1>Premium template</h1>']);
    $item = purchase($product, $buyer = buyer());
    $this->actingAs($buyer);

    $response = downloadPost(route('products.download', $product))->assertOk();

    expect($response->headers->get('Content-Disposition'))
        ->toContain('attachment')
        ->toContain('PURCHASED-FROM-www.templatr.site')
        ->toContain('.zip');

    $entries = readZipEntries($response->streamedContent());

    // The original asset survives untouched.
    expect($entries)->toHaveKey('template/index.html')
        ->and($entries['template/index.html'])->toBe('<h1>Premium template</h1>');

    // And the notice is now part of the archive.
    expect(noticeFrom($entries))
        ->not->toBeNull()
        ->toContain('Purchased from www.templatr.site')
        ->toContain('Aurora Dashboard Kit')
        ->toContain($item->order->order_number)
        ->toContain($buyer->name);
});

test('a repeat download reuses the cached artifact', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);
    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    $first = downloadPost(route('products.download', $product))->assertOk();
    $artifact = Storage::disk('local')->allFiles('watermarked');

    expect($artifact)->toHaveCount(1);

    $second = downloadPost(route('products.download', $product))->assertOk();

    // Same file on disk, byte-identical delivery, no second render.
    expect(Storage::disk('local')->allFiles('watermarked'))->toBe($artifact)
        ->and($second->streamedContent())->toBe($first->streamedContent());
});

test('a raster product image is watermarked in place', function () {
    fakeStorages();

    $product = publishedProduct();
    $path = 'products/files/graphic.png';
    Storage::disk('private_assets')->put(
        $path,
        UploadedFile::fake()->image('graphic.png', 600, 400)->getContent()
    );
    $product->update(['file_path' => $path, 'storage_disk' => 'private_assets', 'original_file_name' => 'graphic.png']);
    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    $response = downloadPost(route('products.download', $product))->assertOk();
    $delivered = $response->streamedContent();

    expect($response->headers->get('Content-Disposition'))
        ->toContain('PURCHASED-FROM-www.templatr.site')
        ->toContain('.png')
        ->and(imagecreatefromstring($delivered))->not->toBeFalse();
});

test('a format that cannot be stamped is delivered inside a branded archive', function () {
    fakeStorages();

    $product = publishedProduct();
    $path = 'products/files/beats.mp3';
    Storage::disk('private_assets')->put($path, str_repeat('ID3', 100));
    $product->update(['file_path' => $path, 'storage_disk' => 'private_assets', 'original_file_name' => 'beats.mp3']);
    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    $response = downloadPost(route('products.download', $product))->assertOk();

    expect($response->headers->get('Content-Disposition'))
        ->toContain('PURCHASED-FROM-www.templatr.site')
        ->toContain('.zip');

    $entries = readZipEntries($response->streamedContent());

    expect($entries)->toHaveKey('beats.mp3')
        ->and($entries['beats.mp3'])->toBe(str_repeat('ID3', 100));

    expect(noticeFrom($entries))->toContain('Purchased from www.templatr.site');
});

// ─── Private storage ─────────────────────────────────────────────────────────

test('the original is stored on the private disk, not the public one', function () {
    fakeStorages();

    $product = publishedProduct();
    $path = attachArchive($product);

    expect(Storage::disk('private_assets')->exists($path))->toBeTrue()
        ->and(Storage::disk('public')->exists($path))->toBeFalse()
        ->and($product->storage_disk)->toBe('private_assets');
});

test('a direct public storage URL cannot retrieve the original', function () {
    fakeStorages();

    $product = publishedProduct();
    $path = attachArchive($product, ['secret/design.psd' => 'PROPRIETARY SOURCE']);

    // The public disk — the only thing `public/storage` exposes — has no copy.
    $publicUrl = Storage::disk('public')->url($path);

    expect(Storage::disk('public')->exists($path))->toBeFalse()
        ->and($publicUrl)->toContain('/storage/')
        // And the bytes are not reachable through it.
        ->and(Storage::disk('public')->get($path))->toBeNull();

    $this->get(parse_url($publicUrl, PHP_URL_PATH))->assertNotFound();
});

test('the shipped public symlink cannot reach storage/app/private-assets', function () {
    // storage/app/private-assets is outside the symlink target (storage/app/public).
    $symlinkTarget = config('filesystems.links')[public_path('storage')];
    $privateRoot = Storage::disk('private_assets')->path('');

    expect(str_starts_with($privateRoot, rtrim($symlinkTarget, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR))->toBeFalse()
        ->and($symlinkTarget)->toBe(storage_path('app/public'));
});

test('path traversal in a stored file path is refused', function () {
    fakeStorages();

    $product = publishedProduct();
    Storage::disk('private_assets')->put('products/files/real.zip', 'REAL');
    $product->update([
        'file_path' => '../../.env',
        'storage_disk' => 'private_assets',
        'original_file_name' => 'x.zip',
    ]);

    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    downloadPost(route('products.download', $product))->assertSessionHas('error');
});

// ─── Access control ──────────────────────────────────────────────────────────

test('a buyer who has not purchased cannot download', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);
    $this->actingAs(buyer());

    downloadPost(route('products.download', $product))->assertSessionHas('error');

    expect(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty();
});

test('a guest without a token cannot download', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);

    $this->get(route('products.download.guest', ['product' => $product->slug]))
        ->assertSessionHas('error');

    expect(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty();
});

test('a guest with the emailed token can download the branded file', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);

    $order = Order::create([
        'user_id' => null,
        'guest_name' => 'Ada Guest',
        'guest_email' => 'ada@example.com',
        'guest_phone' => '08030000000',
        'order_number' => 'ORD-'.strtoupper(Str::random(8)),
        'total_amount' => $product->price,
        'currency' => 'NGN',
        'status' => 'completed',
        'payment_method' => 'paystack',
        'payment_reference' => null,
        'payment_status' => 'paid',
    ]);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'author_id' => $product->user_id,
        'price' => $product->price,
        'author_earnings' => 0,
    ]);

    $token = app(DownloadSecurityManager::class)->generateDownloadToken($item, 72);

    $response = $this->get(route('products.download.guest', [
        'product' => $product->slug,
        'token' => $token,
    ]))->assertOk();

    expect($response->headers->get('Content-Disposition'))
        ->toContain('PURCHASED-FROM-www.templatr.site');

    // The buyer's own order details are recorded in the notice.
    expect(noticeFrom(readZipEntries($response->streamedContent())))
        ->toContain('Ada Guest')
        ->toContain($order->order_number);
});

test('a forged guest token is rejected', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);

    $this->get(route('products.download.guest', [
        'product' => $product->slug,
        'token' => str_repeat('a', 64),
    ]))->assertSessionHas('error');

    expect(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty();
});

test('an expired guest token is rejected', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);

    $order = Order::create([
        'user_id' => null,
        'guest_name' => 'Ada Guest',
        'guest_email' => 'ada@example.com',
        'guest_phone' => '08030000000',
        'order_number' => 'ORD-'.strtoupper(Str::random(8)),
        'total_amount' => $product->price,
        'currency' => 'NGN',
        'status' => 'completed',
        'payment_method' => 'paystack',
        'payment_status' => 'paid',
    ]);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'author_id' => $product->user_id,
        'price' => $product->price,
        'author_earnings' => 0,
    ]);

    $token = app(DownloadSecurityManager::class)->generateDownloadToken($item, 72);
    $item->forceFill(['download_token_expires_at' => now()->subMinute()])->save();

    $this->get(route('products.download.guest', [
        'product' => $product->slug,
        'token' => $token,
    ]))->assertSessionHas('error');

    expect(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty();
});

test('an unpaid order cannot download', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);
    $buyer = buyer();
    $item = purchase($product, $buyer);
    $item->order->forceFill(['payment_status' => 'unpaid'])->save();
    $this->actingAs($buyer);

    downloadPost(route('products.download', $product))->assertSessionHas('error');

    expect(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty();
});

test('a download past the per-item limit is refused', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);
    $buyer = buyer();
    $item = purchase($product, $buyer);
    $item->update(['download_count' => OrderItem::MAX_DOWNLOADS]);
    $this->actingAs($buyer);

    downloadPost(route('products.download', $product))->assertSessionHas('error');

    expect(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty();
});

// ─── Watermark failure policy ────────────────────────────────────────────────

test('a watermark failure under the reject policy does not expose the original', function () {
    fakeStorages();
    config()->set('watermark.on_failure', 'reject');

    $product = publishedProduct();
    $path = 'products/files/asset.psd';
    attachPrivateFile($product, $path, 'RAW-PSD-BYTES-THAT-CANNOT-BE-STAMPED');

    $buyer = buyer();
    $item = purchase($product, $buyer);
    $this->actingAs($buyer);

    $response = downloadPost(route('products.download', $product));

    $response->assertSessionHas('error');

    // The raw original was never delivered and the failure was not credited.
    expect($response->streamedContent())->not->toContain('RAW-PSD-BYTES-THAT-CANNOT-BE-STAMPED')
        ->and($item->fresh()->download_count)->toBe(0)
        ->and(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty();
});

test('the documented original-fallback policy is explicit and still tracked', function () {
    fakeStorages();
    config()->set('watermark.on_failure', 'original');

    $product = publishedProduct();
    $path = 'products/files/asset.psd';
    attachPrivateFile($product, $path, 'RAW-PSD-BYTES');

    $buyer = buyer();
    $item = purchase($product, $buyer);
    $this->actingAs($buyer);

    $response = downloadPost(route('products.download', $product))->assertOk();

    expect($response->streamedContent())->toBe('RAW-PSD-BYTES')
        // A filename that a non-editable asset is wrapped in is still branded.
        ->and($response->headers->get('Content-Disposition'))->toContain('PURCHASED-FROM-www.templatr.site')
        ->and($item->fresh()->download_count)->toBe(1);
});

// ─── Concurrency guards ──────────────────────────────────────────────────────

test('an account already streaming its maximum downloads is throttled', function () {
    fakeStorages();
    config()->set('watermark.max_concurrent_per_user', 1);

    $product = publishedProduct();
    attachArchive($product);
    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    // Saturate the buyer's only slot the way an interrupted download would.
    Cache::put(
        'download_concurrency:user:'.$buyer->id,
        ['active-download' => now()->getTimestamp()],
        now()->addMinutes(5)
    );

    downloadPost(route('products.download', $product))->assertSessionHas('error');

    expect(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty();
});

test('a finished download releases its concurrency slot', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);
    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    downloadPost(route('products.download', $product))->assertOk();

    expect(Cache::get('download_concurrency:user:'.$buyer->id))->toBeNull();
});

// ─── Maintenance ─────────────────────────────────────────────────────────────

test('the watermark cache can be pruned', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);
    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    downloadPost(route('products.download', $product))->assertOk();

    expect(Storage::disk('local')->allFiles('watermarked'))->toHaveCount(1);

    $this->artisan('watermark:prune')->assertSuccessful();

    expect(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty();
});

test('rendering stays off when watermarking is disabled', function () {
    fakeStorages();
    config()->set('watermark.enabled', false);

    $product = publishedProduct();
    $original = attachArchive($product);
    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    $response = downloadPost(route('products.download', $product))->assertOk();

    expect(Storage::disk('local')->allFiles('watermarked'))->toBeEmpty()
        // The filename is still branded even when rendering is switched off.
        ->and($response->headers->get('Content-Disposition'))->toContain('PURCHASED-FROM-www.templatr.site')
        ->and($response->streamedContent())->toBe(Storage::disk('private_assets')->get($original));
});

// ─── Resumable delivery ──────────────────────────────────────────────────────

test('a ranged request is answered with a resumable partial response', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);
    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    $token = csrfToken();
    $response = $this->withSession(['_token' => $token])
        ->post(route('products.download', $product), ['_token' => $token], ['Range' => 'bytes=0-9'])
        ->assertStatus(206);

    expect($response->headers->get('Content-Range'))->toStartWith('bytes 0-9/')
        ->and((int) $response->headers->get('Content-Length'))->toBe(10)
        ->and(strlen($response->streamedContent()))->toBe(10);
});

test('an unsatisfiable range is rejected', function () {
    fakeStorages();

    $product = publishedProduct();
    attachArchive($product);
    $buyer = buyer();
    purchase($product, $buyer);
    $this->actingAs($buyer);

    $token = csrfToken();
    $this->withSession(['_token' => $token])
        ->post(route('products.download', $product), ['_token' => $token], ['Range' => 'bytes=999999-'])
        ->assertStatus(416);
});
