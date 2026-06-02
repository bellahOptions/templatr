<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\Notification;
use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Stats
        $totalOrders = Order::where('user_id', $user->id)->count();
        $totalSpent = Order::where('user_id', $user->id)->where('payment_status', 'paid')->sum('total_amount');
        $totalDownloads = OrderItem::whereHas('order', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('payment_status', 'paid');
        })->sum('download_count');
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();

        // Recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->with('items.product')
            ->latest()
            ->take(5)
            ->get();

        // Downloadable items (purchased + has remaining)
        $purchasedItems = OrderItem::whereHas('order', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('payment_status', 'paid');
        })->with('product', 'order')->get();

        $downloadableItems = $purchasedItems->filter(fn($item) => $item->isDownloadable());
        $expiredItems = $purchasedItems->filter(fn($item) => !$item->isDownloadable());

        // Wishlist items
        $wishlistItems = Wishlist::where('user_id', $user->id)->with('product')->latest()->take(5)->get();

        // Unread notifications
        $unreadNotifications = Notification::forUser($user->id)->unread()->active()->latest()->take(5)->get();

        // Referral stats
        $referralCount = \App\Models\Referral::where('referrer_id', $user->id)->count();
        $coins = $user->coins;
        $pendingCommission = $user->pending_commission;

        return view('user.dashboard', compact(
            'user', 'totalOrders', 'totalSpent', 'totalDownloads', 'wishlistCount',
            'recentOrders', 'downloadableItems', 'expiredItems', 'wishlistItems',
            'unreadNotifications', 'referralCount', 'coins', 'pendingCommission'
        ));
    }
}
