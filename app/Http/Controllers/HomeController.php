<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::published()
            ->featured()
            ->withStats()
            ->with(['category', 'author'])
            ->latest()
            ->take(8)
            ->get();

        $newProducts = Product::published()
            ->withStats()
            ->with(['category', 'author'])
            ->latest()
            ->take(12)
            ->get();

        $categories = Category::withCount([
            'products' => fn ($query) => $query->where('is_published', true),
        ])->orderBy('order')->get();

        $topAuthors = User::authors()
            ->has('products')
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(6)
            ->get();

        $stats = Cache::remember('home.stats', now()->addHours(6), fn (): array => [
            'products' => Product::published()->count(),
            'creators' => User::authors()->count(),
            'downloads' => (int) Product::sum('download_count'),
        ]);

        return view('home', compact(
            'featuredProducts',
            'newProducts',
            'categories',
            'topAuthors',
            'stats'
        ));
    }
}
