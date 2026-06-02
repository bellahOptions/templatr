<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Category;
use App\Models\Review;
use App\Models\Referral;
use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{
    public function dashboard()
    {
        // ── Core Stats ──
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalUsers = User::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalAuthors = User::whereIn('role', ['author', 'admin'])->count();

        // ── Live / Active Users (last 15 minutes) ──
        $liveUsers = Cache::get('online_users', 0);
        $recentActiveUsers = User::where('updated_at', '>=', now()->subMinutes(15))->count();

        // ── Most Viewed Products ──
        $mostViewed = Product::published()->with('category')
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->get();

        // ── Most Purchased Products ──
        $mostPurchased = Product::withCount(['orderItems' => function ($q) {
                $q->whereHas('order', fn($q) => $q->where('payment_status', 'paid'));
            }])
            ->with('category')
            ->orderBy('order_items_count', 'desc')
            ->take(5)
            ->get();

        // ── Revenue Chart Data (last 12 months) ──
        $revenueChart = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthRevenue = Order::where('payment_status', 'paid')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
            $revenueChart[] = [
                'month' => $date->format('M Y'),
                'revenue' => (float) $monthRevenue,
            ];
        }

        // ── User Growth Chart Data (last 12 months) ──
        $userChart = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $userChart[] = [
                'month' => $date->format('M Y'),
                'count' => $count,
            ];
        }

        // ── Orders Chart Data (last 30 days) ──
        $ordersChart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Order::whereDate('created_at', $date->toDateString())->count();
            $ordersChart[] = [
                'day' => $date->format('D'),
                'count' => $count,
            ];
        }

        // ── Payment Stats ──
        $paymentStats = [
            'paystack' => Order::where('payment_method', 'paystack')->where('payment_status', 'paid')->count(),
            'flutterwave' => Order::where('payment_method', 'flutterwave')->where('payment_status', 'paid')->count(),
            'interswitch' => Order::where('payment_method', 'interswitch')->where('payment_status', 'paid')->count(),
            'direct' => Order::where('payment_method', 'direct')->where('payment_status', 'paid')->count(),
        ];

        // ── Paystack Balance (cached, refreshed every 5 min) ──
        $paystackBalance = Cache::remember('paystack_balance', 300, function () {
            return $this->fetchPaystackBalance();
        });

        // ── Recent Activity ──
        $recentOrders = Order::with('user')->latest()->take(7)->get();
        $recentProducts = Product::with('category', 'author')->latest()->take(5)->get();
        $topProducts = Product::with('category')->orderBy('download_count', 'desc')->take(5)->get();
        $topViewed = Product::with('category')->orderBy('view_count', 'desc')->take(5)->get();
        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');

        // ── Category Stats ──
        $categoryStats = Category::withCount('products')->orderBy('products_count', 'desc')->get();

        // ── Pending Reviews ──
        $pendingReviews = Review::where('is_approved', false)->count();

        return view('admin.dashboard', compact(
            'totalProducts', 'totalOrders', 'totalUsers', 'totalRevenue',
            'totalAuthors', 'liveUsers', 'recentActiveUsers',
            'mostViewed', 'mostPurchased',
            'revenueChart', 'userChart', 'ordersChart',
            'paymentStats', 'paystackBalance',
            'recentOrders', 'recentProducts', 'topProducts', 'topViewed', 'monthlyRevenue',
            'categoryStats', 'pendingReviews'
        ));
    }

    /**
     * Fetch Paystack balance via API.
     */
    protected function fetchPaystackBalance(): array
    {
        $secretKey = env('PAYSTACK_SECRET_KEY');

        if (!$secretKey) {
            return [
                'available' => 0,
                'ledger' => 0,
                'currency' => 'NGN',
                'error' => 'Paystack not configured',
            ];
        }

        try {
            $response = Http::withToken($secretKey)
                ->get('https://api.paystack.co/balance');

            if ($response->successful() && ($response['status'] ?? false)) {
                $data = $response['data'][0] ?? [];
                return [
                    'available' => ($data['balance'] ?? 0) / 100,
                    'ledger' => ($data['ledger_balance'] ?? 0) / 100,
                    'currency' => $data['currency'] ?? 'NGN',
                    'pending' => ($data['pending_balance'] ?? 0) / 100,
                ];
            }

            return [
                'available' => 0,
                'ledger' => 0,
                'currency' => 'NGN',
                'error' => 'Could not fetch balance',
            ];
        } catch (\Exception $e) {
            return [
                'available' => 0,
                'ledger' => 0,
                'currency' => 'NGN',
                'error' => $e->getMessage(),
            ];
        }
    }
}
