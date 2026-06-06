<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SlideshowController;
use App\Http\Controllers\Admin\TermsController as AdminTermsController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\Admin2faController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Profile2faController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\User2faLoginController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\RedirectAdminToPanel;
use App\Livewire\AdminAdvertisements;
use App\Livewire\AdminAffiliates;
use App\Livewire\AdminNotifications;
use App\Livewire\AdminPopups;
use App\Livewire\SearchProducts;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware(RedirectAdminToPanel::class);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,5');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Email Verification Routes (Laravel Standard - Signed URLs)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->name('verification.resend');
});
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify')
    ->middleware(['signed', 'throttle:6,1']);

// User 2FA Login
Route::middleware('auth')->group(function () {
    Route::get('/2fa/login', [User2faLoginController::class, 'showForm'])->name('profile.2fa.login');
    Route::post('/2fa/login/verify', [User2faLoginController::class, 'verify'])->name('profile.2fa.login.verify')->middleware('throttle:5,1');
    Route::get('/2fa/login/resend', [User2faLoginController::class, 'resend'])->name('profile.2fa.login.resend')->middleware('throttle:3,1');
});

// Admin 2FA Routes
Route::middleware('guest')->group(function () {
    Route::get('/admin/2fa', [Admin2faController::class, 'showForm'])->name('admin.2fa.form');
    Route::post('/admin/2fa/send', [Admin2faController::class, 'sendCode'])->name('admin.2fa.send')->middleware('throttle:3,1');
    Route::post('/admin/2fa/verify', [Admin2faController::class, 'verify'])->name('admin.2fa.verify')->middleware('throttle:5,1');
    Route::post('/admin/2fa/cancel', [Admin2faController::class, 'cancel'])->name('admin.2fa.cancel');
});

// Public static pages
Route::get('/terms', [TermsController::class, 'show'])->name('terms.show');

// Product routes
Route::middleware(RedirectAdminToPanel::class)->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/search', SearchProducts::class)->name('products.search');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
});

// Cart routes
Route::middleware(RedirectAdminToPanel::class)->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});

// Checkout routes (guest users can also purchase)
Route::middleware(RedirectAdminToPanel::class)->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/callback/{gateway}', [CheckoutController::class, 'callback'])->name('checkout.callback');
    Route::get('/orders/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');
});

// Authenticated user routes (require verified email)
Route::middleware(['auth', 'verified', RedirectAdminToPanel::class])->group(function () {
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/remove/{product}', [WishlistController::class, 'remove'])->name('wishlist.remove');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/edit', [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Profile 2FA
    Route::get('/profile/2fa', [Profile2faController::class, 'index'])->name('profile.2fa');
    Route::post('/profile/2fa/enable', [Profile2faController::class, 'enable'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/confirm', [Profile2faController::class, 'confirmEnable'])->name('profile.2fa.confirm');
    Route::post('/profile/2fa/disable', [Profile2faController::class, 'disable'])->name('profile.2fa.disable');

    // User Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Product download & review
    Route::post('/products/{product}/download', [ProductController::class, 'download'])->name('products.download');
    Route::post('/products/{product}/review', [ProductController::class, 'storeReview'])->name('products.review');

    // Affiliate / Referral
    Route::get('/affiliate', [AffiliateController::class, 'index'])->name('affiliate.index');
    Route::get('/affiliate/payouts', [AffiliateController::class, 'payouts'])->name('affiliate.payouts');
    Route::post('/affiliate/request-payout', [AffiliateController::class, 'requestPayout'])->name('affiliate.request-payout');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Async uploads
    Route::post('/uploads/image', [UploadController::class, 'image'])->name('uploads.image');
    Route::post('/uploads/file', [UploadController::class, 'file'])->name('uploads.file');

    // Products
    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Orders
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Slideshows
    Route::get('/slideshows', [SlideshowController::class, 'index'])->name('slideshows.index');
    Route::get('/slideshows/create', [SlideshowController::class, 'create'])->name('slideshows.create');
    Route::post('/slideshows', [SlideshowController::class, 'store'])->name('slideshows.store');
    Route::get('/slideshows/{slideshow}/edit', [SlideshowController::class, 'edit'])->name('slideshows.edit');
    Route::put('/slideshows/{slideshow}', [SlideshowController::class, 'update'])->name('slideshows.update');
    Route::delete('/slideshows/{slideshow}', [SlideshowController::class, 'destroy'])->name('slideshows.destroy');

    // Admin Livewire pages
    Route::get('/notifications', AdminNotifications::class)->name('notifications');
    Route::get('/advertisements', AdminAdvertisements::class)->name('advertisements');
    Route::get('/popups', AdminPopups::class)->name('popups');
    Route::get('/affiliates', AdminAffiliates::class)->name('affiliates');

    // Terms of Service
    Route::get('/terms', [AdminTermsController::class, 'edit'])->name('terms.edit');
    Route::put('/terms', [AdminTermsController::class, 'update'])->name('terms.update');

    // Webhooks
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::get('/webhooks/create', [WebhookController::class, 'create'])->name('webhooks.create');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::get('/webhooks/{webhook}/edit', [WebhookController::class, 'edit'])->name('webhooks.edit');
    Route::put('/webhooks/{webhook}', [WebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::get('/webhooks/{webhook}/logs', [WebhookController::class, 'logs'])->name('webhooks.logs');
    Route::post('/webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');
    Route::post('/webhooks/logs/{log}/retry', [WebhookController::class, 'retry'])->name('webhooks.retry');
});
