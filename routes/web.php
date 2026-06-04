<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\Admin2faController;
use App\Livewire\SearchProducts;
use App\Livewire\CartCount;
use App\Livewire\WishlistButton;
use App\Livewire\ProductSuggestions;
use App\Livewire\ReferralLink;
use App\Livewire\NotificationBell;
use App\Livewire\PopupModal;
use App\Livewire\BannerAd;
use App\Livewire\AdminNotifications;
use App\Livewire\AdminAdvertisements;
use App\Livewire\AdminPopups;
use App\Livewire\AdminAffiliates;

use App\Http\Controllers\UserDashboardController;

// Guest routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Catch referral code on registration
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
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
    Route::get('/2fa/login', [App\Http\Controllers\User2faLoginController::class, 'showForm'])->name('profile.2fa.login');
    Route::post('/2fa/login/verify', [App\Http\Controllers\User2faLoginController::class, 'verify'])->name('profile.2fa.login.verify');
    Route::get('/2fa/login/resend', [App\Http\Controllers\User2faLoginController::class, 'resend'])->name('profile.2fa.login.resend');
});

// Admin 2FA Routes
Route::middleware('guest')->group(function () {
    Route::get('/admin/2fa', [Admin2faController::class, 'showForm'])->name('admin.2fa.form');
    Route::post('/admin/2fa/send', [Admin2faController::class, 'sendCode'])->name('admin.2fa.send');
    Route::post('/admin/2fa/verify', [Admin2faController::class, 'verify'])->name('admin.2fa.verify');
    Route::post('/admin/2fa/cancel', [Admin2faController::class, 'cancel'])->name('admin.2fa.cancel');
});

// Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/search', SearchProducts::class)->name('products.search');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Checkout routes (guest users can also purchase)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/callback/{gateway}', [CheckoutController::class, 'callback'])->name('checkout.callback');
Route::get('/orders/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');

// Authenticated user routes (require verified email)
Route::middleware(['auth', 'verified'])->group(function () {
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
    Route::get('/profile/2fa', [App\Http\Controllers\Profile2faController::class, 'index'])->name('profile.2fa');
    Route::post('/profile/2fa/enable', [App\Http\Controllers\Profile2faController::class, 'enable'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/confirm', [App\Http\Controllers\Profile2faController::class, 'confirmEnable'])->name('profile.2fa.confirm');
    Route::post('/profile/2fa/disable', [App\Http\Controllers\Profile2faController::class, 'disable'])->name('profile.2fa.disable');

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
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

    // Products
    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::get('/categories', [App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Orders
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');

    // Users
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Reviews
    Route::get('/reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('/reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Admin Livewire pages
    Route::get('/notifications', AdminNotifications::class)->name('notifications');
    Route::get('/advertisements', AdminAdvertisements::class)->name('advertisements');
    Route::get('/popups', AdminPopups::class)->name('popups');
    Route::get('/affiliates', AdminAffiliates::class)->name('affiliates');

    // Webhooks
    Route::get('/webhooks', [App\Http\Controllers\Admin\WebhookController::class, 'index'])->name('webhooks.index');
    Route::get('/webhooks/create', [App\Http\Controllers\Admin\WebhookController::class, 'create'])->name('webhooks.create');
    Route::post('/webhooks', [App\Http\Controllers\Admin\WebhookController::class, 'store'])->name('webhooks.store');
    Route::get('/webhooks/{webhook}/edit', [App\Http\Controllers\Admin\WebhookController::class, 'edit'])->name('webhooks.edit');
    Route::put('/webhooks/{webhook}', [App\Http\Controllers\Admin\WebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('/webhooks/{webhook}', [App\Http\Controllers\Admin\WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::get('/webhooks/{webhook}/logs', [App\Http\Controllers\Admin\WebhookController::class, 'logs'])->name('webhooks.logs');
    Route::post('/webhooks/{webhook}/test', [App\Http\Controllers\Admin\WebhookController::class, 'test'])->name('webhooks.test');
    Route::post('/webhooks/logs/{log}/retry', [App\Http\Controllers\Admin\WebhookController::class, 'retry'])->name('webhooks.retry');
});
