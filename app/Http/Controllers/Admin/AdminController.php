<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use App\Models\Review;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalUsers = User::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalAuthors = User::whereIn('role', ['author', 'admin'])->count();

        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $recentProducts = Product::with('category', 'author')->latest()->take(5)->get();
        $topProducts = Product::with('category')->orderBy('download_count', 'desc')->take(5)->get();
        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');

        return view('admin.dashboard', compact(
            'totalProducts', 'totalOrders', 'totalUsers', 'totalRevenue',
            'totalAuthors', 'recentOrders', 'recentProducts', 'topProducts', 'monthlyRevenue'
        ));
    }
}
