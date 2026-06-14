<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

// Helper: create an authenticated admin session
function adminSession(): array
{
    return ['admin_2fa_verified' => true];
}

function adminUser(): User
{
    return User::factory()->create([
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
}

function makeAuthor(): User
{
    return User::factory()->create([
        'role' => 'author',
        'email' => 'author@test.com',
        'email_verified_at' => now(),
    ]);
}

function makeCategory(string $name = 'Templates'): Category
{
    return Category::create(['name' => $name, 'slug' => Str::slug($name)]);
}

function makeCsv(array $rows, ?array $headers = null): UploadedFile
{
    $defaultHeaders = ['title', 'category', 'author_email', 'price', 'sale_price', 'file_type', 'file_name', 'thumbnail', 'tags', 'is_published', 'description'];
    $cols = $headers ?? $defaultHeaders;

    $lines = [implode(',', $cols)];
    foreach ($rows as $row) {
        $lines[] = implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $row));
    }

    return UploadedFile::fake()->createWithContent('import.csv', implode("\n", $lines));
}

// ─── Access control ───────────────────────────────────────────────────────────

test('guests cannot access import page', function () {
    $this->get(route('admin.products.import'))->assertRedirect();
});

test('non-admin users cannot access import page', function () {
    $user = User::factory()->create(['role' => 'author', 'email_verified_at' => now()]);
    $this->actingAs($user)
        ->withSession(adminSession())
        ->get(route('admin.products.import'))
        ->assertForbidden();
});

test('admin can view import page', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->withSession(adminSession())
        ->get(route('admin.products.import'))
        ->assertOk()
        ->assertViewIs('admin.products.import');
});

// ─── Template download ────────────────────────────────────────────────────────

test('admin can download csv template', function () {
    $admin = adminUser();
    $response = $this->actingAs($admin)
        ->withSession(adminSession())
        ->get(route('admin.products.import.template'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    expect($response->getContent())->toContain('title')
        ->toContain('file_type')
        ->toContain('price');
});

// ─── CSV validation ───────────────────────────────────────────────────────────

test('rejects upload without csv_file', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), [])
        ->assertSessionHasErrors('csv_file');
});

test('rejects csv missing required columns', function () {
    $admin = adminUser();
    $csv = UploadedFile::fake()->createWithContent('bad.csv', "name,cost\n\"A\",\"10\"");

    $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), ['csv_file' => $csv])
        ->assertSessionHasErrors('csv_file');
});

// ─── Successful import ────────────────────────────────────────────────────────

test('imports valid csv rows and creates products', function () {
    Storage::fake('public');
    $admin = adminUser();
    $author = makeAuthor();
    $category = makeCategory('Templates');

    $csv = makeCsv([
        ['Product Alpha', 'Templates', $author->email, '1999', '', 'graphic', '', '', '', '1', 'Desc A'],
        ['Product Beta', 'Templates', $author->email, '2999', '1999', 'template', '', '', 'ui,kit', '1', 'Desc B'],
    ]);

    $response = $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), ['csv_file' => $csv]);

    $response->assertRedirect(route('admin.products.import'));

    $results = $response->getSession()->get('import_results');
    expect($results['created'])->toBe(2)
        ->and($results['skipped'])->toBe(0)
        ->and($results['failed'])->toBe(0);

    $this->assertDatabaseHas('products', ['title' => 'Product Alpha', 'price' => 1999, 'file_type' => 'graphic']);
    $this->assertDatabaseHas('products', ['title' => 'Product Beta', 'sale_price' => 1999]);
});

// ─── Duplicate detection ──────────────────────────────────────────────────────

test('skips rows where title already exists in the database', function () {
    Storage::fake('public');
    $admin = adminUser();
    $author = makeAuthor();
    makeCategory('Templates');

    Product::factory()->create(['title' => 'Existing Product']);

    $csv = makeCsv([
        ['Existing Product', 'Templates', $author->email, '1000', '', 'graphic', '', '', '', '1', ''],
        ['New Product', 'Templates', $author->email, '1000', '', 'graphic', '', '', '', '1', ''],
    ]);

    $response = $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), ['csv_file' => $csv]);

    $results = $response->getSession()->get('import_results');
    expect($results['created'])->toBe(1)
        ->and($results['skipped'])->toBe(1);
});

test('skips duplicate titles within the same csv', function () {
    Storage::fake('public');
    $admin = adminUser();
    $author = makeAuthor();
    makeCategory('Templates');

    $csv = makeCsv([
        ['Same Title', 'Templates', $author->email, '1000', '', 'graphic', '', '', '', '1', ''],
        ['Same Title', 'Templates', $author->email, '2000', '', 'graphic', '', '', '', '1', ''],
    ]);

    $response = $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), ['csv_file' => $csv]);

    $results = $response->getSession()->get('import_results');
    expect($results['created'])->toBe(1)
        ->and($results['skipped'])->toBe(1);

    expect(Product::where('title', 'Same Title')->count())->toBe(1);
});

// ─── Row-level failures ───────────────────────────────────────────────────────

test('fails row with unknown category and no default', function () {
    Storage::fake('public');
    $admin = adminUser();
    $author = makeAuthor();

    $csv = makeCsv([
        ['My Product', 'NonExistent', $author->email, '999', '', 'graphic', '', '', '', '1', ''],
    ]);

    $response = $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), ['csv_file' => $csv]);

    $results = $response->getSession()->get('import_results');
    expect($results['failed'])->toBe(1)
        ->and($results['rows'][0]['reason'])->toContain('Category');
});

test('fails row with invalid file_type', function () {
    Storage::fake('public');
    $admin = adminUser();
    $author = makeAuthor();
    makeCategory('Templates');

    $csv = makeCsv([
        ['My Product', 'Templates', $author->email, '999', '', 'invalidtype', '', '', '', '1', ''],
    ]);

    $response = $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), ['csv_file' => $csv]);

    $results = $response->getSession()->get('import_results');
    expect($results['failed'])->toBe(1);
});

test('fails row when staged file is not found', function () {
    Storage::fake('public');
    $admin = adminUser();
    $author = makeAuthor();
    makeCategory('Templates');

    $csv = makeCsv([
        ['My Product', 'Templates', $author->email, '999', '', 'graphic', 'missing.zip', '', '', '1', ''],
    ]);

    $response = $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), ['csv_file' => $csv]);

    $results = $response->getSession()->get('import_results');
    expect($results['failed'])->toBe(1)
        ->and($results['rows'][0]['reason'])->toContain('not found');
});

// ─── File staging ─────────────────────────────────────────────────────────────

test('copies staged file to public storage when file_name matches', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = adminUser();
    $author = makeAuthor();
    makeCategory('Templates');

    Storage::disk('local')->put('products/bulk/product.zip', 'fake zip content');

    $csv = makeCsv([
        ['Staged Product', 'Templates', $author->email, '999', '', 'graphic', 'product.zip', '', '', '1', ''],
    ]);

    $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), ['csv_file' => $csv]);

    $product = Product::where('title', 'Staged Product')->first();
    expect($product)->not->toBeNull()
        ->and($product->file_path)->toStartWith('products/files/')
        ->and($product->original_file_name)->toBe('product.zip');

    Storage::disk('public')->assertExists($product->file_path);

    // Staging file is kept (not deleted) for re-imports
    Storage::disk('local')->assertExists('products/bulk/product.zip');
});

// ─── Security ─────────────────────────────────────────────────────────────────

test('path traversal in file_name is blocked', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = adminUser();
    $author = makeAuthor();
    makeCategory('Templates');

    // Place a file at the traversal target to confirm it stays untouched
    Storage::disk('local')->put('secret.zip', 'secret');

    $csv = makeCsv([
        ['Traversal Attempt', 'Templates', $author->email, '999', '', 'graphic', '../../secret.zip', '', '', '1', ''],
    ]);

    $response = $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), ['csv_file' => $csv]);

    $results = $response->getSession()->get('import_results');
    // basename() strips the path, so 'secret.zip' won't exist in products/bulk/ — row fails
    expect($results['failed'])->toBe(1);
    $this->assertDatabaseMissing('products', ['title' => 'Traversal Attempt']);
});

test('default author and category are used when csv columns are blank', function () {
    Storage::fake('public');
    $admin = adminUser();
    $author = makeAuthor();
    $category = makeCategory('Templates');

    // CSV has no category or author_email columns
    $csv = UploadedFile::fake()->createWithContent(
        'import.csv',
        "title,price,file_type\n\"Default Fallback Product\",\"999\",\"graphic\""
    );

    $this->actingAs($admin)
        ->withSession(adminSession())
        ->post(route('admin.products.import.store'), [
            'csv_file' => $csv,
            'default_author_id' => $author->id,
            'default_category_id' => $category->id,
        ]);

    $this->assertDatabaseHas('products', [
        'title' => 'Default Fallback Product',
        'user_id' => $author->id,
        'category_id' => $category->id,
    ]);
});
